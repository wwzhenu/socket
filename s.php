<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/4
 * Time: 20:53
 */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE)
    die('socket_create fail');
$ip = '127.0.0.1';
$port = 8888;
if (socket_bind($socket, $ip, $port) === FALSE)
    die('socket_bind fail');
if (socket_listen($socket, 4) === FALSE)
    die('socket_listen fail');
$i = 1;
$read = [$socket];
$write = [];
$clients = [];
do {
    echo 'client num ' . count($clients) . PHP_EOL;
    array_unshift($read, $socket);
    $read=array_merge($read,$clients);
    $listen = socket_select($read, $write, $except, NULL);
    if ($listen !== FALSE) {
        if (in_array($socket, $read)) {
            echo 'a new connection' . PHP_EOL;
            $read[] = $clients[] = $get = socket_accept($socket);
            $data = socket_read($get, 10000);
            preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match);
            $key = $match[1];
            if (!empty($key)) {
                $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
                $in = "HTTP/1.1 101 Switching Protocols\r\n" .
                    "Upgrade: websocket\r\n" .
                    "Connection: Upgrade\r\n" .
                    "Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
                    "\r\n";
                socket_write($get, $in, strlen($in));
            }
            $key = array_search($socket, $read);
            unset($read[$key]);
        }
        if (count($clients)>1){
            foreach ($read as $soc) {
                $data = socket_read($soc, 1500);
                echo 'receive message' . PHP_EOL;
                if (strlen($data)<7){
                    echo strlen($data).PHP_EOL;
                    socket_close($soc);
                    continue;
                }
                if ($data === FALSE) {
                    echo 'data invalid'.PHP_EOL;
                    $key = array_search($soc, $read);
                    unset($read[$key]);
                    continue;
                } else {
                    if (strlen($data)<7){
                        echo strlen($data).PHP_EOL;
                        continue;
                    }else{
                        echo 'ready to send message'.PHP_EOL;
                        echo 'send to socket num ' . count($clients) . PHP_EOL;
                        if (count($clients)>1){
                            foreach ($clients as $w) {
                                if ($w != $soc){
                                    echo 'send message '.PHP_EOL;
                                    $a = str_split($data, 125);
                                    $ns = "";
                                    if (count($a) == 1) {
                                        $ns= "\x81" . chr(strlen($a[0])) . $a[0];
                                    }else{
                                        foreach ($a as $o) {
                                            $ns .= "\x81" . chr(strlen($o)) . $o;
                                        }
                                    }
                                    echo $ns.PHP_EOL;
                                    if(socket_write($w, $ns, strlen($ns))===FALSE)
                                        echo 'send fail';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}while (TRUE) ;
socket_close($socket);