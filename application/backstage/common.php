<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 上午 9:20
 */
/**
 * Notes: 获取数组中设置了的数据
 * Date: 2018/6/27 0027
 * Time: 下午 2:20
 * @param $param  //传入的参数数组
 * @param $string //想要的字段
 * @return array 返回数组
 */
function getValidParam($param ,$string)
{
    $data = [];
    $array = explode(',' , $string);
    foreach ($array as $v){
        if (isset($param[$v])) $data[$v] = $param[$v];
    }
    return $data;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function getClientIp($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip   =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}