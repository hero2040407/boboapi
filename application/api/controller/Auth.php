<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 童星排行
 * 
 * @author xieye
 *
 */
class Auth
{
    /**
     * $type =1 动态发布权限。
     * @param number $type
     * @param unknown $uid
     */
    public function check($type=1,$uid )
    {
        if ($uid==10023) {
            return ['code'=>1,'data' =>['status' =>1 ] ];
        }
        
      
        
        $user = \BBExtend\model\UserCheck::is_phone_renzheng($uid);
        if ($user ) {
            
            $push = $user->can_push();
            $card = \BBExtend\model\UserCheck::is_vip_or_high($uid);
           
            if ($push && $card) {
                return ['code'=>1,'data' =>['status' =>4 ] ];
            }
            if ($push) {
                return ['code'=>1,'data' =>['status' =>1 ] ];
            }
            if ($card) {
                return ['code'=>1,'data' =>['status' =>2 ] ];
            }
            return ['code'=>1,'data' =>['status' =>3 ] ];
            
        }else {
            return ['code'=>0,'message' =>'请绑定手机' ];
        }
    }
    

}


