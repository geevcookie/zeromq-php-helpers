<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = new \GeevCookie\ZMQ\HeartbeatBroker\BrokerConfig(5555, 5556);
$broker = new \GeevCookie\ZMQ\HeartbeatBroker\HeartbeatBroker($config, new \GeevCookie\ZMQ\QueueIterator());

if ($broker->connect(new ZMQContext())) {
    echo "Broker connected! Listening..." . PHP_EOL;

    $broker->execute();
} else {
    echo "Broker could not connect!";
}
