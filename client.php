<?php
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2018/1/18
 * Time: 12:50
 */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!socket_connect($socket, 'www.wanglovechu.com', 9001))
    echo '连接服务器失败';
if(!socket_connect($client, 'localhost', 9001)){
    echo '连接本地9001端口失败';
}
$data='wanglovechu';
socket_write($socket, $data, strlen($data));
do{
    socket_recv($socket, $data, 10000000, 0);
    socket_write($client,$data,strlen($data));
}while(1);
