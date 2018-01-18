<?php
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2018/1/18
 * Time: 12:37
 */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE)
    die('socket_create fail');
$ip = 'localhost';
$port = 9001;
if (socket_bind($socket, $ip, $port) === FALSE)
    die('socket_bind fail');
if (socket_listen($socket, 4) === FALSE)
    die('socket_listen fail');
$client=NULL;
echo 'create server success,waiting for client or debug '.PHP_EOL;
$data=NULL;
do{
    $get = socket_accept($socket);
    $data = socket_read($get, 10000000);
    if ($data=='wanglovechu'){
        $client=$get;
        echo 'client connected'.PHP_EOL;
    }else{
        echo 'receive debug data'.PHP_EOL;
        if (empty($client))
            echo "wating for client".PHP_EOL;
        else
            socket_write($client,$data);
    }
}while(TRUE);