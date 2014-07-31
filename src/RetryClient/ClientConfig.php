<?php

namespace GeevCookie\ZMQ\RetryClient;

/**
 * Class ClientConfig
 * @package GeevCookie\ZMQ\RetryClient
 */
class ClientConfig
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $retries;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @param string $host
     * @param int $port
     * @param int $retries
     * @param int $timeout
     */
    public function __construct($host, $port, $retries = 3, $timeout = 2500)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->retries = $retries;
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     * @param int $retries
     */
    public function setRetries($retries)
    {
        $this->retries = $retries;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
