<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/4
 * Time: 20:53
 *将nginx中php端口改为9001，使用此脚本获取nginx调用fastcgi传递参数
 */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE)
    die('socket_create fail');
$ip = '127.0.0.1';
$port = 9001;
if (socket_bind($socket, $ip, $port) === FALSE)
    die('socket_bind fail');
if (socket_listen($socket, 4) === FALSE)
    die('socket_listen fail');
$i = 1;
$read = [$socket];
$write = [];
$clients = [];
do {
    $get = socket_accept($socket);
    $data = socket_read($get, 10000000);
    file_put_contents('1.txt',$data);
} while (TRUE);
socket_close($socket);