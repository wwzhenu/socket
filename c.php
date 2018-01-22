<?php
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2018/1/22
 * Time: 14:44
 */
# 连接远程
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE)
    die('socket_create fail');
$ip="127.0.0.1";
$port=9000;
socket_connect($socket, $ip, $port);
socket_write($socket,'wang',4);

#连接PHPstorm
$phpstorm = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($phpstorm === FALSE)
    die('socket_create fail');
$ip="127.0.0.1";
$port=9000;
socket_connect($phpstorm, $ip, $port);

$read[] = $socket;
$read[] = $phpstorm;

do{
    socket_select($read,$w,$e,NULL);
    foreach ($read as $s){
        # 收到远程数据
        if ($s==$socket){
            $data=socket_read($s,10000000);
            socket_write($phpstorm,$data,strlen($data));
        }
        # 收到PHPstorm数据
        elseif ($s==$phpstorm){
            $data=socket_read($s,10000000);
            socket_write($socket,$data,strlen($data));
        }
    }
}while(1);
