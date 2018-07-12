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

class Edit
{
   
    
   
    
    // 查个人公共信息
    private function public2($user,$status)
    {
        $uid = $user->uid;
        $role = $user->role;
        if ($role==3 || $status==3 ) {
            $new=[];
            $new['parent_phone'] = $user->get_parent_phone();
            $new['height'] = $user->get_height();
            $new['weight'] = $user->get_weight();
            
            $new['gexing'] = $user->get_gexing_arr();
            $new['jingyan'] = $user->get_jingyan_arr() ;
            return [
                    'vip'=>$new,
                    'tutor' => null,
                    'brandshop' =>null,
            ];
        }
        
        if ($role==2 || $status==2 ) {
            $new=[];
            // 查导师信息
            
            $new['tutor_brandshop_name'] = $user->get_tutor_brandshop_name();
            
            $new['tutor_parent_phone'] = $user->get_parent_phone();
            $new['tutor_zhuanye'] = $user->get_tutor_zhuanye_arr();
            $new['tutor_huojiang'] = $user->get_tutor_huojiang_arr();
            return [
                    'vip'=>null,
                    'tutor' => $new,
                    'brandshop' =>null,
            ];
        }
        
        if ($role==4 || $status==4 ) {
            $new=[];
            // 查导师信息
            // 地址
            $new['brandshop_address'] = $user->get_brandshop_address();
            $new['brandshop_phone'] = $user->get_brandshop_phone();
            
            $new['brandshop_info'] = $user->get_brandshop_word_jianjie(); // 文字简介
            $new['brandshop_html_info'] = $user->get_brandshop_h5_jianjie(); // h5
            $new['brandshop_rongyu'] = $user->get_brandshop_rongyu();           // 荣誉
            $new['brandshop_html_rongyu'] = $user->get_brandshop_html_rongyu();           // 荣誉
            $new['brandshop_html_kecheng'] = $user->get_brandshop_html_kecheng();           // 荣誉
            
            
            $new['brandshop_free'] = $user->get_brandshop_free();           // 荣誉
            $new['brandshop_id'] = $user->get_brandshop_id();
            
            $new['brandshop_url_show'] = \BBExtend\common\BBConfig::get_server_url_https().
                 "/user/infohtml/index?uid={$uid}&type=";
            $new['brandshop_url_edit'] = \BBExtend\common\BBConfig::get_server_url_https().
            "/photoimg/";
            
            
            return [
                    'vip'=>null,
                    'tutor' => null,
                    'brandshop' =>$new,
            ];
        }
        
        
        return [];
        
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
        $new['address']      = $user->address;
        
        
        $new['badge']  = $user->get_badge();
        $new['frame']  = $user->get_frame();
        
        
     //   $new['nickname'] = $user->get_nickname();
        
        $new['follow_count'] = \BBExtend\user\Focus::getinstance($uid)
        ->get_guanzhu_count();
        $new['fans_count'] = \BBExtend\user\Focus::getinstance($uid)
        ->get_fensi_count();
        $new['role'] = $user->role;
        
        $new['speciality_arr'] = $user->hobby_arr_id_name();
        
        
        
        $new['signature'] = strval( $user->signature );
        
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
  
  
    
    
    
    public function get_info_for_edit($uid,$token)
    {
        Sys::display_all_error();
        
        $db = Sys::get_container_dbreadonly();
        $uid = intval($uid);
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'请重新登录'];
        }
        $word='';
        $status = $user->get_status();
        if ( $status == 3 && $user->role ==1 ) {
            
            $word ="恭喜你成为小童星，请完善个人信息升级个人主页！";
        }
        
        
        $sql="select id,name,image from bb_label order by id asc ";
        
