<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;

use BBExtend\model\Achievement as Ach;
use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\BBUser;
use BBExtend\model\AchievementBonus;
use BBExtend\Currency;

class Achievement
{
    public function get($uid=0)
    {
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=> ' 用户不存在'];
        }
        $ach2 = new Ach();
        $ach = $ach2->create_default_by_user($user);
        $data = $ach->get_all_data();
        return ["code"=>1,'data'=>$data ];
    }
    
    /**
     * 某一成就明细。
     * 
     * 返回的内容很多，包括
     * 
     * event_name 成就汉字名，
     * level  整型， 成就当前的等级。
     * all_bonus 整型，该成就共获取多少波币奖励。
     * 
     * [
     * complete_status 1未获得，2已获得未领奖。3已获得，已领奖
     * condition: 条件语句，不含未获得3个字。
     * create_time: 整型，成就达成时间，时间戳
     * get_time: 整型，领取奖励时间，时间戳
     * bonus：整型，领取的波币数额。
     * pic：图标。
     * ]
     * 
     * 1 未获得，
     * 条件语句写上！！
     * 
     * 2、写什么时候达成（成就，即create_time）
     * 
     * 3、写什么时候获得波币，波币数量。
     * 
     * 
     * @param number $uid
     * @param unknown $event
     */
    public function one_detail($uid=0, $event)
    {
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=> ' 用户不存在'];
        }
        
        $ach2 = new Ach();
        $ach = $ach2->create_default_by_user($user);
        $data = $ach->get_one_detail($event);
        return ["code"=>1,'data'=>$data ];
    }
    
    /**
     * 领奖接口
     * @param unknown $uid
     * @param unknown $userlogin_token
     * @param unknown $level
     * @param unknown $event
     */
    public function award($uid,$userlogin_token, $level, $event)
    {
        $userlogin_token=strval($userlogin_token);
        if (!BBUser::validation_token($uid,$userlogin_token))
        {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        //uid level event get_time 
        $bonus = AchievementBonus::where('uid', $uid)->where('level',$level)
            ->where('event',$event)
            ->where('get_time',0)
            ->first();
        if (!$bonus) {
            return ['message'=>'您没有可以领取的成就奖励','code'=>0];
        }
        if ($bonus->bonus <=0) {
            return ['message'=>'成就数据错误','code'=>0];
        }
        Currency::add_currency($uid,1,$bonus->bonus,'成就奖励');
        $bonus->get_time=time();
        $bonus->save();
        
        $ach2 = new Ach();
        $ach = $ach2->create_default_by_user(User::find($uid));
        $data = $ach->get_one_detail($event);
        
        return ['code'=>1,'data'=>[
            'bonus_count' => $bonus->bonus,
            'one_detail' => $data,
        ]];
    }
    
    
    public function msg($uid)
    {
       // \BBExtend\Sys::display_all_error();
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=> ' 用户不存在'];
        }
        
        
        $result =\BBExtend\model\AchievementMsg::where("is_read",0)->where("uid", $uid)
          ->orderBy("id",'asc')->get();
        $result = json_decode( $result->toJson(),1);
        $result=(array)$result;
        
        // 这里，必须全部清理成已读
//         $db =Sys::
        \BBExtend\model\AchievementMsg::where("is_read",0)->where("uid", $uid)->update(
            ["is_read"=>1]    );
        return ["code"=>1, 'data' => $result ];
    }
    
    // 后台专用接口，审核用户评论成功加1次成就
    public function houtai_pinglun($uid)
    {
        $ach = new \BBExtend\user\achievement\Pinglun($uid);
        $ach->update(1);
        return ['code'=>1];
    }
    
    
    
    
    
    
}
