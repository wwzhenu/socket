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
    die('socket_bind fail');
if (socket_listen($socket, 4) === FALSE)
    die('socket_listen fail');
#客户端socket 与本地通信
$client = NULL;
echo 'create server success,waiting for client or debug ' . PHP_EOL;
$data = NULL;
# 服务器端socket 写入debug信息
$server = NULL;
$sockets=[];
$read=[];
do {
    $read = [$socket];
    /*if (!empty($client))
        $read=array_merge($read,$sockets);
    $read=array_unique($read);*/
    socket_select($read, $write, $expect, null);
    if (in_array($socket, $read)) {
        echo 'a new connection ' . PHP_EOL;
        $get = socket_accept($socket);
        $data = socket_read($get, 10000000);
        if ($data == 'wanglovechu') {
            echo "a client socket" . PHP_EOL;
            $client = $get;
        } else {
            if (substr($data,0,4)=='wang'){
                $client=$get;
            }
            $data=substr($data,4);
            if ($get==$client){
                echo 'receive client data'.PHP_EOL;
                $des='server';
            }else if($get==$server){
                echo 'receive server data'.PHP_EOL;
                $des='client';
            }else if(empty($server)){
                echo 'a server client'.PHP_EOL;
                $server = $get;
                $des='client';
            }
            if ($des=='client'){
                if (empty($client)){
                    echo 'waiting for a client'.PHP_EOL;
                }else{
                    echo 'send debug data to client'.PHP_EOL;
                    socket_write($client, $data,strlen($data));
                }
            }else if($des=='server'){
                if (empty($server)){
                    echo 'waiting for a server'.PHP_EOL;
                }else{
                    echo 'send data to server'.PHP_EOL;
                    socket_write($server, $data,strlen($data));
                }
            }
        }
        /*$sockets[]=$read[] = $get;
        unset($read[array_search($socket, $read)]);*/
    } /*else {
        foreach ($read as $sock) {
            echo 'a new message ' . PHP_EOL;
            $data = socket_read($sock, 10000000000);
            foreach ($sockets as $wk => $w) {
                if ($w != $sock)
                    socket_write($w, $data,strlen($data));
            }
        }
    }*/
} while (1);
