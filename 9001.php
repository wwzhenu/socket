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
$ip = '0.0.0.0';
$port = 9001;
if (socket_bind($socket, $ip, $port) === FALSE)
    die('9001 socket_bind fail');
if (socket_listen($socket, 4) === FALSE)
    die('9001 socket_listen fail');
#客户端socket 与本地通信
$client = NULL;
echo 'create server success,waiting for client or debug ' . PHP_EOL;
$data = NULL;
# 服务器端socket 写入debug信息
$server = NULL;
$sockets = [];
$read = [$socket];
do {
    echo 'a new connection ' . PHP_EOL;
    socket_select($read, $w, $e, NULL);
    if (in_array($socket, $read)) {
        $get = socket_accept($socket);
        $data = socket_read($get, 10000000);
        echo 'receive data' . PHP_EOL;
        echo $data . PHP_EOL;
        if ($data == 'wanglovechu') {
            $client = $get;
            $read[]=$client;
            continue;
        } elseif (substr($data, 0, 3) == 'chu') {
            if ($client!=$get){
                $client=$get;
                $read=[$socket,$client];
            }
            $data = substr($data, 3);
            echo 'receive client data' . PHP_EOL;
            $des = 'server';
        } elseif ($get == $server) {
            echo 'receive server data' . PHP_EOL;
            $des = 'client';
        } else if (empty($server)) {
            echo 'a server client' . PHP_EOL;
            $server = $get;
            $des = 'client';
        }
        if ($des == 'client') {
            if (empty($client)) {
                echo 'waiting for a client' . PHP_EOL;
            } else {
                echo 'send debug data to client' . PHP_EOL;
                socket_write($client, 'wang' . $data, strlen($data) + 4);
            }
        } else if ($des == 'server') {
            if (empty($server)) {
                echo 'waiting for a server' . PHP_EOL;
            } else {
                echo 'send data to server' . PHP_EOL;
                socket_write($server, $data, strlen($data));
            }
        }
    }
} while (1);
