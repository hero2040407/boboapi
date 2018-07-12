<?php
namespace BBExtend\user;

/**
 * 拉黑和举报类
 * 
 * User: 谢烨
 */

use BBExtend\Sys;
use think\Db;
use BBExtend\BBUser;
use BBExtend\Focus as Fo2;
use think\Request;

class Common 
{
    
    /**
     * 201702 目前暂时被你可能感兴趣的人调用
     * @param unknown $uid
     * selfuid, 这是当前用户
     */
    public static function get_xijie($uid,$selfuid)
    {
        $uid =intval($uid);
        $selfuid = intval($selfuid);
        
        $user = BBUser::get_user($uid);
        if ($user) {
            $pic = BBUser::get_userpic($uid);
            $vip =$user['vip'];
            
            //年龄
            $age=date('Y') - substr( $user['birthday'], 0,4 );
            $phone ='';
            if (preg_match('/^[\d]{11}$/', $user['phone'])) {
                $phone = $user['phone'];
            }
            
            return [
                'uid' =>  $uid,
                'pic' => $pic,
                'vip' => $vip,
                //'address' => strval($user['address']),
                'nickname'=> $user['nickname'],
                'age'     => $age,
                'is_focus' => Fo2::get_focus_state($selfuid, $uid),
                'sex'     => $user['sex'],
                'phone'  =>$phone,
            ];
        }else {
            return [];
        }
    }
    
    public static function get_qudao()
    {
        $agent = Request::instance()->header('User-Agent');
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
        return $qudao;
    }
    
    /**
     * 201702 目前暂时被你可能感兴趣的人调用
     * @param unknown $uid
     * selfuid, 这是当前用户
     */
    public static function get_xijie_hobby($uid,$selfuid)
    {
//        $db = Sys::get_container_db();
//         //总特长表$spec_list
//         $sql="select id,name from bb_speciality";
//         $temp = $db->fetchAll($sql);
//         $spec_list = [];
//         foreach ($temp as $v) {
//             $spec_list[$v['id']] = $v['name'];
//         }
        
        $result = self::get_xijie($uid, $selfuid);
       // foreach ($result as $k=> $v) {
            $user = \app\user\model\UserModel::getinstance($result['uid']);
            $result['specialty']  = $user->get_hobbys();
            
            $result['address']  = $user->get_user_address();
            $result['level']  = $user->get_user_level();
            
            
            $help = \BBExtend\user\Focus::getinstance($uid);
            $fans_count = $help->get_fensi_count();
            $fans_count = intval( $fans_count );
            
            $follow_count = $help->get_guanzhu_count();
            $follow_count = intval( $follow_count );
            
            $result['fans_count']  = $fans_count;
            $result['follow_count']  = $follow_count;
            
            
            
       // }
        return $result;
    
    }
    
    
    
    public static function get_userlist2($list)
    {
        $data =[];
        foreach ($list as $uid) {
            //             $uid = $v['uid'];
            
            $user = \BBExtend\model\User::find( $uid );
            if ($user) {
                
                $pic = $user->get_userpic();
                
                $data[]= [
                        'uid' =>  $uid,
                        'pic' => $pic,
                        'vip' => 0,
                        'address' =>  strval( $user->address ),
                        'nickname'=>  $user->get_nickname() ,
                        
                        'role' => $user->role,
                        'badge' => $user->get_badge(),
                        'frame' => $user->get_frame(),
                ];
            }
            
            
//             $user = BBUser::get_user($uid);
//             if ($user) {
//                 $pic = BBUser::get_userpic($uid);
//                 $vip =$user['vip'];
//                 $data[]= [
//                     'uid' =>  $uid,
//                     'pic' => $pic,
//                     'vip' => $vip,
//                     'address' => strval($user['address']),
//                     'nickname'=> $user['nickname'],
//                 ];
    
//             } else {
//                 //                 $pic='';
//                 //                 $vip=0;
//             }
             
        }
        return $data;
    }
    
    
    public static function get_userlist($list)
    {
        $data =[];
        foreach ($list as $uid) {
//             $uid = $v['uid'];
            $user = BBUser::get_user($uid);
            if ($user) {
                $pic = BBUser::get_userpic($uid);
                $vip =$user['vip'];
                $data[]= [
                    'uid' =>  $uid,
                    'pic' => $pic,
                    'vip' => $vip,
                ];
                
            } else {
//                 $pic='';
//                 $vip=0;
            }
           
        }
        return $data;
    }
    
    /**
     * 返回 android ，还是ios
     * 
     * @param unknown $uid
     */
    public static function get_phone_type($uid)
    {
        $db = Sys::get_container_db();
        $uid = intval($uid);
        $sql="select * from bb_umeng_push_msg where uid = {$uid}";
        $result = $db->fetchRow($sql);
        //Android的device_token是44位字符串, iOS的device-token是64位。
        if ($result) {
            $token = $result['token'];
            if (strlen($token) == 44 ) {
                return "android";
            }else {
                return 'ios';
            }
        }
        $sql="select user_agent from bb_users where uid={$uid}";
        $result = $db->fetchOne($sql);
        if ($result) {
            $result = strtolower($result);
            if (preg_match('#android#', $result)) {
                return "android";
            }else {
                return 'ios';
            }
            
        }
        return 'android';
    }
    
    /**
     * 注册日志
     * @param unknown $uid
     */
    public static function register_log($uid,$qudao2)
    {
        $db = Sys::get_container_db();
        $uid = intval($uid);
        $sql="select * from bb_users where uid = {$uid}";
        $row = $db->fetchRow($sql);
        // 机器人放过，不记录
        if ($row['permissions']>4) {
            return;
        }
        
        
        $agent =  $row['user_agent'];
        if (preg_match('#android#', $agent)) {
            $pattern = '#^.+?android.+?/(\S+).*$#';
            if (preg_match($pattern, $agent)) {
                $qudao = preg_replace($pattern, '$1', $agent);
            }else {
                $qudao='bobo';
            }
            
            $pattern = '#^.+?\(phone:(.+?)\).*$#';
            if (preg_match($pattern, $agent)) {
                $model = preg_replace($pattern, '$1', $agent);
            }else {
                $model='android';
            }
            
        }else {
            $qudao = 'ios';
            $model = 'ios';
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $datestr = date("Ymd");
        $login_type = $row['login_type'];
        $db->insert("bb_users_register_log", [
            'uid' =>intval($uid),
            'login_type' =>$login_type,
            'ip' =>$ip,
            'create_time' =>time(),
            'model'=>$model,
            'qudao' => $qudao2,
            'datestr'=>$datestr,
        ]);
        \BBExtend\user\Tongji::getinstance($uid)->register($qudao2);
    }
    
    public static function login_log($uid)
    {
        
        
    }
    

}
