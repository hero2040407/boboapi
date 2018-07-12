<?php

namespace BBExtend\common;
use BBExtend\BBUser;
use BBExtend\Sys;

/**
 * 通用
 * 
 * 
 * @author 谢烨
 */
class User
{
    public static function is_vip($uid)
    {
        $result = BBUser::get_user($uid);
        if ($result) {
            return intval($result['vip']);
        }else {
            return 0;
        }
    }
    
    public static function exists($uid)
    {
        $db = Sys::get_container_db();
        $uid = intval($uid);
        if (!$uid) {
            return false;
        }
        $sql ="select count(*) from bb_users where uid={$uid}";
        return $db->fetchOne($sql);
    }
   
  
}//end class

