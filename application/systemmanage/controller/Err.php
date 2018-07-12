<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
// use BBExtend\Sys;
// use BBExtend\DbSelect;
use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
// use Illuminate\Database\Capsule\Manager;

class Err extends Handle{
    
    public function render(Exception $e)
    {
        echo  123;
    //    return ['code'=>0,'message'=>'参数异常啦'];
        
        
        // 参数验证错误
//         if ($e instanceof ValidateException) {
//             return json($e->getError(), 422);
//         }
        
//         // 请求异常
//         if ($e instanceof HttpException && request()->isAjax()) {
//             return response($e->getMessage(), $e->getStatusCode());
//         }
        
//         //TODO::开发者对异常的操作
//         //可以在此交由系统处理
         return parent::render($e);
    }
    
    
    
}