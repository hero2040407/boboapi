<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;
use app\api\controller\Boboapi;
use BBExtend\BBUser;
use think\Db;
use app\monster\controller\Monsterapi;
use app\task\controller\Taskapi;
use app\record\controller\Recordmanager;
use app\push\controller\Pushmanager;
use BBExtend\Focus;
use BBExtend\BBRedis;
use BBExtend\PushMsg;
use think\Request;

//登录类型 1： 微信 2：QQ  3：手机 4：微博
define('LOGIN_TYPE_WEIXIN',1);
define('LOGIN_TYPE_QQ',2);
define('LOGIN_TYPE_PHONE',3);
define('LOGIN_TYPE_WEIBO',4);
class Userfalse extends BBUser
{
    //设备服务器登录验证
    public function phone_login()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
        if (!self::validation_token($uid,$userlogin_token))
        {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        Db::table('bb_users')->where('uid',$uid)->update(['login_time'=>time(),'is_online'=>1]);
        BBRedis::getInstance('user')->hSet($uid,'login_time',time());
        
        //检查结束
        return ['code'=>\app\user\model\Exists::userhExists($uid)];
    }
//开放平台登录
    public function register(){
        $platform_id = input('?param.platform_id')?(string)input('param.platform_id'):'';
        $nickname = input('?param.nickname')?(string)input('param.nickname'):'未知的火星人';
        $login_type = input('?param.login_type')?(int)input('param.login_type'):0;
        
        $platform_id = 'xx' . mt_rand(100000, 999999) ;
        $login_type = 3;
        
        $pic = input('?param.pic')?(string)input('param.pic'):'';
        $login_address = input('?param.login_address')?(string)input('param.login_address'):'未设定';
        $device  = input('?param.device')?(string)input('param.device'):'';
        $User_Agent = Request::instance()->header('User-Agent');
        if (!$platform_id||!$login_type){
            return ['code'=>-1,'message'=>'第三方平台重要信息不完整!'];
        }
        $user_platform = md5($platform_id);
        $PlatformDB = null;
        switch ($login_type)
        {
//             case LOGIN_TYPE_WEIXIN:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_WEIXIN])->find();
//                 break;
//             case LOGIN_TYPE_QQ:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_QQ])->find();
//                 break;
//             case LOGIN_TYPE_PHONE:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_PHONE])->find();
//                 break;
//             case LOGIN_TYPE_WEIBO:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_WEIBO])->find();
//                 break;
        }

    //    if ($PlatformDB)
        if (false)
        {
            $UserDB = Db::table('bb_users')->where('uid',$PlatformDB['uid'])->find();
            if ($UserDB)
            {
                $UserDB['userlogin_token'] = self::userlogin_token($user_platform);
                Db::table('bb_users')->where('uid',$UserDB['uid'])->update(['login_type'=>$PlatformDB['type'],'userlogin_token'=>$UserDB['userlogin_token'],'is_online'=>1,'login_count'=>$UserDB['login_count']++,'login_time'=>time()] );
            }
        }else
        {
            //第一次登录需要注册
            $UserDB = self::registered($user_platform,$nickname,$device,$login_type,$login_address,$pic,$platform_id);
            //先写到缓存中
            BBRedis::getInstance('user')->hMset($UserDB['uid'],$UserDB);

            // 谢烨 2016 10 17
            $log = new \app\pay\model\Alitemp();
            $log->data('url', 'register');
            $log->data('content', json_encode(['uid'=>$UserDB['uid'], 
                'platform_id'=>$platform_id,
                'user_platform'=> $user_platform,
                'user_db' => $UserDB,
            ]) );
            $log->data('create_time',date("Y:m:d H-i-s"));
            $log->save();
            
            
            $UserDB['currency'] = self::get_currency($UserDB['uid']);
            $UserDB['age']=date('Y') - substr($UserDB['birthday'],0,4);
            //xieye count
            $UserDB['user_count'] = Db::table('bb_users')->count();
            $pic =  $UserDB['pic'];
            //如果没有http://
            if ( !(strpos($pic, 'http://') !== false) )
            {
                $ServerURL = \BBExtend\common\BBConfig::get_server_url();
                $UserDB['pic'] =$ServerURL.$pic;
            }
            
            //xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
            $temp = \BBExtend\Level::get_user_exp($UserDB['uid']);
            
            $UserDB = self::conversion($UserDB);
            return ['data'=>$UserDB,'monsterinfo'=>Monsterapi::get_new_monster($UserDB['uid']),'code'=>1];
        }
        $info = Request::instance()->header();
        if ($info)
        {
            $User_Agent = Request::instance()->header('User-Agent');
            if ($User_Agent)
            {
                $UserDB['user_agent'] = $User_Agent;
            }
        }
        $UserDB['login_count'] = $UserDB['login_count']+1;
        // xieye count
        $UserDB['monster_count'] = Db::table('bb_monster_data')->where('uid',$UserDB['uid'])->count();
        
        BBRedis::getInstance('user')->hMset($UserDB['uid'],$UserDB);
        self::update($UserDB);
        $UserDB['currency'] = self::get_currency($UserDB['uid']);
        $UserDB['age']=date('Y') - substr($UserDB['birthday'],0,4);
        $pic =  $UserDB['pic'];
        if ( !(strpos($pic, 'http://') !== false) )
        {
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            $UserDB['pic'] =$ServerURL.$pic;
        }
        
        // 谢烨 2016 排名重新搞。
        //对目标用户修改排名
        $UserDB['ranking'] =  \BBExtend\user\Ranking::getinstance($UserDB['uid'])->get_caifu_ranking();
        
        $UserDB = self::conversion($UserDB);
        return ['data'=>$UserDB,'monsterinfo'=>Monsterapi::get_monster_list($UserDB['uid']),'code'=>1];
    }
    
    
    
    
    
