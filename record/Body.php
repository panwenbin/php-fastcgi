<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class Body
{
    /**
     * @var string
     */
    protected $contentData = '';

    /**
     * @var string
     */
    protected $paddingData = '';

    public function getContentData()
    {
        return $this->contentData;
    }

    public function setContentData($contentData)
    {
        $this->contentData = $contentData;
        $this->setPaddingData();
    }

    private function setPaddingData()
    {
        $paddingLength = Protocal::calPaddingLength(strlen($this->contentData));
        $this->paddingData = str_repeat(pack('C', 0), $paddingLength);
    }

    public static function fromBuffer($buffer, $contentLength, $paddingLength)
    {
        $body = new static();
        list(
            $body->contentData,
            $body->paddingData
            ) = array_values(unpack("a{$contentLength}contentData/a{$paddingLength}paddingData", $buffer));

        return $body;
    }

    public function __toString()
    {
        return $this->contentData . $this->paddingData;
    }
}