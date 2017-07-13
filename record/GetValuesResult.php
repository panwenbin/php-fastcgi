<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi\record;


use panwenbin\fastcgi\Protocal;

class GetValuesResult extends Params
{
    public static function newInstance($requestId, array $results)
    {
        $record = parent::newInstance($requestId, $results);
        $record->header->setType(Protocal::TYPE_GET_VALUES_RESULT);
        return $record;
    }
}