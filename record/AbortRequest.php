<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class AbortRequest extends BaseRecord
{
    public static function newInstance($requestId)
    {
        $header = new Header();
        $header->setType(Protocal::TYPE_ABORT_REQUEST);
        $header->setRequestId($requestId);
        $body = new Body();
        $record = new static();
        $record->header = $header;
        $record->body = $body;
        $record->fixContentLength();
        return $record;
    }
}