<?php

namespace GeevCookie\ZMQ;

use Iterator;

/**
 * Class QueueIterator
 * @package GeevCookie\ZMQ
 */
class QueueIterator implements Iterator
{
    /**
     * @var array
     */
    private $queue = array();

    /**
     * Rewind the Iterator to the first element.
     *
     * @return mixed|void
     */
    public function rewind()
    {
        return reset($this->queue);
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool|mixed
     */
    public function valid()
    {
        return current($this->queue);
    }

    /**
     * Return the key of the current element.
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->queue);
    }

    /**
     * Move forward to next element.
     *
     * @return mixed|void
     */
    public function next()
    {
        return next($this->queue);
    }

    /**
     * Return the current element.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->queue);
    }

    /**
     * Insert worker at end of queue, reset expiry
     * Worker must not already be in queue
     *
     * @param string $identity
     * @param int $interval
     * @param int $liveness
     */
    public function appendWorker($identity, $interval, $liveness)
    {
        if (isset($this->queue[$identity])) {
            printf("E: duplicate worker identity %s", $identity);
        } else {
            $this->queue[$identity] = microtime(true) + $interval * $liveness;
        }
    }

    /**
     * Remove worker from queue, if present
     *
     * @param string $identity
     */
    public function deleteWorker($identity)
    {
        unset($this->queue[$identity]);
    }

    /**
     * Reset worker expiry, worker must be present
     *
     * @param string $identity
     * @param int $interval
     * @param int $liveness
     */
    public function refreshWorker($identity, $interval, $liveness)
    {
        if (!isset($this->queue[$identity])) {
            printf("E: worker %s not ready\n", $identity);
        } else {
            $this->queue[$identity] = microtime(true) + $interval * $liveness;
        }
    }

    /**
     * Pop next available worker off queue, return identity
     *
     * @return mixed
     */
    public function getWorker()
    {
        reset($this->queue);
        $identity = key($this->queue);
        unset($this->queue[$identity]);

        return $identity;
    }

    /**
     * Look for & kill expired workers
     */
    public function purgeQueue()
    {
        foreach ($this->queue as $id => $expiry) {
            if (microtime(true) > $expiry) {
                unset($this->queue[$id]);
            }
        }
    }

    /**
     * Returns the size of the queue
     *
     * @return int
     */
    public function size()
    {
        return count($this->queue);
    }
}
