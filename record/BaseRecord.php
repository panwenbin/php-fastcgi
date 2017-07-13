<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Exception;
use panwenbin\fastcgi\Protocal;

class BaseRecord
{
    /**
     * @var Header
     */
    protected $header;

    /**
     * @var Body
     */
    protected $body;

    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader(Header $header)
    {
        $this->header = $header;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody(Body $body)
    {
        $this->body = $body;
    }

    public function fixContentLength()
    {
        if ($this->header && $this->body) {
            $this->header->setContentLength(strlen($this->body->getContentData()));
        }
    }

    /**
     * @var array
     */
    protected static $recordTypeMapping = [
        Protocal::TYPE_BEGIN_REQUEST => BeginRequest::class,
        Protocal::TYPE_ABORT_REQUEST => AbortRequest::class,
        Protocal::TYPE_END_REQUEST => EndRequest::class,
        Protocal::TYPE_PARAMS => Params::class,
        Protocal::TYPE_STDIN => Stdin::class,
        Protocal::TYPE_STDOUT => Stdout::class,
        Protocal::TYPE_STDERR => Stderr::class,
        Protocal::TYPE_DATA => Data::class,
        Protocal::TYPE_GET_VALUES => GetValues::class,
        Protocal::TYPE_GET_VALUES_RESULT => GetValuesResult::class,
        Protocal::TYPE_UNKNOWN_TYPE => UnknownType::class,
    ];

    public static function typeClass($type)
    {
        return isset(static::$recordTypeMapping[$type]) ? static::$recordTypeMapping[$type] : null;
    }

    /**
     * @param $buffer
     * @return BaseRecord
     */
    public static function parseFromString($buffer)
    {
        $header = Header::fromBuffer($buffer);
        $data = substr($buffer, Protocal::HEADER_LEN);
        $body = Body::fromBuffer($data, $header->getContentLength(), $header->getPaddingLength());

        $className = static::typeClass($header->getType());
        /* @var \panwenbin\fastcgi\record\BaseRecord $record */
        $record = new $className;
        $record->setHeader($header);
        $record->setBody($body);
        return $record;
    }

    public static function parseFromStream($fp)
    {
        $buffer = fread($fp, 8);
        if (strlen($buffer) < Protocal::HEADER_LEN) {
            throw new Exception("Not enough data in the buffer to parse");
        }
        $header = Header::fromBuffer($buffer);
        $contentLength = $header->getContentLength();
        $paddingLength = $header->getPaddingLength();
        $dataLength = $contentLength + $paddingLength;
        $buffer = fread($fp, $dataLength);
        while (($bufferLen = strlen($buffer)) < $dataLength) {
            $thunk = fread($fp, $dataLength - $bufferLen);
            if (empty($thunk)) break;
            if ($thunk) $buffer .= $thunk;
        }
        $body = Body::fromBuffer($buffer, $contentLength, $paddingLength);

        $className = static::typeClass($header->getType());
        /* @var \panwenbin\fastcgi\record\BaseRecord $record */
        $record = new $className;
        $record->setHeader($header);
        $record->setBody($body);
        return $record;
    }


    public function __toString()
    {
        return $this->header . $this->body;
    }
}