<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

spl_autoload_register(function($className){
    if(strpos($className, 'panwenbin\\fastcgi\\') === 0) {
        include __DIR__ . '/' . str_replace('\\','/',substr($className, strlen('panwenbin\\fastcgi\\'))).'.php';
    }
});