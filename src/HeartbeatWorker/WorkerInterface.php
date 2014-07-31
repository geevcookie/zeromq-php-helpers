<?php

namespace GeevCookie\ZMQ\HeartbeatWorker;

/**
 * Interface WorkerInterface
 * @package GeevCookie\ZMQ\HeartbeatWorker
 */
interface WorkerInterface
{
    /**
     * @param string $body
     * @return bool
     */
    public function run($body);
}
