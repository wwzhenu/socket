<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/4
 * Time: 20:53
 */
$socket=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
if ($socket===FALSE)
    die('socket_create fail');
$ip='127.0.0.1';
$port=8888;
if (socket_bind($socket,$ip,$port)===FALSE)
    die('socket_bind fail');
if (socket_listen($socket,4)===FALSE)
    die('socket_listen fail');
do{
    $get=socket_accept($socket);
    if ($get!==FALSE){
        echo 'create a connection '.PHP_EOL;
        $data=socket_read($get,10000);
        preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match);
        $key=$match[1];
        $acceptKey=base64_encode(sha1($key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11',true));
        $in = "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
            "\r\n";
        //$data=json_decode($data,TRUE);
        //echo 'receive message from '.$data['name'].'.he says '.$data['message'].' to '.$data['to'].PHP_EOL;
        socket_write($get,$in,strlen($in));
        //socket_close($get);
    }
}while(TRUE);
socket_close($socket);