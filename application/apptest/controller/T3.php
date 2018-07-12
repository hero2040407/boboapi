<?php

$a = [1,3];
function change_arr($arr) {
    $arr[0] = 200;
    return $arr;
}
$a = change_arr($a);
var_dump($a);

