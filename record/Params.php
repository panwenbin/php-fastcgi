<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class Params extends BaseRecord
{
    public static function newInstance($requestId, array $params)
    {
        $header = new Header();
        $header->setType(Protocal::TYPE_PARAMS);
        $header->setRequestId($requestId);
        $body = new Body();
        if (!empty($params)) {
            $contentData = '';
            foreach ($params as $name => $value) {
                $contentData .= Protocal::packNameValuePair($name, $value);
            }
            $body->setContentData($contentData);
        }
        $record = new static();
        $record->header = $header;
        $record->body = $body;
        $record->fixContentLength();
        return $record;
    }
}