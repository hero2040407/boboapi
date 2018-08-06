<?php
/**
 * 用户个人信息
 */

namespace app\user\controller;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\Achievement as Ach;

use BBExtend\model\UserInfo;
use think\Config;
use think\Cookie;

class Info 
{
    public $is_bottom=0;
    
    
    public function get_money($uid, $token)
    {
        $uid = intval($uid);
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_currency where uid=?";
        $row = $db->fetchRow($sql,[ $uid ]);
        if (!$row) {
            return ['code'=>0,'message'=>'currency error'];
        }
        
        return [
                'code'=>1,
                'data'=>[
                    'gold' =>  $row['gold'],
                        'bean' => $row['gold_bean'],
                ],
        ];
    }
    
    public function get_user_video($uid, $self_uid,  $startid=0, $length=2)
    {
        $list = $this->private_get_user_video($uid,$self_uid,$startid, $length);
        return [
                'code'=>1,
                'data'=> [
                        'list'=>$list,
                        'is_bottom'=> $this->is_bottom,
                ]
        ];
    }
    
    
    
    // 机构主页，旗下导师列表
    public function brandshop_tutor_list($uid, $startid=0, $length=2)
    {
        Sys::display_all_error();
        $uid=intval($uid );
        $startid=intval($startid );
        $length=intval( $length );
        
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        $db = Sys::get_container_dbreadonly();
        // 旗下导师
        $sql="select uid from bb_users_starmaker where brandshop_id=? and is_show =1 
   limit {$startid},{$length}";
        $result = $db->fetchCol($sql,[ $user->get_brandshop_id() ]);
        $new=[];
        foreach ($result as $v) {
//             $detail = \BBExtend\model\UserDetail::find($v);
//             $new[]= $detail->get_jiav();
            
            $detail = \BBExtend\model\UserStarmaker::where('uid', $v)->first();
            $new[]= $detail->get_info();
            
        }
        
        // 梁晨要求
        if (empty( $new )) {
            $new=null;
        }
        
        return [
                'code'=>1,
                'data'=>[
                        'list' =>$new,
                        'is_bottom'=> (count($new)== $length)? 0:1,
                ]
        ];
    }
    
    
    
    // 导师点评列表
    public function tutor_comment_list($uid, $startid=0, $length=2)
    {
        $uid=intval($uid );
        $startid=intval($startid );
        $length=intval( $length );
        
        $db = Sys::get_container_dbreadonly();
        $sql="
select id from bb_record 
where audit=1
  and is_remove=0
  and exists (
  select 1 from bb_record_invite_starmaker a
   where a.starmaker_uid = ?
     and a.new_status =4
     and a.record_id = bb_record.id
)
order by id desc
limit {$startid},{$length}
"
;
        
        $result  = $db->fetchAll($sql, [ $uid ]);
        // dump($result);
        $new=[];
        foreach ($result as $v) {
                $temp = \BBExtend\model\RecordDetail::find( $v['id'] );
                $temp->self_uid = $uid;
                $temp->_is_show=true;
                $new[]= $temp->get_all();
                
        }
        if (count( $new ) == $length ) {
            $this->is_bottom = 0;
        }else {
            $this->is_bottom=1;
        }
        return [
          'code'=>1,
                'data'=>[
                   'is_bottom' =>$this->is_bottom,
                        'list' => $new,
                ],
        ];
    }
    
    
    /**
     * 完善资料
     * 1，两种情况，1已面试通过。
     * 2，6种条件满足。
     * 
     * @param unknown $uid
     * @param unknown $token
     * @return number[]|string[]
     */
    public function apply_write_info($uid,$token)
    {
        Sys::display_all_error();
        $uid = intval($uid);
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        $db = Sys::get_container_db_eloquent();
        $sql="select count(*) from bb_vip_application_log  where status=7 and uid=? ";
        $count = DbSelect::fetchOne($db, $sql,[ $uid ]);
        if ($count) {
            return ['code'=>1];
        }
        
        $time = APP_TIME;
        // 没有的话，分两种情况。
        $db::table('bb_vip_application_log')->insert(
            [
                'uid' => $uid,
                'status' => 7,
                'create_time'=>$time,
            ]
        );
        return ['code'=>1];
        
    }
    
    
    
