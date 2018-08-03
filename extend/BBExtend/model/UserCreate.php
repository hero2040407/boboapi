<?php
namespace BBExtend\model;
//use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class UserCreate extends User 
{
    public static function create($phone)
    {
        if (!$phone) {
            return false;
        }
        
        $db = Sys::get_container_db();
        $sql="select uid from bb_users_platform
               where type=3 and platform_id =md5(?) ";
        $uid = $db->fetchOne($sql,[$phone ]);
        
        if ($uid) {
            return $uid;
        }
        
        $result= \BBExtend\BBUser::registered( '', '', 3,
                '', '', $phone, '');
        $uid = $result['uid'];
        
        \BBExtend\Currency::get_currency( $uid );
        
        
        // xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
        \BBExtend\Level::get_user_exp( $uid );
        //$success = ( $count >0 ) ? true: false;
        return $uid;
    }
    
    
    
}
