<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestWorker.php';

$config = new \GeevCookie\ZMQ\HeartbeatWorker\WorkerConfig('localhost', 5556);
$worker = new \GeevCookie\ZMQ\HeartbeatWorker\HeartbeatWorker($config);

if ($worker->connect(new ZMQContext())) {
    $worker->execute(new TestWorker());
} else {
    echo "Worker could not connect!";
}
