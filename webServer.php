<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/4
 * Time: 20:53
 * 简易web服务器
 * 分发html与php请求
 * 将php请求分发至127.0.0.1:9000处理
 */

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === FALSE)
    die('socket_create fail');
$ip = '127.0.0.1';
$port = 1234;
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
    $data = socket_read($get, 1000);
    $url = explode(PHP_EOL, $data)[0];
    preg_match('/\w+\s\/(.*)\sHTTP/', $url, $match);
    $fileName = $match[1];
    preg_match('/.*\.(.*)$/', $fileName, $ext);
    $extName = $ext[1];
    socket_write($get, 'HTTP/1.1 200 OK' . PHP_EOL);
    if (in_array($extName, ['html']))
        $put = file_get_contents($fileName);
    else if (in_array($extName, ['php'])) {
        echo 'exec php' . PHP_EOL;
        $php = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($php, '127.0.0.1', 9000);
        $data=createData($fileName);
        socket_write($php, $data, strlen($data));
        socket_recv($php, $data, 10000, 0);
        if ($data === FALSE) {
            echo 'read failed' . PHP_EOL;
            var_dump(socket_last_error());
        }else{
            $data=dealData($data);
            foreach ($data['header'] as $v){
                if (preg_match('/Content-type/i',$v)){
                    $content_type_flag=TRUE;
                }
                socket_write($get, $v . PHP_EOL);
            }
        }
        socket_close($php);
        $put=$data['body'];
    }
    socket_write($get, 'Date:' . date('Y-m-d H:i:s') . PHP_EOL);
    if ($content_type_flag!==TRUE)
        socket_write($get, 'Content-Type:text/html; charset=UTF-8' . PHP_EOL);
    socket_write($get, '' . PHP_EOL);
    socket_write($get, $put . PHP_EOL, strlen($put));
    socket_close($get);
} while (TRUE);
socket_close($socket);
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2017/12/12
 * Time: 17:38
 * 打包fastcgi请求包
 * SCRIPT_FILENAME为比选参数，其他参数均可不给
 */
function createData($file)
{
    $param = [
        'SCRIPT_FILENAME' => 'D:/socket/'.$file,#必选参数
        'PATH_INFO' => '',
        'PATH_TRANSLATED' => 'D:/socket',
        'QUERY_STRING' => '',
        'REQUEST_METHOD' => 'GET',
        'CONTENT_TYPE' => "",
        'CONTENT_LENGTH' => "",
        'SCRIPT_NAME' => '/index.php',
        'REQUEST_URI' => '/index.php',
        'DOCUMENT_URI' => '/index.php',
        'DOCUMENT_ROOT' => 'D:/socket',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'GATEWAY_INTERFACE' => 'CGI/1.1',
        'SERVER_SOFTWARE' => 'nginx/1.11.5',
        'REMOTE_ADDR' => '127.0.0.1',
        'REMOTE_PORT' => '12345',
        'SERVER_ADDR' => '127.0.0.1',
        'SERVER_PORT' => '80',
        'SERVER_NAME' => 'localhost',
        'REDIRECT_STATUS' => '200',
        'HTTP_HOST' => '127.0.0.1',
        'HTTP_CONNECTION' => 'keep-alive',
        'HTTP_CACHE_CONTROL' => 'max-age=0',
        'HTTP_UPGRADE_INSECURE_REQUEST' => 'S1',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8'
    ];
    $pStr = NUll;
    foreach ($param as $key => $val) {
        $pStr .= pack('CC', strlen($key), strlen($val));
        $pStr .= $key;
        $pStr .= $val;
    }
    #a b表示参数长度
    $pLen = strlen($pStr);
    $a = floor($pLen / (16 * 16));
    $b = $pLen - $a * 16 * 16;
    $str = NULL;
    $str .= pack('CCCCCCCC', '01', '01', '00', '01', '00', '08', '00', '00');
    $str .= pack('CCCCCCCC', '00', '01', '00', '00', '00', '00', '00', '00');
    $str .= pack('CCCCCCCC', '01', '04', '00', '01', $a, $b, '00', '00');
    $str .= $pStr;
    $str .= pack('CCCCCCCC', '01', '04', '00', '01', '00', '00', '00', '00');
    $str .= pack('CCCCCCCC', '01', '05', '00', '01', '00', '00', '00', '00');
    return $str;
}
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2017/12/13
 * Time: 10:27
 * 处理接收的数据
 * 前8个字符为接收到的数据头，其后为数据
 * 后16个字符为发送的结束请求及请求体
 */
function dealData($data){
    #前8个字符为收到的数据头
    #01     06          00   01                     00 4F           01      00
    #版本  数据为输出    请求ID(与发送请求ID一致)    数据长度       填充字节  保留
    /*
    $head=substr($data,0,8);

    for ($i=0;$i<8;$i++)
        echo( unpack('C',$head[$i])[1].'-');
    echo PHP_EOL;
    $tail=substr($data,-16);
    for ($i=0;$i<16;$i++)
        echo( unpack('C',$tail[$i])[1].'-');
    */
    #返回的有头信息与数据，以两个换行分隔  尾部截去16+填充字节
    $data=substr($data,8,-(16+unpack('C',$data[6])[1]));
    $data=explode("\r\n\r\n",$data);
    $header=explode("\r\n",$data[0]);
    $body=$data[1];
    return ['header'=>$header,'body'=>$body];
}