    public function private_get_user_video_count($uid,$self_uid)
    {
        $uid = intval($uid);
       // $startid= intval( $startid );
       // $length = intval( $length );
        $self_uid = intval( $self_uid );
        
        $db = Sys::get_container_dbreadonly();
        
        if ($uid == $self_uid) {
            $sql="
           select count(*) from bb_record
where bb_record.uid={$uid}
  and is_remove=0
  and type !=3
";
            $count1 = $db->fetchOne($sql);
            $sql="
           select count(*) from bb_rewind
where uid={$uid}
  and event='rewind'
  and is_remove=0
  and is_save=1
";
            $count2 = $db->fetchOne($sql);
        }
        else {
            $sql="
select count(*) from bb_record
           where bb_record.uid={$uid}
  and is_remove=0
  and type !=3
  and audit=1
";
            $count1 = $db->fetchOne($sql);
            $sql="
           select count(*) from bb_rewind
where uid={$uid}
  and event='rewind'
  and is_remove=0
  and is_save=1
";
            $count2 = $db->fetchOne($sql);
        }
        return $count1+$count2;;
    }
    
    
    public function private_get_user_video($uid,$self_uid,$startid=0, $length=2,$role=1)
    {
        $uid = intval($uid);
        $startid= intval( $startid );
        $length = intval( $length );
        $self_uid = intval( $self_uid );
        
        $db = Sys::get_container_db();
        $sql="
           select id,type,time from bb_record
where bb_record.uid={$uid} 
  and is_remove=0
  and type !=3
union all
select id,-100, start_time as time from bb_rewind
where uid={$uid}
  and event='rewind'
  and is_remove=0
  and is_save=1
order by time desc
limit {$startid},{$length}
                ";
        
        if ($uid != $self_uid) {
            $sql="
           select id,type,time from bb_record
where bb_record.uid={$uid}
  and is_remove=0
  and type !=3
  and audit=1
union all
select id,-100, start_time as time from bb_rewind
where uid={$uid}
  and event='rewind'
  and is_remove=0
  and is_save=1
order by time desc
limit {$startid},{$length}
                ";
            
        }
        
        
        $result  = $db->fetchAll($sql);
        // dump($result);
        $new=[];
        foreach ($result as $v) {
            if ($v['type'] == -100) { // 回播
                $temp = \BBExtend\model\Rewind::find( $v['id'] );
                $temp->self_uid = $self_uid;
                $new[]= $temp->get_all();
            }else {                    // 短视频
                $temp = \BBExtend\model\RecordDetail::find( $v['id'] );
                $temp->self_uid = $self_uid;
                if ($role==2) {
                 //   $temp->_is_show=true;
                }
                $new[]= $temp->get_all();
                
            }
            
        }
        if (count( $new ) == $length ) {
            $this->is_bottom = 0;
        }else {
            $this->is_bottom=1;
        }
        return $new;
    }
    
     
    /**
     * 查个人工公共信息
     * 
     * @param unknown $user
     * @param number $self_uid
     * @return number[]|NULL[]|array[]|unknown[]|NULL
     */
    private function public2($user,$self_uid=0)
    {
        $uid = $user->uid;
        $role = $user->role;
        if ($role==3) {
            $new=[];
            $userinfo =   UserInfo::getinfo($uid);
            
            $db = Sys::get_container_db_eloquent();
            $sql="select count(*) from  bb_users_card where uid={$uid} and status=3 ";
            $card_count = DbSelect::fetchOne($db, $sql);
            
            //查询我的动态有多少
            // 查询我的模卡有多少。
            $new['dongtai_count'] = $this->private_get_user_video_count($uid, $self_uid);
            $new['card_count'] = $card_count;
//             if ($uid==$self_uid) {
//                $new['parent_phone'] = $userinfo->parent_phone;
//             }
            $new['height'] = $userinfo->height;
            $new['weight'] = $userinfo->weight;
           // $new['parent_phone'] = $userinfo->parent_phone;
            $new['gexing'] = $user->get_gexing_arr(); 
            $new['jingyan'] = $user->get_jingyan_arr();
            return [
                    'vip' => $new,
                    'tutor' => null,
                    'brandshop' =>null,
            ];
        }
        if ($role==2) {
            $new=[];
          //  $userinfo =   UserInfo::getinfo($uid);
            
            $db = Sys::get_container_dbreadonly();
            $sql="select count(*) 
from bb_record_invite_starmaker a
 where a.starmaker_uid = ?
and a.new_status =4
and exists (
  select 1 from bb_record 
   where bb_record.id = a.record_id 
    and bb_record.audit=1
    and bb_record.is_remove=0
) ";
            $comment_count = $db->fetchOne( $sql,[ $uid ]);
            
            //查询我的动态有多少
            // 查询我的模卡有多少。
            $new['tutor_dongtai_count'] = $this->private_get_user_video_count($uid, $self_uid);
            $new['tutor_comment_count'] = $comment_count;
           
            $new['tutor_zhuanye'] = $user->get_tutor_zhuanye_arr();
            $new['tutor_huojiang'] = $user->get_tutor_huojiang_arr();
            $new['tutor_brandshop_id'] = $user->get_tutor_brandshop_id();
            $new['tutor_brandshop_name'] = $user->get_tutor_brandshop_name();
            $temp =  \BBExtend\model\UserStarmaker::where('uid', $uid)->first();
            $new['tutor_price'] = $temp->get_price();
            
            return [
                    'vip' => null,
                    'tutor' => $new,
                    'brandshop' =>null,
            ];
        }
        
        if ($role==4) {
            $new=[];
            $db = Sys::get_container_dbreadonly();
            //查询我的动态有多少
            $new['brandshop_dongtai_count'] = $this->private_get_user_video_count($uid, $self_uid);
            // 地址
            $new['brandshop_address'] = $user->get_brandshop_address();
            $new['brandshop_info'] = $user->get_brandshop_word_jianjie(); // 文字简介
            $new['brandshop_html_info'] = $user->get_brandshop_h5_jianjie(); // h5
            $new['brandshop_rongyu'] = $user->get_brandshop_rongyu();           // 荣誉
            $new['brandshop_html_rongyu'] = $user->get_brandshop_html_rongyu();     // 荣誉
            $new['brandshop_html_kecheng'] = $user->get_brandshop_html_kecheng();      // 荣誉
            $new['brandshop_html_free'] = $user->get_brandshop_free();            
            
            $new['brandshop_id'] = $user->get_brandshop_id();
            
            // 旗下导师
            $sql="select uid from bb_users_starmaker where brandshop_id=? and is_show =1 limit 6";
         //   echo '--';echo $user->get_brandshop_id();echo '--';
            $result = $db->fetchCol($sql,[ $user->get_brandshop_id() ]);
            $temp2=[];
            foreach ($result as $v) {
                $detail = \BBExtend\model\UserStarmaker::where('uid', $v)->first();
                if ($detail)
                   $temp2[]= $detail->get_info();
            }
            if ( empty( $temp2 ) ) {
                $temp2=null;
            }
            $new['brandshop_tutor_list'] = $temp2;
            
            $new['brandshop_lunbo_list'] = [];
            
            
            $new['brandshop_url_show'] =  \BBExtend\common\BBConfig::get_server_url_https(). 
               "/user/infohtml/index?uid={$uid}&type=";
            
            
            return [
                    'vip' => null,
                    'tutor' => null,
                    'brandshop' =>$new,
            ];
        }
        
        
        
        return null;
        
    }
    
