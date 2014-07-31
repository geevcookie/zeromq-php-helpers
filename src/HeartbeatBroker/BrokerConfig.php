<?php

namespace GeevCookie\ZMQ\HeartbeatBroker;

/**
 * Class BrokerConfig
 * @package GeevCookie\ZMQ\HeartbeatBroker
 */
class BrokerConfig
{
    /**
     * @var int
     */
    private $maxWorkers;

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
    private $clientPort;

    /**
     * @var int
     */
    private $workerPort;

    /**
     * @param int $clientPort
     * @param int $workerPort
     * @param int $maxWorkers
     * @param int $liveness
     * @param int $interval
     */
    public function __construct($clientPort, $workerPort, $maxWorkers = 100, $liveness = 3, $interval = 10)
    {
        $this->clientPort = $clientPort;
        $this->workerPort = $workerPort;
        $this->maxWorkers = $maxWorkers;
        $this->liveness   = $liveness;
        $this->interval   = $interval;
    }

    /**
     * @return int
     */
    public function getClientPort()
    {
        return $this->clientPort;
    }

    /**
     * @param int $clientPort
     */
    public function setClientPort($clientPort)
    {
        $this->clientPort = $clientPort;
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
    public function getMaxWorkers()
    {
        return $this->maxWorkers;
    }

    /**
     * @param int $maxWorkers
     */
    public function setMaxWorkers($maxWorkers)
    {
        $this->maxWorkers = $maxWorkers;
    }

    /**
     * @return int
     */
    public function getWorkerPort()
    {
        return $this->workerPort;
    }

    /**
     * @param int $workerPort
     */
    public function setWorkerPort($workerPort)
    {
        $this->workerPort = $workerPort;
    }
}
