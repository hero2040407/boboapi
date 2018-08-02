<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;

use think\Request;
use think\Db;
 
use BBExtend\Sys;
use BBExtend\Focus;
use BBExtend\BBRedis;
use BBExtend\PushMsg;
use BBExtend\BBRecord;
use BBExtend\Currency;
use BBExtend\message\Message;
use BBExtend\BBUser;
use think\Config;
use think\Cookie;

use BBExtend\user\TaskManager;
use BBExtend\user\Ranking;
use BBExtend\user\Tongji;
use BBExtend\user\exp\Exp;
use BBExtend\common\Client;
use BBExtend\fix\TableType;
use BBExtend\fix\MessageType;
use BBExtend\fix\Err;

use BBExtend\common\Oss;
use BBExtend\DbSelect;

//登录类型 1： 微信 2：QQ  3：手机 4：微博   5:机器人
define('LOGIN_TYPE_WEIXIN',1);
define('LOGIN_TYPE_QQ',2);
define('LOGIN_TYPE_PHONE',3);
define('LOGIN_TYPE_WEIBO',4);
define('LOGIN_TYPE_ROBOT',5);

class User extends BBUser
{
    /**
     * nodejs 使用接口，用户上线。
     */
    public function phone_login()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $ip = input('?param.ip')?input('param.ip'):'';
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
        
        if (!self::validation_token($uid,$userlogin_token))
        {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        Db::table('bb_users')->where('uid',$uid)->update(['login_time'=>time(),'is_online'=>1,'email'=> $ip ]);
        BBRedis::getInstance('user')->hSet($uid,'login_time',time());
        BBRedis::getInstance('user')->hSet($uid,'is_online',1);
        
        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_LOGIN)->add_exp();
        Tongji::getinstance($uid)->login();
        
        $db = Sys::get_container_db_eloquent();
        $db::table('bb_push')->where( 'uid', $uid )->where('price_type',2)->update([
                'event' =>'publish_done',
                'price_type' => 1,
        ]);
        
