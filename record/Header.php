<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class Header
{
    protected $version = Protocal::VERSION_1;
    protected $type = Protocal::TYPE_UNKNOWN_TYPE;
    protected $requestId = Protocal::REQUEST_ID_NULL;
    protected $contentLength = 0;
    protected $paddingLength = 0;
    protected $reserved = 0;

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getRequestId()
    {
        return $this->requestId;
    }

    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    public function getContentLength()
    {
        return $this->contentLength;
    }

    public function setContentLength($contentLength)
    {
        $this->contentLength = $contentLength;
        $this->setPaddingLength();
    }

    public function getPaddingLength()
    {
        return $this->paddingLength;
    }

    private function setPaddingLength()
    {
        $this->paddingLength = Protocal::calPaddingLength($this->contentLength);
    }

    public static function fromBuffer($buffer)
    {
        $header = new static();
        list(
            $header->version,
            $header->type,
            $header->requestId,
            $header->contentLength,
            $header->paddingLength,
            $header->reserved
            ) = array_values(unpack(Protocal::UNPACK_HEADER, $buffer));
        return $header;
    }

    public function __toString()
    {
        return pack(
            Protocal::PACK_HEADER,
            $this->version,
            $this->type,
            $this->requestId,
            $this->contentLength,
            $this->paddingLength,
            $this->reserved
        );
    }
}