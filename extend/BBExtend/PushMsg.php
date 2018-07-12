<?php

/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/22
 * Time: 16:21
 */
namespace BBExtend;
use think\Db;
use BBExtend\Sys;
use BBExtend\BBUser;
use BBExtend\message\Message;

require_once(dirname(__FILE__) . '/../umeng/UmengPush.php');


class PushMsg
{
    public static function Registered_Token($uid,$tokens)
    {
        $UMengDB = Db::table('bb_umeng_push_msg')->where('uid',$uid)->find();
        if ($UMengDB)
        {
            Db::table('bb_umeng_push_msg')->where('uid',$uid)->update(['token'=>$tokens]);
        }else
        {
            Db::table('bb_umeng_push_msg')->insert(['uid'=>$uid,'token'=>$tokens]);
        }
        
        // 这里，如果用户是今天注册的，且一条消息都没有，则
//         $db = Sys::get_container_db();
//         $user = BBUser::get_user($uid);
//         $time = \BBExtend\common\Date::pre_day_start(0);
//         if ($user && $user['register_time'] > $time ) {
//             //假如 用户的消息表数量为0
//             $count = Db::table('bb_msg')->where('uid', $uid)->count();
//             if (!$count) {
//                 $nickname = ($user['nickname']=='未知的火星人')?"小朋友":$user['nickname'];
//                 Message::get_instance()
//                     ->set_title('系统消息')
//                     ->add_content(Message::simple()->content($nickname)->color(0x32c9c9))
//                     ->add_content(Message::simple()->content('欢迎您加入怪兽BOBO,在这里每个孩子'.
//                             '都是大明星，请共同维护怪兽岛绿色直播宣言——'))
//                     ->add_content(Message::simple()->content('BOBO童心梦，传递正能量')->color(0xf4a560))
//                     ->add_content(Message::simple()->content("。"))
//                     ->set_type(110)
//                     ->set_uid($uid)
//                     ->send();
//             }
//         }
    }
    
    /**
     * 系统公告
     */
    public static function broadcast($title='公告', $info)
    {
        $Umpush = new \UmengPush();
        $Umpush->sendIOSBroadcast($title,$info);
    }
    
    
    /**
     * 推送 单个消息
     * 
     * @param unknown $uid
     * @param unknown $event
     * @param unknown $info
     */
    public static function unicast()
    {
        
    }
    
    
    public static function Push_message($uid,$event,$info = null)
    {
        $Umpush = new \UmengPush();
        $UMDB = Db::table('bb_umeng_push_msg')->where('uid',$uid)->find();
        $tokens = 0;
        if ($UMDB)
        {
            $tokens = $UMDB['token'];
        }
        if (!$tokens)
        {
            return;
        }

        switch ($event)
        {
            case \BBExtend\fix\Message::PUSH_MSG_MESSAGE:
                $Umpush->sendIOSBroadcast('公告',$info);
                break;
            case \BBExtend\fix\Message::PUSH_MSG_ADMIN_MESSAGE:
                $Umpush->sendIOSCustomizedcast($uid,$tokens,$event,'公告',$info);
                break;
            case \BBExtend\fix\Message::PUSH_MSG_LEVEL_UP:
                $Umpush->sendIOSCustomizedcast($uid,$tokens,$event,'恭喜您升级了,获得了一个宠物蛋,请点击领取吧!');
                break;
            case \BBExtend\fix\Message::PUSH_MSG_NEW_MONSTER:
                $Umpush->sendIOSCustomizedcast($uid,$tokens,$event,'恭喜您获得了一个宠物蛋,请点击领取吧');
                break;
            case \BBExtend\fix\Message::PUSH_MSG_TASK:
                $Umpush->sendIOSCustomizedcast($uid,$tokens,$event,'恭喜您完成了一个任务');
                break;
            case \BBExtend\fix\Message::PUSH_MSG_BUY:
                $Umpush->sendIOSCustomizedcast($uid,$tokens,$event,'充值成功');
                break;
            case \BBExtend\fix\Message::PUSH_MSG_CHONGFU_LOGIN:
                $Umpush->sendIOSCustomizedcast($uid,$tokens,$event,'您的帐号已在其他设备登录');
                break;
                
        }
    }

   
}