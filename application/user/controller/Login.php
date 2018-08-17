<?php
/**
 * 新登录接口
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

use BBExtend\service\Sms;

class Login extends BBUser
{
    const appid = 'wx190ef9ba551856b0';
    const secret = '55a4e4aa42e36a3691ee242c967ffd5f';
    
    
    public $weixin_openid=null;  //这个参数专门用于微信注册的。
    public $weixin_token=null; //这个参数专门用于微信注册的。
    
    
    
//     public function man_machine_recognition($token,$type=1){
//         $a = mt_rand( 1,100 );
//         $b = mt_rand( 1,100 );
//         session( 'man_machine_recognition', $a+$b );
//         $str = mt_rand( 1,100 ) . " + " . mt_rand(1, 100) ;
//         return ['code'=>1, 'data' =>[ 'result'  => $str ] ];
//        // session('man_machine_recognition');
//     }
    
    // 这是访问过多的对付手段。
    public function man_machine_recognition_check($token,$type=1,$cal_result)
    {
        
        if ( !session('?man_machine_recognition') ) {
            return ['code'=>0];
        }
        
        
//         if ( $token && ( $redis->get( \BBExtend\Secure::key_prefix_token.$token )!== false ) ) {
//          //   $redis->setex( "limit:ip:pic:check".$token,  120 ,$c );
//             $save_check = $redis->get( "limit:ip:pic:check".$token);
//             if (!$save_check) {
//                 return ['code'=>0];
//             }
//         }else {
//             return ['code'=>0];
//         }
        
        
        if ( $cal_result== session('man_machine_recognition') ) {
                // 首先，这个token得存在。且和 header中的token相同，
            $obj = new \BBExtend\Secure();
                
            if ( $obj->get_header_token() == $token && $obj->test_valid($token) ) {
                $obj->clear_token_count($token);
                
                return ['code'=>1, ];
            } else {
                return ['code'=>0,'message' =>'token参数错误' ];
            }
        }
        
        return ['code'=>0,'message' =>'填写校验错误' ];
    }
    
    public function man_machine_recognition_pic($token)
    {
        $a = mt_rand( 1,100 );
        $b = mt_rand( 1,100 );
        $c = $a +$b;
        
        $redis = Sys::getredis2();
        
        
        if ( $token && ( $redis->get( \BBExtend\Secure::key_prefix_token.$token )!== false ) ) {
            $redis->setex( "limit:ip:pic:check".$token,  120 ,$c );
        }else {
            return ['code'=>0];
        }
        
        
        session( 'man_machine_recognition', $a+$b );
      //  if (session('?man_machine_recognition')) {
            $temp = $c;
            $im = imagecreate(252, 88);
            $bg = imagecolorallocate($im, 255, 255, 255);
            $textcolor = imagecolorallocate($im, 0, 0, 0);
            imagestring($im, 5, 0, 0, $temp, $textcolor);
            // 输出图像 白纸黑字
            header("Content-type: image/png");
            imagepng($im); 
            exit;
        //}
        //return ['code'=>0];
    }
    
    
    public function polling($token='')
    {
        if ($token) {
            $db = Sys::get_container_db();
            $sql="select uid from bb_users where userlogin_token=?";
            $result = $db->fetchOne($sql,$token);
            if ($result) {
                
                $secure_help = new \BBExtend\Secure();
                $token = $secure_help->get_good_token($result);
                
                $token = base64_encode($token );
                return ['code'=>1, 'data'=>['result' => $token ] ];
            }
        }
        
        return ['code'=>0];
    }
    
    
    // 换token接口
    public function check($uid,$token   ){
        $user_self = \BBExtend\model\User::find( $uid);
            if (!$user_self->check_token($token)) {
                
                
                return ['code'=>-201, 'message' => '' ];
            }
         $secure_help = new \BBExtend\Secure();
         $token = $secure_help->set_new_http_header_temptoken($uid);
         $token = base64_encode($token );
         return ['code'=>1, 'data'=>['result' => $token ] ];
    }
    
    public function qq_index($code)
    {
//         if (!$code) {
//             return ['code'=>0,'message'=>'code err'];
//         }
        
//         $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='. self::appid .
//         '&secret=' . self::secret . '&code='. $code.'&grant_type=authorization_code' ;
//         $result = file_get_contents ( $url );
//         //  $redis->set ( $key, $result ); // 保存在redis里的是一个json字符串，包括token和失效时间。
//         $json = json_decode ( $result, true );
//         //   $redis->setTimeout ( $key, $json ['expires_in'] );
        
//         //   $json = json_decode ( $result, true );
//         if ($json && isset( $json['access_token'] ) && isset( $json['unionid'] )   ){
//             //return ['code'=>1,'data' =>$json ] ;
//             return $this->index(
//                     $json['openid'], '', \BBExtend\fix\TableType::bb_users__login_type_weixin,
//                     '',  '',  $json['unionid'] ,  ''  );
//         }
//         return ['code'=>0,'message'=>'解析错误'];
        
    }
    
    public function weixin_index($code)
    {
        if (!$code) {
            return ['code'=>0,'message'=>'code err'];
        }
        
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='. self::appid .
        '&secret=' . self::secret . '&code='. $code.'&grant_type=authorization_code' ;
        $result = file_get_contents ( $url );
        //  $redis->set ( $key, $result ); // 保存在redis里的是一个json字符串，包括token和失效时间。
        $json = json_decode ( $result, true );
        //   $redis->setTimeout ( $key, $json ['expires_in'] );
        
        //   $json = json_decode ( $result, true );
        if ($json && isset( $json['access_token'] ) && isset( $json['unionid'] )   ){
            //return ['code'=>1,'data' =>$json ] ;
            
            $nickname= $pic= '';
            $this->weixin_openid = $json['openid'];
            $this->weixin_token  = $json['access_token'];
            
//             $temp = \BBExtend\user\Weixin::get_pic_by_scope_userinfo($json['openid'], $json['access_token']);
//             if ($temp) {
//                 $nickname = $temp['nickname'];
//                 $pic = $temp['pic'];
//             }
            
            return $this->index(
                    $json['openid'], $nickname, \BBExtend\fix\TableType::bb_users__login_type_weixin, 
                    $pic,  '',  $json['unionid'] ,  ''  );
            
        }
        return ['code'=>0,'message'=>'授权错误'];
        
    }
    

    public function index ( $platform_id = '', $nickname = '未知的火星人', $login_type = 0, $pic = '', $login_address = '',
             $unionid = '', $check_code = '' )
    {
        if (! $platform_id || ! $login_type) {
            return [
                    'code' => - 1,
                    'message' => '第三方平台重要信息不完整!'
            ];
        }
        $login_type = intval( $login_type );
        
        if (! in_array( $login_type,
                [
                        TableType::bb_users__login_type_weixin,
                        TableType::bb_users__login_type_qq,
                        TableType::bb_users__login_type_shouji,
                        TableType::bb_users__login_type_weibo,
                        TableType::bb_users__login_type_jiqiren
                ] )) {
            return [
                    'code' => 0,
                    'message' => 'login_type错误'
            ];
        }
        $db = Sys::get_container_db( );
        // 谢烨 2018 04 微信特别登录。
        if ( $login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin && $unionid){
          //  $db = Sys::get_container_dbreadonly();
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
        
        // 谢烨 2018 04 微信特别登录。
        if ( $login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin && $pic){
            // pic需要变成本地。
            $help = new \BBExtend\common\Oss();
           // $img_url="http://bobo-upload.oss-cn-beijing.aliyuncs.com/public/temp/5abb126903008";
            $oss_file_path_no_filename = 'uploads/headpic_date/'.date("Ymd") .'/' ;
            $result= $help->upload_remote_pic($pic, $oss_file_path_no_filename)  ;
            if ($result) {
                $pic = $result;
            }else {
                $pic='';
            }
        }
        
        
        $sql = "select * from bb_users_platform where platform_id=? and type =? ";
        $row = $db->fetchRow( $sql, [
                md5( $platform_id ),
                $login_type
        ] );
        
        if ($login_type == 3) {
            // 谢烨，这里非常特别，必须校验
//             Sys::debugxieye('登录手机校验');
            $time11 = time();
            $sms = new Sms( $platform_id );
            $result = $sms->check( $check_code );
            $time22 = time();
       //     Sys::debugxieye('登录手机校验'.( $time22-$time11 )."秒");
            
            if (isset( $result['code'] ) && $result['code'] == 1) {
            
            } else {
                return $result;
            }
        }
        
        if (! $row) { // 不存在平台表，说明需要注册
            return $this->otherlogin_register( $platform_id, $nickname, '', $login_type,
                    $login_address, $pic, $unionid );
        }
        // 否则走登录流程
        return $this->otherlogin_login( $row['uid'], $login_type, $platform_id, $unionid );
    }

    public function send_login_message ( $phone )
    {
        if (! preg_match( '#^[\d]{11}$#', $phone )) {
            return [
                    'code' => 0,
                    'message' => '手机格式错误'
            ];
        }
        $sms = new Sms( $phone );
        $result = $sms->send_verification_code( );
        // if ($result['code']==1) {
        return $result;
        // }
    
    }
    
    
    public function check_phone_code ( $phone,$code )
    {
        if (! preg_match( '#^[\d]{11}$#', $phone )) {
            return [
                    'code' => 0,
                    'message' => '手机格式错误'
            ];
        }
        $sms = new Sms( $phone );
        $result = $sms->check($code);
        
        // if ($result['code']==1) {
        return $result;
        // }
        
    }
    

    /**
     * 注册流程
     */
    private function otherlogin_register ( $platform_id, $nickname, $device, $login_type,
            $login_address, $pic, $unionid )
    {
        // 注册必须查敏感词。
        $db = Sys::get_container_db( );
        $sql = "select * from bb_minganci where name =?";
        $result = $db->fetchRow( $sql, trim( $nickname ) );
        if ($result) {
            return [
                    'code' => 0,
                    'message' => '您的昵称不合适'
            ];
        }
        $UserDB = BBUser::registered( $nickname, $device, $login_type, $login_address, $pic,
                $platform_id, $unionid );
        $uid = $UserDB['uid'];
        
        // 谢烨，今天加微信图片。
        if ( $this->weixin_openid ) {
            $client = new \BBExtend\service\pheanstalk\Client();
            $data = new \BBExtend\service\pheanstalk\DataWeixin($uid, $this->weixin_openid, 
                    $this->weixin_token  );
            $client->add_weixin($data);
            
        }
        
        
        $UserDB['currency'] = Currency::get_currency( $uid );
        
        $obj = \app\user\model\UserModel::getinstance( $uid );
        $UserDB['age'] = $obj->get_userage( );
        $UserDB['pic'] = $obj->get_userpic( );
       // $UserDB['user_count'] = Db::table( 'bb_users' )->count( );
        
        // 谢烨 201808
        $UserDB['user_count'] = 0;
        
        
        
        // xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
        \BBExtend\Level::get_user_exp( $uid );
        
        // 谢烨，新功能。新用户注册，自动关注10000号用户，只在正式服。
        if (Sys::is_product_server( )) {
            $help = \BBExtend\user\Focus::getinstance( $uid );
            $help->focus_guy( 10000 );
        }
        // 系统消息 ， 20161110，
        $nickname = ( $nickname == '未知的火星人' ) ? "小朋友" : $nickname;
        Message::get_instance( )->set_title( '系统消息' )
            ->add_content( Message::simple( )->content( $nickname )       )
            ->add_content( 
                Message::simple( )->content( 
                        '欢迎您加入怪兽BOBO,在这里每个孩子' . '都是大明星，请共同维护怪兽岛绿色直播宣言——' ) )
            ->add_content( Message::simple( )->content( 'BOBO童心梦，传递正能量' ) )
            ->add_content( Message::simple( )->content( "。" ) )
            ->set_type( MessageType::register )
            ->set_uid( $uid )
            ->send( );
        $UserDB['unread_count'] = 1;
        $UserDB = self::conversion_for_login( $UserDB ); // 强制转换，和登录一样。
        $bonus = BBUser::regis_additional( $uid ); // 注册有一个额外流程，必须走。
        $return_platform_id='';
        if ($login_type==1) {// 微信登录返回openid
            $return_platform_id=$platform_id;
        }
        $secure_help = new \BBExtend\Secure();
        $secure_help->set_new_http_header_temptoken($uid);
        
        return [
                'code' => 1,
                'data'=>[
                        'user'=>$UserDB,
                        'bonus' => $bonus['result_bonus'],
                        'lottery' => $bonus['result_lottery'],
                        'platform_id' =>$return_platform_id,
                ]
        ];
    }

    private function has_deny ( $uid )
    {
        
        $agent = \BBExtend\common\Client::user_agent( );
        if (preg_match( '#3c:b6:b7:58:3d:86#', $agent )) {
            return true;
        }
        return false;
    }

    /**
     * 登录流程
     */
    private function otherlogin_login ( $uid, $login_type, $platform_id, $unionid )
    {
        $user_arr = Db::table( 'bb_users' )->where( 'uid', $uid )->find( );
        // 201708 查询禁止用户登录
        if ($user_arr['not_login'] != 0) {
            return [
                    'code' => Err::code_not_login,
                    'message' => '您已被禁止登录'
            ];
        }
//         if ($this->has_deny( $uid )) {
//             return [
//                     'code' => Err::code_not_login,
//                     'message' => '您已被禁止登录'
//             ];
//         }
        
        // 更新token
        $user_arr['userlogin_token'] = BBUser::userlogin_token( md5( $platform_id ) );
        $user_arr['user_agent'] = Client::user_agent( );
        $user_arr['login_count'] = $user_arr['login_count'] + 1;
        
        
        
        $log_arr = [
                'login_type' => $login_type,
                'userlogin_token' => $user_arr['userlogin_token'],
                'is_online' => 1,
                'login_count' => $user_arr['login_count'],
                'login_time' => time( ),
                'user_agent' => $user_arr['user_agent'],
                'email' => \BBExtend\common\Client::ip(),
        ];
        if ($login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin) {
            $log_arr['openid'] = $platform_id;
            $log_arr['unionid'] = $unionid;
        }
        
        // 存入表里
        Db::table( 'bb_users' )->where( 'uid', $uid )->update( $log_arr  );
        // 为防止假用户真的登录，做转换。
        if ($user_arr['permissions'] > 5 && $user_arr['permissions'] != 99) {
            Db::table( 'bb_users' )->where( 'uid', $uid )->update( 
                    [
                            'permissions' => 1
                    ] );
        }
        
        
        $user_arr['monster_count'] = 0;
        $user_arr['currency'] = Currency::get_currency( $uid );
        
        $obj = \app\user\model\UserModel::getinstance( $uid );
        $user_arr['age'] = $obj->get_userage( );
        $user_arr['pic'] = $obj->get_userpic( );
        $user_arr['ranking'] = 0; // 暂时这样
        $user_arr['unread_count'] = Db::table( 'bb_msg' )->where( "uid", $uid )
            ->where( 'is_read', 0 )
            ->count( );
        $user_arr = self::conversion_for_login( $user_arr ); // xieye 2016 11 ,这句话必须最后！
        
        \BBExtend\user\Tongji::getinstance( $uid )->otherlogin( );
        $return_platform_id='';
        if ($login_type==1) {// 微信登录返回openid
            $return_platform_id=$platform_id;
        }
        $temp =  ['code' => 1,
                 'data'=>[
                'user'=>$user_arr,
                'bonus' => null,
                'lottery' => null,
                 'platform_id' =>$return_platform_id,
        ]];
        $secure_help = new \BBExtend\Secure();
        $secure_help->set_new_http_header_temptoken($uid);
        return $temp;
    
    }
    
    
    public function only_login($uid,$token)
    {
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        
//         $db = Sys::get_container_dbreadonly();
//         $sql="select * from bb_users_platform where ";
        
        if ($user->openid) {
            $platform_id =$user->openid;
        }else {
            $platform_id = time();
        }
        
        return $this->otherlogin_login($uid, $user->login_type, $platform_id, $user->unionid);
    }
    
    
    
    public function bind($platform_id='', $uid, $userlogin_token, $login_type, 
            $unionid='', $check_code='')
    {
        
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($userlogin_token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if (! in_array( $login_type,
                [
                        TableType::bb_users__login_type_weixin,
                        TableType::bb_users__login_type_qq,
                        TableType::bb_users__login_type_shouji,
                        TableType::bb_users__login_type_weibo,
                        TableType::bb_users__login_type_jiqiren
                ] )) {
                    return [
                            'code' => 0,
                            'message' => 'login_type错误'
                    ];
                }
        
        $user_platform = md5($platform_id);
        
        $db = Sys::get_container_db();
        
        // 谢烨，你应该先查 该uid和type 怎么有？
        $sql="select count(*) from bb_users_platform where uid=? and type=?";
        $temp = $db->fetchOne($sql,[ $uid, $login_type ]);
        if ($temp) {
            return ['code'=>0,'message' =>'绑定错误，因为该登录方式您已经使用' ];
        }
        
        
        $sql="select * from bb_users_platform where type=? and platform_id=? limit 1";
        $PlatformDB = $db->fetchRow($sql,[ $login_type, $user_platform ]);
    //    Sys::debugxieye('进入方法');
//         $PlatformDB = Db::table('bb_users_platform')
//             ->where(['platform_id'=>$user_platform,'type'=>$login_type])->find();
        if (!$PlatformDB)  {
            if ($login_type==\BBExtend\fix\TableType::bb_users__login_type_shouji )   {
      //          Sys::debugxieye('进入手机验证');
                // 谢烨，这里非常特别，必须校验
                $sms = new Sms( $platform_id );
                $result = $sms->check( $check_code );
                if (isset( $result['code'] ) && $result['code'] == 1) {
        //            Sys::debugxieye('手机验证正确');
                } else {
          //          Sys::debugxieye('手机验证错误'.$platform_id." 验证吗：". $check_code );
                    return $result;
                }
                
                
                $UserDB = self::get_user($uid);
                if ($UserDB) {
                    $UserDB['phone'] = $platform_id;
                    self::update($UserDB);
                }
            }
            
            
            
            // 微信有一个额外的处理动作
            if ( $login_type == \BBExtend\fix\TableType::bb_users__login_type_weixin ){
                if ( empty( $unionid) ) {
                    return ['code'=>0,'unionid not exists'];
                }
                
                Db::table('bb_users')->where('uid',$uid)->update( [
                        //  'openid'      => $platform_id,
                        'unionid'     =>$unionid,
                ] );
            }
            
            // 这是真正的绑定操作。
            Db::table('bb_users_platform')->insert(
                    ['platform_id'=>$user_platform,'type'=>$login_type,
                     'original'=>$platform_id ,
                            
                            'uid'=>$uid]);
            
            return ['message'=>'绑定成功','code'=>1];
        }
        return ['message'=>'绑定失败,该帐号已经绑定其他帐号','code'=>0];
        
    }
    
    

}

