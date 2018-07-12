<?php
namespace BBExtend\user\exp;

/**
 * 经验值类，新的，老的Level逐渐废止。
 * 
 * 谢烨 2016 12
 */

use BBExtend\Sys;
// use think\Db;


class ExpPush extends ExpInterface
{
    const EXP_LIMIT = 60;// 用户的当日直播经验上限。
    public function add_exp(Exp $exp)
    {
        $uid = intval($exp->uid);
        $typeint = intval( $exp->typeint);
        $who_uid = $exp->who_uid;
        $datestr = date("Ymd");
        
       
        $limit  = self::EXP_LIMIT;// 用户的当日直播经验上限。
        
        $db = Sys::get_container_db();
        $sql="select sum(exp) from bb_users_exp_log where uid = {$uid} and datestr='{$datestr}'
        and typeint = {$typeint}
        ";
//        Sys::d
        $sum = $db->fetchOne($sql);// 今天用户已经获得的直播经验
        if ($sum >= $limit) {
            return false;
        }
        //现在开始计算 该加的经验
        $shi_cha = $exp->shi_cha;
        if (!$shi_cha) {
            return false;
        }
        
        $every_exp = $exp->exp_config;// 每20分钟即 每 1200秒 得到 exp_config目前是5点经验。
        $temp = $shi_cha / (20 * 60);
        $temp = (int)$temp;
        if (!$temp) {  // 谢烨，如果不足20分钟，则完全无用。
            return false;
        }
        $exp_increment = $temp * $every_exp;
        // 计算是否超过上限。
        if ($exp_increment + $sum > $limit ) {
            // 重新计算上限。
            $exp_increment = $limit - $sum;
            
        }
        
        // 没限制最简单。
        return $exp->update($exp_increment);
    }

}