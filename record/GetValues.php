<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class GetValues extends Params
{
    public static function newInstance($requestId, array $keys)
    {
        $record = parent::newInstance($requestId, array_fill_keys($keys, ''));
        $record->header->setType(Protocal::TYPE_GET_VALUES);
        return $record;
    }
}