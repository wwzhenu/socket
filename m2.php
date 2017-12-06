<?php
/**
 * Created by PhpStorm.
 * User: wangwenzeng
 * Date: 2017/12/6
 * Time: 16:02
 */
$str='ab+cd-ef+gh-ij';
preg_match_all('/^([a-z]{2})\+([a-z]{2})/',$str,$match);
var_dump($match);