<?php 
require  dirname(__FILE__) . "/../shop/common.php";

function throwErrorMessage($message, $code = 0)
{
    throw new \think\exception\HttpResponseException(json(['code' => $code, 'message' => $message]));
}

