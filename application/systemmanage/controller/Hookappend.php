<?php

/**
 *
 * 所有怪兽bobo的程序的结尾调用的程序。
 * 由tags.php设置。
 * 
 * 20180104
 *
 * @author 谢烨
 *
 */

namespace app\systemmanage\controller;



class Hookappend
{

    public function run(&$param){
       $bb_request_arr =  \think\Config::get("bb_request_arr");
        
        
//         \think\Debug::remark('end_bobo' );
//         $temp = \think\Debug::getRangeTime('begin_bobo','end_bobo');
//         $temp = floatval($temp );
//         $temp = $temp * 1000;
//         $temp = intval($temp);
//         $bb_request_arr['duration'] = $temp;
        
//         $db = \BBExtend\Sys::get_container_db_eloquent();
//         $db::table('bb_request')->insert( $bb_request_arr );
        
    }
    

}
      
    
   
