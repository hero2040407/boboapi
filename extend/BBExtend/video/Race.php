<?php
/**
 * 视频点赞类
 * 
 * User: 谢烨
 */
namespace BBExtend\video;



use think\Db;

use BBExtend\common\Str;
use BBExtend\Sys;
use BBExtend\common\User;
use BBExtend\BBUser;
use think\Session;

// use app\push\controller\Pushmanager;
// use app\record\controller\Recordmanager;
// use think\Request;

class Race
{
    /**
     * 
     * 得到一个大赛的 所有主办方和协办方。
     * 为了速度，使用了一分钟缓存！
     * 
     * 貌似，最合适的是
     * 
     */
    public static function get_owner($ds_id)
    {
        $ds_id = intval($ds_id);
        if (!$ds_id) {
            return [];
        }
        
        $redis = Sys::getredis11();
        $key = 'ds:owner:' . $ds_id ;
        $owners = $redis->sMembers($key);
        if ($owners) {
            return $owners;
        }
        // 如果不存在
        $db = Sys::get_container_db();
        $sql = "select uid from ds_race where id ={$ds_id}";
        $uid = $db->fetchOne($sql);
        if (!$uid) {
            return [];
        }
        
        $sql = "select uid from ds_sponsor where ds_id ={$ds_id}";
        $uid2_arr = $db->fetchCol($sql);
        
        $result = [];
        $result[]= $uid;
        foreach ($uid2_arr as $v) {
            $result[] = $v;
        }
        foreach ($result as $v) {
            $redis->sAdd($key,$v);
        }
        $redis->setTimeout($key,60);
        return $redis->sMembers($key); 
         
    }
    
    
//     // 网络报名, 注意参数里的大赛id ，渠道id---
//     public static function register_web_new($uid=0,$phone='',$name='',$sex=1,$birthday='',$ds_id=0,
//             $area1_name='',$area2_name='',$height=0,$weight=0,$qudao_id=0,$pic='' )
//     {
        
      
//     }
    
    
    
    
//     // 网络报名, 注意参数里的大赛id ，渠道id---
//     public static function register_web_new($uid=0,$phone='',$name='',$sex=1,$birthday='',$ds_id=0,
//             $area1_name='',$area2_name='',$height=0,$weight=0,$qudao_id=0,$pic='' )
//     {
//    //     Sys::display_all_error();
//         $ds_id = intval( $ds_id );
//         $phone = strval($phone);
//         $name =strval($name);
//         $sex = intval($sex)?1:0;
//         $birthday = strval($birthday);
//         //   $qudao_id = intval($qudao_id);
//         $uid = intval($uid);
        
//         $height=intval( $height );
//         $weight = intval( $weight );
//         $qudao_id = intval($qudao_id);
        
//     //    $qudao_id = self::check_qudao_id($ds_id, $qudao_id);
// //         if ($qudao_id === false ){
// //             return ['code'=>0,'message'=>'渠道错误，请联系大赛主办方。'];
// //         }
        
//         if ( $height < 200 && $height>0 ) {
//             //$new['height'] = $height;
//         }else {
//             return ['code'=>0,'message'=>'请填写正确的身高'];
//         }
//         if ( $weight < 100 && $weight>5 ) {
//             //$new['weight'] = $weight;
//         }else {
//             return ['code'=>0,'message'=>'请填写正确的体重'];
//         }
//         if (!$name) {
//             return ['code'=>0,'message' => '姓名错误' ];
//         }
//         if (!Str::is_valid_phone($phone)) {
//             return ['code'=>0,'message' => '电话错误' ];
//         }
//         if (!Str::is_valid_birthday_month($birthday)) { // 必须 2018-01
//             return ['code'=>0,'message' => '生日错误' ];
//         }
//         $db = Sys::get_container_db();
//         $time = time();
//         //先查大赛信息
//         $sql ="select * from ds_race where id = {$ds_id}
//                  and register_start_time < {$time}
//                  and register_end_time > {$time}
//                  ";
//       //  Sys::debugxieye($sql);
        
//         $ds = $db->fetchRow($sql);
        
//         if (!$ds) {
//             return ['code'=>0,'message' => '大赛报名已经结束。' ];
//         }
//         $race = \BBExtend\model\Race::find( $ds_id );
//         if (!User::exists($uid)) {
//             return ['code'=>0,'message' => 'uid错误' ];
//         }
        
        
//         //如果用户不存在，必须看用户是否存在，存在找到uid，否则注册得到uid
//         if (!$uid) {
//             return ['code'=>0,'message' => 'uid错误' ];
//         }
        
//         //重复报名是一种错误
//         //         $sql="select * from ds_register_log where  zong_ds_id = {$ds_id}
//         //            and uid ={$uid} ";
//         //         $temp = $db->fetchRow($sql,$phone);
//         //         if ($temp) {
//         //             return ['code'=>0, 'data'=>[ 'status'=>$status,'money' => $ds['money'],'openid'=> $openid,],
//         //                     'message'=>'您已报名过，不可以重复报名' ];
//         //         }
        
//         // 现在，获取状态。
//         $status_arr = RaceStatus::get_status($uid, $ds_id);
        
        
//         if ( $status_arr['data']['status'] == \BBExtend\fix\Err::code_not_bind ) {
//             return ['code'=>0,'message' => '大赛报名需要绑定手机号' ];
//         }
        
        
//         if ( $status_arr['data']['status'] != 2 ) {
//             return ['code'=>0,'message' => '您已报名过' ];
//         }
        
     
        
//         // 查赛区。
//         if ($race->online_type==1 && $qudao_id !=0 ) { //1 纯线上，2线下
//             return ['code'=>0,'message' => '线上比赛无需赛区。' ];
//         }
//         if ($race->online_type==2  ) { //1 纯线上，2线下
//             if ( $qudao_id==0 ) {
            
//                  return ['code'=>0,'message' => '请选择赛区。' ];
//             }
//             $field = \BBExtend\backmodel\RaceField::find( $qudao_id );
//             if ($field->race_id  != $ds_id ) {
//                 return ['code'=>0,'message' => '赛区错误。' ];
//             }
            
//         }
        
        
        
      
//         // 1需要填档案 和 付钱
//         // 2需要填档案 和 不要付钱
//         // 3不需要填档案 和 要付钱
//         // 4 不需要填档案 和 不要付钱
//         //现在记录到注册表里。表示报名过了。
        
//         if ($ds['money'] >=0.001) { // 如果表中为1，则表示需要付钱。
//             $has_pay = 0;
//         } else {
//             $has_pay = 1;
//         }
        
        
        
        
//         $db->insert("ds_register_log", [
//                 'ds_id' =>$qudao_id,
//                 'zong_ds_id' => $ds_id,
//                 'uid' =>$uid,
//                 'create_time' => time(),
//                 'money' =>0,// 未支付过，固定0
//                 'phone' => $phone,
//                 'sex' => $sex,
//                 'birthday' => $birthday,
//                 'name' => $name,
//                 'has_pay' => $has_pay,
//                 'has_dangan' =>1, // xieye 20180416 ，这里固定填写为1
//                 'area1_name' =>$area1_name,
//                 'area2_name' =>$area2_name,
//                 'height' => $height,
//                 'weight' => $weight,
//                 'is_web_baoming' =>1,
//                 'qudao_id' =>$qudao_id,
//                 'pic' =>$pic,
//         ]);
//         $last_id = $db->lastInsertId();
        
//        $arr= RaceStatus::get_status($uid, $ds_id);
//        $code = $arr['data']['status'];
        
//         return [ 'code'=>1,
//                 'data'=>['status'=>  $code,
//                         'has_pay' => $has_pay,
//                         'money' => $ds['money'],  ] ];
//     }
    
    
    
    
    
    
    
    
    
    
    
    
    // 网络报名, 注意参数里的大赛id ，渠道id---
    public static function register_web($uid=0,$phone='',$name='',$sex=1,$birthday='',$ds_id=0,
            $area1_name='',$area2_name='',$height=0,$weight=0,$openid='',$qudao_id=0,$pic='' )
    {
   //     Sys::display_all_error();
        $ds_id = intval( $ds_id );
        $phone = strval($phone);
        $name =strval($name);
        $sex = intval($sex)?1:0;
        $birthday = strval($birthday);
     //   $qudao_id = intval($qudao_id);
        $uid = intval($uid);
        
        $height=intval( $height );
        $weight = intval( $weight );
        
        $qudao_id = self::check_qudao_id($ds_id, $qudao_id);
        if ($qudao_id === false ){
            return ['code'=>0,'message'=>'渠道错误，请联系大赛主办方。'];
        }
        
        if ( $height < 200 && $height>0 ) {
            //$new['height'] = $height;
        }else {
            return ['code'=>0,'message'=>'请填写正确的身高'];
        }
        if ( $weight < 100 && $weight>5 ) {
            //$new['weight'] = $weight;
        }else {
            return ['code'=>0,'message'=>'请填写正确的体重'];
        }
        if (!$name) {
            return ['code'=>0,'message' => '姓名错误' ];
        }
        if (!Str::is_valid_phone($phone)) {
            return ['code'=>0,'message' => '电话错误' ];
        }
        if (!Str::is_valid_birthday_month($birthday)) { // 必须 2018-01
            return ['code'=>0,'message' => '生日错误' ];
        }
        $db = Sys::get_container_db();
        $time = time();
        //先查大赛信息
        $sql ="select * from ds_race where id = {$ds_id}
                 and register_start_time < {$time}
                 and register_end_time > {$time}
                 ";
        $ds = $db->fetchRow($sql);
        if (!$ds) {
            return ['code'=>0,'message' => '大赛报名已经结束。' ];
        }
        
        if (!User::exists($uid)) {
            return ['code'=>0,'message' => 'uid错误' ];
        }
        
        
        //如果用户不存在，必须看用户是否存在，存在找到uid，否则注册得到uid
        if (!$uid) {
            return ['code'=>0,'message' => 'uid错误' ];
        }
        
        //重复报名是一种错误
//         $sql="select * from ds_register_log where  zong_ds_id = {$ds_id}
//            and uid ={$uid} ";
//         $temp = $db->fetchRow($sql,$phone);
//         if ($temp) {
//             return ['code'=>0, 'data'=>[ 'status'=>$status,'money' => $ds['money'],'openid'=> $openid,], 
//                     'message'=>'您已报名过，不可以重复报名' ];
//         }
        
        // 现在，获取状态。
        $status_arr = self::get_user_race_status($uid, $ds_id);
        $status= $status_arr['data'];
        // xieye ,报名第一步可能，只有，
        if ($status!= 11) {
            return ['code'=> 0, 'message'=>self::get_errcode($status), 'status'=>$status, ];
            
        }
        // 1需要填档案 和 付钱
        // 2需要填档案 和 不要付钱
        // 3不需要填档案 和 要付钱
        // 4 不需要填档案 和 不要付钱
        //现在记录到注册表里。表示报名过了。
        
        if ($ds['money'] >=0.001) { // 如果表中为1，则表示需要付钱。
            $has_pay = 0;
        } else {
            $has_pay = 1;
        }
        
        
       
        
        $db->insert("ds_register_log", [
                'ds_id' =>$qudao_id,
                'zong_ds_id' => $ds_id,
                'uid' =>$uid,
                'create_time' => time(),
                'money' =>0,// 未支付过，固定0
                'phone' => $phone,
                'sex' => $sex,
                'birthday' => $birthday,
                'name' => $name,
                'has_pay' => $has_pay,
                'has_dangan' =>1, // xieye 20180416 ，这里固定填写为1
                'area1_name' =>$area1_name,
                'area2_name' =>$area2_name,
                'height' => $height,
                'weight' => $weight,
                'is_web_baoming' =>1,
                'qudao_id' =>$qudao_id,
                'pic' =>$pic,
        ]);
        $last_id = $db->lastInsertId();
        
        if ($qudao_id == DASAI_PUSH_QUDAO_ID ) {
        //  $uid=0,$id=0,$time=0,$type=1
            $data= new \BBExtend\service\pheanstalk\DataDasai( $uid,$last_id,time(),1);
            //  $service = new
            $client = new \BBExtend\service\pheanstalk\Client();
            $client->add_dasai($data);
        }
        
        return [ 'code'=>1, 
                
//                 'data'=>['status'=>  18,
//                         'money' => $ds['money'],'openid'=> $openid,  ] ];
                
                'data'=>['status'=>  self::get_user_race_status_code($uid, $ds_id), 
                        'money' => $ds['money'],'openid'=> $openid,  ] ];
    }
    
    
    
