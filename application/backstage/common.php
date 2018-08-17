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