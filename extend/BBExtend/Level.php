<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/21
 * Time: 18:38
 */

namespace BBExtend;
use think\Db;
use BBExtend\Currency;
use BBExtend\PushMsg;


use BBExtend\message\Message;
use BBExtend\BBUser;
use BBExtend\Sys;

define('LEVEL_LOGIN',0);//每日登录
define('LEVEL_COMPLETE_UPLOAD_PIC',1);//上传头像
define('LEVEL_COMPLETE_ATTESTAION',2);//完成直播认证
define('LEVEL_COMPLETE_CHANG_USERINFO',3); //资料完善
define('LEVEL_PUSH',4); //发起直播
define('LEVEL_RECORD',5); //发布短视频
define('LEVEL_COMMENTS',6); //发布文字评论
define('LEVEL_SHARE',7); //分享
define('LEVEL_SHARE_OTHER_USER',8); //内容被他人分享
define('LEVEL_INVITATION_REGISTER',9); //邀请好友注册
define('LEVEL_ACTIVITY_LIKE',10);//活动点赞
define('LEVEL_COMPLETE_TASK',11);//完成任务
define('LEVEL_LIKE',12);//被关注
define('LEVEL_SHOW_LIVE_COURSE',13);//点播课程
define('LEVEL_SHOP',14);//商城购买/兑换

//经验增加数量
define('LEVEL_ADD_LOGIN',10);
define('LEVEL_ADD_UPLOAD_PIC',5);
define('LEVEL_ADD_ATTESTAION',30);
define('LEVEL_ADD_CHANG_USERINFO',30);
define('LEVEL_ADD_PUSH',5);
define('LEVEL_ADD_RECORD',3);
define('LEVEL_ADD_COMMENTS',1);
define('LEVEL_ADD_SHARE',5);
define('LEVEL_ADD_SHARE_OTHER_USER',1);
define('LEVEL_ADD_INVITATION_REGISTER',5);
define('LEVEL_ADD_ACTIVITY_LIKE',2);
define('LEVEL_ADD_COMPLETE_TASK',2);
define('LEVEL_ADD_LIKE',2);
define('LEVEL_ADD_SHOW_LIVE_COURSE',15);
define('LEVEL_ADD_SHOP',5);

class Level extends Currency
{
    public static function get_user_exp_log($uid,$startid,$length)
    {
        $focus_array = Db::table('bb_users_exp_log')->where('uid',$uid)->order('time','desc')-> limit($startid,$length)->select();
        return $focus_array;
    }
    //得到用户等级
    public static function get_user_level($uid)
    {
        $UserDB = self::get_user($uid);
//         if ($UserDB['reset_time'] < time())
//         {
//             $UserDB = self::reset($uid);
//         }
//         Db::table('bb_users_exp')->where('uid',$uid)->update($UserDB);
        return $UserDB['level'];
    }
    //得到用户升级还差多少经验
    public static function get_next_exp($uid)
    {
        $UserDB = self::get_user($uid);
        $Exp = $UserDB['next_exp'] - $UserDB['exp'];
        return (int)$Exp;
    }
    //得到用户当前经验
    public static function get_user_exp($uid)
    {
        $UserDB = self::get_user($uid);
        return (int)$UserDB['exp'];
    }