        //检查结束
        return ['code'=>\app\user\model\Exists::userhExists($uid)];
    }
    
    
    /**
     * 注册机器人用接口，
     * login_type = 5 
     * @return number[]|string[]
     */
    public function res_users()
    {
        $start_id = input('?param.start_id')?(int)input('param.start_id'):'90000000';
        $User_List = Db::table('bb_users_jqr')->order()->select();
        $node_service = Sys::get_container_node();
        foreach ($User_List as $UserDB)
        {
            $node_service->http_Request('http://127.0.0.1/user/user/otherlogin',
                ['platform_id'=>$start_id,'nickname'=>$UserDB['nickname'],'pic'=>$UserDB['pic'],
                    'login_type'=>LOGIN_TYPE_ROBOT]);
            echo "platform_id: ".$start_id." ok\n";
            
            $start_id = $start_id + 1;
            Db::table('bb_users_jqr')->where(['id'=>$UserDB['id']])->delete();
        }
        return ['code'=>1,'message'=>'注册成功'];
    }

    
    //整理渠道函数
    public function agent()
    {
        $user = Db::table('bb_users_qudao')->select();

        $arr='';
        if ($user) {
            foreach ($user as $val) {
                $mac= ' (MacAddress:'.(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).')';
                $reg_month=rand(1,5);
                switch ($reg_month){
                    case 1:
                        $bobov = '(BoBo)/(1.'.rand(3,4).'.'.rand(1,3).') ';
                        break;
                    case 2:
                        $bobov = '(BoBo)/(2.'.rand(0,1).'.'.rand(0,4).') ';
                        break;
                    case 3:
                        $bobov = '(BoBo)/(3.0.'.rand(0,2).') ';
                        break;
                    case 4:
                        $bobov = '(BoBo)/(3.0.'.rand(3,4).') ';
                        break;
                    case 5:
                        $bobov = '(BoBo)/(3.1.'.rand(0,3).') ';
                        break;
                }

            }
        }
    }


    /**
     * 注册其他用户，
     * permissions = 10 or 11
     */
    public function reg_other_users()
    {exit;
        if(file_get_contents('./state.txt')=='0')exit;
        file_put_contents('./state.txt', '0');
        $user = Db::table('bb_users_others')->order('rand()')->limit(0,200)->select();
        if($user){
            $del_arr= array();
            foreach ($user as $val){
                $del_arr[]=$val['id'];
            }
            Db::table('bb_users_others')->delete($del_arr);
            file_put_contents('./state.txt','1');
            foreach ($user as $val){
                $num = date('s',time())>39? date('s',time())-30 :date('s',time());
                $pic = Db::table('bb_users_jqr')->where(['id'=>rand($num*100+1,$num*100+100)])->find()['pic'];
                $val['phone'] = substr($val['phone'],0,3).substr($val['phone'],7,4).substr($val['phone'],3,4);
                $user_phone = Db::table('bb_users')->where(['phone'=>$val['phone']])->find();
                $address ='未设定';
                if(!$user_phone && strlen($val['phone'])==11){
                        if(strpos($val['province'],'北京') !==false){
                            $val['province'] = '北京市';
                            if(strpos($val['city'],'区') >0)$address = $val['province'].' '.
                                substr($val['city'],0,strpos($val['city'],'区')+3);
                            if(strpos($val['city'],'县') > 0)$address = $val['province'].' '.
                                substr($val['city'],0,strpos($val['city'],'县')+3);
                            if($val['city'] == '' || strpos($val['city'],'区') === false || 
                                strpos($val['city'],'县') === false )
                                $address = $val['province'].' 北京市';
                        }else if(strpos($val['province'],'上海') !==false){
                            $val['province'] = '上海市';
                            if(strpos($val['city'],'区') >0)$address = $val['province'].' '.
                                substr($val['city'],0,strpos($val['city'],'区')+3);
                            if(strpos($val['city'],'县')> 0)$address = $val['province'].' '.
                                substr($val['city'],0,strpos($val['city'],'县')+3);
                            if($val['city'] == '' || strpos($val['city'],'区') === false || 
                                strpos($val['city'],'县') === false )
                                $address = $val['province'].' 上海市';
                        }else{
                            if($val['province'] != '' && $val['city'] != ''){
                                $val['province'] =  strpos($val['province'],'省')> 0 ? $val['province']:$val['province'].'省';
                                if(strpos($val['city'],'市') >0)
                                    $address = $val['province'].' '. substr($val['city'],0,strpos($val['city'],'市')+3);
                                if(strpos($val['city'],'县') > 0)
                                    $address = $val['province'].' '. substr($val['city'],0,strpos($val['city'],'县')+3);
                                if($val['city'] == '' && strpos($val['city'],'市') === false &&
                                    strpos($val['city'],'县') === false )
                                    $address = $val['province'].' ';
                            }
                        }

                    $month = ['01','02','03','04','05','06','07','08','09','10','11','12'];
                    $user_agent = Db::table('bb_users_qudao')->where(['id'=>rand(1,4092)])->find();
                    $mac= ' (MacAddress:'.(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                        .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).')';
                    $reg_month=rand(1,5);
                    switch ($reg_month){
                        case 1:
                            $bobov = '(BoBo)/(1.'.rand(3,4).'.'.rand(1,3).') ';
                            break;
                        case 2:
                            $bobov = '(BoBo)/(2.'.rand(0,1).'.'.rand(0,4).') ';
                            break;
                        case 3:
                            $bobov = '(BoBo)/(3.0.'.rand(0,2).') ';
                            break;
                        case 4:
                            $bobov = '(BoBo)/(3.0.'.rand(3,4).') ';
                            break;
                        case 5:
                            $bobov = '(BoBo)/(3.1.'.rand(0,3).') ';
                            break;
                        default :
                            $bobov = '(BoBo)/(3.1.'.rand(0,3).') ';
                    }

                    $uid = BBUser::get_new_uid();
                    $infodata = [
                        'uid' =>$uid,
                        'nickname' => substr($val['nickname'],0,6).mt_rand(10,999),
                        'pic'=>$pic,
                        'phone' => $val['phone'],
                        'device' => '',
                        'email' => '',
                        'sex' => rand(0,1) ==0?0:rand(0,1),
                        'login_type' => 3,
                        'login_time' => time()-rand(1000,90000),
                        'address' => $address,
                        'login_count'=>1,
                        'register_time' => strtotime('2017-'.$reg_month.'-'.rand(1,28).' '.
                                rand(9,22).':'.rand(0,59).':'.rand(0,59)),
                        'userlogin_token' => md5($val['nickname'].time()),
                        'birthday'=>rand(2001,2015).'-'.$month[rand(0,11)],
                        'specialty'=>'',
                        'permissions'=>11,
                        'max_record_time'=>120,
                        'min_record_time'=>8,
                        'vip'=>0,
                        'vip_time'=>0,
                        'logout_time'=>0,
                        'sign_board'=>0,
                        'series_sign_max'=>0,
                        'series_sign'=>0,
                        'signature'=>'',
                        'attestation'=>0,
                        'ranking'=>10000,
                        'longitude' => $val['longitude'],
                        'latitude' => $val['latitude'],
                        'user_agent'=>$bobov.$user_agent['user_agent'].$mac,
                        'qudao'=>$user_agent['qudao'],
                    ];

                    Db::table('bb_users')->insert($infodata);
                    Db::table('bb_users_achievement')->insert(['uid'=>$uid]);
                    Db::table('bb_users_achievement_summary')->insert(['uid'=>$uid]);

                    \BBExtend\user\Common::register_log($uid,$user_agent['qudao']);

                    Db::table('bb_users_platform')->insert(['platform_id'=>md5($val['phone']),
                        'original' => $val['phone'],
                        'uid'=>$uid,'type'=>3]);

                    $bb_user_task = ['uid'=> $uid,'time'=> time(),'complete_task_group'=>'0',
                        'complete'=>'0,0,0','reward'=>'0,0,0','task_group'=>'1,2,3',
                        'refresh_time'=>strtotime(date('Ymd')) + 104400];

                    Db::table('bb_task_user')->insert($bb_user_task);
                    Currency::get_currency($uid);
                    \BBExtend\Level::get_user_exp($uid);
                }
            }
        }else{
            file_put_contents('./state.txt','1');
        }
        return ['code'=>1,'message'=>'注册成功'];
    }
    
    
    public function reg_one_users()
    {
        $time=time();
        $val = Db::table('bb_users_other')->order('rand()')->find();
        if($val){
            Db::table('bb_users_other')->delete($val['id']);
            $num = date('s',$time)>39? date('s',$time)-30 :date('s',$time);
            $pic = Db::table('bb_users_jqr')->where(['id'=>rand($num*100+1,$num*100+100)])->find()['pic'];
            $user_phone = Db::table('bb_users')->where(['phone'=>$val['phone']])->find();
            $address ='未设定';
            if(!$user_phone && strlen($val['phone'])==11){
                if(strpos($val['province'],'北京') !==false){
                    $val['province'] = '北京市';
                    if(strpos($val['city'],'区') >0)$address = $val['province'].' '.
                        substr($val['city'],0,strpos($val['city'],'区')+3);
                    if(strpos($val['city'],'县') > 0)$address = $val['province'].' '.
                        substr($val['city'],0,strpos($val['city'],'县')+3);
                    if($val['city'] == '' || strpos($val['city'],'区') === false || 
                        strpos($val['city'],'县') === false )$address = $val['province'].' 北京市';
                }else if(strpos($val['province'],'上海') !==false){
                    $val['province'] = '上海市';
                    if(strpos($val['city'],'区') >0)$address = $val['province'].' '.
                        substr($val['city'],0,strpos($val['city'],'区')+3);
                    if(strpos($val['city'],'县')> 0)$address = $val['province'].' '.
                        substr($val['city'],0,strpos($val['city'],'县')+3);
                    if($val['city'] == '' || strpos($val['city'],'区') === false || 
                        strpos($val['city'],'县') === false )$address = $val['province'].' 上海市';
                }else{
                    $val['province'] =  strpos($val['province'],'省')> 0 ?$val['province']:
                        $val['province'].'省';
                    if(strpos($val['city'],'市') >0)$address = $val['province'].' '.
                        substr($val['city'],0,strpos($val['city'],'市')+3);
                    if(strpos($val['city'],'县') > 0)$address = $val['province'].' '.
                        substr($val['city'],0,strpos($val['city'],'县')+3);
                    if($val['city'] == '' && strpos($val['city'],'市') === false && 
                        strpos($val['city'],'县') === false )
                        $address = $val['province'].' ';
                }

                $month = ['01','02','03','04','05','06','07','08','09','10','11','12'];
                $user_agent = Db::table('bb_users_qudao')->where(['id'=>rand(1,4092)])->find();
                $mac= ' (MacAddress:'.(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).':'
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9))
                    .(rand(0,1) ==0?chr(rand(97, 103)):rand(0,9)).')';
                $bobov = '(BoBo)/(3.1.4) ';
                $uid = BBUser::get_new_uid();
                $regtime =strtotime(date('Y-m-d',$time).' '.rand(9,date('H',$time)).':'.
                        rand(0,date('i',$time)).':'.rand(0,59));
                $infodata = [
                    'uid' =>$uid,
                    'nickname' => substr($val['nickname'],0,8),
                    'pic'=>$pic,
                    'phone' => $val['phone'],
                    'device' => '',
                    'email' => '',
                    'sex' => rand(0,1) ==0?0:rand(0,1),
                    'login_type' => 3,
                    'login_time' => $regtime+rand(1,10),
                    'address' => $address,
                    'login_count'=>1,
                    'register_time' => $regtime,
                    'userlogin_token' => md5($val['nickname'].$time),
                    'birthday'=>rand(2003,2012).'-'.$month[rand(0,11)],
                    'specialty'=>'',
                    'permissions'=>10,
                    'max_record_time'=>120,
                    'min_record_time'=>8,
                    'vip'=>0,
                    'vip_time'=>0,
                    'logout_time'=>0,
                    'sign_board'=>0,
                    'series_sign_max'=>0,
                    'series_sign'=>0,
                    'signature'=>'',
                    'attestation'=>0,
                    'ranking'=>10000,
                    'longitude' => $val['longitude'],
                    'latitude' => $val['latitude'],
                    'user_agent'=>$bobov.$user_agent['user_agent'].$mac,
                    'qudao'=>$user_agent['qudao'],
                ];

                Db::table('bb_users')->insert($infodata);

                \BBExtend\user\Common::register_log($uid,$user_agent['qudao']);

                Db::table('bb_users_platform')->insert(['platform_id'=>md5($val['phone']),
                    'original' => $val['phone'],
                    'uid'=>$uid,'type'=>3]);

                $bb_user_task = ['uid'=> $uid,'time'=> time(),'complete_task_group'=>'0',
                    'complete'=>'0,0,0','reward'=>'0,0,0','task_group'=>'1,2,3',
                    'refresh_time'=>strtotime(date('Ymd')) + 104400];

                Db::table('bb_task_user')->insert($bb_user_task);
                Currency::get_currency($uid);
                \BBExtend\Level::get_user_exp($uid);
            }
        }
        return ['code'=>1,'message'=>'注册成功'];
    }
    
    
    /**
     * 获取机器人接口
     */
    public function get_robot_users()
    {
        $User_List = Db::table('bb_users')->where(['permissions'=>99])->select();
        $Data = array();
        foreach ($User_List as $UserDB) {
            $DB = array();
            $DB['uid'] = $UserDB['uid'];
            $DB['nickname'] = $UserDB['nickname'];
            $DB['pic'] = $UserDB['pic'];
            $DB['is_vip'] = $UserDB['vip'];
            array_push($Data,$DB);
        }
        return ['code'=>1,'data'=>$Data];
    }

    
    /**
     * 用户注册和登录
     * 
     * 逻辑:帐号存在，则登录，否则注册后登录
     * 
     */
    public function otherlogin()
    {
        $platform_id = input('?param.platform_id')?(string)input('param.platform_id'):'';
        $nickname = input('?param.nickname')?(string)input('param.nickname'):'未知的火星人';
        $login_type = input('?param.login_type')?(int)input('param.login_type'):0;
        $pic = input('?param.pic')?(string)input('param.pic'):'';
        $login_address = input('?param.login_address')?(string)input('param.login_address'):'未设定';
        $device  = input('?param.device')?(string)input('param.device'):'';
        
        $unionid = input('?param.unionid')?(string)input('param.unionid'):'';
        
        if (!$platform_id||!$login_type){
            return ['code'=>-1,'message'=>'第三方平台重要信息不完整!'];
        }
        if (!in_array($login_type, [
            TableType::bb_users__login_type_weixin,
            TableType::bb_users__login_type_qq,
            TableType::bb_users__login_type_shouji,
            TableType::bb_users__login_type_weibo,
            TableType::bb_users__login_type_jiqiren,
        ])) {
            return ['code'=>0,'message'=>'login_type错误'];
        }
//         Sys::debugxieye('-----');
//      Sys::debugxieye($platform_id);
//      Sys::debugxieye($unionid);
//      Sys::debugxieye('-----');
        if ( $login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin && $unionid ){
            $db = Sys::get_container_db();
            $sql="select * from bb_users where unionid=? 
and exists(
 select 1 from bb_users_platform
  where bb_users_platform.uid = bb_users.uid
    and bb_users_platform.type =1

)

limit 1";
            $row = $db->fetchRow($sql,[ $unionid ]);
            if ($row ) {
                // 微信方式登录
                return $this->otherlogin_login($row['uid'], $login_type, $platform_id,$unionid);
                
            }
            
        }
     
        $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>md5($platform_id),
            'type'=>$login_type ])->find();
        if (!$PlatformDB) { // 不存在平台表，说明需要注册
            return $this->otherlogin_register($platform_id, $nickname, $device, $login_type, 
                    $login_address, $pic,$unionid);
        }
        // 否则走登录流程
        $uid = $PlatformDB['uid'];
        return $this->otherlogin_login($uid, $login_type, $platform_id,$unionid);
    }
    
    
    /**
     * 注册流程
     */
    private function otherlogin_register( $platform_id, $nickname, $device, $login_type,
            $login_address,  $pic, $unionid)
    {
        //注册必须查敏感词。
        $db = Sys::get_container_db();
        $sql ="select * from bb_minganci where name =?";
        $result = $db->fetchRow($sql, trim($nickname));
        if ($result) {
            return ['code'=>0,'message'=>'您的昵称不合适'];
        }
        $UserDB = BBUser::registered($nickname,$device,$login_type,
                $login_address,$pic,$platform_id, $unionid);
        $uid = $UserDB['uid'];
        $UserDB['currency'] = Currency::get_currency($uid);
    
        $obj = \app\user\model\UserModel::getinstance($uid);
        $UserDB['age'] = $obj->get_userage();
        $UserDB['pic'] = $obj->get_userpic();
        $UserDB['user_count'] = Db::table('bb_users')->count();
    
        //xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
        \BBExtend\Level::get_user_exp($uid);
        
        // 谢烨，新功能。新用户注册，自动关注10000号用户，只在正式服。
        if (Sys::is_product_server()) {
            $help = \BBExtend\user\Focus::getinstance($uid);
            $help->focus_guy(10000) ;
        }
        // 系统消息 ， 20161110，
        $nickname = ($nickname=='未知的火星人')?"小朋友":$nickname;
        Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content($nickname)->color(0x32c9c9))
            ->add_content(Message::simple()->content('欢迎您加入怪兽BOBO,在这里每个孩子'.
                '都是大明星，请共同维护怪兽岛绿色直播宣言——'))
            ->add_content(Message::simple()->content('BOBO童心梦，传递正能量')->color(0xf4a560))
            ->add_content(Message::simple()->content("。"))
            ->set_type(MessageType::register)
            ->set_uid($uid)
            ->send();
        $UserDB['unread_count'] =1;
        $UserDB = self::conversion($UserDB); // 强制转换，和登录一样。
        $bonus = BBUser::regis_additional($uid); // 注册有一个额外流程，必须走。
        return ['data'=>$UserDB,'bonus'=> $bonus['result_bonus'], 
             'lottery' => $bonus['result_lottery'],  'monsterinfo'=>[],'code'=>1];
    }
    
    
    private function has_deny($uid){
        
        $agent = \BBExtend\common\Client::user_agent();
        if ( preg_match('#3c:b6:b7:58:3d:86#', $agent) ) {
            return true;
        }
        return false;
    }
    
    /**
     * 登录流程
     */
    private function otherlogin_login($uid, $login_type, $platform_id ,$unionid)
    {
//         Db::table('bb_alitemp')->insert( 
//                 [
//                         'create_time' => date("Y-m-d H:i:s",time()),
//                         'url' =>'login',
//                         'content' => $platform_id,
//                         'test1' => $login_type,
//                 ]
//                 );
        
        
        $UserDB = Db::table('bb_users')->where('uid', $uid )->find();
        // 201708 查询禁止用户登录
        if ($UserDB['not_login']!=0) {
            return ['code'=>Err::code_not_login,'message'=>'您已被禁止登录'];
        }
        if ($this->has_deny($uid)) {
            return ['code'=>Err::code_not_login,'message'=>'您已被禁止登录'];
        }
        
        
        // 更新token
        $UserDB['userlogin_token'] = BBUser::userlogin_token(md5($platform_id));
        $UserDB['user_agent'] = Client::user_agent();
        $UserDB['login_count'] = $UserDB['login_count']+1;
        // 存入表里
        Db::table('bb_users')->where('uid',$uid)->update( [
            'login_type'      => $login_type,
            'userlogin_token' => $UserDB['userlogin_token'],
            'is_online'       => 1,
            'login_count'     => $UserDB['login_count'],
            'login_time'      => time(),
            'user_agent'      => $UserDB['user_agent'],
                'email' => \BBExtend\common\Client::ip(),
        ] );
        
        if ($UserDB['permissions'] > 5 && $UserDB['permissions'] !=99) {
            Db::table('bb_users')->where('uid',$uid)->update( [
                    'permissions'      => 1,
            ] );
        }
        
        if ( $login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin ){
            Db::table('bb_users')->where('uid',$uid)->update( [
                    'openid'      => $platform_id,
                    'unionid'     =>$unionid,
            ] );
        }
         
        
        $UserDB['monster_count'] = 0;
        $UserDB['currency'] = Currency::get_currency($uid);
        
        $obj = \app\user\model\UserModel::getinstance($uid);
        $UserDB['age'] = $obj->get_userage();
        $UserDB['pic'] = $obj->get_userpic();
        $UserDB['ranking'] =  0;//  暂时这样
        $UserDB['unread_count'] = Db::table('bb_msg')->where("uid",$uid )
            ->where('is_read',0)->count();
        $UserDB = self::conversion($UserDB); // xieye 2016 11 ,这句话必须最后！
        
        
        
        \BBExtend\user\Tongji::getinstance($uid)->otherlogin();
        
//         if ($uid==10041 || $uid==10023 || $uid==11136||$uid==10010) {
//             $tt= 10000;
//             $invite_user = \app\user\model\UserModel::getinstance($tt);
//             $result_bonus=[
//                 'version' =>1,
//                 'invite_user' =>['uid'=>$tt, 'head' =>$invite_user->get_userpic(),
//                     'nickname' => $invite_user->get_nickname(),
//                 ],
//                 'list' =>[
//                     ['word' =>' 500 BO币',
//                         'pic' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(
//                                 '/public/pic/present/img_bobi@2x.png'),],
//                     ['word' =>' 5200 BO币',
//                     'pic' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(
//                             '/public/pic/present/img_bobi@2x.png'),],
//                     ['word' =>' 5100 BO币',
//                     'pic' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(
//                             '/public/pic/present/img_bobi@2x.png'),],
//                 ],
//             ];
            
//             $result_lottery = ['open_lottery' =>1,
//                 'url'=>\BBExtend\common\BBConfig::get_server_url_https().
//                 "/game/lottery/store_index/uid/{$uid}/userlogin_token/".$UserDB['userlogin_token'],
            
//                 ];
            
//           return ['data'=>$UserDB,'bonus'=>$result_bonus,'lottery'=>$result_lottery,  
//               'monsterinfo'=>[],'code'=>1];
//         }
        return ['data'=>$UserDB,'bonus'=>null,'lottery'=>null, 'monsterinfo'=>[],'code'=>1];
        
    }
    
    
    
    /**
     * vip购买续费

     * 谢烨 20161008，加type参数
     */
    public function pay_vip($uid, $type=1)
    {
        $uid = intval($uid);
        $is_uid = \app\user\model\Exists::userhExists($uid);
        if ($is_uid!=1)
        {
            return ['message'=>'没有这个用户','code'=>$is_uid];
        }
        return \BBExtend\pay\UserPay::buy_vip($uid, $type);
    }
    
    
    /**
     * 退出登录
     * @return number[]
     */
    public function logout()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):"";
        Db::table('bb_users')->where('uid',$uid)->update(['logout_time'=>time(),'is_online'=>0]);
        BBRedis::getInstance('user')->hSet($uid,'logout_time',time());
        BBRedis::getInstance('user')->hSet($uid,'is_online',0);
        Tongji::getinstance($uid)->logout();
        return ['code'=>1];
    }

    
    private function _set_bb_userspeciality($uid,$speciality_list) 
    {
        if (!$speciality_list) {
            return ["code"=>0];
        }
        $temp = json_decode($speciality_list,true);
        $temp2 =[];
        foreach ($temp as $v) {
            $temp2[]= intval($v);
        }
        $speciality_list = json_encode($temp2);
        
        if (\app\user\model\Exists::userhExists($uid))
        {
            // 谢烨2017 02改，勿删
            $db = Sys::get_container_db();
            $sql="delete from bb_user_hobby where uid = {$uid}";
            $db->query($sql);
            foreach ($temp2 as $hobby_id) {
                $db->insert("bb_user_hobby", [
                    'uid' => $uid,
                    'hobby_id' => $hobby_id,
                    'create_time' => time(),
                ]);
            }
            BBRedis::getInstance('user')->hSet($uid,'specialty',$speciality_list);
            Db::table('bb_users')->where('uid',$uid)->update(['specialty'=>$speciality_list]);
            //完成完善资料任务
            TaskManager::getinstance($uid)->success_complete(1);
            return ['code'=>1];
        }else
        {
            return ['code'=>0];
        }
    }
    
    
    /**
     * 设置用户特长
     * 
     * 谢烨201702 新建了用户兴趣表bb_user_hobby，必须同时改。
     * @return number[]
     */
    public function set_bb_userspeciality() 
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $speciality_list = input('?param.speciality_list')?input('param.speciality_list'):'';
        return $this->_set_bb_userspeciality($uid, $speciality_list);
    }
    
    
    /**
     * 获取用户全部特长列表
     */
    public function get_bb_speciality() 
    {
        $speciality = Db::table('bb_speciality')->select();
        $SpecDB = array();
        foreach ($speciality as $DB)
        {
            $DB['id'] = (int)$DB['id'];
            array_push($SpecDB,$DB);
        }
        if(!$speciality){
            return ['code'=>-1,'message'=>'获取用户特长信息失败!'];
        }else{
            return ['data'=>$SpecDB,'code'=>1];
        }
    }
    
    
    /**
     * 返回用户基本信息
     */
    public function get_user_info()
    {
        $self_uid = input('?param.self_uid')?(int)input('param.self_uid'):0;
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):0;
        $UserDB = self::get_user($query_uid);
        if (!$UserDB)
        {
            return ['message'=>'没有这个用户~请检查是否有这个用户','code'=>0];
        }
        $UserDB['ranking'] = (string)$UserDB['ranking'];
        $UserDB['age']=date('Y') - substr($UserDB['birthday'],0,4);
        $UserDB['pic'] = self::get_userpic($query_uid);
        $UserDB['level'] = self::get_user_level($UserDB['uid']);
        $UserDB['next_exp'] = self::get_next_exp($UserDB['uid']);
        $UserDB['exp'] = self::get_user_exp($UserDB['uid']);
        $UserDB['specialty'] = self::get_specialty($query_uid);
        $UserDB['is_focus'] = Focus::get_focus_state($self_uid,$query_uid);
        $UserDB['focus_count'] = \BBExtend\user\Focus::getinstance($UserDB['uid'])
          ->get_fensi_count();
        if (isset( $UserDB['userlogin_token'] )){
            unset($UserDB['userlogin_token']);
        }
        return ['data'=>$UserDB,'code'=>1];
    }
    
    
    public function  get_falseinfo($uid=100){
        $random = mt_rand(100000,999999);
        $random = md5($random);
        $random = substr($random,0,13);
        
        $phone = mt_rand(10000000, 99999999);
        $phone = "139".$phone;
        $email = mt_rand(10000000, 99999999) ."@qq.com";
        $pic = "http://upload.guaishoubobo.com/uploads/headpic/{$random}.JPG";
        $data = [
           'uid' =>$uid,
                'platform_id'=>'',
                'nickname'=>'小朋友',
                'pic'=>$pic,
                'phone'=>$phone,
                'device'=>'',
                'address'=>'上海市',
                'login_type'=>'3',
                'login_time'=>time()-24*3600,
                'login_count'=>'2',
                'logout_time'=>'',
                'sex'=>'1',
                'email'=>$email,
                'birthday'=>'2007-10-10',
                'register_time'=>'',
                'signature'=>'',
                'level'=>'',
                'age'=>8,
                'gold'=>100,
                'constellation'=>'白羊座',
                'user_agent'=>"(BoBo)/(4.1.2) (android;7.0)/bobo (phone:MI 5) (MacAddress:02:00:00:00:00:00)",
                
        ];
        
        return [
            'code'=>1,
                'data'=>$data,
                
        ];
        
       
    }
    
    
    /**
     * 返回用户详细信息
     */
    public function get_userallinfo($token='')
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $user_agent =Config::get("http_head_user_agent");
        
        if ( preg_match('#python#i', $user_agent) ) {
            return $this->get_falseinfo($uid);
        }
        
        $redis = Sys::get_container_redis();
        $ip = Config::get("http_head_ip");
        $key ="limit:ip:{$ip}";
        if ($ip == '122.224.90.210' || $ip =='127.0.0.1' ) {
            
        }else {
            // 每分钟最多60次。
            $new = $redis->incr($key);
            if ($new<3) {
                $redis->setTimeout($key,60 );// 仅能存活60秒
            }
            if ($new >30) {
                sleep(3);
                // 限制每分钟每个ip最多访问30次这个接口。
                
                return ['code'=>0];
            }
        }
        
        
        $self_uid = input('?param.self_uid')?(int)input('param.self_uid'):$uid;//2016 10加字段。兼容性
        $token = input('?param.token')?input('param.token'):'';
        
        if (!$token) {
            $token = Cookie::get('token');
            
        }
        
        
        $UserDB = self::get_user($uid);
        if ($UserDB)
        {
            //添加返回的信息
            
            
            $user_detail = \BBExtend\model\User::find( $UserDB['uid']);
            $user_self = \BBExtend\model\User::find( $self_uid);
            if ( $token ){
                if (!$user_self->check_token($token)) {
                    return ['code'=>0, 'message' => '' ];
                }
            }
            
            
            $UserDB['currency'] = self::get_currency($UserDB['uid']);
            
            
            
            $UserDB['role'] = $user_detail->role;
            
            $UserDB['frame'] = $user_detail->get_frame();
            $UserDB['address'] = $user_detail->get_user_address();
            
            $UserDB['badge'] = $user_detail->get_badge();
            
            $UserDB['sex'] = isset($UserDB['sex'])?(int)$UserDB['sex']:0;
            $UserDB['age']=date('Y') - substr($UserDB['birthday'],0,4);
            $UserDB['rewind_count'] = Db::table('bb_rewind')->where(['uid'=>$uid,'event'=>'rewind',
                'is_remove'=>0,'is_save'=>1])->count();
            $UserDB['movies_count'] = BBRecord::get_movies_count_2($uid,$self_uid);
            $UserDB['level'] = self::get_user_level($UserDB['uid']);
            $UserDB['next_exp'] = self::get_next_exp($UserDB['uid']);
            $UserDB['exp'] = self::get_user_exp($UserDB['uid']);
            $UserDB['attestation'] = isset($UserDB['attestation'])?(int)$UserDB['attestation']:0;
            //=====================谢峰 添加参数================================
            $UserDB['black_count'] = 0;
            $UserDB['follow_count'] = \BBExtend\user\Focus::getinstance($UserDB['uid'])
              ->get_guanzhu_count();
            $UserDB['focus_count'] = \BBExtend\user\Focus::getinstance($UserDB['uid'])
                ->get_fensi_count();
            $UserDB['specialty'] = self::get_specialty($uid);
            
            $temp_user = \BBExtend\model\User::find( $uid );
            $UserDB['speciality_arr'] = $temp_user->hobby_arr_id_name();
            
            
            // xieye 2018 03 优化
//             $UserDB['ranking'] = 
//               \BBExtend\user\Ranking::getinstance($UserDB['uid'])->get_caifu_ranking();
            $UserDB['ranking'] = '0';
              
            $unread_count = Db::table('bb_msg')->where("uid",$UserDB['uid'] )
               ->where('is_read',0)->count();
            $UserDB['unread_count'] = $unread_count;
            
            // 谢烨20171022
            $UserDB['is_starmaker'] = Db::table('bb_users_starmaker')->where("uid",$UserDB['uid'] )
                ->count();
            $UserDB['is_starmaker'] = $UserDB['is_starmaker'] ? 1: 0;
                
            //谢烨2016 10，加字段,兼容性处理。
            if ($self_uid) {
              $UserDB['is_focus'] = Focus::get_focus_state($self_uid,$uid);
            }

            $UserDB['pic'] = BBUser::get_userpic_givepic($UserDB['pic']);
            
            // 谢烨 2016 10 28 为解决直播视频 审核失败 却显示 为 “未完成” 的情况
            if (!$UserDB['attestation']) {
              $db = Sys::get_container_db();
              $sql="select count(*) from bb_record where uid = {$uid} and type=3 and audit=2";
              if ($db->fetchOne($sql)) {
                  $UserDB['attestation'] =3; //3 表示审核未通过，明天继续努力
              }
            }
            // 看他人，需要加 成就
            if ($self_uid && $self_uid!= $uid) {
                $user = \BBExtend\model\User::find($uid);
                $ach2 = new \BBExtend\model\Achievement();
                $ach = $ach2->create_default_by_user($user);
                $UserDB['achievement'] = $ach->get_pic_arr();
                
                
                $UserDB['phone']='***********';
                
            }
            // xieye 20171017，关于禁止token字段的代码
            if (isset($UserDB['userlogin_token'] )) {
                
                unset($UserDB['userlogin_token']);
                
//                 $date = \DateTime::createFromFormat('Y-m-d', '2017-12-17');
//                 if (time() > $date->getTimestamp()  ) {
//                     unset($UserDB['userlogin_token']);
//                 }else {
//                     if ( Client::is_android() ) { //安卓处理代码
//                         if (Client::big_than_version('3.2.1')) {
//                             unset($UserDB['userlogin_token']);
//                         }
//                     }else { // 苹果处理代码
//                         if (Client::big_than_version('3.2.0')) {
//                             unset($UserDB['userlogin_token']);
//                         }
//                     }
//                 }
            }
            
            // 谢烨测试用，勿删
//             if ($uid==10876) {
//                 $UserDB['nickname'] = intval( \BBExtend\common\Client::big_than_version('3.2.0')).' '.
//                 intval(\BBExtend\common\Client::is_ios())
//                 ;
//             }
            
            return ['data'=>$UserDB,'monsterinfo'=>[],'code'=>1];
        }
        // 谢烨20160926，改错误代码0为－107，表示用户根本不存在。
        return ['message'=>'没有这个用户','code'=>-107];
    }
    
    /**
     * 这是把本机的头像图片传到阿里云的代码
     * 
     * @param string $bigpic 类似  '/uploads/headpic/10000/123.jpg'
     */
    private function oss_pic( $bigpic )
    {
        $full_path = '/mnt'. $bigpic;
        $oss_file_path = preg_replace('#^.+?(uploads.+)$#','$1', $full_path);
        
        $bucket = Oss::getBucketName( );
        $ossClient = Oss::getOssClient( );
        if (is_null( $ossClient )){
            return false;
        }
        // 上传本地文件
        $result = $ossClient->uploadFile( $bucket, $oss_file_path, $full_path );
        if ($result && isset( $result['x-oss-request-id'] )) {
           // echo "upload {$oss_file_path} success !\n";
            unlink($full_path);
            return 'http://resource.guaishoubobo.com'.$bigpic;
        } else {
            return false;
        }
    }
    
    /**
     * 修改用户信息
     * 
     * @return number[]|string[]|string[]|number[]|number[]|string[][]|number[][]|unknown[][]
     */
    public function edit_userinfo_api() 
    {
        $uid      =  input('?param.uid')?(int)input('param.uid'):0;
        $nickname =  input('?param.nickname')?input('param.nickname'):'';
        $sex      =  input('?param.sex')?(int)input('param.sex'):-1;
        $birthday =  input('?param.birthday')?input('param.birthday'):'';
        $constellation =  input('?param.constellation')?input('param.constellation'):'';// 星座
        $signature =   input('?param.signature')?input('param.signature'):'';// 个性签名
        $address =   input('?param.address')?input('param.address'):'';// 地址
        //谢烨20160929，强行把客户端转来 的月设为2位。
        if ($birthday) {
            $temp = explode('-', $birthday);
            if (strlen($temp[1]) == 1 ) {
                $temp[1] = "0".$temp[1];
                $birthday = implode('-', $temp);
            }
        }
        $nickname = trim($nickname);
        //改名必须查敏感词。
        if ($nickname) {
            $db = Sys::get_container_db();
            $sql ="select * from bb_minganci where name =?";
            $result = $db->fetchRow($sql, trim($nickname));
            if ($result) {
                return ['code'=>0,'message'=>'您的昵称不合适'];
            }
        }
        
        if ($signature) {
            $db = Sys::get_container_db();
            $sql ="select * from bb_minganci where name =?";
            $result = $db->fetchRow($sql, trim($signature));
            if ($result) {
                return ['code'=>0,'message'=>'您的个性签名不合适'];
            }
        }
        if (strlen($signature) > 360 ) {
            return ['code'=>0,'message'=>'您的个性签名太长'];
        }
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
        if (!self::validation_token($uid,$userlogin_token))
        {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        //检查结束
        $UserDB = self::get_user($uid);
        if (!$UserDB)
        {
            return ['message'=>'没有这个用户','code'=>0];
        }
        //按时间文件夹存放头像
        $type=array("jpg","gif","jpeg","png");//文件上传类型
        $file =  request()->file('image');
        $httppath = '/uploads/headpic/'.$uid.'/';
        $bigpicpath = '.'.$httppath;
        if (!is_dir($bigpicpath)){
            mkdir($bigpicpath,0775,true);
        }
        $bigpic = '';
        $UpDB = array();
        $UpDB['pic']='';
        //完成上传头像任务
        if ($file and in_array(pathinfo($file->getInfo()['name'],PATHINFO_EXTENSION), $type)) {
            $info = $file->rule('uniqid')->move($bigpicpath);
            $bigpic = $httppath.$info->getFilename();
            if ( Sys::is_product_server() ) {
                // 上传阿里云oss
                $new = $this->oss_pic($bigpic);
                if ($new) {
                    $UpDB['pic'] = $new;
                }else {
               // TaskManager::getinstance($uid)->success_complete(2);
                    $UpDB['pic'] = $bigpic;
                }
            } else {
                $UpDB['pic'] = $bigpic;
            }
        } else {
            $UpDB['pic'] =User::get_user_pic_no_http($uid);
        }
        if ($nickname!='') {
            $UpDB['nickname'] = $nickname;
        }
        if ($sex >=0)  {
            $UpDB['sex'] = $sex;
        }
        if($birthday!='') {
            $UpDB['birthday'] = $birthday;
        }
        if ($signature !='') {
            $UpDB['signature'] = $signature;

        }
        if ($constellation ) {
            $UpDB['constellation'] = $constellation;
        }
        if ($address ) {
            $UpDB['address'] = $address;
        }
        Db::table('bb_users')->where('uid',$uid)->update($UpDB);
        BBRedis::getInstance('user')->hMset($uid,$UpDB);
        
         $UpDB['pic'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                 $UpDB['pic'] );
        
        $speciality_list = input('?param.speciality_list')?input('param.speciality_list'):'';
        if ($speciality_list) { // 谢烨，专为安卓设计的！！2017 05
            $this->_set_bb_userspeciality($uid, $speciality_list);
            $UpDB['speciality'] = $speciality_list;
        }
        return ['data'=>$UpDB,'code'=>1];
    }

    
    /**
     * 关注用户
     * @param int $self_uid   关注发起人
     * @param int $focus_uid  目标对象
     */
    public function focus($self_uid=0, $focus_uid=0)
    {
        $self_uid = intval($self_uid);
        $focus_uid = intval($focus_uid);
        $UserDB = self::get_user($self_uid);
        $focusDB = self::get_user($focus_uid);
        if (!$UserDB) {
            return  ['message'=>'没有这个用户','code'=>0];
        }
        if (!$focusDB) {
            return  ['message'=>'没有这个关注对象','code'=>0];
        }
        $help = \BBExtend\user\Focus::getinstance($self_uid);
        if ( $help->focus_guy($focus_uid) ) {
            $ach = new \BBExtend\user\achievement\Hongren($focus_uid);
            $ach->update(1);
            // 2017 06 新功能。如果被关注用户是特邀用户，则需同时加3个机器人粉丝。
            if ($focusDB['permissions'] == 3) {
                $obj = \BBExtend\user\Focus::getinstance($focus_uid);
                $obj->add_robot_fensi();
            }
            return ['message'=>'关注成功','code'=>1];
        } 
        return ['message'=>$help->message,'code'=>0];
    }

    
    /**
     * 取消关注
     * 
     * @param number $self_uid
     * @param number $focus_uid
     */
    public function un_focus($self_uid=0, $focus_uid=0)
    {
        $UserDB = self::get_user($self_uid);
        $focusDB = self::get_user($focus_uid);
        if (!$UserDB)
        {
            return  ['message'=>'没有这个用户','code'=>0];
        }
        if (!$focusDB)
        {
            return  ['message'=>'没有这个要取消的关注对象','code'=>0];
        }
        if(\BBExtend\user\Focus::getinstance($self_uid)->un_focus_guy($focus_uid) )
        {
            $ach = new \BBExtend\user\achievement\Hongren($focus_uid);
            $ach->update(-1);
            
            return ['message'=>'取消关注','code'=>1];
        }
        return ['message'=>'取消失败','code'=>0];
    }
    
    
    /**
     * 过滤sql，使得结果可以用于like语句
     * @param unknown $s
     */
    private  function filter_str($s)
    {
        //先把换行改成空格
        $pattern = '/(\r\n|\n)/';
        $s = preg_replace($pattern, '', $s);
        //20-7e 包括了0－9a-zA-Z空格，英文标点。是ascii表的主要一部分
        // 4e00- 9fa5 全部汉字，但不含中文标点
        $pattern = '/[^\x{4e00}-\x{9fa5}0-9a-zA-Z]/u';
        $s = preg_replace($pattern, '', $s);
        return $s;
    }
    
    
    /**
     * 查找用户
     */
    public function search_users()
    {
        //谢烨20160928，修改模糊查找，加防注入
        $search_content =$search_content_backup =
            input('?param.search_content')?(string)input('param.search_content'):'';
        $search_content = $this->filter_str($search_content);
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $UserDB_Array  =[];
        $db = \BBExtend\Sys::get_container_db();
        if (is_numeric($search_content)) {
           // $db = \BBExtend\Sys::get_container_db();
            $sql = "select * from bb_users where uid='{$search_content}'
              or nickname like '%{$search_content}%'
              limit 30
            "; 
        }else {
            if ($search_content) {
              $sql = "select * from bb_users where  nickname like '%{$search_content}%'
              limit 30
              ";
            }else {
                $sql = "select * from bb_users where  uid=0";
            }
        }
        $UserDB_Array  = $db->fetchAll($sql);
        if (!$UserDB_Array) {
            $sql = "select * from bb_users where  nickname =? limit 30";
            $UserDB_Array  = $db->fetchAll($sql,$search_content_backup);
        }
        $Data = array();
        foreach ($UserDB_Array as $UserDB)
        {
            $DB = array();
            $pic = $UserDB['pic'];
            //如果没有http://
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            if (!$pic)
            {
                $pic =$ServerURL.'/public/toppic/topdefault.png';
            }
            $pic =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                    $pic );
            
            $DB['pic'] = $pic;
            $DB['address'] = $UserDB['address'];
            $DB['nickname'] = $UserDB['nickname'];
            $DB['uid'] = (int)$UserDB['uid'];
            //谢烨20160922，加vip返回字段
            try{
            $DB['vip'] = \BBExtend\common\User::is_vip($DB['uid']) ;
            }catch(\Exception $s){
                $DB['vip']=0;
            }
            $DB['is_focus'] = Focus::get_focus_state($uid,$UserDB['uid']);
            $DB['sex'] =intval($UserDB['sex']);
            
            // 谢烨 2017 04
            $user = \app\user\model\UserModel::getinstance($UserDB['uid']);
            $DB['level'] = $user->get_user_level();
            $DB['age'] = $user->get_userage();
            $DB['specialty'] = $user->get_hobbys();
            
            $user_detail = \BBExtend\model\User::find( $UserDB['uid'] );
            
            $DB['role'] = $user_detail->role;
            $DB['frame'] = $user_detail->get_frame();
            $DB['badge'] = $user_detail->get_badge();
            $DB['signature'] = $user_detail->signature;//201806
            
            
            // 2018 03
            $DB = \BBExtend\model\UserDetail::correct201804($DB);
            
            
            array_push($Data,$DB);
        }
        return ['data'=>$Data,'code'=>1];
    }
    
    
    /**
     * 得到用户关注列表
     * 
     * 201704 谢烨修改，加上我有这个人的多少短视频未看。
     * @return number[]|string[]
     */
    public function get_user_focus201704()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):0;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $FocusDB_Array = Db::table('bb_focus')->where('uid',$uid)->order('time','desc')
           ->limit($startid,$length)->select();
        $UserArray = array();
        $db = Sys::get_container_db();
    
        foreach ($FocusDB_Array as $FocusDB)
        {
            $User_UID = $FocusDB['focus_uid'];
            $UserDB = self::get_user($User_UID);
            $ADDUser_DB = array();
            $ADDUser_DB['uid'] = (int)$User_UID;
            //谢烨20160922，加vip返回字段
            $ADDUser_DB['vip'] = \BBExtend\common\User::is_vip($ADDUser_DB['uid']) ;
            $ADDUser_DB['age'] = self::get_userage($User_UID);
            $ADDUser_DB['pic'] = self::get_userpic($User_UID);
            $ADDUser_DB['nickname'] = $UserDB['nickname'];
            $ADDUser_DB['address'] = $UserDB['address'];
            $ADDUser_DB['signature'] = $UserDB['signature'];
            $phone = '';
            if (preg_match('/^[\d]{11}$/', $UserDB['phone'])) {
                $phone = $UserDB['phone'];
            }
            $ADDUser_DB['phone'] = $phone;
            //2017 04
            $user = \app\user\model\UserModel::getinstance($ADDUser_DB['uid']);
            // $DB['level'] = $user->get_user_level();
            $ADDUser_DB['level'] = $user->get_user_level();
            $ADDUser_DB['sex'] = $user->get_usersex();
            $ADDUser_DB['specialty'] = $user->get_hobbys();
            if ($query_uid==$uid) {
                $ADDUser_DB['is_focus'] = true;
            }else {
                $ADDUser_DB['is_focus'] = $user->is_fensi($query_uid);
            }
            //新增未看视频个数
            $sql ="select count(*) from bb_record
            where uid={$User_UID}
            and  type in (1,2)
            and audit=1
            and is_remove=0
            and usersort in (1,2,3)
            and not exists (select 1 from  bb_moive_view_log
            where bb_moive_view_log.target_uid = {$User_UID}
            and bb_moive_view_log.uid = {$uid}
            and bb_moive_view_log.movie_id = bb_record.id
            )";
            $ADDUser_DB['new_movie_count'] = $db->fetchOne($sql);
            
            $user = \BBExtend\model\UserDetail::find( $ADDUser_DB['uid'] );
            $ADDUser_DB['fans_count']= $user->get_fans_count();
            $ADDUser_DB['follow_count'] = $user->get_follow_count();
            $ADDUser_DB['role'] = $user->role;
            $ADDUser_DB['badge'] = $user->get_badge();
            $ADDUser_DB['frame'] = $user->get_frame();
            
            
            
            array_push($UserArray,$ADDUser_DB);
        }
        // 谢烨2017 04 直播人数好友
        $sql ="
        select count(*) from bb_focus
        where uid={$uid}
        and exists (select 1 from bb_push where bb_push.event='publish'
        and bb_push.uid = bb_focus.focus_uid
        )
        ";
        $zhibo_count = $db->fetchOne($sql);
        return ['data'=>$UserArray,'is_bottom'=>(count($FocusDB_Array)==$length)? 0 : 1,
            'zhibo_count' => $zhibo_count,
            'code'=>1];
    }
    
    /**
     * 查出$target_uid中的每个人，uid有多少未读视频。
     * 
     * @param number $uid
     * @param string $target_uid
     * @return number[]|string[]|number[]|number[][][][]|NULL[][][][]|unknown[][][][]|array[][][][]
     */
    public function get_hongdian($uid=0,$target_uid='')
    {
        $uid = intval($uid);
        if (!$target_uid) {
            return ['code'=>0,'message'=>'参数错误'];
        }
        $uid_arr=explode(',', $target_uid);
        $db = Sys::get_container_db_eloquent();
        $new=[];
        $i=0;
        foreach ( $uid_arr as $v ) {
            $i++;
            $User_UID = intval($v);
          
          $sql ="select count(*) from bb_record
            where uid={$User_UID}
            and  type in (1,2)
            and audit=1
            and is_remove=0
            and usersort in (1,2,3)
            and not exists (select 1 from  bb_moive_view_log
            where bb_moive_view_log.target_uid = {$User_UID}
            and bb_moive_view_log.uid = {$uid}
            and bb_moive_view_log.movie_id = bb_record.id
            )";
          $count = DbSelect::fetchOne($db, $sql);
          $temp=["target"=> intval($User_UID),'no_read_count' => $count];
          $new[] = $temp;
          if ($i >= 20) {
              break;
          }
        }
        return ['code'=>1,'data'=>['list'=> $new  ]  ];
    }
    
    /**
     * 得到用户关注列表
     *
     * 201704 谢烨修改，加上我有这个人的多少短视频未看。
     * @return number[]|string[]
     */
    public function get_user_focus_v3()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):0;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $FocusDB_Array = Db::table('bb_focus')->where('uid',$uid)->order('time','desc')
        ->limit($startid,$length)->select();
        $UserArray = array();
        $db = Sys::get_container_db();
        
        foreach ($FocusDB_Array as $FocusDB)
        {
            $User_UID = $FocusDB['focus_uid'];
            $UserDB = self::get_user($User_UID);
            $ADDUser_DB = array();
            $ADDUser_DB['uid'] = (int)$User_UID;
            //谢烨20160922，加vip返回字段
            $ADDUser_DB['vip'] = \BBExtend\common\User::is_vip($ADDUser_DB['uid']) ;
            $ADDUser_DB['age'] = self::get_userage($User_UID);
            $ADDUser_DB['pic'] = self::get_userpic($User_UID);
            $ADDUser_DB['nickname'] = $UserDB['nickname'];
            $ADDUser_DB['address'] = $UserDB['address'];
            $ADDUser_DB['signature'] = $UserDB['signature'];
            $phone = '';
            if (preg_match('/^[\d]{11}$/', $UserDB['phone'])) {
                $phone = $UserDB['phone'];
            }
            $ADDUser_DB['phone'] = $phone;
            //2017 04
            $user = \app\user\model\UserModel::getinstance($ADDUser_DB['uid']);
            // $DB['level'] = $user->get_user_level();
            $ADDUser_DB['level'] = $user->get_user_level();
            $ADDUser_DB['sex'] = $user->get_usersex();
            $ADDUser_DB['specialty'] = $user->get_hobbys();
            if ($query_uid==$uid) {
                $ADDUser_DB['is_focus'] = true;
            }else {
                $ADDUser_DB['is_focus'] = $user->is_fensi($query_uid);
            }
            //新增未看视频个数
            $sql ="select count(*) from bb_record
            where uid={$User_UID}
            and  type in (1,2)
            and audit=1
            and is_remove=0
            and usersort in (1,2,3)
            and not exists (select 1 from  bb_moive_view_log
            where bb_moive_view_log.target_uid = {$User_UID}
            and bb_moive_view_log.uid = {$uid}
            and bb_moive_view_log.movie_id = bb_record.id
            )";
            $ADDUser_DB['new_movie_count'] = 0;
            array_push($UserArray,$ADDUser_DB);
        }
        // 谢烨2017 04 直播人数好友
        $sql ="
        select count(*) from bb_focus
        where uid={$uid}
        and exists (select 1 from bb_push where bb_push.event='publish'
        and bb_push.uid = bb_focus.focus_uid
        )
        ";
        $zhibo_count = $db->fetchOne($sql);
        return ['data'=>$UserArray,'is_bottom'=>(count($FocusDB_Array)==$length)? 0 : 1,
                'zhibo_count' => $zhibo_count,
                'code'=>1];
    }
    
    
    
    /**
     * 得到用户关注列表
     * 
     * 201702 谢烨修改，加上我有这个人的多少短视频未看。
     * 已废止。
     */
    public function get_user_focus()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $FocusDB_Array = Db::table('bb_focus')->where('uid',$uid)->order('time','desc')
          ->limit($startid,$length)->select();
        $UserArray = array();
        $db = Sys::get_container_db();
        foreach ($FocusDB_Array as $FocusDB)
        {
            $User_UID = $FocusDB['focus_uid'];
            $UserDB = self::get_user($User_UID);
            $ADDUser_DB = array();
            $ADDUser_DB['uid'] = (int)$User_UID;
            //谢烨20160922，加vip返回字段
            $ADDUser_DB['vip'] = \BBExtend\common\User::is_vip($ADDUser_DB['uid']) ;
            $ADDUser_DB['age'] = self::get_userage($User_UID);
            $ADDUser_DB['pic'] = self::get_userpic($User_UID);
            $ADDUser_DB['nickname'] = $UserDB['nickname'];
            $ADDUser_DB['address'] = $UserDB['address'];
            $phone = '';
            if (preg_match('/^[\d]{11}$/', $UserDB['phone'])) {
                $phone = $UserDB['phone'];
            }
            $ADDUser_DB['phone'] = $phone;
            //2017 04
            $user = \app\user\model\UserModel::getinstance($ADDUser_DB['uid']);
           // $DB['level'] = $user->get_user_level();
            $ADDUser_DB['level'] = $user->get_user_level();
            $ADDUser_DB['sex'] = $user->get_usersex();
            $ADDUser_DB['specialty'] = $user->get_hobbys();
            //新增未看视频个数
            $sql ="select count(*) from bb_record 
where uid={$User_UID}
      and  type in (1,2)
      and audit=1
      and is_remove=0
      and usersort in (1,2,3)
and not exists (select 1 from  bb_moive_view_log
 where bb_moive_view_log.target_uid = {$User_UID}
   and bb_moive_view_log.uid = {$uid}
   and bb_moive_view_log.movie_id = bb_record.id
)";
            $ADDUser_DB['new_movie_count'] = $db->fetchOne($sql);
            array_push($UserArray,$ADDUser_DB);
        }
        // 谢烨2017 04 直播人数好友
        $sql ="
            select count(*) from bb_focus 
