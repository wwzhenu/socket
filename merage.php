<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6
 * Time: 21:52
 */
$arr1=[1,2,3,4,5,6];
$arr2=[3,6,8,9,20,25];
$i=0;$j=0;
$int = array();
while($i<count($arr1) && $j<count($arr2)){
    $int[] = $arr1[$i]<$arr2[$j]?$arr1[$i++]:$arr2[$j++];
}
while($i<count($arr1)){
    $int[] = $arr1[$i++];
}
while($j<count($arr2)){
    $int[] = $arr2[$j++];
}
print_r($int);