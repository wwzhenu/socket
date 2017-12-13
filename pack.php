<?php
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