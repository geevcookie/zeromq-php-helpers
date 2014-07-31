# ZeroMQ PHP Helpers

A couple of helper classes to make it easier to work with ZeroMQ.

## Notice

This is still in the very early stages of development. Use at own risk.

## Installation

Add the following to your composer.json:

```json
{
    "require-dev": {
        "geevcookie/zeromq-php-helpers": "dev-master"
    }
}
```

Then run `composer install --dev` or `composer update --dev`.

## Usage

Check the examples folder. Each of the files has to be run from the command line e.g:

```shell
php RequestReply/Broker.php
```

## Examples

Below you will find quick breakdowns of the available examples:

### RequestReply

This example shows how you can have one broker managing the messages between multiple clients and multiple workers.
The worker and the broker constantly send "heartbeat" messages between each other to ensure that the connection is still alive.
When the client sends a message the broker receives it, finds the next worker, sends the message to the worker, receives the reply from the worker, and then sends it back to the original client.
The client itself also has retry and timeout limits.

To see the example in action run the following commands:

```shell
// First start the broker.
php examples/Broker.php

// Then start a worker or 2 or 3.
php examples/Worker.php

// Then run a client to see how the broker and the workers react.
php examples/Client.php
```