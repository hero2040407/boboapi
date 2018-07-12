<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;

//use think\Request;
//use think\Db;

use BBExtend\common\Str;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\UserInviteRegister;
use BBExtend\model\ShanghuInviteRegister;
use BBExtend\model\Shanghu;


class Invite
{
    /**
     * 邀请注册
     * 
     * 特点：一个人可以邀请多个手机号
     * 1个手机号，表里只能出现一次。
     * 重要：这个手机号不能是已经有的用户！！
     * 
     **/
    public function register($uid=0,$phone)
    {
        $uid = intval($uid);
        
        if (\app\user\model\Exists::userhExists($uid) !=1) {
            return ['code'=>0,'message'=>'用户不存在'];
        }
        //检验手机号
        if (!Str::is_valid_phone($phone)) {
            return ['code'=>0,'message'=>'手机格式错误'];
        }
        
        $db = Sys::get_container_db_eloquent();
        // 检查是否是已有用户
        $sql="select count(*) from bb_users where phone=?";
        $count = DbSelect::fetchOne($db, $sql,[$phone]);
        if ($count) {
            return ['code'=>0,'message'=>'该手机号已被使用'];
        }
        
        // 检查是否已被邀请过，重要啊
        $sql="select count(*) from bb_users_invite_register where phone=?";
        $count = DbSelect::fetchOne($db, $sql,[$phone]);
        if ($count) {
            return ['code'=>1,'message'=>'该手机号被邀请过'];
        }
        $invite = new UserInviteRegister();
        $invite->uid = $uid;
        $invite->phone = $phone;
        $invite->create_time = time();
        $invite->save();
        
        return ['code'=>1,];
    }
    
    
    /**
     * 商户邀请注册
     *
     * 特点：一个人可以邀请多个手机号
     * 1个手机号，表里只能出现一次。
     * 重要：这个手机号不能是已经有的用户！！
     *
     **/
    public function register_shanghu($shanghu_id=0,$phone)
    {
        $uid = intval($shanghu_id);
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_shanghu where id=?";
        $shanghu_row = DbSelect::fetchRow($db, $sql,[ $shanghu_id ]);
        if (!$shanghu_row) {
            return ['code'=>0,'message'=>'商户不存在'];
        }
        
        
        //检验手机号
        if (!Str::is_valid_phone($phone)) {
            return ['code'=>0,'message'=>'手机格式错误'];
        }
    
        
        // 检查是否是已有用户
        $sql="select count(*) from bb_users where phone=?";
        $count = DbSelect::fetchOne($db, $sql,[$phone]);
        if ($count) {
            return ['code'=>0,'message'=>'该手机号已被使用'];
        }
    
        // 检查是否已被邀请过，重要啊
        $sql="select count(*) from bb_users_shanghu_invite_register where phone=?";
        $count = DbSelect::fetchOne($db, $sql,[$phone]);
        if ($count) {
            return ['code'=>1,'message'=>'该手机号已被商户邀请过'];
        }
        $invite = new ShanghuInviteRegister();
        $invite->shanghu_id = $uid;
        $invite->phone = $phone;
        $invite->create_time = time();
        $invite->save();
        
        $shanghu = Shanghu::find($uid);
        $shanghu->invite_count = $shanghu->invite_count +1;
        $shanghu->save();
        
        return ['code'=>1,];
    }
    
    
    
    
    /**
     * 给客户端查询用，该用户 已成功邀请 $count 人，获 $sum 积分
     * @param int $uid 用户id
     * @return array
     */
    public function get_info($uid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql = "select count(*) from bb_users_invite_register where uid=? and is_complete>0";
        $count = DbSelect::fetchOne($db, $sql,[$uid ]);
        $sql = "select sum(count) from bb_currency_log where uid=? and msg_type=171";
        $sum = DbSelect::fetchOne($db, $sql,[$uid ]);
        $sum = intval($sum);
        return ['code'=>1, 'data'=>[
            'count' => $count,
            'sum' => $sum,
        ] ];
        
    }
    
    
    public function new_trade_info()
    {
        //$type
        Sys::display_all_error();
        $db = Sys::get_container_db_eloquent();
        $sql = "select * from bb_shop_order where type=4 order by id desc limit 30";
        $result = DbSelect::fetchAll($db, $sql);
        $new=[];
        // 2017/9/28 17:00 【玩家昵称】兑换了礼品名称
        foreach ($result as $v) {
            $user = \app\user\model\UserModel::getinstance($v['uid']);
            $nickname = $user->get_nickname();
            $goods = \BBExtend\model\Goods::find($v['goods_id']);
            $goods_title = $goods->title;
            
            $content = date("Y/m/d H:i", $v['create_time']) . " 【{$nickname}】兑换了 {$goods_title}";
            
            $new[]= ['content'=> $content  ];
        }
        
        return ['code'=>1, 'data'=>$new ];
    
    }
    
}

