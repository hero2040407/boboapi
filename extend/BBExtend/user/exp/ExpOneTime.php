<?php
namespace BBExtend\user\exp;

/**
 * 经验值类，新的，老的Level逐渐废止。
 * 
 * 谢烨 2016 12
 */

use BBExtend\Sys;
// use think\Db;


class ExpOneTime extends ExpInterface
{
    
    
    public function add_exp(Exp $exp)
    {
        $uid = intval($exp->uid);
        $typeint = intval( $exp->typeint);
        $who_uid = $exp->who_uid;
        
        $db = Sys::get_container_db();
        $sql="select count(*) from bb_users_exp_log where uid = {$uid} and typeint = {$typeint}";
        $count = $db->fetchOne($sql);
        if ($count ) {
            return false;
        }
        
        // 没限制最简单。
        return $exp->update();
    }

}