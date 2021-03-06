<?php
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2017/12/6
 * Time: 14:13
 * websocket例子服务器端
 */
#创建socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE)
    die('socket_create fail');
$ip = '127.0.0.1';
$port = 8888;
#绑定socket到ip:port
if (socket_bind($socket, $ip, $port) === FALSE)
    die('socket_bind fail');
#监听socket
if (socket_listen($socket, 4) === FALSE)
    die('socket_listen fail');
$client = [];
do {
    echo 'waiting for change' . PHP_EOL;
    #初始化监听socket
    $read[] = $socket;
    $read=array_merge($read,$client);
    $read=array_unique($read);
    #选择出有变动的socket
    socket_select($read, $write, $expect, null);
    if (in_array($socket,$read)) {
        echo 'a new connection ' . PHP_EOL;
        $get = socket_accept($socket);
        $data = socket_read($get, 10000);
        #进行握手
        preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match);
        $key = $match[1];
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $in = "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
            "\r\n";
        socket_write($get, $in, strlen($in));
        $client[] = $read[] = $get;
        unset($read[array_search($socket,$read)]);
    } else {
        foreach ($read as $sock) {
            echo 'a new message ' . PHP_EOL;
            //socket_recv($sock, $data, 1000, 0);
            $data=socket_read($sock,1000);
            echo 'message strlen ' . strlen($data) . PHP_EOL;
            #长度小于7为断开连接
            if (strlen($data) < 7) {
                echo 'close connection' . PHP_EOL;
                unset($read[array_search($sock, $read)]);
                socket_close($sock);
                if (count($client>1)){
                    foreach ($client as $w) {
                        if ($w != $sock){
                            $data=array_search($sock,$client)."已离开";
                            $data=json_encode(['message'=>$data,'type'=>'info']);
                            socket_write($w, mask($data));
                        }

                    }
                }
            } else {
                $data=decode($data);
                echo 'message:' . ($data) . PHP_EOL;
                echo 'send message to other clients,clients num is ' . (count($client) - 1) . PHP_EOL;
                $str=json_decode($data,true);
                if ($str['type']=='add'){
                    $key=array_search($sock,$client);
                    if ($key!==false){
                        $client[$str['from']]=$client[$key];
                        unset($client[$key]);
                    }
                }
                if (count($client) < 2) {
                    echo 'no other available client' . PHP_EOL;
                } else {
                    foreach ($client as $wk=>$w) {
                        if ($str['type']=='message' && $str['to']!='all'){
                            $receiver=explode(',',$str['to']);
                            if (!in_array($wk,$receiver))
                                continue;
                        }
                        if ($w != $sock)
                            socket_write($w, mask($data));
                    }
                }
            }
        }
    }
} while (1);
//解码函数
function decode($text)
{
    //echo 'decode data---------->'.PHP_EOL;
    $length = ord($text[1]) & 127;
    if ($length == 126) {
        $masks = substr($text, 4, 4);
        $data = substr($text, 8);
    } elseif ($length == 127) {
        $masks = substr($text, 10, 4);
        $data = substr($text, 14);
    } else {
        $masks = substr($text, 2, 4);
        $data = substr($text, 6);
    }
    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    echo $text . PHP_EOL;
    return $text;

}

//编码数据
function mask($text)
{
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if ($length <= 125)
        $header = pack('CC', $b1, $length);
    elseif ($length > 125 && $length < 65536)
        $header = pack('CCn', $b1, 126, $length);
    elseif ($length >= 65536)
        $header = pack('CCNN', $b1, 127, $length);
    return $header . $text;
}