    private function public1($user)
    {
        $uid = $user->uid;
        $new=[];
        //  $new=[];
        $new['nickname'] = $user->get_nickname();
        $new['uid']      = $user->uid;
        $new['level']      = $user->get_user_level();
        $new['pic']      = $user->get_userpic();
        
        $new['sex']      = $user->get_usersex();
        $new['age']      = $user->get_userage();
        $new['birthday'] = $user->get_birthday();
        $new['address']      = $user->get_user_address();
        
     //   $new['nickname'] = $user->get_nickname();
        
        $new['follow_count'] = \BBExtend\user\Focus::getinstance($uid)
        ->get_guanzhu_count();
        $new['fans_count'] = \BBExtend\user\Focus::getinstance($uid)
        ->get_fensi_count();
        
        $new['fans_count'] = intval( $new['fans_count']  );
        
        $new['role'] = $user->role;
        $new['speciality_arr'] = $user->hobby_arr_id_name();
        $new['signature'] = strval( $user->signature );
        
        
        $new['badge']  = $user->get_badge();
        $new['frame']  = $user->get_frame();
        
        
        $ach2 = new Ach();
        $ach = $ach2->create_default_by_user($user);
        $data = $ach->get_simple_data();
        
        $temp=[];
        
        foreach ($data as $v) {
            if ($v['level'] ) {
                $temp[]= $v['pic'];
            }
        }
        $new['achievement'] =$temp; 
        return $new;
    }
    
    
    private function get_public($uid)
    {
        $uid = intval($uid);
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message' =>'用户不存在' ];
        }
        
