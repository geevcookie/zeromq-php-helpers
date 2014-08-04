<?php

use Monolog\Logger;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestWorker.php';

$logger = new Logger('worker');
$logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());

$config = new \GeevCookie\ZMQ\HeartbeatWorker\WorkerConfig('localhost', 5556);
$worker = new \GeevCookie\ZMQ\HeartbeatWorker\HeartbeatWorker($config, $logger);

if ($worker->connect(new ZMQContext())) {
    $worker->execute(new TestWorker());
} else {
    $logger->error("Worker could not connect!");
}
