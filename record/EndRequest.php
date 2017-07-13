<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class EndRequest extends BaseRecord
{
    public static function newInstance($requestId, $appStatus, $protocolStatus = Protocal::STATUS_REQUEST_COMPLETE)
    {
        $header = new Header();
        $header->setType(Protocal::TYPE_END_REQUEST);
        $header->setRequestId($requestId);
        $body = new Body();
        $body->setContentData(Protocal::packEndRequestBody($appStatus, $protocolStatus));
        $record = new static();
        $record->header = $header;
        $record->body = $body;
        $record->fixContentLength();
        return $record;
    }
}