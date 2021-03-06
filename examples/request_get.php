<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

use panwenbin\fastcgi\Client;

include __DIR__ . '/../autoload.php';

$req = new Client('tcp://127.0.0.1:9000');
$req->request([
    'GATEWAY_INTERFACE' => 'FastCGI/1.0',
    'REQUEST_METHOD' => 'GET',
    'SCRIPT_FILENAME' => __DIR__ . "/app.php",
    'SCRIPT_NAME' => "app.php",
    'QUERY_STRING' => "a=b",
    'REQUEST_URI' => "/examples/app.php",
    'SERVER_SOFTWARE' => 'fcgi_client',
    'REMOTE_ADDR' => '127.0.0.1',
    'REMOTE_PORT' => '9985',
    'SERVER_ADDR' => '127.0.0.1',
    'SERVER_PORT' => '80',
    'SERVER_NAME' => php_uname('n'),
    'SERVER_PROTOCOL' => 'HTTP/1.1',
    'CONTENT_TYPE' => '',
    'CONTENT_LENGTH' => 0
]);
