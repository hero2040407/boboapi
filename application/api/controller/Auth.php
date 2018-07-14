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
        $result = \BBExtend\model\UserCheck::is_phone_renzheng($uid);
        if ($result ) {
            
            // 如果是经纪人，则直播。
            if (\BBExtend\model\UserCheck::is_agent_check($uid)) {
                
                return ['code'=>1,'data' =>['status' =>1 ] ];
            }
            if (\BBExtend\model\UserCheck::is_vip_or_high($uid)) {
                
                return ['code'=>1,'data' =>['status' =>2 ] ];
            }
            
            return ['code'=>1,'data' =>['status' =>3 ] ];
            
            
            
        }else {
            return ['code'=>0,'message' =>'请绑定手机' ];
        }
    }
    

}