where uid={$uid} 
and exists (select 1 from bb_push where bb_push.event='publish'
  and bb_push.uid = bb_focus.focus_uid
)    
                ";
        $zhibo_count = $db->fetchOne($sql);
        return ['data'=>$UserArray,'is_bottom'=>(count($FocusDB_Array)==$length)? 0 : 1, 
            'zhibo_count' => $zhibo_count,
            'code'=>1];
    }
    
    
    /**
     * 得到关注我的用户 也就是粉丝
     */
    public function get_focus_user201704()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):0;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $FocusDB_Array = Focus::get_focus_user($uid,$startid,$length);
        $UserArray = array();
        foreach ($FocusDB_Array as $FocusDB)
        {
            $User_UID = $FocusDB['uid'];
            $UserDB = self::get_user($User_UID);
            $ADDUser_DB = array();
            $ADDUser_DB['uid'] = (int)$User_UID;
            //谢烨20160922，加vip返回字段
            $ADDUser_DB['vip'] = \BBExtend\common\User::is_vip($ADDUser_DB['uid']) ;
            $ADDUser_DB['age'] = self::get_userage($User_UID);
            $ADDUser_DB['pic'] = self::get_userpic($User_UID);
            $ADDUser_DB['nickname'] = $UserDB['nickname'];
            $ADDUser_DB['address'] = $UserDB['address'];
            $ADDUser_DB['is_focus'] = Focus::get_focus_state($query_uid,$User_UID);
            $user = \app\user\model\UserModel::getinstance($ADDUser_DB['uid']);
            $ADDUser_DB['level'] = $user->get_user_level();
            $ADDUser_DB['sex'] = $user->get_usersex();
            $ADDUser_DB['specialty'] = $user->get_hobbys();
            $ADDUser_DB['signature'] = $user->get_signature();
            array_push($UserArray,$ADDUser_DB);
        }
        if (count($FocusDB_Array)==$length)
        {
            return ['data'=>$UserArray,'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$UserArray,'is_bottom'=>1,'code'=>1];
    }
    
    
    /**
     * 得到关注我的用户 也就是粉丝
     * 
     * 已废止
     */
    public function get_focus_user()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $FocusDB_Array = Focus::get_focus_user($uid,$startid,$length);
        $UserArray = array();
        foreach ($FocusDB_Array as $FocusDB)
        {
            $User_UID = $FocusDB['uid'];
            $UserDB = self::get_user($User_UID);
            $ADDUser_DB = array();
            $ADDUser_DB['uid'] = (int)$User_UID;
    
            //谢烨20160922，加vip返回字段
            $ADDUser_DB['vip'] = \BBExtend\common\User::is_vip($ADDUser_DB['uid']) ;
            $ADDUser_DB['age'] = self::get_userage($User_UID);
            $ADDUser_DB['pic'] = self::get_userpic($User_UID);
            $ADDUser_DB['nickname'] = $UserDB['nickname'];
            $ADDUser_DB['address'] = $UserDB['address'];
            $ADDUser_DB['is_focus'] = Focus::get_focus_state($uid,$User_UID);
            $user = \app\user\model\UserModel::getinstance($ADDUser_DB['uid']);
            $ADDUser_DB['level'] = $user->get_user_level();
            $ADDUser_DB['sex'] = $user->get_usersex();
            $ADDUser_DB['specialty'] = $user->get_hobbys();
            array_push($UserArray,$ADDUser_DB);
        }
        if (count($FocusDB_Array)==$length)
        {
            return ['data'=>$UserArray,'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$UserArray,'is_bottom'=>1,'code'=>1];
    }
    
    
    /**
     * 签到系统
     */
    public function sign_in()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        if (self::add_user_exp($uid,LEVEL_LOGIN))
        {
            return ['message'=>'签到成功','code'=>1];
        }
        return ['message'=>'您已经签到过了','code'=>0];
    }
    
    
    public function gold($uid=0)
    {
        $user = BBUser::get_user($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid错误'];
        }
        $user = Currency::get_currency($uid);
        return ['code'=>1,'data'=> ["gold" => $user['gold'],'bean'=> $user['gold_bean'], ] ];
    }
    
    
    /**
     * 得到经验日志
     * 
     * xieye 2016 1018
     */
    public function get_exp_log()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $LogDB_Array = self::get_user_exp_log($uid,$startid,$length);
        $LogArray = array();
        foreach ($LogDB_Array as $LogDB) {
                $LogDB['exp'] = (int)$LogDB['exp'];
                unset($LogDB['who_uid']);
                unset($LogDB['id']);
                array_push($LogArray,$LogDB);
        }
        if (count($LogArray) == $length) {
            return ['data'=>$LogArray,'level'=>self::get_user_level($uid),
                'current_exp' =>self::get_user_exp($uid),
                'next_exp'=>self::get_next_exp($uid),'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$LogArray,'level'=>self::get_user_level($uid),
            'current_exp' =>self::get_user_exp($uid),
            'next_exp'=>self::get_next_exp($uid),'is_bottom'=>1,'code'=>1];
    }
    

    /**
     * 得到用户的头像
     * @param string $uid
     */
    public function get_user_pic($uid='')
    {
        if (!$uid){
           $uid      =  input('?param.uid')?(int)input('param.uid'):0;
        }
        return self::get_userpic($uid);
    }
    
    
    /**
     * 注册友盟的token绑定UID
     */
    public function bind_umeng()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $tokens = input('?param.token')?(string)input('param.token'):'';
        PushMsg::Registered_Token($uid,$tokens);
        return ['message'=>'注册成功','code'=>1];
    }

    
    /**
     * 绑定帐号平台
     */
    public function binding_login()
    {
        $platform_id = input('?param.platform_id')?(string)input('param.platform_id'):'';
        $uid = input('?param.uid')?(int)input('param.uid'):'';
        $login_type = input('?param.login_type')?(int)input('param.login_type'):0;
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
        
        $unionid = input('?param.unionid')?(string)input('param.unionid'):'';
        
        if (\app\user\model\Exists::userhExists($uid)!=1)   {
            return ['message'=>'没有当前的用户ID','code'=>0];
        }
        if (!self::validation_token($uid,$userlogin_token)) {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        
        if (empty( $platform_id )) {
            return ['message'=>'绑定账号不能为空','code'=>0];
        }
        
        $user_platform = md5($platform_id);
        $PlatformDB = Db::table('bb_users_platform')
           ->where(['platform_id'=>$user_platform,'type'=>$login_type])->find();
        if (!$PlatformDB)  {
            if ($login_type==LOGIN_TYPE_PHONE)   {
                $UserDB = self::get_user($uid);
                if ($UserDB) {
                    $UserDB['phone'] = $platform_id;
                    self::update($UserDB);
                }
            }
            Db::table('bb_users_platform')->insert(
                    ['platform_id'=>$user_platform,'type'=>$login_type,'uid'=>$uid]);
            
            if ( $login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin ){
                Db::table('bb_users')->where('uid',$uid)->update( [
                      //  'openid'      => $platform_id,
                        'unionid'     =>$unionid,
                ] );
            }
            
            return ['message'=>'绑定成功','code'=>1];
        }
        return ['message'=>'绑定失败,该帐号已经绑定其他帐号','code'=>0];
    }
    
    
    /**
     * 解除绑定
     */
    public function un_binding_login()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):'';
        $login_type = input('?param.login_type')?(int)input('param.login_type'):0;
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
        