        $all_speclity =$db->fetchAll($sql);
        if ( $user->role==1 &&  $status==1  ) {
            // 这种情况要 
            return [
                    'code'=>1,
                    'data' =>[
                            'public'=> $this->public1($user),
                            'addi'  => [
                                    'word' =>$word,
                                    'status' => $status,
                            ],
                            'help'  =>null,
                            'all_speclity'=>$all_speclity,
                    ],
            ];
        }
        $help = [
                'head_pic'=>'http://resource.guaishoubobo.com/public/help/logo.png',
                'name'=>'怪兽bobo',
                'introduce'=>'每个孩子都是大明星，怪兽bobo客服等待您的咨询！',
                'qr_code'=>'https://bobo-upload.oss-cn-beijing.aliyuncs.com/public/help/qrcode.jpg',
                'code'=>'3446711614',
                'group_or_person'=>2,
        ];
        $addi = $this->public2($user,$status);
        $addi['word'] = $word;
        $addi['status'] = $status;
        
        return [
                'code'=>1,
                'data' =>[
                        'public'=> $this->public1($user),
                        'addi'  => $addi,
                        'help' => $help,
                        'all_speclity'=>$all_speclity,
                      //  'header_pic_upload_dir'=> 'uploads/headpic_date/'.date("Ymd"),
                ],
        ];
        
    }
    
    
    public function random($uid)
    {
        Sys::display_all_error();
        
        $uid = intval($uid);
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_users where role=3 and uid !={$uid}
       
   order by rand() limit 1";
        $result = $db->fetchRow($sql);
        $new_uid = $result['uid'];
        $user = \BBExtend\model\User::find($new_uid);
        $userinfo =   UserInfo::getinfo($new_uid);
        
        return [
          'code'=>1,
                'data'=>[
                        'nickname' => $user->get_nickname(),
                        'pic'      => $user->get_userpic(),
                        'jingyan'  => explode('|', $userinfo->jingyan),
                        'gexing'  => explode('|', $userinfo->gexing),
                        
                ],
        ];
        
        
    }
    
    /**
     * 个人资料编辑
     *  
     *  @param int $role  1普通用户，2导师，3vip童星，4机构 
     * @param int $uid
     * @param string $token
     * @param string $cleanup
     * @param string $pic
     */
    public function edit($status=1, $uid,$token='',$cleanup='',$pic='',$speciality_list='', 
            $birthday='',$address  ='', $signature='',
            $height=0, $weight=0, $gexing='', $jingyan='',$parent_phone='',
            $tutor_zhuanye='', $tutor_huojiang='',$tutor_parent_phone='',
            $brandshop_free=-1,
            $brandshop_info='',
            $brandshop_html_info = '',
            $brandshop_rongyu='',
            $brandshop_html_rongyu = '',
            $brandshop_html_kecheng = '',
            
            $brandshop_address = '',
            $brandshop_phone=-1
            )
    {
      //  Sys::display_all_error();
        $sex      =  input('?param.sex')?(int)input('param.sex'):-1;
        $db = Sys::get_container_db();
        
      //  Sys::debugxieye(111);
      //  Sys::debugxieye($role);
        $role = intval( $status );
        
        if (!in_array($role, [1,2,3,4])) {
            return ['code'=>0,'message'=>'role error'];
        }
        
        $uid = intval($uid);
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        $new=[];
        if ($pic) {
            if (preg_match('#\.(jpg|png|jpeg|gif)$#i', $pic)) {
                $new['pic'] = $pic;
            }else {
                return ['code'=>0,'message' =>'头像错误'];
            }
        }
        if ($sex >=0)  {
            $new['sex'] = $sex;
        }
        
        if ( $address )  {
            $new['address'] = $address;
        }
        
        if ($speciality_list) { // 谢烨，专为安卓设计的！！2017 05
            $speciality =  $this->_set_bb_userspeciality($uid, $speciality_list);
            $new['specialty'] = $speciality;
        }
        
        if ($birthday) {
            $temp = explode('-', $birthday);
            if (strlen($temp[1]) == 1 ) {
                $temp[1] = "0".$temp[1];
                $birthday = implode('-', $temp);
            }
            $new['birthday'] = $birthday;
        }
        
        if ($signature) {
            
            $sql ="select * from bb_minganci where name =?";
            $result = $db->fetchRow($sql, trim($signature));
            if ($result) {
                return ['code'=>0,'message'=>'您的个性签名不合适'];
            }
            $new['signature'] = $signature;
        }
        
        
        if ($new) {
           $db->update('bb_users', $new, "uid={$uid}");
        }
        
        if ($role==3) {
            $new=[];
            if ($height) {
            if ( $height < 200 && $height>0 ) {
                $new['height'] = $height;
            }else {
                return ['code'=>0,'message'=>'请填写正确的身高'];
            }
            }
            
            if ($weight) {
            if ( $weight < 100 && $weight>5 ) {
                $new['weight'] = $weight;
            }else {
                return ['code'=>0,'message'=>'请填写正确的体重'];
            }
            }
            
            
            if ($gexing) {
                $new['gexing'] = $gexing;
            }
            
            if ($parent_phone) {
                $new['parent_phone'] = $parent_phone;
            }
            
            if ($jingyan) {
                $new['jingyan'] = $jingyan;
            }
            if ($cleanup) {
                $temp = explode(',', $cleanup);
                foreach ($temp as $v) {
                    if ($v=='jingyan') {
                        $new['jingyan']='';
                    }
                    if ($v=='gexing') {
                        $new['gexing']='';
                    }
                }
                
            }
            
            $help= new  \BBExtend\model\UserInfo();
            $help->updateinfo($uid, $new);
            $help->addlog($uid, $role );
            
        }
        
        
        if ($role==2) {
            $new=[];
            if ($tutor_zhuanye) {
                $new['zhuanye'] = $tutor_zhuanye;
            }
            
            if ($tutor_parent_phone) {
                $new['phone'] = $tutor_parent_phone;
            }
            
            if ($tutor_huojiang) {
                $new['huojiang'] = $tutor_huojiang;
            }
            
            
            $help= new  \BBExtend\model\Starmaker();
            $help->updateinfo($uid, $new);
            $help->addlog($uid, $role );
            
        }
        
        if ($role==4) {
            $new=[];
            
            if ($brandshop_phone != -1 ) {
                $new['phone'] = $brandshop_phone;
            }
            
            if ($brandshop_info) {
                $new['info'] = $brandshop_info;
            }
            if ($brandshop_html_info) {
                $new['html_info'] = $brandshop_html_info;
            }
            
            if ($brandshop_rongyu) {
                $new['rongyu'] = $brandshop_rongyu;
            }
            if ($brandshop_html_rongyu) {
                $new['html_rongyu'] = $brandshop_html_rongyu;
            }
            if ($brandshop_html_kecheng) {
                $new['html_kecheng'] = $brandshop_html_kecheng;
            }
            
            if ($brandshop_free) {
                $new['is_free'] = intval( $brandshop_free);
            }
            if ($brandshop_address) {
                $new['address'] = $brandshop_address;
            }
            
            
            
            $help= new  \BBExtend\model\BrandShop();
            $help->updateinfo($uid, $new);
              $help->addlog($uid, $role );
            
        }
        
        
        
        return ['code'=>1];
    }
    
    
    private function _set_bb_userspeciality($uid,$speciality_list)
    {
        if (!$speciality_list) {
            return 0;
        }
        $temp =  explode(',', $speciality_list) ;
        
        $temp2 =[];
        foreach ($temp as $v) {
            $temp2[]= intval($v);
        }
        $speciality_list = json_encode($temp2);
        
//         if (\app\user\model\Exists::userhExists($uid))
//         {
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
            
          //  $sql="update bb_users set specialty=? where uid= ?";
          //  $db->query($sql,[$speciality_list,$uid  ]);
            
            return $speciality_list;
    }
    
    
    
    
    
    
    
    
    
    
}