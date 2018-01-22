<?php
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2018/1/22
 * Time: 14:33
 */
# 服务器将数据发送至此接口
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE)
    die('socket_create fail');
$ip = '0.0.0.0';
$port = 9001;
if (socket_bind($socket, $ip, $port) === FALSE)
    die('9001 socket_bind fail');
if (socket_listen($socket, 4) === FALSE)
    die('9001 socket_listen fail');

$client=NULL;
$server=NULL;

$read[]=$socket;
do{
    socket_select($read,$w,$e,NULL);
    foreach ($read as $s){
        if ($s==$socket){
            $get=socket_accept($s);
            $data=socket_read($get,10000000);
            # 收到客户端数据
            if ($data=='wang'){
                $client=$get;
            }
            # 收到chrome数据
            else{
                $server=$get;
                if (isset($client))
                    socket_write($client,$data,strlen($data));
            }
        }else if($s==$client){
            $data=socket_read($s,10000000);
            socket_write($server,$data,strlen($data));
        }else if ($s==$server){
            $data=socket_read($s,10000000);
            socket_write($client,$data,strlen($data));
        }
    }
    $read=[$socket];
    if (isset($client))
        $read[]=$client;
    if (isset($server))
        $read[]=$server;
}while(1);