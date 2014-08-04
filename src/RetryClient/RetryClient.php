<?php

namespace GeevCookie\ZMQ\RetryClient;

use Psr\Log\LoggerInterface;
use ZMQ;
use ZMQContext;
use ZMQPoll;
use ZMQSocket;

/**
 * Class RetryClient
 * @package GeevCookie\ZMQ\RetryClient
 */
class RetryClient
{
    /**
     * @var ClientConfig
     */
    private $config;

    /**
     * @var ZMQContext
     */
    private $context;

    /**
     * @var ZMQSocket
     */
    private $clientProcess;

    /**
     * @param ClientConfig $config
     */
    public function __construct(ClientConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param ZMQContext $context
     * @return bool
     */
    public function connect(ZMQContext $context)
    {
        $this->context = $context;

        try {
            $this->clientProcess = new ZMQSocket($context, ZMQ::SOCKET_REQ);
            $this->clientProcess->connect("tcp://{$this->config->getHost()}:{$this->config->getPort()}");

            //  Configure socket to not wait at close time
            $this->clientProcess->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);

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
     * @param string $body
     * @return string
     * @throws \Exception
     */
    public function send($body)
    {
        $retriesLeft = $this->config->getRetries();
        $read        = array();
        $write       = array();

        while ($retriesLeft) {
            //  We send a request, then we work to get a reply
            $this->clientProcess->send($body);

            $expectReply = true;
            while ($expectReply) {
                //  Poll socket for a reply, with timeout
                $poll = new ZMQPoll();
                $poll->add($this->clientProcess, ZMQ::POLL_IN);
                $events = $poll->poll($read, $write, $this->config->getTimeout());

                //  If we got a reply, process it
                if ($events > 0) {
                    //  We got a reply from the server, must match sequence
                    $reply = $this->clientProcess->recv();
                    if ($reply != '') {
                        return $reply;
                    } else {
                        throw new \Exception("Invalid message received from server!");
                    }
                } elseif (--$retriesLeft == 0) {
                    throw new \Exception("Server seems to be offline. Abandoning!");
                } else {
                    //  Old socket will be confused; close it and open a new one
                    $this->connect($this->context);
                    //  Send request again, on new socket
                    $this->clientProcess->send($body);
                }
            }
        }

        return false;
    }
}
