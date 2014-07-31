<?php

namespace GeevCookie\ZMQ\HeartbeatWorker;

use GeevCookie\ZMQ\MultipartMessage;
use ZMQ;
use ZMQContext;
use ZMQPoll;
use ZMQSocket;

/**
 * Class HeartbeatWorker
 * @package GeevCookie\ZMQ\HeartbeatWorker
 */
class HeartbeatWorker
{
    /**
     * @var WorkerConfig
     */
    private $config;

    /**
     * @var ZMQSocket
     */
    private $workerProcess;

    /**
     * @var string
     */
    private $identity;

    /**
     * @var ZMQContext
     */
    private $context;

    /**
     * @param WorkerConfig $config
     */
    public function __construct(WorkerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param \ZMQContext $context
     * @return bool
     */
    public function connect(\ZMQContext $context)
    {
        $this->context = $context;

        try {
            $this->workerProcess = new ZMQSocket($context, ZMQ::SOCKET_DEALER);

            //  Set random identity to make tracing easier
            $this->identity = sprintf("%04X-%04X", rand(0, 0x10000), rand(0, 0x10000));
            $this->workerProcess->setSockOpt(ZMQ::SOCKOPT_IDENTITY, $this->identity);
            $this->workerProcess->connect("tcp://{$this->config->getHost()}:{$this->config->getPort()}");

            //  Configure socket to not wait at close time
            $this->workerProcess->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);

            //  Tell queue we're ready for work
            printf("I: (%s) worker ready%s", $this->identity, PHP_EOL);
            $this->workerProcess->send("READY");

            return true;
        } catch (\ZMQSocketException $e) {
            return false;
        } catch (\ZMQException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param WorkerInterface $worker
     * @throws \Exception
     */
    public function execute(WorkerInterface $worker)
    {
        // Ensure that the socket connections have been made.
        if (!$this->workerProcess) {
            throw new \Exception("Please run 'connect' before executing the worker!");
        }

        // Some initialization
        $read        = array();
        $write       = array();
        $heartbeatAt = microtime(true) + $this->config->getInterval();
        $poll        = new ZMQPoll();
        $poll->add($this->workerProcess, ZMQ::POLL_IN);
        $liveness    = $this->config->getLiveness();
        $interval    = $this->config->getInitInterval();

        // Main loop
        while (true) {
            $events = $poll->poll($read, $write, $this->config->getInterval() * 1000);

            if ($events) {
                //  Get message
                //  - 3-part envelope + content -> request
                //  - 1-part "HEARTBEAT" -> heartbeat
                $message = new MultipartMessage($this->workerProcess);
                $message->recv();

                if ($message->parts() == 3) {
                    printf("I: (%s) Responding to message - %s%s", $this->identity, $message->body(), PHP_EOL);
                    $response = $worker->run($message->body());
                    $message->setBody($response);
                    $message->send();
                    $liveness = $this->config->getLiveness();
                } elseif ($message->parts() == 1 && $message->body() == 'HEARTBEAT') {
                    printf('I: Got heartbeat from server!%s', PHP_EOL);
                    $liveness = $this->config->getLiveness();
                } else {
                    printf("E: (%s) invalid message%s%s", $this->identity, PHP_EOL, $message->__toString());
                }
                $interval = $this->config->getInitInterval();
            } elseif (--$liveness == 0) {
                printf("W: (%s) heartbeat failure, can't reach queue%s", $this->identity, PHP_EOL);
                printf("W: (%s) reconnecting in %d msec...%s", $this->identity, $this->config->getInterval(), PHP_EOL);
                usleep($this->config->getInterval() * 1000 * 1000);

                if ($interval < $this->config->getMaxInterval()) {
                    $interval *= 2;
                }

                $this->connect($this->context);
                $poll->clear();
                $poll->add($this->workerProcess, ZMQ::POLL_IN);
                $liveness = $this->config->getLiveness();
            }

            // Send a heartbeat
            $heartbeatAt = $this->sendHeartbeat($heartbeatAt);
        }
    }

    /**
     * Send a heartbeat to the server.
     *
     * @param float $heartbeatAt
     * @return float
     */
    public function sendHeartbeat($heartbeatAt)
    {
        //  Send heartbeat to queue if it's time
        if (microtime(true) > $heartbeatAt) {
            $heartbeatAt = microtime(true) + $this->config->getInterval();
            printf("I: (%s) worker heartbeat - %s%s", $this->identity, microtime(), PHP_EOL);
            $this->workerProcess->send("HEARTBEAT");
        }

        return $heartbeatAt;
    }
}
