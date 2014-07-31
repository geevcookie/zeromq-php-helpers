<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = new \GeevCookie\ZMQ\RetryClient\ClientConfig('localhost', 5555);
$client = new \GeevCookie\ZMQ\RetryClient\RetryClient($config);

if ($client->connect(new ZMQContext())) {
    echo "Client connected! Sending message..." . PHP_EOL;

    try {
        if ($response = $client->send("Hello")) {
            echo $response . PHP_EOL;
        }
    } catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }
} else {
    echo "Client could not connect!";
}