        $new=$this->public1($user);
            
        return [
            'code'=>1,
            'data' => ['public' => $new   ],    
        ];     
    }
    
    
    
    private function get_public_addi($uid,$self_uid)
    {
        $uid = intval($uid);
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message' =>'用户不存在' ];
        }
        
        $new=$this->public1($user);
        $addi = $this->public2($user,$self_uid);
        
       
        
        return [
                'code'=>1,
                'data' => ['public' => $new,'addi' => $addi   ],
        ];
    }
    
    
    /**
     * 获取主页全部信息
     * @param unknown $uid
     * @return number[]|string[]|number[]|NULL[][]|string[][][]|NULL[][][]|number[][][]|NULL[][][][]|string[][][][]
     */
    public function get_public_addi_video($uid,$self_uid, $length=2)
    {
//         Sys::display_all_error();

        
        
        $user_agent =Config::get("http_head_user_agent");
        $ip = Config::get("http_head_ip");
        
        $redis = Sys::get_container_redis();
        
        $key ="limit:ip:{$ip}";
        $key_hour ="limit:ip:hour:{$ip}";
        $key_list ="limit:ip:week";
        
      //  $limit_ip = $redis->
        $has_limit =  $redis->sIsMember($key_list, $ip);
        if ($has_limit===true) {
            sleep(30);exit;
        }
          
        //$limit = $redis->get( $k );
        
        
        if ($ip == '122.224.90.210' || $ip =='127.0.0.1' ) {
            
        }else {
            // 每分钟最多60次。
            $new = $redis->incr($key);
            $new2 = $redis->incr($key_hour);
            if ($new < 3) {
                $redis->setTimeout($key,60 );// 仅能存活1分钟
            }
            if ($new2  < 3) {
                $redis->setTimeout($key_hour,  600 );// 存活10分钟
            }
            
            if ($new2 >10) { // 10分钟超过100次，永久限制。
                $redis->sadd( $key_list, $ip );
                Sys::debugxieye("get_public_addi_video, 封禁ip成功，ip:{$ip},agent:{$user_agent}");
                exit();
            }
            
            if ($new >20) { // 每分钟超过20次，限制。
                Sys::debugxieye("get_public_addi_video, 每分钟30次限制，ip:{$ip},agent:{$user_agent}");
                sleep(30);
                // 限制每分钟每个ip最多访问30次这个接口。
                
                return ['code'=>0];
            }
            
            
            // xieye ,特殊情况，查此ip之前是否访问至少2个url
            $requst_redis_key =  "limit_index:ip:request_list:{$ip}";
            $redis2 = Sys::getredis2();
            $request_size = $redis2->lSize( $redis2 );
            if ( $request_size && $request_size >=2  ) {
            }else {
                
                $redis->sadd( $key_list, $ip );exit;
            }
            
        }
        
        
        
        
        $uid = intval($uid);
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message' =>'用户不存在' ];
        }
        
        // 谢烨，查询是否1转vip
        $db = Sys::get_container_dbreadonly();
        