    public static function register_app($uid=0,$phone='',$name='',$sex=1,$birthday='',$qudao_id=0,
            $captcha='',$area1_name='',$area2_name='',$height=0,$weight=0 )
    {
        //  Sys::debugxieye(12333);
        Sys::display_all_error();
        // $check = strval($check);
        $phone = strval($phone);
        $name =strval($name);
        $sex = intval($sex)?1:0;
        $birthday = strval($birthday);
        $qudao_id = intval($qudao_id);
        $uid = intval($uid);
    
        $captcha_session_key = 'ds_captcha';
    
        //         if (!$captcha  ) {
        //             return ['code'=>0,'message' => '请填写图片验证码' ];
        //         }
        //         if (!Session::has($captcha_session_key)   ) {
        //             return ['code'=>0,'message' => '图片验证码未设置错误' ];
        //         }
    
        //         if ($captcha != Session::get($captcha_session_key)) {
        //             return ['code'=>0,'message' => '请填写正确的图片验证码' ];
        //         }
         
        if (!$name) {
            return ['code'=>0,'message' => '姓名错误' ];
        }
        if (!Str::is_valid_phone($phone)) {
            return ['code'=>0,'message' => '电话错误' ];
        }
        if (!Str::is_valid_birthday_month($birthday)) {
            return ['code'=>0,'message' => '生日错误' ];
        }
        //        $miyao = 'KW7Fsl5d2mhLg';
        //         $right_check= strtolower( md5($miyao. date("Ymd")  ));
        //         if ($check != $right_check) {
        //             return ['code'=>0,'message' => '参数错误' ];
        //         }
        $db = Sys::get_container_db();
        $sql = "select * from ds_race where id = {$qudao_id} and is_active=1 and level=2";
        $qudao_row = $db->fetchRow($sql);
        if (!$qudao_row) {
            return ['code'=>0,'message' => '大赛参数错误' ];
        }
        $time = time();
        //先查大赛信息
        $sql ="select * from ds_race where id = {$qudao_row['parent']} 
                 and register_start_time < {$time} 
                 and register_end_time > {$time}
                 ";
        $ds = $db->fetchRow($sql);
        if (!$ds) {
            return ['code'=>0,'message' => '大赛不存在' ];
        }
        $ds_id = $ds['id'];
    
        //如果传了uid，则必须存在，否则错误！
       
            if (!User::exists($uid)) {
                return ['code'=>0,'message' => 'uid错误' ];
            }
        
    
        //如果用户不存在，必须看用户是否存在，存在找到uid，否则注册得到uid
        if (!$uid) {
            return ['code'=>0,'message' => 'uid错误' ];
        }
    
        //重复报名是一种错误
        $sql="select * from ds_register_log where  zong_ds_id = {$ds_id}
           and uid !={$uid} and phone=?";
        $temp = $db->fetchRow($sql,$phone);
        if ($temp) {
            return ['code'=>0, 'message'=>'该手机号已报过名，请使用手机号登录app进行大赛报名。' ];
        }
    
        // 现在，获取状态。
        $status_arr = self::get_user_race_status($uid, $ds_id);
        $status= $status_arr['data'];
        // xieye ,报名第一步可能，只有，
        if ($status!= 11) {
            return ['code'=> $status,'message'=>'' ];
        }
        
        
        // 1需要填档案 和 付钱
        // 2需要填档案 和 不要付钱
        // 3不需要填档案 和 要付钱
        // 4 不需要填档案 和 不要付钱
        //现在记录到注册表里。表示报名过了。
    
        if ($ds['money'] >=0.001) { // 如果表中为1，则表示需要付钱。
            $has_pay = 0;
        } else {
            $has_pay = 1;
        }
        if ($ds['has_dangan']) {
            $has_dangan = 0;
        } else {
            $has_dangan =1;
        }
        $db->insert("ds_register_log", [
            'ds_id' =>$qudao_id,
            'zong_ds_id' => $ds_id,
            'uid' =>$uid,
            'create_time' => time(),
            'money' =>0,// 未支付过，固定0
            'phone' => $phone,
            'sex' => $sex,
            'birthday' => $birthday,
            'name' => $name,
            'has_pay' => $has_pay,
            'has_dangan' =>$has_dangan,
            'area1_name' =>$area1_name,
            'area2_name' =>$area2_name,
                'height' => $height,
                'weight' => $weight,
        ]);
        
        $sql="update bb_users set phone=? where uid = {$uid}";
        $db->query( $db->quoteInto($sql, $phone) );
//         $dangan_config=[];
//         // 如果需要档案，现在就返回档案
//         if ($ds['has_dangan']) {
//             $sql ="select * from ds_dangan_config where ds_id = {$ds_id}
//             order by type asc, sort desc ";
//             $dangan_config = $db->fetchAll($sql);
//         }
//         $money = $ds['money'];
        // 谢烨，安全措施。
        Session::delete($captcha_session_key);
        return ['code'=>self::get_user_race_status_code($uid, $ds_id), ];
    }
     
  
    public static function register($uid=0,$phone='',$name='',$sex=1,$birthday='',$qudao_id=0,
            $captcha='',$area1_name='',$area2_name='',$height=0,$weight=0 )
    {
        $phone = strval($phone);
        $name =strval($name);
        $sex = intval($sex)?1:0;
        $birthday = strval($birthday);
        $qudao_id = intval($qudao_id);
        $uid = intval($uid);
    
        $captcha_session_key = 'ds_captcha';
    
//         if (!$captcha  ) {
//             return ['code'=>0,'message' => '请填写图片验证码' ];
//         }
//         if (!Session::has($captcha_session_key)   ) {
//             return ['code'=>0,'message' => '图片验证码未设置错误' ];
//         }
    
//         if ($captcha != Session::get($captcha_session_key)) {
//             return ['code'=>0,'message' => '请填写正确的图片验证码' ];
//         }
     
        if (!$name) {
            return ['code'=>0,'message' => '姓名错误' ];
        }
        if (!Str::is_valid_phone($phone)) {
            return ['code'=>0,'message' => '电话错误' ];
        }
        if (!Str::is_valid_birthday_month($birthday)) {
            return ['code'=>0,'message' => '生日错误' ];
        }
        $db = Sys::get_container_db();
        $sql = "select * from ds_race where id = {$qudao_id} and is_active=1 and level=2";
        $qudao_row = $db->fetchRow($sql);
        if (!$qudao_row) {
            return ['code'=>0,'message' => '大赛参数错误' ];
        }
        
        $time =time();
        //先查大赛信息
        $sql ="select * from ds_race where id = {$qudao_row['parent']}
                 and register_start_time < {$time} 
                 and register_end_time > {$time}
        ";
        $ds = $db->fetchRow($sql);
        if (!$ds) {
            return ['code'=>0,'message' => '大赛不存在' ];
        }
        $ds_id = $ds['id'];
        
        //如果传了uid，则必须存在，否则错误！
        if ($uid) {
            if (!User::exists($uid)) {
                return ['code'=>0,'message' => 'uid错误' ];
            }
        }
    
        //如果用户不存在，必须看用户是否存在，存在找到uid，否则注册得到uid
        if (!$uid) {
            
//             $sql ="select uid from bb_users where phone=? order by uid asc limit 1";
//             $uid = $db->fetchOne($sql,$phone);
            $sql = "select * from bb_users_platform where type=3 and platform_id=?";
            $temp = $db->fetchRow($sql, md5( $phone ));
            if ($temp) {
                $uid = $temp['uid'];
            }
            
//             $PlatformDB = Db::table('bb_users_platform')->where(['platform_id'=>md5($platform_id),
//                     'type'=>$login_type ])->find();
            
            
            if (!$uid) {
                $platform_id = $phone;
                $user_platform = md5($platform_id);
                $nickname='小朋友';
                $device='';
                $login_type=3;
                $login_address='';
                $pic='';
                $uid=BBUser::ds_registered($user_platform,$nickname,$device,$login_type,
                        $login_address,$pic,$platform_id, $birthday );
            }
        }
    

        // 现在，获取状态。
        $status_arr = self::get_user_race_status($uid, $ds_id);
        $status= $status_arr['data'];
        // xieye ,报名第一步可能，只有，
        if ($status!= 11) {
            return ['code'=> $status,'message'=>'','data'=>[ 'uid'=> $uid ]];
        }
        
        // 1需要填档案 和 付钱
        // 2需要填档案 和 不要付钱
        // 3不需要填档案 和 要付钱
        // 4 不需要填档案 和 不要付钱
        //现在记录到注册表里。表示报名过了。
    
        if ($ds['money'] >=0.001) { // 如果表中为1，则表示需要付钱。
            $has_pay = 0;
        } else {
            $has_pay = 1;
        }
        if ($ds['has_dangan']) {
            $has_dangan = 0;
        } else {
            $has_dangan =1;
        }
        
        $db->insert("ds_register_log", [
            'ds_id' =>$qudao_id,
            'zong_ds_id' => $ds_id,
            'uid' =>$uid,
            'create_time' => time(),
            'money' =>0,
            'phone' => $phone,
            'sex' => $sex,
            'birthday' => $birthday,
            'name' => $name,
            'has_pay' => $has_pay,
            'has_dangan' =>$has_dangan,
            'area1_name' =>$area1_name,
            'area2_name' =>$area2_name,
                'height' => $height,
                'weight' => $weight,
        ]);
        $sql="update bb_users set phone=? where uid = {$uid}";
        $db->query( $db->quoteInto($sql, $phone) );
        
        // 谢烨，安全措施。
        Session::delete($captcha_session_key);
        return ['code'=>self::get_user_race_status_code($uid, $ds_id),'data'=>[ 'uid'=> $uid] ];
    }
    
