<?php
namespace app\user\model;

use BBExtend\BBRedis;
use BBExtend\DbSelect;
use BBExtend\Sys;

/**
 * 查用户是否存在
 * 
 * 使用方法：\app\user\model\Exists::userhExists($uid)
 * 
 * @author Administrator
 *
 */
class Exists 
{
    public static function userhExists($uid)
    {
        if ($uid==0)
        {
            return -101;
        }
        $db = Sys::get_container_db_eloquent();
        $sql = "select * from bb_users where uid=?";
        $keys = DbSelect::fetchRow($db, $sql,[$uid]);
        
//         $keys=Db::table('bb_users')->where('uid',$uid)->find();
        if ($keys)
        {
            BBRedis::getInstance('user')->hMset($uid,$keys);
            return 1;
        }
    
        return -100;
    }
    
}