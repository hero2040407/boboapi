<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/3
 * Time: 21:04
 */

namespace BBExtend;
use think\Db;
use think\Request;

use app\push\controller\Pushmanager;
use app\record\controller\Recordmanager;

use BBExtend\Sys;
use BBExtend\message\Message;
use BBExtend\Currency;
use BBExtend\service\pheanstalk\Data;
use BBExtend\fix\TableType;
use BBExtend\common\Client;

class BBUser extends Level
{
    
    //转换类型
    public function conversion_for_login($UserDB)
    {
        $UserDB['rewind_count'] = 0;
        $UserDB['movies_count'] = 0;
        $UserDB['next_exp'] = 0;
        $UserDB['uid'] = (int)$UserDB['uid'];
        $UserDB['sex'] = (int)$UserDB['sex'];
        $UserDB['attestation'] = (int)$UserDB['attestation'];
        $UserDB['permissions'] = (int)$UserDB['permissions'];
        $UserDB['max_record_time'] = (int)$UserDB['max_record_time'];
        $UserDB['min_record_time'] = (int)$UserDB['min_record_time'];
        $UserDB['vip'] = (int)$UserDB['vip'];
        $UserDB['level'] = self::get_user_level($UserDB['uid']);
        $UserDB['black_count'] = 0;
        //         $UserDB['follow_count'] = Db::table('bb_focus')->where('uid',$UserDB['uid'])->count();
        //         $UserDB['focus_count'] = Db::table('bb_focus')->where('focus_uid',$UserDB['uid'])->count();
        
        // xieye 2016 10 24
        $UserDB['follow_count'] = \BBExtend\user\Focus::getinstance($UserDB['uid'])
           ->get_guanzhu_count();
        $UserDB['focus_count'] = \BBExtend\user\Focus::getinstance($UserDB['uid'])
           ->get_fensi_count();
        
        $UserDB['ranking'] = strval($UserDB['ranking']) ;
        
        
        // 谢烨，2018 04
        $user =  \BBExtend\model\User::find($UserDB['uid']);
        $UserDB['role'] = $user->role;
        $UserDB['frame'] = $user->get_frame();
        $UserDB['badge'] = $user->get_badge();
        
        $UserDB['is_starmaker'] = 0;
        if ( $user->role==2 ) {
            $UserDB['is_starmaker'] = 1;
        }
        return $UserDB;
    }
    
    
    
    
    
    
    
    
    