//         if ( $login_type==3 ) {
//             return ['code'=>0, 'message' =>'对不起，手机帐号不可以解除绑定' ];
//         }
        
        if (\app\user\model\Exists::userhExists($uid)!=1) {
            return ['message'=>'没有当前的用户ID','code'=>0];
        }
        if (!self::validation_token($uid,$userlogin_token)) {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        $PlatformDB = Db::table('bb_users_platform')->where(['uid'=>$uid,'type'=>$login_type])->find();
        if ($PlatformDB)  {
            // xieye count
            if (Db::table('bb_users_platform')->where(['uid'=>$uid])->count() >= 2) {
                Db::table('bb_users_platform')->where(['uid'=>$uid,'type'=>$login_type])->delete();
                
                if ( $login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin ){
                    Db::table('bb_users')->where('uid',$uid)->update( [
                            'unionid'     =>'',
                    ] );
                }
                
                
                return ['message'=>'解除绑定成功','code'=>1];
            } else {
                return ['message'=>'不能解绑所有帐号，至少要保留一个帐号','code'=>0];
            }
        }
        return ['message'=>'该平台没有绑定该帐号','code'=>0];
    }
    
    
    /**
     * 得到所有用户绑定的帐号信息
     */
    public function get_binding_login()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):'';
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
        if (\app\user\model\Exists::userhExists($uid)!=1) {
            return ['message'=>'没有当前的用户ID','code'=>0];
        }
        if (!self::validation_token($uid,$userlogin_token)) {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        $PlatformDB_array = Db::table('bb_users_platform')->where(['uid'=>$uid])->select();
        $Data = array();
        foreach ($PlatformDB_array as $PlatformDB) {
            unset($PlatformDB['uid']);
            unset($PlatformDB['platform_id']);
            $PlatformDB['type'] = (int)$PlatformDB['type'];
            array_push($Data,$PlatformDB);
        }
        return ['data'=>$Data,'code'=>1];
    }
    
    
    /**
     * 排行榜分页显示
     * 
     * @param number $uid
     * @param number $startid
     * @param number $length
     * @param number $type
     */
    public function get_ranking($uid=0, $startid=0, $length=10,$type=1)
    {
        return \BBExtend\user\Ranking::getinstance($uid)->get_list($type,$startid,$length);
    }
    
    
    /**
     * 刷新排行版名次
     */
    public function refresh_ranking()
    {
        $ListDB = Db::table('bb_users')->order(
                ['monster_count'=>'desc','register_time'=>'asc'])->select();
        $index = 1;
        foreach ($ListDB as $UserDB) {
            $UserDB['ranking'] = (string)$index;
            $RedisUserDB = BBRedis::getInstance('user')->hGetAll($UserDB['uid']);
            if ($RedisUserDB) {
                BBRedis::getInstance('user')->hSet($UserDB['uid'],'ranking',$index);
            }
            Db::table('bb_users')->where('uid',$UserDB['uid'])->update(['ranking'=>$index]);
            $index++;
        }
        return ['code'=>1];
    }
    
    
    /**
     * 发送反馈信息
     */
    public function send_feedback()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;//用户ID
        $content = input('?param.content')?input('param.content'):'';//内容
        $contact = input('?param.contact')?input('param.contact'):'';//联系方式
        if ($content&&$contact) {
            $Data = array();
            $Data['uid'] = $uid;
            $Data['content'] = $content;
            $Data['contact'] = $contact;
            $Data['time'] = time();
            $info = Request::instance()->header();
            if ($info) {
                $User_Agent = Request::instance()->header('User-Agent');
                if ($User_Agent) {
                    $Data['user_agent'] = $User_Agent;
                }
            }
            Db::table('bb_feedback')->insert($Data);
            return ['message'=>'发送成功','code'=>1];
        }
        return ['message'=>'没有内容以及联系方式请补全信息后发送','code'=>0];
    }
    
    
    /**
     * 分享成功接口
     */
    public function share_ok()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;//用户ID
        $share_uid = input('?param.share_uid')?(int)input('param.share_uid'):0;//分享谁的UID
        $record_id = input('?param.record_id')?(int)input('param.record_id'):0;//短视频id
        $user = \BBExtend\model\User::find($uid);        
        if (!$user ) {
            return ['message'=>'没有当前的用户ID','code'=>0];
        }
        if ($share_uid && \app\user\model\Exists::userhExists($share_uid)!=1) {
            return ['message'=>'请确定您分享的用户存在','code'=>0];
        }
        if ($uid != $share_uid) {
            Exp::getinstance($share_uid)->set_typeint(Exp::LEVEL_SHARE_OTHER_USER)->add_exp();
            Exp::getinstance($uid)->set_typeint(Exp::LEVEL_SHARE)->add_exp();
        } else {
            Exp::getinstance($share_uid)->set_typeint(Exp::LEVEL_SHARE)->add_exp();
        }
        
        if ($record_id >0 ) {
           $db = Sys::get_container_db_eloquent();
           $sql = "update bb_record set share_count = share_count+1 where id = ?";
           $db::update($sql, [$record_id]);
        }
        
        Tongji::getinstance($uid)->share();
        
        // 谢烨，当天分享5次，送10个波币，注意只送1次。
        $redis = Sys::getredis11();
        $key = "/user/user/share_ok:{$uid}:".date("Ymd");
        $count = $redis->incr($key);
        $redis->setTimeout($key, 48 * 3600);//确保redis内存空间不会过大
        if ($count==5) {//仅在当天第5次分享时，奖励。
            Currency::add_currency($uid,CURRENCY_GOLD,10,'分享5次奖励');
            
            Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content('恭喜您达成今日分享，系统奖励您10bo币。'))
            ->set_type(MessageType::fenxiang )
            ->set_uid($uid)
            ->send();
        }
        return ['code'=>1];
    }
    
}