    public static function get_errcode($status)
    {
        $arr=[
                '1' => '大赛报名未开始，或已结束',
                '2' => '大赛活动未开始',
                '10' => '大赛报名已开始，用户完成报名，大赛比赛时间未开始',
                '11' => '大赛报名已开始，用户未报名',
                '13' => '大赛报名已开始，用户完成报名，未上传视频',
                '14' => '大赛报名已开始，用户完成报名，上传视频，待审核',
                '15' => '大赛报名已开始，用户完成报名，上传视频，审核成功',
                '16' => '大赛报名已开始，用户完成报名，上传视频，审核失败',
                '17' => '大赛报名已开始，用户档案未填',
                '18' => '大赛报名已开始，用户报名费未付',
                '19' => '大赛活动已开始',
                '21' => '大赛活动已结束',
                
                
                
        ];
        return $arr[$status];
    }
    
    
    public static function get_user_race_status_code($uid,$ds_id)
    {
        $arr = self::get_user_race_status($uid,$ds_id);
        return $arr['data'];
    }
    
    
    public static function get_user_race_status_v2($uid,$ds_id)
    {
        $result = self::get_user_race_status($uid, $ds_id);
        if ($result['data']== 11) {
            // xieye ,这里查一下，是否绑定手机
            $bind_help = new \BBExtend\user\BindPhone($uid);
            if (!$bind_help->check()) {
                 $temp =  $bind_help->get_result_arr();
                 return ['code'=>1,'data' => $temp['code']  ]; // 未报名
            }
            
        }
        return $result;
    }
    
