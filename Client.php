<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi;

use panwenbin\fastcgi\record\BaseRecord;
use panwenbin\fastcgi\record\BeginRequest;
use panwenbin\fastcgi\record\EndRequest;
use panwenbin\fastcgi\record\Params;
use panwenbin\fastcgi\record\Stdin;
use panwenbin\fastcgi\record\Stdout;

/**
 * FastCGI Client implement in PHP
 *
 * @package panwenbin\fastcgi
 */
class Client
{
    // to generate different request id
    protected static $lastRequestId = 0;

    // hold requestId for one request
    protected $requestId;

    // stream fp instance
    protected $fp;

    // stdout callback
    protected $stdoutCallback;

    public function __construct($fcgiServer)
    {
        $this->fp = stream_socket_client($fcgiServer, $errno, $errstr, 3);
        if(false === $this->fp) {
            throw new Exception($errstr, $errno);
        }
    }

    public function __destruct()
    {
        fclose($this->fp);
    }

    /**
     * Set stdout callback
     * @param callable $callback
     */
    public function setStdoutCallback(callable $callback)
    {
        $this->stdoutCallback = $callback;
    }

    /**
     * next requestId (None-Zero)
     * @return int
     */
    public static function nextRequestId()
    {
        static::$lastRequestId = static::$lastRequestId % 65535;
        return ++static::$lastRequestId;
    }

    /**
     * Calculate padding length
     * @param $contentLength
     * @return int
     */
    private function calPaddingLength($contentLength)
    {
        return (8 - ($contentLength % 8)) % 8;
    }

    /**
     * Send a request to fastcgi application
     * @param array $params
     * @param string|\resource $stdin
     * @throws Exception
     */
    public function request(array $params = [], $stdin = null)
    {
        if (is_string($stdin) && strlen($stdin) > 65535) {
            throw new Exception('stdin string is too long(>=64KB), please use a stream.');
        }
        // get a request id
        $this->requestId = self::nextRequestId();

        // begin request
        fwrite($this->fp, BeginRequest::newInstance($this->requestId));

        // send params
        foreach ($params as $name => $value) {
            fwrite($this->fp, Params::newInstance($this->requestId, [$name => $value]));
        }
        // end sending params
        fwrite($this->fp, Params::newInstance($this->requestId, []));

        // send stdin (http request body)
        if ($stdin) {
            if (is_resource($stdin)) {
                while ($input = stream_get_contents($stdin, 65535)) {
                    fwrite($this->fp, Stdin::newInstance($this->requestId, $input));
                }
            } elseif (is_string($stdin)) {
                fwrite($this->fp, Stdin::newInstance($this->requestId, $stdin));
            }
        }
        // end sending stdin
        fwrite($this->fp, Stdin::newInstance($this->requestId, ''));

        // read response
        while (true) {
            $record = BaseRecord::parseFromStream($this->fp);
            if ($record instanceof Stdout) {
                $contentData = $record->getBody()->getContentData();
                if (is_callable($this->stdoutCallback)) {
                    $callback = $this->stdoutCallback;
                    $callback($contentData);
                } else {
                    echo $contentData;
                }
            } elseif ($record instanceof EndRequest) {
                break;
            }
        }
    }
}

