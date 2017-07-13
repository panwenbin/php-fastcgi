<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class Stdout extends BaseRecord
{
    public static function newInstance($requestId, $contentData)
    {
        $header = new Header();
        $header->setType(Protocal::TYPE_STDOUT);
        $header->setRequestId($requestId);
        $body = new Body();
        $body->setContentData($contentData);
        $record = new static();
        $record->header = $header;
        $record->body = $body;
        $record->fixContentLength();
        return $record;
    }
}