    public static function get_user_race_status($uid,$ds_id)
    {
        $uid = intval($uid);
        $ds_id = intval($ds_id);
        $time = time();
        $db = Sys::get_container_db();
        $user = \app\user\model\UserModel::getinstance($uid);
        if ($user->error==1) {
            return ['code'=>0, 'message'=>'用户不存在','data' => 0];
        }
        $sql ="select * from ds_race where level=1 and id = {$ds_id}";
        $ds = $db->fetchRow($sql);
        if (!$ds) {
            return ['code'=>0, 'message'=>'大赛不存在','data' => 0];
        }
         
        if ($time > $ds['end_time']) { //大赛已结束
            return ['code'=>1,'data' => 21  ];
        }
         
         
        // 谢烨，现在确定是否是主办方。
        $master_arr=[];
        $master_arr []= $ds["uid"];
        
        $sql = "select  uid from ds_sponsor where ds_id ={$ds_id}";
//         return $db->fetchCol($sql);
        
        $temp = $db->fetchCol($sql);
        foreach ($temp as $v) {
            $master_arr[]= $v;
        }
        if (in_array($uid, $master_arr )) {
            $is_master=1;
        }else {
            $is_master=0;
        }
         
        if ($is_master) {
            //如果是主办方，有，
            if ($time > $ds['start_time'] ) { //大赛已开始，主办方 可以直播
                return ['code'=>1,'data' => 19  ];
            }else {
                return ['code'=>1,'data' => 2  ]; // 不能直播
            }
        }
        // 下面全部是普通用户了！！！
         
        // 看是否报名开始
        if ($ds['register_start_time'] > $time || $ds['register_end_time'] < $time ) {
            return ['code'=>1,'data' => 1  ]; // 不能报名哦。
        }
         
         
        $sql ="select ds_record.*,
        (select audit from bb_record where bb_record.id = ds_record.record_id) audit
        from ds_record where uid={$uid} and ds_id = {$ds_id}";
        $records = $db->fetchAll($sql);
        $i =3; //没有record。
        foreach ($records as $v ) {
            if ( $v['audit'] == 2 ) {
                $i =2;
                break;
            }
        }
        foreach ($records as $v ) {
            if ( $v['audit'] == 0 ) {
                $i =0;
                break;
            }
        }
        foreach ($records as $v ) {
            if ( $v['audit'] == 1 ) {
                $i =1;
                break;
            }
        }
//         return $i;//
        $result  = $i;
        if ($result==0) {
            return ['code'=>1,'data' => 14  ]; // 待审核
        }
        if ($result==1) {
            return ['code'=>1,'data' => 15  ]; // 审核成功
        }
        if ($result==2) {
            return ['code'=>1,'data' => 16  ]; // 审核失败
        }
        // 没有视频，则看是否报名，以及报名状态
        $sql ="select * from ds_register_log where zong_ds_id={$ds_id} and uid ={$uid}";
        $row = $db->fetchRow($sql);
        if (!$row) {
            
            // xieye ,这里查一下，是否绑定手机
//             $bind_help = new \BBExtend\user\BindPhone($uid);
//             if (!$bind_help->check()) {
//                  $temp =  $bind_help->get_result_arr();
//                  return ['code'=>1,'data' => $temp['code']  ]; // 未报名
//             }
            
            
            return ['code'=>1,'data' => 11  ]; // 未报名
        }
        if ($row['has_dangan'] && $row['has_pay'] ) {
            if ($time > $ds['start_time'] ) { // 如果比赛时间已经开始
                return ['code'=>1,'data' => 13  ]; // 请上传视频
            }else {
                return ['code'=>1,'data' => 10  ]; // 上传视频黑色。
            }
        }else {
            
            if ($row['has_dangan'] == 0 ) {
                return ['code'=>1,'data' => 17  ]; // 继续报名,填档案
            }
            if ($row['has_pay'] == 0 ) {
                return ['code'=>1,'data' => 18  ]; // 继续报名，支付。
            }
//             return ['code'=>1,'data' => 12  ]; // 继续报名
        }
         
        
    }
    
    /**
     * 测试正确的渠道号，注意必须用 === 处理本函数返回。
     * 返回false，表示有渠道但是错误。
     * 
     * 否则 返回一个正确的渠道号。
     * 
     * @param unknown $ds_id
     * @param number $qudao_id
     */
    public static function check_qudao_id($ds_id, $qudao_id=0)
    {
        $db = Sys::get_container_dbreadonly();
        if (!$qudao_id) {
            // 假设 ds_id不存在，则返回false
            // 假设 ds_id没有任何is_app=1渠道，返回0
            // 否则返回查到的一条。
            
            $sql="select * from ds_race where parent=0 and id = ?";
            $row = $db->fetchRow($sql,[ $ds_id ]);
            if (!$row) {
                return false;
            }
            $sql = "select * from ds_race where parent=? and is_app=1 limit 1  ";
            $child = $db->fetchRow($sql,[ $row['id'] ]);
            if (!$child) {
                return 0;
            }
            return $child['id'];
            
            
        }else {
            $sql="select * from ds_race where parent=? and id = ?";
            $row = $db->fetchRow($sql,[ $ds_id , $qudao_id]);
            if (!$row) {
                return false;
            }
            
            return intval( $qudao_id );
        }
        
    }
    
    

}