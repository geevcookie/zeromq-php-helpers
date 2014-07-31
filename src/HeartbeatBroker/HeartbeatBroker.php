<?php

namespace GeevCookie\ZMQ\HeartbeatBroker;

use GeevCookie\ZMQ\MultipartMessage;
use GeevCookie\ZMQ\QueueIterator;
use ZMQ;
use ZMQPoll;

/**
 * Helper class to create a nice little paranoid broker.
 *
 * Class HeartbeatBroker
 * @package GeevCookie\ZMQ\HeartbeatBroker
 */
class HeartbeatBroker
{
    /**
     * @var BrokerConfig
     */
    private $config;

    /**
     * @var \GeevCookie\ZMQ\QueueIterator
     */
    private $queue;

    /**
     * @var \ZMQSocket|null
     */
    private $clientChannel = null;

    /**
     * @var \ZMQSocket|null
     */
    private $workerChannel = null;

    /**
     * @param BrokerConfig $config
     * @param QueueIterator $queue
     */
    public function __construct(BrokerConfig $config, QueueIterator $queue)
    {
        $this->config = $config;
        $this->queue  = $queue;
    }

    /**
     * @param \ZMQContext $context
     * @return bool
     */
    public function connect(\ZMQContext $context)
    {
        $this->clientChannel = new \ZMQSocket($context, ZMQ::SOCKET_ROUTER);
        $this->workerChannel = new \ZMQSocket($context, ZMQ::SOCKET_ROUTER);

        try {
            $this->clientChannel->bind("tcp://*:{$this->config->getClientPort()}");
            $this->workerChannel->bind("tcp://*:{$this->config->getWorkerPort()}");

            return true;
        } catch (\ZMQSocketException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Executes the broker
     *
     * @throws \Exception
     */
    public function execute()
    {
        // Ensure that the socket connections have been made.
        if (!$this->clientChannel || !$this->workerChannel) {
            throw new \Exception("Please run 'connect' before executing the broker!");
        }

        // Some initialization
        $read        = array();
        $write       = array();
        $heartbeatAt = microtime(true) + $this->config->getInterval();

        // Main loop
        while (true) {
            $poll     = new ZMQPoll();
            $poll->add($this->workerChannel, ZMQ::POLL_IN);

            // Poll the client channel only if we have available workers
            if ($this->queue->size()) {
                $poll->add($this->clientChannel, ZMQ::POLL_IN);
            }

            // Get all the events and if any events occurred process them.
            $events = $poll->poll($read, $write, $this->config->getInterval() * 1000);
            if ($events > 0) {
                foreach ($read as $socket) {
                    $message = new MultipartMessage($socket);
                    $message->recv();

                    //  Handle worker activity on backend
                    if ($socket === $this->workerChannel) {
                        $identity = $message->unwrap();

                        $this->handleMessage($message, $identity);
                    } else {
                        //  Now get next client request, route to next worker
                        $identity = $this->queue->getWorker();
                        $message->wrap($identity);
                        $message->setSocket($this->workerChannel)->send();
                    }
                }

                $heartbeatAt = $this->validateQueue($heartbeatAt);
            }
        }
    }

    /**
     * Handles the message
     *
     * @param MultipartMessage $message
     * @param string $identity
     */
    public function handleMessage(MultipartMessage $message, $identity)
    {
        //  Return reply to client if it's not a control message
        if ($message->parts() == 1) {
            if ($message->address() == "READY") {
                $this->queue->deleteWorker($identity);
                $this->queue->appendWorker($identity, $this->config->getInterval(), $this->config->getLiveness());
                printf("I: Worker connected - %s%s", $identity, PHP_EOL);
            } elseif ($message->address() == 'HEARTBEAT') {
                printf("I: Got heartbeat from %s - %s%s", $identity, microtime(), PHP_EOL);
                $this->queue->refreshWorker($identity, $this->config->getInterval(), $this->config->getLiveness());
            } else {
                printf("E: invalid message from %s%s%s", $identity, PHP_EOL, $message->__toString());
            }
        } else {
            $message->setSocket($this->clientChannel)->send();
            $this->queue->appendWorker($identity, $this->config->getInterval(), $this->config->getLiveness());
        }
    }

    /**
     * Checks if workers are still valid.
     *
     * @param string $heartbeatAt
     * @return mixed|string
     */
    public function validateQueue($heartbeatAt)
    {
        if (microtime(true) > $heartbeatAt) {
            $this->queue->rewind();

            while ($this->queue->valid()) {
                $message = new MultipartMessage($this->workerChannel);
                $message->setBody("HEARTBEAT");
                $message->wrap($this->queue->key(), null);
                $message->send();

                $this->queue->next();
            }
            $heartbeatAt = microtime(true) + $this->config->getInterval();
        }
        $this->queue->purgeQueue();

        return $heartbeatAt;
    }
}
