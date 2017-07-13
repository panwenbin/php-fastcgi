<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class BeginRequest extends BaseRecord
{
    public static function newInstance($requestId, $flags = 0)
    {
        $header = new Header();
        $header->setType(Protocal::TYPE_BEGIN_REQUEST);
        $header->setRequestId($requestId);
        $body = new Body();
        $body->setContentData(Protocal::packBeginRequestBody($flags));
        $record = new static();
        $record->header = $header;
        $record->body = $body;
        $record->fixContentLength();
        return $record;
    }
}