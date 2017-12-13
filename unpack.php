<?php
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
    #返回的有头信息与数据，以两个换行分隔
    $data=substr($data,8,-16);
    $data=explode("\r\n\r\n",$data);
    $header=explode("\r\n",$data[0]);
    $body=$data[1];
    return ['header'=>$header,'body'=>$body];
}