    //转换类型
    public function conversion($UserDB)
    {
        $UserDB['rewind_count'] = Pushmanager::get_rewind_count($UserDB['uid']);
        $UserDB['movies_count'] = Recordmanager::get_movies_count($UserDB['uid']);
        $UserDB['next_exp'] = self::get_next_exp($UserDB['uid']);
        $UserDB['uid'] = (int)$UserDB['uid'];
        $UserDB['sex'] = (int)$UserDB['sex'];
        $UserDB['attestation'] = (int)$UserDB['attestation'];
        $UserDB['permissions'] = (int)$UserDB['permissions'];
        $UserDB['max_record_time'] = (int)$UserDB['max_record_time'];
        $UserDB['min_record_time'] = (int)$UserDB['min_record_time'];
        $UserDB['vip'] = (int)$UserDB['vip'];
        $UserDB['level'] = self::get_user_level($UserDB['uid']);
        $UserDB['black_count'] = 0;
//         $UserDB['follow_count'] = Db::table('bb_focus')->where('uid',$UserDB['uid'])->count();
//         $UserDB['focus_count'] = Db::table('bb_focus')->where('focus_uid',$UserDB['uid'])->count();
        
        // xieye 2016 10 24
        $UserDB['follow_count'] = \BBExtend\user\Focus::getinstance($UserDB['uid'])
          ->get_guanzhu_count();
        $UserDB['focus_count'] = \BBExtend\user\Focus::getinstance($UserDB['uid'])
          ->get_fensi_count();
        
        $UserDB['ranking'] = strval($UserDB['ranking']) ;
        $UserDB['is_starmaker'] = Db::table('bb_users_starmaker')->where("uid",$UserDB['uid'] )
            ->count();
        
            // 谢烨，2018 04
            $user =  \BBExtend\model\User::find($UserDB['uid']);
            $UserDB['role'] = $user->role;
            $UserDB['frame'] = $user->get_frame();
            $UserDB['badge'] = $user->get_badge();
            
        return $UserDB;
    }
    
    
    //验证用户token
    // 201708 
    public static function validation_token($uid,$token)
    {
        $UserDB = self::get_user($uid);
        if (!$UserDB) {
            return false;
        }
        
        if ($UserDB['not_login'] !=0) {
            return false;
        }
        
        
        $_token = strtoupper($UserDB['userlogin_token']);
        if (strtoupper($token) == $_token)
        {
            return true;
        }
        return false;
    }
    
    
//设置用户地址
    public static function set_address($uid,$address)
    {
        $UserDB = self::get_user($uid);
        if ($UserDB)
        {
            if ($address)
            {
                $UserDB['address'] = $address;
                BBRedis::getInstance('user')->hSet($uid,'address',$address);
                Db::table('bb_users')->where('uid',$uid)->update(['address'=>$address]);
            }
        }
    }
    
    
    public static function get_specialty($uid)
    {
        $UserDB = self::get_user($uid);
        if ($UserDB)
        {
            return $UserDB['specialty'];
        }
        return '';
    }
    
    
    //谢烨 2016 10 20 大改，以后只需用此函数，即可得到准确的布尔类型vip的user数据行
    public static function get_user($uid)
    {
        
        $UserDB = Db::table('bb_users')->where('uid',$uid)->find();
        if (!$UserDB)
        {
            return null;
        }
        $UserDB['uid'] = (int)$UserDB['uid'];
        $UserDB['sex'] = (int)$UserDB['sex'];
        $UserDB['vip'] = (int)$UserDB['vip'];
        $UserDB['min_record_time'] = (int)$UserDB['min_record_time'];
        $UserDB['max_record_time'] = (int)$UserDB['max_record_time'];
        
        
//         //缓存中有，则必须每次都核查
//         if ($UserDB['vip'] ) {
//             if ($UserDB['vip_time'] < APP_TIME)
//             {
//                 $UserDB['vip'] = 0;
//                 self::update($UserDB);
//             }
//         }
        
        BBRedis::getInstance('user')->hMset($uid,$UserDB);
        return $UserDB;
    }
    
    
    //返回用户头像地址
    public static function get_user_pic_no_http($uid)
    {
        $UserDB = self::get_user($uid);
        $pic = $UserDB['pic'];
        if (!$pic)
        {
            $pic ='/public/toppic/topdefault.png';
        }
        return $pic;
    }
    
    
    public static function get_user_cover($uid)
    {
        $UserDB = self::get_user($uid);
        $pic = $UserDB['live_cover'];
        if (!$pic)
        {
            $pic ='/public/toppic/topdefault.png';
        }
        return \BBExtend\common\Image::geturl($pic);
    }
    
    
    //返回用户头像地址
    public static function get_userpic($uid)
    {
        $UserDB = self::get_user($uid);
        
        //谢烨 20160928
        if (!$UserDB) return '';
        
        $pic = $UserDB['pic'];
        

        return self::get_userpic_givepic($pic);
    }
    
    
    //返回用户头像地址，参数是用户数据库数据头像
    public static function get_userpic_givepic($pic)
    {
        
        $ServerURL = \BBExtend\common\BBConfig::get_server_url();
        
        return \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                $pic, $ServerURL.'/public/toppic/topdefault.png' );
       
    }
    
    
    //返回用户昵称
    public static function get_nickname($uid)
    {
        $nickname = self::get_user($uid)['nickname'];
        return $nickname;
    }
    
    
    //返回用户性别
    public static function get_usersex($uid)
    {
        $age=(int)self::get_user($uid)['sex'];
        return $age;
    }
    
    
    //返回用户年龄
    public static function get_userage($uid)
    {
        $age=date('Y') - substr(self::get_user($uid)['birthday'],0,4);
        return $age;
    }
    
    
    //返回用户地址
    public static function get_user_address($uid)
    {
        $address=self::get_user($uid)['address'];
        return $address;
    }

    
    //得到用户数据，vip是准确的。
    public static function get_user_vip($uid)
    {
        $UserDB = self::get_user($uid);
        if ($UserDB['vip'])
        {
            if ($UserDB['vip_time'] < time())
            {
                $UserDB['vip'] = 0;
                self::update($UserDB);
                return $UserDB;
            }
        }
        return $UserDB;
    }
    
    
    public static function get_user_level($uid)
    {
        $uid = intval($uid);
        $db = Sys::get_container_db();
        $sql = "select level from bb_users_exp where uid={$uid}";
        $level = $db->fetchOne($sql);
        return intval($level);
    }
    
    
    //设置个人认证 0：未认证 1审核中 2已认证
    public static function set_attestation($uid,$attestation = 2)
    {
      //  \BBExtend\Sys::debugxieye($uid."|" .$attestation );
        Db::table('bb_users')->where('uid',$uid)->update(['attestation'=>$attestation]);
        
        BBRedis::getInstance('user')->hSet($uid,'attestation',$attestation);
        //Db::table('bb_users')->where('uid',$uid)->update(['attestation'=>$attestation]);
        
//         $db = \BBExtend\Sys::get_container_db();
//         $sql="select * from bb_users where uid={$uid}";
//         \BBExtend\Sys::debugxieye($db->fetchRow($sql) );
        
    }

    
    public static function update($UserDB)
    {
        $SQLDB = array();
        $SQLDB['uid'] = (int)$UserDB['uid'];
        $SQLDB['nickname'] = $UserDB['nickname'];
        $SQLDB['pic'] = $UserDB['pic'];
        $SQLDB['phone'] = $UserDB['phone'];
        $SQLDB['device'] = $UserDB['device'];
        $SQLDB['address'] = $UserDB['address'];
        $SQLDB['login_time'] = $UserDB['login_time'];
        $SQLDB['login_count'] = $UserDB['login_count'];
        $SQLDB['logout_time'] = $UserDB['logout_time'];
        $SQLDB['userlogin_token'] = $UserDB['userlogin_token'];
        $SQLDB['sex'] = $UserDB['sex'];
        $SQLDB['email'] = $UserDB['email'];
        $SQLDB['birthday'] = $UserDB['birthday'];
        $SQLDB['attestation'] = $UserDB['attestation'];
        $SQLDB['sign_board'] = $UserDB['sign_board'];
        $SQLDB['series_sign_max'] = $UserDB['series_sign_max'];
        $SQLDB['series_sign'] = $UserDB['series_sign'];
        $SQLDB['signature'] = $UserDB['signature'];
        $SQLDB['vip'] = (int)$UserDB['vip'];
        $SQLDB['vip_time'] = $UserDB['vip_time'];
        $SQLDB['min_record_time'] = (int)$UserDB['min_record_time'];
        $SQLDB['max_record_time'] = (int)$UserDB['max_record_time'];
        BBRedis::getInstance('user')->hMset($UserDB['uid'],$SQLDB);
        Db::table('bb_users')->where('uid',$UserDB['uid'])->update($SQLDB);
    }
    
    
    /**
     * 开放平台注册
     * 
     * @param unknown $user_platform
     * @param unknown $nickname
     * @param unknown $device
     * @param unknown $login_type
     * @param unknown $login_address
     * @param unknown $pic
     * @param string $platform_id
     * @return string[]|number[]|unknown[]|NULL[]
     */
    public static function registered( $nickname, $device, $login_type,
            $login_address, $pic, $platform_id, $unionid) 
    {
        //增加新用户数据
        $time = time();
        $user_platform = md5( $platform_id );
        $uid = self::get_new_uid();
        
        // 谢烨 2018 05
        $obj = new \BBExtend\model\Minganci();
        $nickname =  $obj->filter_by_asterisk($nickname );
        
        
        $infodata = [
            'uid'      =>$uid,
            'nickname' => $nickname,
            'live_cover' => '',
            'device'=>$device,
            'email' => '',
            'login_type' => $login_type,
            'login_time' => $time,
            'address' => $login_address,
            'login_count'=>1,
            'register_time' => $time,
            'userlogin_token' => self::userlogin_token($user_platform),
            'pic'=>$pic,
            'birthday'=>'2014-01',
            'specialty'=>'',
            'is_online'=>1,
            'permissions'=>1,
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
                'unionid' =>$unionid,
            'user_agent'=>'(BoBo)/(1.2.0) (android;5.1.1)/bobo',
        ];
        if ($login_type == TableType::bb_users__login_type_jiqiren) {
            $infodata['permissions'] = TableType::bb_users__permissions_jiqiren ;
        }
        $temp  = Client::user_agent();
        if ($temp) {
           $infodata['user_agent'] = $temp; //如果存在，就覆盖默认值。
        }
        $qudao='';
        $agent =  $infodata['user_agent'];
        if (preg_match('#android#i', $agent)) {
            $pattern = '#^.+?android.+?/(\S+).*$#i';
            if (preg_match($pattern, $agent)) {
                $qudao = preg_replace($pattern, '$1', $agent);
            }else {
                $qudao='bobo';
            }
        }else {
            $qudao = 'ios';
        }
        $infodata['qudao'] = $qudao;
        
        if (!$pic)  {
            $infodata['pic'] = '/uploads/headpic/default.png';
        }
        if ($login_type == TableType::bb_users__login_type_shouji) {
            $infodata['phone'] = $platform_id;
            if ($infodata['phone'] == $infodata['nickname']) {
                $infodata['nickname'] = preg_replace('/^(\d{5})(\d{2})(\d{4})$/','$1^^$3',
                        $infodata['nickname']);
            }
        }
        //添加数据
        Db::table('bb_users')->insert($infodata);
        \BBExtend\user\Common::register_log($uid,$qudao);
        // 添加平台表数据
        Db::table('bb_users_platform')->insert([
            'platform_id'=>$user_platform,
            'original' => $platform_id,
            'create_time' =>time(),
            'uid'=>$uid,
            'type'=>$login_type
        ]);
        $infodata['uid'] = $uid;
        $infodata['sex'] = 0;
        $infodata['attestation'] = 0;
        // 添加任务
        $bb_user_task = ['uid'=> $uid,'time'=> time(),'complete_task_group'=>'0',
            'complete'=>'0,0,0','reward'=>'0,0,0','task_group'=>'1,2,3',
            'refresh_time'=>strtotime(date('Ymd')) + 104400];
        Db::table('bb_task_user')->insert($bb_user_task);
        $infodata['rewind_count'] = 0;
        $infodata['movies_count'] = 0;
        return $infodata;
    }
    
    
    /**
     * 大赛自动注册
     * @param unknown $user_platform 手机号md5后
     * @param unknown $nickname      呢称
     * @param unknown $device          空
     * @param unknown $login_type     固定3
     * @param unknown $login_address  空
     * @param unknown $pic            空
     * @param unknown $platform_id    手机号
     * @param string $birthday        生日，类似2014-01
     */
    public static function ds_registered($user_platform,$nickname,$device,$login_type,
             $login_address,$pic,$platform_id='', $birthday='2014-01' )
    {
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_users where phone=?";
        $result = $db->fetchOne($sql, $platform_id );
        if ($result) {
            return false;
        }
        $time = time();
        $uid = self::get_new_uid();
        $infodata = [
            'uid'      =>$uid,
            'nickname' => $nickname,
            'device'=>$device,
            'email' => '',
            'login_type' => $login_type,
            'login_time' => $time,
            'address' => $login_address,
            'login_count'=>1,
            'register_time' => $time,
            'userlogin_token' => self::userlogin_token($user_platform),
            'pic'=>$pic,
            'birthday'=>$birthday,
            'specialty'=>'',
            'is_online'=>1,
            'permissions'=>1,
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
            'user_agent'=>'(BoBo)/(1.2.0) (android;5.1.1)/race',
        ];
        //记录渠道，图片，手机号
        $infodata['qudao'] = 'race';
        $infodata['pic'] = '/uploads/headpic/default.png';
        $infodata['phone'] = $platform_id;
        //增加user用户数据
        Db::table('bb_users')->insert($infodata);
   //       $uid = (int)Db::getLastInsID();
        //记录注册日志    
        \BBExtend\user\Common::register_log($uid,$infodata['qudao']);
        //设置平台表
        Db::table('bb_users_platform')->insert(['platform_id'=>$user_platform,
            'original' => $platform_id,
            'create_time' =>time(),
            'uid'=>$uid,'type'=>$login_type]);
        //下面是设置任务
        $infodata['uid'] = $uid;
        $infodata['sex'] = 0;
        $infodata['attestation'] = 0;
        $bb_user_task = ['uid'=> $uid,'time'=> time(),'complete_task_group'=>'0',
            'complete'=>'0,0,0','reward'=>'0,0,0','task_group'=>'1,2,3',
            'refresh_time'=>strtotime(date('Ymd')) + 104400];
        Db::table('bb_task_user')->insert($bb_user_task);
        BBRedis::getInstance('user')->hMset($uid,$infodata);
       
        $infodata['rewind_count'] = 0;
        $infodata['movies_count'] = 0;
        // 注册后重要的后续工作。
        $UserDB = $infodata;
        \BBExtend\Currency::get_currency($uid);
        //xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
        \BBExtend\Level::get_user_exp($uid);
        // 谢烨，新功能。新用户注册，自动关注10000号用户，只在正式服。
        if (Sys::is_product_server()) {
            $help = \BBExtend\user\Focus::getinstance($uid);
            $help->focus_guy(10000) ;
        }
        
        // 系统消息
        Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content($nickname)->color(0x32c9c9))
            ->add_content(Message::simple()->content('欢迎您加入怪兽BOBO,在这里每个孩子'.
                '都是大明星，请共同维护怪兽岛绿色直播宣言——'))
            ->add_content(Message::simple()->content('BOBO童心梦，传递正能量')->color(0xf4a560))
            ->add_content(Message::simple()->content("。"))
            ->set_type(110)
            ->set_uid($uid)
            ->save_message();
        \app\monster\controller\Monsterapi::get_new_monster($uid);
        
   //     \BBExtend\Currency::add_currency($uid,1,10,'大赛注册新用户');
        self::regis_additional($uid);
        return $uid;
    }
    
    
    /**
     * 2017 05 新任务，20170529日下午1点到晚上9点的注册用户送波币
     * 
     */
    public static function regis_additional($uid)
    {
        // 谢烨，这里要 添加成就表 -------------------------------
        $user = \BBExtend\model\User::find($uid);
        $ach2 = new \BBExtend\model\Achievement();
        $ach2->create_default_by_user($user);
        // --------------------------------------------------
        
        $time1 = "2017-05-29 13:00:00";
        $time2  = "2017-05-29 21:00:00"; 
//         $time1 = "2017-05-26 15:00:00";
//         $time2  = "2017-05-26 15:20:00";
        
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);
        
        $time = time();
        if ($time > $time1 && $time <= $time2) {
            
            Currency::add_currency($uid,CURRENCY_GOLD,100,'注册奖励');
            
            Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content('系统奖励您100波币。'))
            ->set_type(110)
            ->set_uid($uid)
            ->send();
        }
        $db = Sys::get_container_db();
        
        $result_bonus=null;
        $result_lottery=null;
        
        // 20171016, 如果被邀请了，则送礼物给邀请方，和被邀请方。
        if ($user->login_type == 3) {
            
            // 商户抽奖
            $sql = "select * from bb_users_shanghu_invite_register where phone=? and is_complete=0";
            $invite_row = $db->fetchRow($sql, [$user->phone  ] );
            if ($invite_row) {
                //防止反复领取！！
                $sql="update bb_users_shanghu_invite_register set is_complete=1,target_uid={$uid}
                where id={$invite_row['id']}";
                $db->query($sql);
                // 商户表加注册人数
                $sql="update bb_shanghu set register_count = register_count+1
                where id={$invite_row['shanghu_id']}";
                $db->query($sql);
            
                $result_lottery = ['open_lottery' =>1,
                    'url'=>\BBExtend\common\BBConfig::get_server_url_https().
                    "/game/lottery/store_index/uid/{$uid}/userlogin_token/".$user->userlogin_token,
            
                    ];
            }
            // 这里做了判断，商户邀请优先。
            $sql = "select * from bb_users_invite_register where phone=? and is_complete=0";
            $invite_row = $db->fetchRow($sql, [$user->phone  ] );
            if ($invite_row && (!$result_lottery) ) {
                // 邀请人获得50积分,type=171
                Currency::add_score($invite_row['uid'], 50, '邀请奖励',171);
                //被邀请人获得500波币。type=172
                Currency::add_currency($uid,1, 500, '被邀请注册奖励',172);
                //防止反复领取！！
                $sql="update bb_users_invite_register set is_complete=1,target_uid={$uid} 
                    where id={$invite_row['id']}";
                $db->query($sql);
                
                $client = new \BBExtend\service\pheanstalk\Client();
                $client->add(
                    new Data($invite_row['uid'],171,['bonus' => ' 50 积分',], time()  )
                );
                $client->add(
                    new Data($uid,172,['bonus' => ' 500 BO币',], time()  )
                );
                $invite_user = \app\user\model\UserModel::getinstance($invite_row['uid']);
                
                $invite_user_detail = \BBExtend\model\User::find( $invite_row['uid'] );
                
                
                $result_bonus=[
                    'version' =>1,
                    'invite_user' =>['uid'=>$invite_row['uid'], 'head' =>$invite_user->get_userpic(),
                        'nickname' => $invite_user->get_nickname(),
                            'role'=>$invite_user_detail->role,
                            'frame'=>$invite_user_detail->get_frame(),
                            'badge'=>$invite_user_detail->get_badge(),
                            
                    ],
                    'list' =>[
                        ['word' =>' 500 BO币',
                         'pic' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(
                              '/public/pic/present/img_bobi@2x.png'),],
                    ],
                ];
                
            }
            
            
        }
        // 2017 新功能，没注册一个用户，他必须关注10个随机的
        $sql ="select permissions from bb_users where uid=".intval($uid);
        $per = $db->fetchOne($sql);
        
        if ($per==1) {
            $sql ="select uid from bb_users where permissions=3 limit 50";
            $ids = $db->fetchCol($sql);
            if ($ids) {
                shuffle($ids);
                $count =10;
                for ($i=0;$i<$count;$i++) {
                    if (isset($ids[$i])  ) {
                        $help = \BBExtend\user\Focus::getinstance($uid );
                        $help->focus_guy($ids[$i]);
                    }
                }
            }
        }
        return ['result_bonus' =>$result_bonus,'result_lottery' =>$result_lottery  ];
    }
    
    
    
    //创建令牌
    public static function userlogin_token($platform_id)
    {
        $userlogin_token  = md5(time().$platform_id);
        return $userlogin_token;
    }

    
    /**
     * 新用户得到uid
     */
    public static function get_new_uid()
    {
        if (Sys::get_machine_name()=='xieye') {
            return mt_rand(100000,999999);
        }
        
        $db = Sys::get_container_db();
        $sql="SELECT sh1.id FROM bb_user_suiji AS sh1
inner JOIN
(SELECT ROUND(RAND() * 5000000 + 3000000) AS id) AS sh2
WHERE
not exists (select 1 from bb_users where bb_users.uid = sh1.id)
and sh1.id>=sh2.id
limit 1
               ";
        $id = $db->fetchOne($sql);
        if ($id) {
            return $id;
        }else {
            return self::get_new_uid();
        }
    }

}