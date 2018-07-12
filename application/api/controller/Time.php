<?php

namespace app\api\controller;


/**
 * 安卓客户端版本控制器
 * @author xieye
 *
 */
class Time 
{
    public function index()
    {
        $time = time();
        return ['code'=>1,'data' => ['current' =>$time * 1000 ]];
        
    }
    
}
