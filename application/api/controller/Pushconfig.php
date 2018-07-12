<?php

namespace app\api\controller;

use BBExtend\Sys;

/**
 * 安卓客户端版本控制器
 * @author xieye
 *
 */
class Pushconfig 
{
    
    public function set_config($uid,$token, $type,$value)
    {
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message' =>'uid error' ];
        }
//         if (!$user->check_token( $token )) {
//             return ['code'=>0,'message' =>'token error' ];
//         }
        
        $obj = new \BBExtend\model\MsgConfig();
        
        $result = $obj->set_config($uid,$type,$value);
        if ($result !== true) {
            return $result;
        }
        return [
                'code'=>1,
                'data' => ['list'=> $obj->get_all_config( $uid ) ],
                
        ];
        
        
    }
    
    
    public function get_config($uid)
    {
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message' =>'uid error' ];
        }
//         if (!$user->check_token( $token )) {
//             return ['code'=>0,'message' =>'token error' ];
//         }
        
        $obj = new \BBExtend\model\MsgConfig();
        
        return        ['code'=>1,'data' =>['list'=> $obj->get_all_config( $uid ) ] ];
        
        
        
    }
    
    
    
    
    
    
}
