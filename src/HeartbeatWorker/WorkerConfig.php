<?php

namespace GeevCookie\ZMQ\HeartbeatWorker;

/**
 * Class WorkerConfig
 * @package GeevCookie\ZMQ\HeartbeatBroker
 */
class WorkerConfig
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
    private $liveness;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var int
     */
    private $initInterval;

    /**
     * @var int
     */
    private $maxInterval;

    /**
     * @param string $host
     * @param int $port
     * @param int $liveness
     * @param int $interval
     * @param int $initInterval
     * @param int $maxInterval
     */
    public function __construct($host, $port, $liveness = 3, $interval = 10, $initInterval = 1000, $maxInterval = 3000)
    {
        $this->host         = $host;
        $this->port         = $port;
        $this->liveness     = $liveness;
        $this->interval     = $interval;
        $this->initInterval = $initInterval;
        $this->maxInterval  = $maxInterval;
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
    public function getInitInterval()
    {
        return $this->initInterval;
    }

    /**
     * @param int $initInterval
     */
    public function setInitInterval($initInterval)
    {
        $this->initInterval = $initInterval;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return int
     */
    public function getLiveness()
    {
        return $this->liveness;
    }

    /**
     * @param int $liveness
     */
    public function setLiveness($liveness)
    {
        $this->liveness = $liveness;
    }

    /**
     * @return int
     */
    public function getMaxInterval()
    {
        return $this->maxInterval;
    }

    /**
     * @param int $maxInterval
     */
    public function setMaxInterval($maxInterval)
    {
        $this->maxInterval = $maxInterval;
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
}
