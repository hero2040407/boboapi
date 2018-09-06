<?php

namespace app\api\controller;
use think\Controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Config;

/**
 * 加用户
 * 
 * @author xieye
 *
 */
class User extends Controller
{
    /**
     * 谢烨注：这是安全代码，千万保留。
     */
    public function _initialize()
    {
        $ip = Config::get( "http_head_ip" );
        
        if ( !in_array( $ip, [ '127.0.0.1','0.0.0.0', ] )  ) {
            exit('error');
        }
    }
    
    
    public function add( $phone  )
    {
        if (!$phone) {
            return ['code'=>0,'message' =>'phone必传' ];
        }
        
        if (!preg_match('#^1\d{10}$#', $phone)) {
            return ['code'=>0,'message' =>'手机格式错误' ];
        }
        
        $db = Sys::get_container_db_eloquent();
        
        $sql="select * from ";
        
        
    }
    

}


