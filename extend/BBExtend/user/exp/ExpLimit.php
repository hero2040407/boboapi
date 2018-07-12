<?php
namespace BBExtend\user\exp;

/**
 * 经验值类，新的，老的Level逐渐废止。
 * 
 * 这个是限制类，是每天都限制。
 * 
 * 谢烨 2016 12
 */

use BBExtend\Sys;
//use think\Db;


class ExpLimit extends ExpInterface
{
    /**
     * 返回每个类型的 增加的经验值。
     * @param unknown $typint
     */
    public function get_limit_arr()
    {
        $arr = [
            Exp::LEVEL_LOGIN => 10, //登录
            Exp::LEVEL_COMPLETE_UPLOAD_PIC => 0, //头像上传
            Exp::LEVEL_COMPLETE_ATTESTAION => 0, //直播认证
            Exp::LEVEL_COMPLETE_CHANG_USERINFO => 0, //资料完善
            Exp::LEVEL_PUSH => 60, //直播发起
            Exp::LEVEL_RECORD => 30,//发布短视频
            Exp::LEVEL_COMMENTS => 20, //发布文字评论
            Exp::LEVEL_SHARE => 50,    //共享
            Exp::LEVEL_SHARE_OTHER_USER => 100, //他人共享
            Exp::LEVEL_INVITATION_REGISTER => 0, //邀请注册
            Exp::LEVEL_ACTIVITY_LIKE => 50,       //活动点赞
            Exp::LEVEL_COMPLETE_TASK => 50,       //完成任务
            Exp::LEVEL_LIKE => 50,                //被关注
            Exp::LEVEL_SHOW_LIVE_COURSE => 0,   //点播课程
            Exp::LEVEL_SHOP => 0,                // 商城购买
        ];
        return $arr;
    }
    
    public function add_exp(Exp $exp)
    {
        $uid = intval($exp->uid);
        $typeint = intval( $exp->typeint);
        $who_uid = $exp->who_uid;
        $datestr = date("Ymd");
        
        $limit_arr = $this->get_limit_arr();
        $limit  = $limit_arr[$typeint];
        
        $db = Sys::get_container_db();
        $sql="select sum(exp) from bb_users_exp_log where uid = {$uid} and datestr='{$datestr}'
                 and typeint = {$typeint}
        ";
        $sum = $db->fetchOne($sql);
        if ($sum >= $limit) {
            return false;
        }
        
        // 没限制最简单。
        return $exp->update();
    }

}