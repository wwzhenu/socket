<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/4
 * Time: 21:14
 */
$socket=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
$ip='127.0.0.1';
$port=8888;
if ($socket===FALSE)
    die('socket_create fail');
if(socket_connect($socket,$ip,$port)===FALSE)
    die('socket_connect fail');
socket_write($socket,json_encode(['name'=>'jack','message'=>'here is jack','to'=>'tom']));
$s=socket_read($socket,200);
echo $s;
socket_close($socket);