//         $word='';
//         if ( $user->role==1  ) {
//           $sql="select count(*) from bb_vip_application_log
//                 where uid=? and (status=4 or status=7 ) ";
//           $count = $db->fetchOne($sql,[ $uid ]);
//           if ($count) {
//               $word ="恭喜你成为小童星，请完善个人信息升级个人主页！";
//           }
//         }
        
        $word='';
        $status = $user->get_status();
        
        if ( $uid == $self_uid  ) {
        
            if ( $status == 3 && $user->role==1 ) {
                
                $word ="恭喜你成为小童星，请完善个人信息升级个人主页！";
            }
            if ( $status == 2 && $user->role==1 ) {
                
                $word ="恭喜您通过导师审核，请完善个人信息升级个人主页！";
            }
            if ( $status == 4 && $user->role==1 ) {
                
                $word ="恭喜您通过品牌馆审核，请完善个人信息升级个人主页！";
            }
        }
        
        $new=$this->public1($user);
        $addi = $this->public2($user,$self_uid);
        $addi['word']=$word;
        $addi['status'] = $status;
        $list = $this->private_get_user_video($uid, $self_uid,0,$length,$user->role );
        
        
        $self=[];
        
        $is_focus=true;
        if ($uid != $self_uid) {
            $is_focus = \BBExtend\Focus::get_focus_state($self_uid, $uid);
        }
        if ($uid==$self_uid) {
            $role= $user->role;
        }else {
            $temp = \BBExtend\model\User::find($self_uid);
            if (!$temp) {
                return ['code'=>0,'message' =>'self用户不存在' ];
            }
            $role = $temp->role;
        }
        
        
        $self['is_focus']=$is_focus;
        $self['role']=$role;
        if ($uid== $self_uid) {
            $self=null;
        }
        
        return [
                'code'=>1,
                'data' => [
                        'public' => $new,
                        'addi' => $addi,
//                         'word' => $word,
                        'list'=> $list,
                        'is_bottom'=> $this->is_bottom,
                        'self' =>$self,
                ],
        ];
    }
    
    
    
    public function random_vip($uid)
    {
        Sys::display_all_error();
        $uid = intval($uid);
        $db = Sys::get_container_db_eloquent();
        $sql="select uid from bb_users where role=3 and uid != ?   limit 100";
        $ids = DbSelect::fetchCol($db, $sql,[ $uid ]);
        if (!$ids) {
            return ['code'=>0,'message'=>'未查到信息'];
        }
        
        shuffle($ids);
        $id = array_pop($ids);
        $user = \BBExtend\model\User::find($id);
        
        return ['code'=>1,'data'=>[
                'uid' => $id,
                'nickname' => $user->get_nickname(),
                'pic' => $user->get_userpic(),
                'gexing' => $user->get_gexing(),
                'jingyan' => $user->get_jingyan(),
                
        ]];
    }
    
    
    //vip购买价格表
    public function vip()
    {
//         | price_list    | 类型 |含义  |
//         | -------- |:------|:------|
//         | type       | int  | 这是购买时要传给服务器的，默认1  |
//         | price      | int | 波币价格 |
//         | time       | string | 续费时间 ，如一个月，一年 |
//         | additional_info | string | 附加信息，如推荐，优惠，超值  |
//         | additional_yuanjia | string | 如(原价3600)  |
        $arr = array(
            'info_list' => array(
                ["no" => 1, "info" =>'会员图标' ],
                ["no" => 2, "info" =>'无限量存储直播回放数据' ],
                ["no" => 3, "info" =>'免费享受所有普通课程' ],
                ["no" => 4, "info" =>'每月免费获取5张高级课程听课券' ],
                ["no" => 5, "info" =>'免费使用会员魔法表情' ],
                ["no" => 6, "info" =>'拥有高级怪兽蛋' ],
                ["no" => 7, "info" =>'享受商城会员特卖商品' ],
            ),
            'price_list' =>  \BBExtend\pay\UserPay::vip_price(),
        );
        
        return ['code'=>1,"data"=>$arr];
    }
}