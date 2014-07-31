<?php

namespace GeevCookie\ZMQ;

use Exception;
use ZMQ;
use ZMQSocket;

/**
 * Class MultipartMessage
 * @package GeevCookie\ZMQ
 */
class MultipartMessage
{
    /**
     * Store the parts of the message
     *
     * @var array
     */
    private $parts = array();

    /**
     * Socket to send and receive via
     *
     * @var ZMQSocket
     */
    private $socket;

    /**
     * Constructor, accepts optional socket for sending/receiving.
     *
     * @param ZMQSocket $socket
     */
    public function __construct($socket = null)
    {
        $this->socket = $socket;
    }

    /**
     * Formats 17-byte UUID as 33-char string starting with '@'
     * Lets us print UUIDs as C strings and use them as addresses
     *
     * @param  string $data
     * @return string
     */
    public function encodeUuid($data)
    {
        return "@" . bin2hex($data);
    }

    /**
     * Format the hex string back into a packed int.
     *
     * @param  string $data
     * @return string
     */
    public function decodeUuid($data)
    {
        return pack("H*", substr($data, 1));
    }

    /**
     * Set the internal socket to use for sending or receiving.
     *
     * @param  ZMQSocket $socket
     * @return $this
     */
    public function setSocket(ZMQSocket $socket)
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     *  Receive message from socket
     *  Creates a new message and returns it
     *  Blocks on recv if socket is not ready for input
     *
     * @throws Exception if no socket present
     * @return $this
     */
    public function recv()
    {
        if (!isset($this->socket)) {
            throw new Exception("No socket supplied");
        }
        $this->parts = array();
        while (true) {
            $this->parts[] = $this->socket->recv();
            if (!$this->socket->getSockOpt(ZMQ::SOCKOPT_RCVMORE)) {
                break;
            }
        }

        return $this;
    }

    /**
     * Send message to socket. Destroys message after sending.
     *
     * @throws Exception if no socket present
     * @param  boolean $clear
     * @return $this
     */
    public function send($clear = true)
    {
        if (!isset($this->socket)) {
            throw new Exception("No socket supplied");
        }
        $count   = count($this->parts);
        $counter = 1;
        foreach ($this->parts as $part) {
            $mode = $counter++ == $count ? null : ZMQ::MODE_SNDMORE;
            $this->socket->send($part, $mode);
        }
        if ($clear) {
            unset($this->parts);
            $this->parts = array();
        }

        return $this;
    }

    /**
     * Report size of message
     *
     * @return int
     */
    public function parts()
    {
        return count($this->parts);
    }

    /**
     * Return the last part of the message
     *
     * @return string
     */
    public function last()
    {
        return $this->parts[count($this->parts) - 1];
    }

    /**
     * Set the last part of the message
     *
     * @param string $set
     */
    public function setLast($set)
    {
        $this->parts[count($this->parts) - 1] = $set;
    }

    /**
     * Return the body
     *
     * @return string
     */
    public function body()
    {
        return $this->parts[count($this->parts) - 1];
    }

    /**
     * Set message body to provided string.
     *
     * @param  string $body
     * @return $this
     */
    public function setBody($body)
    {
        $pos = count($this->parts);
        if ($pos > 0) {
            $pos = $pos - 1;
        }
        $this->parts[$pos] = $body;

        return $this;
    }

    /**
     * Set message body using printf format
     *
     * @return $this
     */
    public function formatBody()
    {
        $args = func_get_args();
        $this->setBody(vsprintf(array_shift($args), $args));

        return $this;
    }

    /**
     * Push message part to front
     *
     * @param  string $part
     * @return void
     */
    public function push($part)
    {
        array_unshift($this->parts, $part);
    }

    /**
     * Pop message part off front of message parts
     *
     * @return string
     * @author Ian Barber
     */
    public function pop()
    {
        return array_shift($this->parts);
    }

    /**
     * Return the address of the message
     *
     * @return null|string
     * @author Ian Barber
     */
    public function address()
    {
        $address = count($this->parts) ? $this->parts[0] : null;

        return (strlen($address) == 17 && $address[0] == 0) ? $this->encodeUuid($address) : $address;
    }

    /**
     * Wraps message in new address envelope.
     * If delim is not null, creates two part envelope.
     *
     * @param  string $address
     * @param  string $delim
     * @return $this
     */
    public function wrap($address, $delim = null)
    {
        if ($delim !== null) {
            $this->push($delim);
        }
        if ($address[0] == '@' && strlen($address) == 33) {
            $address = $this->decodeUuid($address);
        }
        $this->push($address);

        return $this;
    }

    /**
     * Unwraps outer message envelope and returns address
     * Discards empty message part after address, if any
     *
     * @return string
     * @author Ian Barber
     */
    public function unwrap()
    {
        $address = $this->pop();
        if (!$this->address()) {
            $this->pop();
        }

        return $address;
    }

    /**
     * Dump the contents to a string, for debugging and tracing.
     *
     * @return string
     */
    public function __toString()
    {
        $string = "--------------------------------------" . PHP_EOL;
        foreach ($this->parts as $part) {
            $len = strlen($part);
            if ($len == 17 && $part[0] == 0) {
                $part = $this->encodeUuid($part);
                $len  = strlen($part);
            }
            $string .= sprintf("[%03d] %s %s", $len, $part, PHP_EOL);
        }

        return $string;
    }
}
