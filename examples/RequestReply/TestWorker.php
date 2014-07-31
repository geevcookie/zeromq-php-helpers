<?php

use GeevCookie\ZMQ\HeartbeatWorker\WorkerInterface;

/**
 * Class TestWorker
 */
class TestWorker implements WorkerInterface
{
    /**
     * @param string $body
     * @return bool
     */
    public function run($body)
    {
        return "$body World";
    }
}
