<?php

use Monolog\Logger;

require_once __DIR__ . '/../../vendor/autoload.php';

$logger = new Logger('broker');
$logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());

$config = new \GeevCookie\ZMQ\HeartbeatBroker\BrokerConfig(5555, 5556);
$broker = new \GeevCookie\ZMQ\HeartbeatBroker\HeartbeatBroker(
    $config,
    new \GeevCookie\ZMQ\QueueIterator($logger),
    $logger
);

if ($broker->connect(new ZMQContext())) {
    $logger->info("Broker started! Listening...");
    $broker->execute();
} else {
    $logger->error("Broker could not connect!");
}
