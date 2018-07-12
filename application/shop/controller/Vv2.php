<?php
namespace app\shop\controller;
use think\Controller;



/**
 * 
 * 测试 
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Vv2  extends Controller
{
    public function _initialize()
    {
        $request = request();
        $chekc_action =['t1', 't2', ];
        if ( in_array( $request->action(), $chekc_action )) {
            $help = new \BBExtend\pay\Sign();
            
            $result = $help->check(input('param.v'), input('param.uid'), 
                input('param.time'), input('param.sign')      );
            if (!$result) {
                echo json_encode(["code"=>0, "message"=>$help->get_info() ] , 
                    JSON_UNESCAPED_UNICODE);
                exit();
            }
        }
    }
    
    public function t1()
    {
        return ['code'=>1];
        
    }
    public function t2()
    {
        return ['code'=>1];
    }
    public function t3()
    {
        return ['code'=>1];
    }
    
}

