<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi;

/**
 * FastCGI Client implement in PHP
 *
 * @package panwenbin\fastcgi
 */
class Client
{
    // to generate different request id
    protected static $lastRequestId = 0;

    // fixed header values in a request
    protected $version;
    protected $requestId;

    // fastcgi appliaction host
    protected $fcgiHost;
    // fastcgi appliaction port
    protected $fcgiPort;

    // socket instance
    protected $socket;

    // log file
    protected $logFile;

    // stdout callback
    protected $stdoutCallback;

    public function __construct($fcgiHost, $fcgiPort)
    {
        $this->version = Protocal::VERSION_1;
        $this->fcgiHost = $fcgiHost;
        $this->fcgiPort = $fcgiPort;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $ok = socket_connect($this->socket, $this->fcgiHost, $this->fcgiPort);
        if (false === $ok) {
            $errno = socket_last_error();
            throw new Exception(socket_strerror($errno), $errno);
        }
    }

    public function __destruct()
    {
        socket_close($this->socket);
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
     * Set logFile
     * @param $logFile
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
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
     * build NameValuePair
     * @param $name
     * @param $value
     * @return string
     */
    private function packNameValuePair($name, $value)
    {
        $nLen = strlen($name);
        $vLen = strlen($value);
        if ($nLen > 0x7f) {
            $nLen = $nLen | (1 << 31);
            if ($vLen > 0x7f) {
                $vLen = $vLen | (1 << 31);
                $format = Protocal::PACK_NAME_VALUE_PAIR44;
            } else {
                $format = Protocal::PACK_NAME_VALUE_PAIR41;
            }
        } else {
            if ($vLen > 0x7f) {
                $vLen = $vLen | (1 << 31);
                $format = Protocal::PACK_NAME_VALUE_PAIR14;
            } else {
                $format = Protocal::PACK_NAME_VALUE_PAIR11;
            }
        }
        $nvPair = pack($format, $nLen, $vLen) . $name . $value;
        return $nvPair;
    }

    /**
     * Build a request record (request header + request body)
     * @param $type
     * @param $content
     * @return string
     */
    public function packRecord($type, $content)
    {
        $contentLength = strlen($content);
        $paddingLength = $this->calPaddingLength($contentLength);

        $record = Protocal::packRequestHeader(
            $this->version,
            $type,
            $this->requestId,
            $contentLength,
            $paddingLength
        );
        $record .= $content;
        for ($i = 0; $i < $paddingLength; $i++) {
            $record .= pack('C', 0);
        }
        return $record;
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
        $record = $this->packRecord(Protocal::TYPE_BEGIN_REQUEST, Protocal::packBeginRequestBody());
        socket_write($this->socket, $record);

        // send params
        foreach ($params as $name => $value) {
            $paramRequest = $this->packNameValuePair($name, $value);
            $record = $this->packRecord(Protocal::TYPE_PARAMS, $paramRequest);
            socket_write($this->socket, $record);
        }
        // end sending params
        $record = $this->packRecord(Protocal::TYPE_PARAMS, '');
        socket_write($this->socket, $record);

        // send stdin (http request body)
        if ($stdin) {
            if (is_resource($stdin)) {
                while ($input = stream_get_contents($stdin, 65535)) {
                    $record = $this->packRecord(Protocal::TYPE_STDIN, $input);
                    socket_write($this->socket, $record);
                }
            } elseif (is_string($stdin)) {
                $record = $this->packRecord(Protocal::TYPE_STDIN, $stdin);
                socket_write($this->socket, $record);
            }
        }
        // end sending stdin
        $record = $this->packRecord(Protocal::TYPE_STDIN, '');
        socket_write($this->socket, $record);

        // read response
        while (true) {
            // read header
            $header = socket_read($this->socket, 8);
            if (empty($header) || strlen($header) < 8) break;
            $header = unpack(Protocal::UNPACK_HEADER, $header);
            // log header
            if ($this->logFile) {
                file_put_contents($this->logFile, var_export($header, true) . "\r\n", FILE_APPEND);
            }

            // read body
            $body = '';
            while (($bodyLen = strlen($body)) < $header['contentLength']) {
                $thunk = socket_read($this->socket, $header['contentLength'] - $bodyLen);
                if (empty($thunk)) break;
                if ($thunk) $body .= $thunk;
            }
            // drop padding
            if ($header['paddingLength']) {
                socket_read($this->socket, $header['paddingLength']);
            }
            if ($header['type'] == Protocal::TYPE_STDOUT) {
                if (is_callable($this->stdoutCallback)) {
                    $callback = $this->stdoutCallback;
                    $callback($body);
                } else {
                    echo $body;
                }
            } elseif ($header['type'] == Protocal::TYPE_END_REQUEST) {
                break;
            }
        }
    }
}

