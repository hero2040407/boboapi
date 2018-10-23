<?php 
require  dirname(__FILE__) . "/../shop/common.php";

function throwErrorMessage($message)
{
    throw new \think\exception\HttpResponseException(json(['code' => 0, 'message' => $message]));
}

