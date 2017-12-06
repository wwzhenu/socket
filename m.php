<?php
$str='abc';
$ary=[];
for ($i=0;$i<strlen($str);$i++)
    $ary[]=$str[$i];
$i=1;
foreach ($ary as $k=>$v){
    echo implode('',$ary).PHP_EOL;

}