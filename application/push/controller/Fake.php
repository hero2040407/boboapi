<?php
namespace app\push\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 伪造的直播请求，node调用。
 */

class Fake
{
    
    public function get_robot_push()
    {
        $db = Sys::get_container_db_eloquent();
        
    }
    
    //api
    public function open_push($id)
    {
        
        
        return ['code'=>1];
    }
    
    public function close_push($id)
    {
        
        return ['code'=>1];
    }
    
}