    //得到用户当前等级，经验数据。xieye 2016 10 
    private static function get_user($uid)
    {
//         $UserDB = BBRedis::getInstance('user')->hGetAll($uid.'level');
//         if (!$UserDB)
//         {
            $UserDB = Db::table('bb_users_exp')->where('uid',$uid)->find();
//         }
        if (!$UserDB)
        {
            $NewUser = array();
            $NewUser['uid'] = $uid;
            $NewUser['level'] = 1;
            $NewUser['exp'] = 0;
            $NewUser['next_exp'] = 80;
            $NewUser['login'] = 10;
            $NewUser['upload_pic'] = 5;
            $NewUser['attestaion'] = 30;
            $NewUser['userinfo'] = 30;
            $NewUser['push'] = 60;
            $NewUser['record'] = 30;
            $NewUser['comments'] = 20;
            $NewUser['share'] = 50;
            $NewUser['share_other_user'] = 100;
            $NewUser['invitation_register'] = 0;
            $NewUser['activity_like'] = 50;
            $NewUser['complete_task'] = 50;
            $NewUser['other_user_like'] = 50;
            $NewUser['show_live_course'] = 0;
            $NewUser['shop'] = 0;
            $NewUser['time'] = time();
            $NewUser['reset_time'] = strtotime(date('Ymd')) + 104400;//获得今天凌晨5点的时间戳
            Db::table('bb_users_exp')->insert($NewUser);
            BBRedis::getInstance('user')->hMset($uid.'level',$NewUser);
            return $NewUser;
        }
        else
        {
            $NewUser = array();
            $NewUser['level'] = (int)$UserDB['level'];
            $NewUser['exp'] = (int)$UserDB['exp'];
            $NewUser['next_exp'] = (int)$UserDB['next_exp'];
            $NewUser['login'] = (int)$UserDB['login'];
            $NewUser['upload_pic'] = (int)$UserDB['upload_pic'];
            $NewUser['attestaion'] = (int)$UserDB['attestaion'];
            $NewUser['userinfo'] = (int)$UserDB['userinfo'];
            $NewUser['push'] = (int)$UserDB['push'];
            $NewUser['record'] = (int)$UserDB['record'];
            $NewUser['comments'] = (int)$UserDB['comments'];
            $NewUser['share'] = (int)$UserDB['share'];
            $NewUser['share_other_user'] = (int)$UserDB['share_other_user'];
            $NewUser['invitation_register'] = (int)$UserDB['invitation_register'];
            $NewUser['activity_like'] = (int)$UserDB['activity_like'];
            $NewUser['complete_task'] = (int)$UserDB['complete_task'];
            $NewUser['other_user_like'] = (int)$UserDB['other_user_like'];
            $NewUser['show_live_course'] = (int)$UserDB['show_live_course'];
            $NewUser['shop'] = (int)$UserDB['shop'];
            $NewUser['reset_time'] = $UserDB['reset_time'];
            return $NewUser;
        }
    }
    private static function reset($uid)
    {

        $NewUser = self::get_user($uid);
        $NewUser['login'] = 10;
        $NewUser['push'] = 60;
        $NewUser['record'] = 30;
        $NewUser['comments'] = 20;
        $NewUser['share'] = 50;
        $NewUser['share_other_user'] = 100;
        $NewUser['invitation_register'] = 0;
        $NewUser['activity_like'] = 50;
        $NewUser['complete_task'] = 50;
        $NewUser['other_user_like'] = 50;
        $NewUser['show_live_course'] = 0;
        $NewUser['shop'] = 0;
        $NewUser['time'] = time(); //当前时间
        $NewUser['reset_time'] = strtotime(date('Ymd')) + 104400;//获得今天凌晨5点的时间戳
        BBRedis::getInstance('user')->hMset($uid.'level',$NewUser);
        return $NewUser;
    }
    //得到当前等级的配置文件
    private static function get_level_config($Level)
    {
        $ConfigDB = BBRedis::getInstance('user')->Get($Level.'config_level');
        if (!$ConfigDB)
        {
            $ConfigDB = Db::table('bb_config_level')->where('level',$Level)->find();
            if ($ConfigDB)
            {
                BBRedis::getInstance('user')->Set($Level.'config_level',$ConfigDB['exp']);
                $ConfigDB = $ConfigDB['exp'];
            }

        }
        return $ConfigDB;
    }
    //增加直播经验
    public static function add_push_exp($uid,$start_time,$end_time)
    {
        $UserDB = self::get_user($uid);
        $AddExp = 0;
        $AddCount = (int)(($end_time - $start_time)/1200);
        if ($AddCount == 0)
        {
            return true;
        }
        if ($UserDB['push']>=LEVEL_ADD_PUSH*$AddCount)
        {
            $UserDB['push'] = $UserDB['push'] - LEVEL_ADD_PUSH*$AddCount;
            $AddExp = LEVEL_ADD_PUSH*$AddCount;
            self::add_exp_log($uid,'发起直播',LEVEL_ADD_PUSH*$AddCount);
        }
        if ($AddExp>0)
        {
            $UserDB['exp']+=$AddExp;
            $LevelDB = self::get_level_config($UserDB['level']);

            if ($UserDB['exp']>=$LevelDB)
            {
                //用户升级了
                $UserDB['level'] = $UserDB['level'] + 1;
                $UserDB['exp'] = $UserDB['exp'] - $LevelDB;
                self::add_currency($uid,CURRENCY_MONSTER,1,'用户升级');
                
            }
            Db::table('bb_users_exp')->where('uid',$uid)->update($UserDB);
            // xieye 2016 10 25 这里是写统计排名的地方。
            \BBExtend\user\Ranking::getinstance($uid)->set_dengji_ranking();
            
            
            
            BBRedis::getInstance('user')->hMset($uid.'level',$UserDB);
            return true;
        }
    }
    //增加用户经验
    public static function add_user_exp($uid,$type,$other_uid = 0)
    {
        $UserDB = self::get_user($uid);
        $AddExp = 0;
        switch ($type)
        {
            case LEVEL_LOGIN://每日登录
                if ($UserDB['login']>=LEVEL_ADD_LOGIN)
                {
                    $UserDB['login'] = $UserDB['login'] - LEVEL_ADD_LOGIN;
                    $AddExp = LEVEL_ADD_LOGIN;
                    self::add_exp_log($uid,'每日登录',LEVEL_ADD_LOGIN);
                }
                break;
            case LEVEL_COMPLETE_UPLOAD_PIC://上传头像
                if ($UserDB['upload_pic']>=LEVEL_ADD_UPLOAD_PIC)
                {
                    $UserDB['upload_pic'] = $UserDB['upload_pic'] - LEVEL_ADD_UPLOAD_PIC;
                    $AddExp = LEVEL_ADD_UPLOAD_PIC;
                    self::add_exp_log($uid,'上传头像',LEVEL_ADD_UPLOAD_PIC);
                }
                break;
            case LEVEL_COMPLETE_ATTESTAION://完成直播认证
                if ($UserDB['attestaion']>=LEVEL_ADD_ATTESTAION)
                {
                    $UserDB['attestaion'] = $UserDB['attestaion'] - LEVEL_ADD_ATTESTAION;
                    $AddExp = LEVEL_ADD_ATTESTAION;
                    self::add_exp_log($uid,'完成直播认证',LEVEL_ADD_ATTESTAION);
                }
                break;
            case LEVEL_COMPLETE_CHANG_USERINFO://资料完善
                if ($UserDB['userinfo']>=LEVEL_ADD_CHANG_USERINFO)
                {
                    $UserDB['userinfo'] = $UserDB['userinfo'] - LEVEL_ADD_CHANG_USERINFO;
                    $AddExp = LEVEL_ADD_CHANG_USERINFO;
                    self::add_exp_log($uid,'资料完善',LEVEL_ADD_CHANG_USERINFO);
                }
                break;
            case LEVEL_PUSH://发起直播
                if ($UserDB['push']>=LEVEL_ADD_PUSH)
                {
                    $UserDB['push'] = $UserDB['push'] - LEVEL_ADD_PUSH;
                    $AddExp = LEVEL_ADD_PUSH;
                    self::add_exp_log($uid,'发起直播',LEVEL_ADD_PUSH);
                }
                break;
            case LEVEL_RECORD: //发布短视频
                if ($UserDB['record']>=LEVEL_ADD_RECORD)
                {
                    $UserDB['record'] = $UserDB['record'] - LEVEL_ADD_RECORD;
                    $AddExp = LEVEL_ADD_RECORD;
                    self::add_exp_log($uid,'发布短视频',LEVEL_ADD_RECORD);
                }
                break;
            case LEVEL_COMMENTS://发布文字评论
                if ($UserDB['comments']>=LEVEL_ADD_COMMENTS)
                {
                    $UserDB['comments'] = $UserDB['comments'] - LEVEL_ADD_COMMENTS;
                    $AddExp = LEVEL_ADD_COMMENTS;
                    self::add_exp_log($uid,'评论',LEVEL_ADD_COMMENTS);
                }
                break;
            case LEVEL_SHARE://分享
                if ($UserDB['share']>=LEVEL_ADD_SHARE)
                {
                    $UserDB['share'] = $UserDB['share'] - LEVEL_ADD_SHARE;
                    $AddExp = LEVEL_ADD_SHARE;
                    self::add_exp_log($uid,'分享',LEVEL_ADD_SHARE);
                }
                break;
            case LEVEL_SHARE_OTHER_USER://内容被他人分享
                if ($UserDB['share_other_user']>=LEVEL_ADD_SHARE_OTHER_USER)
                {
                    $UserDB['share_other_user'] = $UserDB['share_other_user'] - LEVEL_ADD_SHARE_OTHER_USER;
                    $AddExp = LEVEL_ADD_SHARE_OTHER_USER;
                    self::add_exp_log($uid,'他人分享',LEVEL_ADD_SHARE_OTHER_USER,$other_uid);
                }
                break;
            case LEVEL_INVITATION_REGISTER: //邀请好友注册
                if ($UserDB['invitation_register']>=LEVEL_ADD_INVITATION_REGISTER)
                {
                    $UserDB['invitation_register'] = $UserDB['invitation_register'] - LEVEL_ADD_INVITATION_REGISTER;
                    $AddExp = LEVEL_ADD_INVITATION_REGISTER;
                    self::add_exp_log($uid,'邀请注册',LEVEL_ADD_INVITATION_REGISTER,$other_uid);
                }
                break;
            case LEVEL_ACTIVITY_LIKE://活动点赞
                if ($UserDB['activity_like']>=LEVEL_ADD_ACTIVITY_LIKE)
                {
                    $UserDB['activity_like'] = $UserDB['activity_like'] - LEVEL_ADD_ACTIVITY_LIKE;
                    $AddExp = LEVEL_ADD_ACTIVITY_LIKE;
                    self::add_exp_log($uid,'活动点赞',LEVEL_ADD_ACTIVITY_LIKE);
                }
                break;
            case LEVEL_COMPLETE_TASK://完成任务
                if ($UserDB['complete_task']>=LEVEL_ADD_COMPLETE_TASK)
                {
                    $UserDB['complete_task'] = $UserDB['complete_task'] - LEVEL_ADD_COMPLETE_TASK;
                    $AddExp = LEVEL_ADD_COMPLETE_TASK;
                    self::add_exp_log($uid,'完成任务',LEVEL_ADD_COMPLETE_TASK);
                }
                break;
            case LEVEL_LIKE://被关注
                
                if (self::has_guanzhu($uid, $other_uid)) {
                    return ; // xieye 2016 12 徐慢慢，关注过就别加经验值了。
                }
                
                
                if ($UserDB['other_user_like']>=LEVEL_ADD_LIKE)
                {
                    $UserDB['other_user_like'] = $UserDB['other_user_like'] - LEVEL_ADD_LIKE;
                    $AddExp = LEVEL_ADD_LIKE;
                    self::add_exp_log($uid,'被关注',LEVEL_ADD_LIKE,$other_uid);
                }
                break;
            case LEVEL_SHOW_LIVE_COURSE://点播课程
                if ($UserDB['show_live_course']>=LEVEL_ADD_SHOW_LIVE_COURSE)
                {
                    $UserDB['show_live_course'] = $UserDB['show_live_course'] - LEVEL_ADD_SHOW_LIVE_COURSE;
                    $AddExp = LEVEL_ADD_SHOW_LIVE_COURSE;
                    self::add_exp_log($uid,'点播课程',LEVEL_ADD_SHOW_LIVE_COURSE);
                }
                break;
            case LEVEL_SHOP://商城购买/兑换
//                 if ($UserDB['shop']>=LEVEL_ADD_SHOP)
//                 {
//                     $UserDB['shop'] = $UserDB['shop'] - LEVEL_ADD_SHOP;
                    $AddExp = LEVEL_ADD_SHOP;
                    self::add_exp_log($uid,'商城购买',LEVEL_ADD_SHOP);
//                 }
                break;
        }
        if ($AddExp>0)
        {
            $UserDB['exp']+=$AddExp;
            $LevelDB = self::get_level_config($UserDB['level']);
            if ($UserDB['exp']>=$LevelDB)
            {
                //用户升级了
                $UserDB['level'] = $UserDB['level'] + 1;
                $UserDB['exp'] = $UserDB['exp'] - $LevelDB;
//                 $ContentDB = array();
//                 $ContentDB = BBMessage::AddMsg($ContentDB,'等级升级：恭喜你升至LV'.$UserDB['level'].'获得怪兽蛋一枚，请进入个人中心->怪兽岛查看');
//                 BBMessage::SendMsg(\BBExtend\fix\Message::PUSH_MSG_LEVEL_UP,'系统消息',$ContentDB,$uid);
                
                Message::get_instance()
                    ->set_title('系统消息')
                    ->add_content(Message::simple()->content("恭喜你升至"))
                    ->add_content(Message::simple()->content("LV{$UserDB['level']}")->color(0xf4a560)  )
                    ->add_content(Message::simple()->content('，请进入'))
                    ->add_content(Message::simple()->content('个人中心')->color(0x32c9c9)  
                            ->url(json_encode(['type'=>1,  ]) )
                            )
                    ->add_content(Message::simple()->content('查看。'))
                    ->set_type(125)
                    ->set_uid($uid)
                    ->send();
                
                
                self::add_currency($uid,CURRENCY_MONSTER,1,'用户升级');
                
            }
            Db::table('bb_users_exp')->where('uid',$uid)->update($UserDB);
            BBRedis::getInstance('user')->hMset($uid.'level',$UserDB);
            
            // xieye 2016 10 25 这里是写统计排名的地方。
            \BBExtend\user\Ranking::getinstance($uid)->set_dengji_ranking();
            
            
            return true;
        }
        return false;
    }
    
    public static function has_guanzhu($target_uid, $uid)
    {
        $db = Sys::get_container_db();
        $target_uid=intval($target_uid);
        $uid = intval($uid);
        $sql ="select count(*) from bb_users_exp_log 
                where uid = {$target_uid}
                  and who_uid = {$uid}
                  and type='被关注'
                ";
        return $db->fetchOne($sql);
    }
    
    private static function add_exp_log($uid,$type,$exp,$who_uid = 0)
    {
        $time = time();
        $LogDB = array();
        $LogDB['uid'] = $uid;
        $LogDB['type'] = $type;
        $LogDB['exp'] = $exp;
        $LogDB['who_uid'] = $who_uid;
        $LogDB['time'] = $time;
        $LogDB['create_time'] = $time;
        $LogDB['datestr'] = date("Ymd");
        Db::table('bb_users_exp_log')->insert($LogDB);
    }
    
}