    //开放平台登录
    public function login(){
        $platform_id = input('?param.platform_id')?(string)input('param.platform_id'):'';
        $nickname = input('?param.nickname')?(string)input('param.nickname'):'未知的火星人';
        $login_type = input('?param.login_type')?(int)input('param.login_type'):0;
        $pic = input('?param.pic')?(string)input('param.pic'):'';
        $login_address = input('?param.login_address')?(string)input('param.login_address'):'未设定';
        $device  = input('?param.device')?(string)input('param.device'):'';
        $User_Agent = Request::instance()->header('User-Agent');
        
        $login_type=3;
        
        if (!$platform_id||!$login_type){
            return ['code'=>-1,'message'=>'第三方平台重要信息不完整!'];
        }
        $user_platform = md5($platform_id);
//         $PlatformDB = null;
//         switch ($login_type)
//         {
//             case LOGIN_TYPE_WEIXIN:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_WEIXIN])->find();
//                 break;
//             case LOGIN_TYPE_QQ:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_QQ])->find();
//                 break;
//             case LOGIN_TYPE_PHONE:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_PHONE])->find();
//                 break;
//             case LOGIN_TYPE_WEIBO:
//                 $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>$user_platform,'type'=>LOGIN_TYPE_WEIBO])->find();
//                 break;
//         }
    
        if (1)
        {
            $UserDB = Db::table('bb_users')->where('uid',$platform_id)->find();
            if ($UserDB)
            {
                $UserDB['userlogin_token'] = self::userlogin_token($user_platform);
                Db::table('bb_users')->where('uid',$UserDB['uid'])->update(['login_type'=>$PlatformDB['type'],'userlogin_token'=>$UserDB['userlogin_token'],'is_online'=>1,'login_count'=>$UserDB['login_count']++,'login_time'=>time()] );
            }
        }else
        {
            return ['code'=>0];
//             //第一次登录需要注册
//             $UserDB = self::registered($user_platform,$nickname,$device,$login_type,$login_address,$pic,$platform_id);
//             //先写到缓存中
//             BBRedis::getInstance('user')->hMset($UserDB['uid'],$UserDB);
    
//             // 谢烨 2016 10 17
//             $log = new \app\pay\model\Alitemp();
//             $log->data('url', 'register');
//             $log->data('content', json_encode(['uid'=>$UserDB['uid'],
//                 'platform_id'=>$platform_id,
//                 'user_platform'=> $user_platform,
//                 'user_db' => $UserDB,
//             ]) );
//             $log->data('create_time',date("Y:m:d H-i-s"));
//             $log->save();
    
    
//             $UserDB['currency'] = self::get_currency($UserDB['uid']);
//             $UserDB['age']=date('Y') - substr($UserDB['birthday'],0,4);
//             //xieye count
//             $UserDB['user_count'] = Db::table('bb_users')->count();
//             $pic =  $UserDB['pic'];
//             //如果没有http://
//             if ( !(strpos($pic, 'http://') !== false) )
//             {
//                 $ServerURL = \BBExtend\common\BBConfig::get_server_url();
//                 $UserDB['pic'] =$ServerURL.$pic;
//             }
    
//             //xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
//             $temp = \BBExtend\Level::get_user_exp($UserDB['uid']);
    
//             $UserDB = self::conversion($UserDB);
//             return ['data'=>$UserDB,'monsterinfo'=>Monsterapi::get_new_monster($UserDB['uid']),'code'=>1];
        }
        $info = Request::instance()->header();
        if ($info)
        {
            $User_Agent = Request::instance()->header('User-Agent');
            if ($User_Agent)
            {
                $UserDB['user_agent'] = $User_Agent;
            }
        }
        $UserDB['login_count'] = $UserDB['login_count']+1;
        // xieye count
        $UserDB['monster_count'] = Db::table('bb_monster_data')->where('uid',$UserDB['uid'])->count();
    
        BBRedis::getInstance('user')->hMset($UserDB['uid'],$UserDB);
        self::update($UserDB);
        $UserDB['currency'] = self::get_currency($UserDB['uid']);
        $UserDB['age']=date('Y') - substr($UserDB['birthday'],0,4);
        $pic =  $UserDB['pic'];
        if ( !(strpos($pic, 'http://') !== false) )
        {
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            $UserDB['pic'] =$ServerURL.$pic;
        }
    
        // 谢烨 2016 排名重新搞。
        //对目标用户修改排名
        $UserDB['ranking'] =  \BBExtend\user\Ranking::getinstance($UserDB['uid'])->get_caifu_ranking();
    
        $UserDB = self::conversion($UserDB);
        return ['data'=>$UserDB,'monsterinfo'=>Monsterapi::get_monster_list($UserDB['uid']),'code'=>1];
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    
}