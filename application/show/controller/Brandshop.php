<?php
/**
 * 推荐栏目
 */

namespace app\show\controller;

use BBExtend\Sys;
use BBExtend\BBRecord;
use BBExtend\BBPush;
use BBExtend\DbSelect;
use think\Db;
use BBExtend\BBUser;
use BBExtend\user\Activity;

use BBExtend\model\BrandShop as Buser;
use BBExtend\common\Image;
use BBExtend\common\PicPrefixUrl;

class Brandshop
{
    public $is_bottom;
    
   /**
    * 推荐，app首页。重要。
    * @param number $uid
    * @param number $startid
    * @param number $length
    */
    public function index($uid=0,$startid=0, $length=10) 
    {
        $uid=intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        
        $db = Sys::get_container_db_eloquent();
        $row = $db::table('bb_toppic')->where('sort_id',3)->where('activity_id',0)->first();
        $pic = \BBExtend\common\Image::geturl($row->picpath);
        if (preg_match('#jpe?g$#i', $pic)) {
            $pic_type='jpg';
        }else {
            $pic_type='png';
        }
        
        $ad =[
            'pic' => $pic,
            'pic_type' =>$pic_type,
            'link' =>  \BBExtend\common\BBConfig::get_server_url(). 
                '/index/brandshop/apply/uid/'. $uid,
        ];
        
        $subject_list = $this->list1($uid,$startid, $length); // 谢烨，请单独写这句，勿删
        return [
            'code'=>1,
            'data' => [
                'is_bottom'  => $this->is_bottom,
                'list' => $subject_list,
                'ad' =>$ad,
             ]
        ];
    }
    
    
//     public function schedule_list($uid=0,$startid=0, $length=10, $brandshop_id   )
//     {
//         $uid = intval($uid);
//         $brandshop_id=intval($brandshop_id);
//         $startid=intval($startid);
//         $length=intval($length);
//         $time = time();
//         $buser = Buser::find( $brandshop_id );
//         if (!$buser) {
//             return ['code' => 1,'message' => '品牌馆不存在' ];
//         }
        
//         $db = Sys::get_container_db_eloquent();
//         $sql="
// select * from 
// (select id,'act', start_time  from bb_task_activity
// where brandshop_id=?
//  and is_remove=0
//  and is_show=1
//  and start_time is not null
//  and start_time < '{$time}'

// ) a
// union all
// select * from 
// (select id,'race', start_time  from ds_race
// where brandshop_id=?

// ) b
// order by start_time desc
// limit {$startid},{$length}
// ";
//         $result = DbSelect::fetchAll($db, $sql,[ $brandshop_id, $brandshop_id ]);
        
//         ///////////////////////////////////////   大赛资料收集start     ///////////////////////////////
//         // 这里要收集一些大赛的资料！！
//         $time = time();
//         $ids = [];
//         foreach ($result as $v) {
//             // $temp=[];
//             if ($v['act']!='act') {
//                 $ids[]= $v["id"];
                
//             }
//         }
//         $child = $lunbo= [];
//         $dbzend = Sys::get_container_db();
//         if ($ids) {
//             $sql ="
//             select * from ds_race
//             where is_active=1 and parent in (?)
//             order by sort desc , start_time desc
//             ";
//             $child = $dbzend->fetchAll($dbzend->quoteInto($sql, $ids));
//             $sql ="
//             select * from ds_lunbo
//             where ds_id in (?)
//             order by sort desc
//             ";
//             $lunbo = $dbzend->fetchAll($dbzend->quoteInto($sql, $ids));
//         }
//         ////////////////////////////////////  大赛资料收集end  ///////////////////////////////////////
        
        
//         $activity = array();
//         foreach ($result as $v) {
//            // $temp=[];
//             if ($v['act']=='act') {
//               //  $temp['bigtype']='act';
//                 $sql="select * from bb_task_activity where id={$v['id']}";
//                 $ActivityDB = DbSelect::fetchRow($db, $sql);
                
//                 $ServerURL = \BBExtend\common\BBConfig::get_server_url();
//                 $activityDB = $ActivityDB;
                
//                 $activityDB['resulttype']='act';
//                 $activityDB['id'] = (int)$activityDB['id'];
//                 $activityDB['type'] = (int)$activityDB['type'];
//                 $activityDB['value'] = (int)$activityDB['value'];
//                 $activityDB['reward'] = (int)$activityDB['reward'];
//                 $activityDB['min_age'] = (int)$activityDB['min_age'];
//                 $activityDB['max_age'] = (int)$activityDB['max_age'];
//                 $activityDB['level'] = (int)$activityDB['level'];
//                 $activityDB['sex'] = (int)$activityDB['sex'];
//                 $activityDB['task_id'] = (int)$activityDB['task_id'];
//                 $activityDB['is_remove'] = (int)$activityDB['is_remove'];
                
//                 // xieye 201709,富文本链接
//                 $activityDB['detail_link'] =  $ServerURL . '/task/taskactivityapi/detail/id/'.
//                         $activityDB['id'];
                        
//                         // xieye Activity
//                 $activityDB['join_people'] = Activity::getinstance(0,$activityDB['id'] )->get_act_count();
                
//                 $bigpic_list = array();
                
//                 //增加轮播图片
//                 if ($activityDB['bigpic_list']) {
//                     $bigpic_list_image = json_decode($activityDB['bigpic_list'], true);
                    
//                     foreach ($bigpic_list_image as $image) {
//                         $activityDB['gray_pic'] = \BBExtend\common\Image::get_grayurl($image['picpath']);
//                         //  $activityDB['first_pic'] = $image['picpath'];
//                         break;
//                     }
                    
//                     foreach ($bigpic_list_image as $image) {
//                         if ($image['picpath'] == 'default.jpg') {
//                             $image['picpath'] = $ServerURL . '/uploads/activity_pic/default.jpg';
//                         } else {
//                             $image['picpath'] = $ServerURL . $image['picpath'];
//                         }
//                         array_push($bigpic_list, $image);
//                     }
                    
//                     foreach ($bigpic_list_image as $image) {
                        
//                         $activityDB['first_pic'] = \BBExtend\common\Image::geturl($image['picpath']);
//                         break;
//                     }
                    
//                 }
//                 $activityDB['bigpic_list'] = $bigpic_list;
//                 //增加活动奖励
//                 if ($activityDB['reward_id']) {
//                     $activityDB['reward_list'] = array();
//                     $RewardList_id = explode(',', $activityDB['reward_id']);
//                     unset($activityDB['reward_id']);
//                     foreach ($RewardList_id as $Reward_id) {
//                         if ($Reward_id) {
//                             $RewardDB = Db::table('bb_task_reward')->where('id', $Reward_id)->find();
//                             if ($RewardDB) {
//                                 $RewardDB['pic_icon'] = $ServerURL . $RewardDB['pic_icon'];
//                                 array_push($activityDB['reward_list'], $RewardDB);
//                             }
//                         }
//                     }
//                 } else {
//                     $activityDB['reward_list'] = array();
//                     $RewardDB = Db::table('bb_task_reward')->where('id', 1)->find();
//                     if ($RewardDB) {
//                         $RewardDB['pic_icon'] = $ServerURL . $RewardDB['pic_icon'];
//                         array_push($activityDB['reward_list'], $RewardDB);
//                     }
//                 }
//                 $Time = time();
                
//                 //只显示开始的活动以及显示的活动
//                 if ($Time > $activityDB['end_time']) {
//                     $activityDB['time_out'] = true;
//                 }else {
//                     $activityDB['time_out'] = false;
//                 }
//                 $activityDB['system_time'] = time();
                
//               //  $activity[$index] = $activityDB;
//                 $Demo_video = BBRecord::get_activity_movies_by_room_id($activityDB['room_id']);
//                 if ($Demo_video && $activityDB['room_id']) {
//                     $pic = $Demo_video['big_pic'];
//                     $moviesDB = array();
//                     //如果没有http://
//                     $demo_uid = $Demo_video['uid'];
//                     if (!(strpos($pic, 'http://') !== false)) {
//                         $moviesDB['big_pic'] = $ServerURL . $pic;
//                     } else {
//                         if (!$pic) {
//                             $moviesDB['big_pic'] = BBUser::get_userpic($uid);
//                         } else {
//                             $moviesDB['big_pic'] = $pic;
//                         }
//                     }
//                     $moviesDB['title'] = $Demo_video['title'];
//                     $moviesDB['room_id'] = $activityDB['room_id'];
//                     $moviesDB['video_path'] = $Demo_video['video_path'];
//                     $moviesDB['comments_num'] = BBRecord::get_comments_count($activityDB['room_id']);
//                     $moviesDB['comments_score'] = BBRecord::get_score_avg($activityDB['room_id']);
//                     $moviesDB['nickname'] = BBUser::get_nickname($demo_uid);
                    
//                     //谢烨20160922，加vip返回字段
//                     $moviesDB['vip'] = \BBExtend\common\User::is_vip($demo_uid) ;
//                     //谢烨20160926，加uid整型字段
//                     $moviesDB['uid'] = intval($demo_uid) ;
                    
//                     $moviesDB['pic'] = BBUser::get_userpic($demo_uid);
//                     $moviesDB['age'] = BBUser::get_userage($demo_uid);
//                     $moviesDB['address'] = strval($Demo_video['address']);
                    
//                     $moviesDB['sex'] = BBUser::get_usersex($demo_uid);
//                     $moviesDB['look'] = (int)$Demo_video['look'];
//                     $moviesDB['like'] = (int)$Demo_video['like'];
//                     $moviesDB['is_like'] = BBRecord::get_is_like($uid,$Demo_video['room_id']);
//                     $activityDB['demo_video'] = $moviesDB;
//                 }
                
//                 $activity[]= $activityDB;
//             }// 这是act 的流程。
//             else {
//                 // 这是大赛 的流程。
//                 $sql="select * from bb_task_activity where id={$v['id']}";
//                 $race = DbSelect::fetchRow($db, $sql);
                
//                 $t =[];
//                 $t['resulttype']='race';
//                 $t['banner'] =Image::geturl($race['banner']);
//                 $t['gray_banner'] =Image::get_grayurl( $race['banner']);
                
//                 $t['photo'] = BBUser::get_userpic($race['uid']);
                
//                 $t['master_uid'] = $race['uid'];
                
//                 $t['money'] = floatval( $race['money']);
                
//                 $t['end_time'] = $race['end_time'];
//                 $t['start_time'] = $race['start_time'];
//                 $t['register_end_time'] = $race['register_end_time'];
//                 $t['register_start_time'] = $race['register_start_time'];
                
//                 if ($time > $race['start_time'] && $time < $race['end_time'] ) {
//                     $t['status_word'] ='比赛进行中';
//                     $t['status_word_color'] =0xff2400;
//                     if ($time > $race['register_start_time'] && $time < $race['register_end_time']) {
//                         $t['status_word'] ='报名进行中';
//                         $t['status_word_color'] =0x69ce6e;
//                     }
//                 }elseif ($time < $race['start_time']) {
//                     $t['status_word'] ='未开始';
//                     $t['status_word_color'] =0xff9000;
//                 }else {
//                     $t['status_word'] ='已结束';
//                     $t['status_word_color'] =0x575757;
//                 }
                
//                 $t['title'] =$race['title'];
//                 $sql ="select count(*) from ds_record where ds_id={$race['id']}";
//                 $t['count'] = $dbzend->fetchOne($sql);
//                 $t['id'] = $race['id'];
//                 $t['current_time'] = $time;  // 当前时间，放到
                
//                 $t['detail_url'] = \BBExtend\common\BBConfig::get_server_url()."/race/index/detail/ds_id/{$race['id']}";
//                 $t['summary'] = $race['summary']; //简介
//                 // 现在显示分区信息。
//                 $t['child_race']=[];
//                 foreach ( $child as $v2 ) {
//                     if ($v2['parent'] == $race['id'] ) {
//                         $t['child_race'][]= [
//                                 'id' => $v2['id'],
//                                 'title' =>$v2['title'],
//                         ];
//                     }
//                 }
                
//                 // 轮播图
//                 $t['bigpic_list']=[];
//                 foreach ( $lunbo as $v2 ) {
//                     if ($v2['ds_id'] == $race['id'] ) {
//                         $t['bigpic_list'][]= [
//                                 'picpath' => \BBExtend\common\Image::geturl( $v2['pic']),
//                                 'title' =>$v2['title'],
//                                 'linkurl' =>$v2['url'],
//                         ];
//                     }
//                 }
                
//                 $sql = "select id from ds_race where parent = {$race['id']} and  is_app=1";
//                 $t['app_qudao_id'] =$dbzend->fetchOne($sql);
//                 $t['app_qudao_id'] = intval($t['app_qudao_id']);
                
//                 //加入大赛群信息
//                 $groups = $this->get_ds_groups($race['id']);
//                 $t['wx_group'] = $groups['wx_group'];
//                 $t['qq_group'] = $groups['qq_group'];
//                 // 加入 是否有直播
//                 $ds  = \BBExtend\model\Race::find($race['id']);
//                 $t['has_live_video'] = $ds->has_live_video();
                
//                 $activity[]= $t;
                
//             }
            
//         }// for循环全部结束。
        
//         return ['code'=>1,'data'=>[
//                 'is_bottom'=> ( count($activity )==$length )? 0:1,
//                 'list' =>$activity,
//         ]];
        
//     }
    
    
    
    public function schedule_list_v2($uid=0, $brandshop_id ,$startid=0, $length=10,$type='act')
    {
        $uid = intval($uid);
        $brandshop_id=intval($brandshop_id);
        $startid=intval($startid);
        $length=intval($length);
        $time = time();
        $db = Sys::get_container_db_eloquent();
        
        $buser = Buser::find( $brandshop_id );
        if (!$buser) {
            return ['code' => 1,'message' => '品牌馆不存在' ];
        }
        
        
        if ($type=='act') {
            $sql_act = "
select *  from bb_task_activity
where brandshop_id=?
 and is_remove=0
 and is_show=1
 and start_time is not null
 and start_time < '{$time}'
 order by start_time desc
 limit {$startid},{$length}
";
            
            $result_act = DbSelect::fetchAll($db, $sql_act,[  $brandshop_id ]);
            $activity = array();
            foreach ($result_act as $ActivityDB) {
                $activity[]= $this->act_detail_new($ActivityDB, $buser, $uid);
            }
            return ['code'=>1,'data'=>[
                    'is_bottom' => ( count($activity) ==$length) ?0:1,
                    'list' =>$activity,
            ]];
            
        }
        
        if ($type=='race') {
            
            $sql_race = "
select * from ds_race
where brandshop_id=?
 and is_active=1 and parent=0
 order by start_time desc
 limit {$startid},{$length}
 
";
            // $result_act = DbSelect::fetchAll($db, $sql_act,[  $brandshop_id ]);
            $result_race = DbSelect::fetchAll($db, $sql_race,[  $brandshop_id ]);
            $race_arr=[];
            foreach ($result_race as $race) {
                $race_arr[]= $this->race_detail_new($race, $buser, $uid);
                
            }
            return ['code'=>1,'data'=>[
                    'is_bottom' => ( count($race_arr) ==$length) ?0:1,
                    'list' =>$race_arr,
            ]];
            
        }
        return ['code'=>0];
    }
    
    
    
    
    
    
    
    public function schedule_list($uid=0, $brandshop_id ,$startid=0, $length=10,$type='act')
    {
        $uid = intval($uid);
        $brandshop_id=intval($brandshop_id);
        $startid=intval($startid);
        $length=intval($length);
        $time = time();
        $db = Sys::get_container_db_eloquent();
        
        $buser = Buser::find( $brandshop_id );
        if (!$buser) {
            return ['code' => 1,'message' => '品牌馆不存在' ];
        }
        
        
        if ($type=='act') {
            $sql_act = "
select *  from bb_task_activity
where brandshop_id=?
 and is_remove=0
 and is_show=1
 and start_time is not null
 and start_time < '{$time}'
 order by start_time desc
 limit {$startid},{$length}
";
            
            $result_act = DbSelect::fetchAll($db, $sql_act,[  $brandshop_id ]);
            $activity = array();
            foreach ($result_act as $ActivityDB) {
                $activity[]= $this->act_detail($ActivityDB, $buser, $uid);
            }
            return ['code'=>1,'data'=>[
                    'is_bottom' => ( count($activity) ==$length) ?0:1,
                    'list' =>$activity,
            ]];
             
        }
        
        if ($type=='race') {
            
            $sql_race = "
select * from ds_race
where brandshop_id=?
 and is_active=1 and parent=0
 order by start_time desc
 limit {$startid},{$length}
                    
";
           // $result_act = DbSelect::fetchAll($db, $sql_act,[  $brandshop_id ]);
            $result_race = DbSelect::fetchAll($db, $sql_race,[  $brandshop_id ]);
            $race_arr=[];
            foreach ($result_race as $race) {
                $race_arr[]= $this->race_detail($race, $buser, $uid);
                
            }
            return ['code'=>1,'data'=>[
                    'is_bottom' => ( count($race_arr) ==$length) ?0:1,
                    'list' =>$race_arr,
            ]];
            
        }
        return ['code'=>0];      
    }
    
    
    /**
     * 通用，活动列表详情
     * @param unknown $row
     * @param unknown $uid
     */
    private function act_detail($row, $buser, $uid)
    {
        $time = time();
        $t = [];
        
        $t['resulttype']='act';
        $t['id'] = (int)$row['id'];
        $t['title'] = $row['title'];
        
        $t['photo'] = BBUser::get_userpic($buser->uid );
        
        
        $user_detail = \BBExtend\model\User::find( $buser->uid );
        
        $t['role'] = $user_detail->role;
        $t['frame'] = $user_detail->get_frame();
        $t['badge'] = $user_detail->get_badge();
        
        $t['start_time'] = (int)$row['start_time'];
        $t['end_time'] = (int)$row['end_time'];
        $t['register_start_time'] = 0;
        $t['register_end_time'] = 0;
        
        $t['gray_banner'] ='';
        $t['banner'] ='';
        
        $t['gray_banner_bignew'] ='';
        $t['banner_bignew'] ='';
        
        
        if ($row['bigpic_list']) {
            $bigpic_list_image = json_decode($row['bigpic_list'], true);
            foreach ($bigpic_list_image as $image) {
                $t['gray_banner'] = \BBExtend\common\Image::get_grayurl($image['picpath']);
                $t['banner']      = \BBExtend\common\Image::geturl($image['picpath']);
                break;
            }
        }
        if ($row['bigpic_list_bignew']) {
            $bigpic_list_image = json_decode($row['bigpic_list_bignew'], true);
            foreach ($bigpic_list_image as $image) {
                $t['gray_banner_bignew'] = \BBExtend\common\Image::get_grayurl($image['picpath']);
                $t['banner_bignew']      = \BBExtend\common\Image::geturl($image['picpath']);
                break;
            }
        }
        
        
        if ($time > $row['start_time'] && $time < $row['end_time'] ) {
            $t['status_word'] ='';
            $t['status_word_color'] =0xff2400;
            
        }elseif ($time < $row['start_time']) {
            $t['status_word'] ='';
            $t['status_word_color'] =0xff9000;
        }else {
            $t['status_word'] ='';
            $t['status_word_color'] =0x575757;
        }
        
        $t['join_people'] = Activity::getinstance($uid,$t['id'] )->get_act_count();
        
        $t['current_time'] = time();
        
        $t['has_live_video'] =true; // 写死，是因为这是大赛专用。
        
        return $t;
    }
    
    
    /**
     * 通用，大赛列表详情
     * @param unknown $row
     * @param unknown $uid
     */
    private function race_detail($row,$buser, $uid)
    {
        $race = $row;
        $time = time();
        $t =[];
        $t['resulttype']='race';
        $t['banner'] =Image::geturl($race['banner']);
        $t['gray_banner'] =Image::get_grayurl( $race['banner']);
        
        
        $t['banner_bignew'] =Image::geturl($race['banner_bignew']);
        $t['gray_banner_bignew'] =Image::get_grayurl( $race['banner_bignew']);
        
        $t['photo'] = BBUser::get_userpic($race['uid']);
        
        
        $user_detail = \BBExtend\model\User::find( $race['uid'] );
        
        $t['role'] = $user_detail->role;
        $t['frame'] = $user_detail->get_frame();
        $t['badge'] = $user_detail->get_badge();
        
        //  $t['master_uid'] = $race['uid'];
        $t['start_time'] = $race['start_time'];
        $t['end_time'] = $race['end_time'];
        $t['register_start_time'] = $race['register_start_time'];
        $t['register_end_time'] = $race['register_end_time'];
        
        if ($time > $race['start_time'] && $time < $race['end_time'] ) {
            $t['status_word'] ='比赛进行中';
            $t['status_word_color'] =0xff2400;
            if ($time > $race['register_start_time'] && $time < $race['register_end_time']) {
                $t['status_word'] ='报名进行中';
                $t['status_word_color'] =0x69ce6e;
            }
        }elseif ($time < $race['start_time']) {
            $t['status_word'] ='未开始';
            $t['status_word_color'] =0xff9000;
        }else {
            $t['status_word'] ='已结束';
            $t['status_word_color'] =0x575757;
        }
        
        $t['title'] =$race['title'];
        
        $t['id'] = $race['id'];
        $t['current_time'] = $time;  // 当前时间，放到
        
        $t['join_people'] =0;// 该字段因为是活动专用。
        $ds  = \BBExtend\model\Race::find($t['id']);
        $t['has_live_video'] = $ds->has_live_video();
        return $t;
    }
    
    
    
    /**
     * 通用，活动列表详情
     * @param unknown $row
     * @param unknown $uid
     */
    private function act_detail_new($row, $buser, $uid)
    {
        $time = time();
        $t = [];
        
        $t['resulttype']='act';
        $t['id'] = (int)$row['id'];
        $t['title'] = $row['title'];
        
        $t['photo'] = BBUser::get_userpic($buser->uid );
        
        
        $user_detail = \BBExtend\model\User::find( $buser->uid );
        
        $t['role'] = $user_detail->role;
        $t['frame'] = $user_detail->get_frame();
        $t['badge'] = $user_detail->get_badge();
        
        $t['start_time'] = (int)$row['start_time'];
        $t['end_time'] = (int)$row['end_time'];
        $t['register_start_time'] = 0;
        $t['register_end_time'] = 0;
        
        $t['gray_banner'] ='';
        $t['banner'] ='';
        
        $t['gray_banner_bignew'] ='';
        $t['banner_bignew'] ='';
        
        
        if ($row['bigpic_list']) {
            $bigpic_list_image = json_decode($row['bigpic_list'], true);
            foreach ($bigpic_list_image as $image) {
                $t['gray_banner'] = \BBExtend\common\Image::get_grayurl($image['picpath']);
                $t['banner']      = \BBExtend\common\Image::geturl($image['picpath']);
                break;
            }
        }
        if ($row['bigpic_list_bignew']) {
            $bigpic_list_image = json_decode($row['bigpic_list_bignew'], true);
            foreach ($bigpic_list_image as $image) {
                $t['gray_banner_bignew'] = \BBExtend\common\Image::get_grayurl($image['picpath']);
                $t['banner_bignew']      = \BBExtend\common\Image::geturl($image['picpath']);
                break;
            }
        }
        
        $temp = \BBExtend\video\ActStatus::get_status($uid, $row['id']);
        $t['status_word'] =$temp['describe'];
        //         if ($time > $row['start_time'] && $time < $row['end_time'] ) {
        //             $t['status_word'] ='';
        //             $t['status_word_color'] =0xff2400;
        
        //         }elseif ($time < $row['start_time']) {
        //             $t['status_word'] ='';
        //             $t['status_word_color'] =0xff9000;
        //         }else {
        //             $t['status_word'] ='';
        //             $t['status_word_color'] =0x575757;
        //         }
        
        $t['join_people'] = Activity::getinstance($uid,$t['id'] )->get_act_count();
        
        $t['current_time'] = time();
        
        $t['has_live_video'] =true; // 写死，是因为这是大赛专用。
        
        return $t;
        }
        
        
        /**
         * 通用，大赛列表详情
         * @param unknown $row
         * @param unknown $uid
         */
        private function race_detail_new($row,$buser, $uid)
        {
            $race = $row;
            $time = time();
            $t =[];
            $t['resulttype']='race';
            $t['banner'] =Image::geturl($race['banner']);
            $t['gray_banner'] =Image::get_grayurl( $race['banner']);
            
            
            $t['banner_bignew'] =Image::geturl($race['banner_bignew']);
            $t['gray_banner_bignew'] =Image::get_grayurl( $race['banner_bignew']);
            
            $t['photo'] = BBUser::get_userpic($race['uid']);
            
            
            $user_detail = \BBExtend\model\User::find( $race['uid'] );
            
            $t['role'] = $user_detail->role;
            $t['frame'] = $user_detail->get_frame();
            $t['badge'] = $user_detail->get_badge();
            
            //  $t['master_uid'] = $race['uid'];
            $t['start_time'] = $race['start_time'];
            $t['end_time'] = $race['end_time'];
            $t['register_start_time'] = $race['register_start_time'];
            $t['register_end_time'] = $race['register_end_time'];
            
            $temp = \BBExtend\video\RaceStatus::get_status($uid, $race['id']);
            $t['status_word'] = $temp['data']['describe'];
            if ($t['status_word']=='1') {
                $t['status_word']='';
            }
            
            //         if ($time > $race['start_time'] && $time < $race['end_time'] ) {
            //             $t['status_word'] ='比赛进行中';
            //             $t['status_word_color'] =0xff2400;
            //             if ($time > $race['register_start_time'] && $time < $race['register_end_time']) {
            //                 $t['status_word'] ='报名进行中';
            //                 $t['status_word_color'] =0x69ce6e;
            //             }
            //         }elseif ($time < $race['start_time']) {
            //             $t['status_word'] ='未开始';
            //             $t['status_word_color'] =0xff9000;
            //         }else {
            //             $t['status_word'] ='已结束';
            //             $t['status_word_color'] =0x575757;
            //         }
            
            $t['title'] =$race['title'];
            
            $t['id'] = $race['id'];
            $t['current_time'] = $time;  // 当前时间，放到
            
            $t['join_people'] =0;// 该字段因为是活动专用。
            $ds  = \BBExtend\model\Race::find($t['id']);
            $t['has_live_video'] = $ds->has_live_video();
            return $t;
        }
        
        
    
    
    
    public function schedules($uid=0, $brandshop_id   )
    {
        $uid = intval($uid);
        $brandshop_id=intval($brandshop_id);
//         $startid=intval($startid);
//         $length=intval($length);
        $time = time();
        $buser = Buser::find( $brandshop_id );
        if (!$buser) {
            return ['code' => 1,'message' => '品牌馆不存在' ];
        }
        
        $db = Sys::get_container_db_eloquent();
        
        $sql_act = "
select *  from bb_task_activity
where brandshop_id=?
 and is_remove=0
 and is_show=1
 and start_time is not null
 and start_time < '{$time}'
 order by start_time desc
 limit 2
";
        $sql_race = "
select * from ds_race
where brandshop_id=?
 order by start_time desc
 limit 2

";
        $result_act = DbSelect::fetchAll($db, $sql_act,[  $brandshop_id ]);
        $result_race = DbSelect::fetchAll($db, $sql_race,[  $brandshop_id ]);
        
        
//         $sql="
// select * from
// (select id,'act', start_time  from bb_task_activity
// where brandshop_id=?
//  and is_remove=0
//  and is_show=1
//  and start_time is not null
//  and start_time < '{$time}'
 
// ) a
// union all
// select * from
// (select id,'race', start_time  from ds_race
// where brandshop_id=?

// ) b
// order by start_time desc
// limit {$startid},{$length}
// ";
        
  //      $result = DbSelect::fetchAll($db, $sql,[ $brandshop_id, $brandshop_id ]);
        
        ///////////////////////////////////////   大赛资料收集start     ///////////////////////////////
        // 这里要收集一些大赛的资料！！
//         $ids = [];
//         foreach ($result as $v) {
//             // $temp=[];
//             if ($v['act']!='act') {
//                 $ids[]= $v["id"];
                
//             }
//         }
//         $child = $lunbo= [];
//         $dbzend = Sys::get_container_db();
//         if ($ids) {
//             $sql ="
//             select * from ds_race
//             where is_active=1 and parent in (?)
//             order by sort desc , start_time desc
//             ";
//             $child = $dbzend->fetchAll($dbzend->quoteInto($sql, $ids));
//             $sql ="
//             select * from ds_lunbo
//             where ds_id in (?)
//             order by sort desc
//             ";
//             $lunbo = $dbzend->fetchAll($dbzend->quoteInto($sql, $ids));
//         }
        ////////////////////////////////////  大赛资料收集end  ///////////////////////////////////////
        
        
        $activity = array();
        foreach ($result_act as $ActivityDB) {
                
                $activity[]= $this->act_detail($ActivityDB, $buser, $uid);
        }
        
        
        $race_arr=[];
        foreach ($result_race as $race) {
            $race_arr[]= $this->race_detail($race, $buser, $uid);
        }
            
        
        return ['code'=>1,'data'=>[
                'list' =>[
                        [
                                'title'=>'活动',
                                'small_list' =>$activity,
                                'link_type'  => 'act',
                        ],
                        [
                                'title'=>'大赛',
                                'small_list' =>$race_arr,
                                'link_type'  => 'race',
                        ],
                        
                        ]
        ]];
    }
    
    //1最新，2网课，3花絮，4学员秀 
    public function record_list($uid=0, $brandshop_id, $startid=0,$length=10,$type=1)
    {
        $uid = intval($uid);
        $brandshop_id=intval($brandshop_id);
        $startid=intval($startid);
        $length=intval($length);
        $type=intval($type);
        if (!in_array($type, [1,2,3,4])) {
            return ['code'=>0,'message'=>'type error'];
        }
        $buser = Buser::find( $brandshop_id );
        if (!$buser) {
            return ['code' => 1,'message' => '品牌馆不存在' ];
        }
        $db = Sys::get_container_db_eloquent();
        $result = $db::table('bb_record');
        if ($type==1 || $type==2 ) {
            $result->where('uid', $buser->uid);
        }
        if ($type==3) {
            $result->whereExists(function ($query)  use ( $db,$brandshop_id ){
                $query->select($db::raw(1))
                ->from('bb_users_starmaker')
                ->whereRaw('bb_users_starmaker.uid= bb_record.uid')
                ->whereRaw('bb_users_starmaker.brandshop_id='.$brandshop_id);
            });
        }
        if ($type==4) {
            $result->whereExists(function ($query)  use ( $db,$brandshop_id ){
                $query->select($db::raw(1))
                ->from('bb_focus')
                ->whereRaw('bb_focus.uid= bb_record.uid')
                ->whereRaw('bb_focus.focus_uid='.$brandshop_id);
            });
        }
        $result =  $result->where('audit',1)
        ->where('type','<>',3)
        ->where('is_remove',0)
        ->orderBy('time','desc')
        ->offset($startid)
        ->limit($length)
        ->get();
        $new =[];
        foreach ($result as $v ) {
            $new[]= get_object_vars($v);
        }
        $result=[];
        foreach ($new as $v) {
            $result[]= \BBExtend\BBRecord::get_detail_by_row($v, $uid);
        }
        return [
                'code'=>1,
                'data'=>[
                        'is_bottom'=>count( $result )== $length? 0:1,
                        'list' => $result,
                ]
        ];
    }
    
    
    
    
    
    //1最新，2网课，3花絮，4学员秀
    public function record_list_v2($uid=0, $brandshop_id, $startid=0,$length=10,$type=1)
    {
        $uid = intval($uid);
        $brandshop_id=intval($brandshop_id);
        $startid=intval($startid);
        $length=intval($length);
        $type=intval($type);
        if (!in_array($type, [1,2,3,4])) {
            return ['code'=>0,'message'=>'type error'];
        }
        $buser = Buser::find( $brandshop_id );
        if (!$buser) {
            return ['code' => 1,'message' => '品牌馆不存在' ];
        }
        $db = Sys::get_container_db_eloquent();
        $result = $db::table('bb_record');
        if ($type==1 || $type==2 ) {
            $result->where('uid', $buser->uid);
        }
        if ($type==3) {
            $result->whereExists(function ($query)  use ( $db,$brandshop_id ){
                $query->select($db::raw(1))
                ->from('bb_users_starmaker')
                ->whereRaw('bb_users_starmaker.uid= bb_record.uid')
                ->whereRaw('bb_users_starmaker.brandshop_id='.$brandshop_id);
            });
        }
        if ($type==4) {
            $result->whereExists(function ($query)  use ( $db,$brandshop_id ){
                $query->select($db::raw(1))
                ->from('bb_focus')
                ->whereRaw('bb_focus.uid= bb_record.uid')
                ->whereRaw('bb_focus.focus_uid='.$brandshop_id);
            });
        }
        $result =  $result->where('audit',1)
        ->where('type','<>',3)
        ->where('is_remove',0)
        ->orderBy('time','desc')
        ->offset($startid)
        ->limit($length)
        ->get();
        $new =[];
        foreach ($result as $v ) {
            $new[]= get_object_vars($v);
        }
        $result=[];
        foreach ($new as $v) {
            
            $temp = \BBExtend\model\RecordDetail::find( $v['id'] );
            $temp->self_uid = $uid;
            $result[]= $temp->get_all();
            
            
        //    $result[]= \BBExtend\BBRecord::get_detail_by_row($v, $uid);
        }
        return [
                'code'=>1,
                'data'=>[
                        'is_bottom'=>count( $result )== $length? 0:1,
                        'list' => $result,
                ]
        ];
    }
    
    
    
    
    
    
    
    
    
    
    
    /**
     * 品牌馆视频列表
     * 
     * @param number $uid
     * @param number $startid
     * @param number $length
     * @param unknown $brandshop_id
     * @return number[]|string[]
     */
    public function records($uid=0, $brandshop_id )
    {
        $uid = intval($uid);
        $brandshop_id=intval($brandshop_id);
         $startid=0;
         $length=2;
        
        $buser = Buser::find( $brandshop_id );
        if (!$buser) {
            return ['code' => 1,'message' => '品牌馆不存在' ];
        }
        $db = Sys::get_container_db_eloquent();
        $result = $db::table('bb_record')
            ->where('uid', $buser->uid)
            ->where('audit',1)
            ->where('type','<>',3)
            ->where('is_remove',0)
            ->orderBy('time','desc')
            ->offset($startid)
            ->limit($length)
            ->get();
        $new =[];
        foreach ($result as $v ) {
            $new[]= get_object_vars($v);
        }  
        $result=[];
        foreach ($new as $v) {
            $result[]= \BBExtend\BBRecord::get_detail_by_row($v, $uid);
        }
        
//         select * from bb_record
//         where exists (
//                 select 1 from bb_users_starmaker
//                 where bb_users_starmaker.uid= bb_record.uid
//                 and bb_users_starmaker.brandshop_id=1
//                 )
        $result2 = $db::table('bb_record')
        ->whereExists(function ($query) use ( $db,$brandshop_id ){
           // $db = Sys::get_container_db_eloquent();
            $query->select($db::raw(1))
            ->from('bb_users_starmaker')
            ->whereRaw('bb_users_starmaker.uid= bb_record.uid')
            ->whereRaw('bb_users_starmaker.brandshop_id='.$brandshop_id);
        })
        ->where('audit',1)
        ->where('type','<>',3)
        ->where('is_remove',0)
        ->orderBy('time','desc')
        ->offset($startid)
        ->limit($length)
        ->get();
        $new =[];
        foreach ($result2 as $v ) {
            $new[]= get_object_vars($v);
        }
        $result2=[];
        foreach ($new as $v) {
            $result2[]= \BBExtend\BBRecord::get_detail_by_row($v, $uid);
        }
        
//         select * from bb_record
//         where exists (
//                 select 1 from bb_focus
//                 where bb_focus.uid= bb_record.uid
//                 and bb_focus.focus_uid=1
//                 )
        $result3 = $db::table('bb_record')
        ->whereExists(function ($query) use ( $db,$brandshop_id ) {
        //    $db = Sys::get_container_db_eloquent();
            $query->select($db::raw(1))
            ->from('bb_focus')
            ->whereRaw('bb_focus.uid= bb_record.uid')
            ->whereRaw('bb_focus.focus_uid='.$brandshop_id);
        })
        ->where('audit',1)
        ->where('type','<>',3)
        ->where('is_remove',0)
        ->orderBy('time','desc')
        ->offset($startid)
        ->limit($length)
        ->get();
        $new =[];
        foreach ($result3 as $v ) {
            $new[]= get_object_vars($v);
        }
        $result3=[];
        foreach ($new as $v) {
            $result3[]= \BBExtend\BBRecord::get_detail_by_row($v, $uid);
        }
        
        $server = \BBExtend\common\BBConfig::get_server_url_https();
        return [
                'code'=>1,
                'data'=>
                ['list'=>   
                  [
                     [
                             'title'=>'免费课程',
                             'small_list' =>$result,
                             'link_type'  => '1',
                     ],
                        [
                                'title'=>'导师精彩视频',
                                'small_list' =>$result2,
                                'link_type'  => '2',
                        ],
                        [
                                'title'=>'学员秀',
                                'small_list' =>$result3,
                                'link_type'  => '3',
                        ],
                        
                        
                  ]
               ],
                
        ];
    }
    
    
    
    public function fans_list($uid=0, $brandshop_id=0,$startid=0,$length=10)
    {
        $uid = intval($uid);
        $brandshop_id=intval( $brandshop_id );
        $startid = intval($startid);
        $length = intval($length);
        
        $buser = Buser::find($brandshop_id);
        if (!$buser) {
            return ['code'=>0,'message'=>'品牌馆不存在' ];
        }
        
        $FocusDB_Array = Db::table('bb_focus')->where('focus_uid',$buser->uid )->order('time','desc')
            ->limit($startid,$length)->select();
        $UserArray = array();
        $db = Sys::get_container_db();
        
        foreach ($FocusDB_Array as $FocusDB) {
            $User_UID = $FocusDB['uid'];
           // $UserDB = \BBExtend\BBUser::get_user($User_UID);
            $user = \app\user\model\UserModel::getinstance($User_UID);
            $a_user = [];
            $a_user['uid'] = $User_UID;
            //谢烨20160922，加vip返回字段
            $a_user['vip'] = \BBExtend\common\User::is_vip($User_UID) ;
            $a_user['age'] = $user->get_userage();
            $a_user['pic'] = $user->get_userpic();
            $a_user['nickname'] = $user->get_nickname();
            $a_user['address'] = $user->get_user_address();
            
            $a_user['signature'] = $user->get_signature();
            //2017 04
            
            // $DB['level'] = $user->get_user_level();
            $a_user['level'] = $user->get_user_level();
            $a_user['sex'] = $user->get_usersex();
            $a_user['specialty'] = $user->get_hobbys();
            if ($buser->uid==$uid) {
                $a_user['is_focus'] = true;
            }else {
                $a_user['is_focus'] = $user->is_fensi($uid);
            }
           
            array_push($UserArray,$a_user);
        }
       
        return [
                'code'=>1,
                'data'=> [
                        'is_bottom'=>(count($UserArray)==$length)? 0 : 1,
                        'list' =>$UserArray,
                ]
                
        ];
        
        
        
    }
    
    /**
     * 品牌馆导师列表
     * 
     * @param number $uid
     * @param number $brandshop_id
     * @param number $startid
     * @param number $length
     * @return number[]|number[][]|NULL[][][]
     */
    public function tutor_list($uid=0, $brandshop_id=0,$startid=0,$length=10)
    {
        $uid=intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        $brandshop_id=intval( $brandshop_id );
        
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $sql="select uid from bb_users_starmaker
where brandshop_id=?
 and is_show=1

and exists (
                 select 1 from bb_users where bb_users.uid = bb_users_starmaker.uid
                    and  bb_users.role=2
               )

limit {$startid},{$length}
";
        $ids = DbSelect::fetchCol($db, $sql,[ $brandshop_id ]);
        $new = [];
        foreach ($ids as $id) {
            $obj = \BBExtend\model\UserStarmaker::where('uid', $id)->first();
            $new[]= $obj->get_info();
        }
        return [
                'code'=>1,
                'data' => [
                        'is_bottom'  => ( count($new)==$length ) ? 0:1 ,
                        'list' => $new,
                ]
        ];
    }
    
    
    private function list1($uid,$startid, $length)
    {
      //  return 1;
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_brandshop where is_show=1
               and exists (
                 select 1 from bb_users where bb_users.uid = bb_brandshop.uid
                    and  bb_users.role=4
               )
               order by sort desc
               limit {$startid},{$length}
        ";
        
        
         $result = DbSelect::fetchAll($db, $sql);
     // return 2;
        foreach ( $result as $k=> $v ) {
        
             $buser = Buser::find($v['id']);
             $result[$k]['pic'] =$buser->get_userpic();
             $result[$k]['nickname'] =$buser->get_nickname();
             $result[$k]['fans_count'] =$buser->fans_count();
             $result[$k]['act_count'] =$buser->act_count();
             
        }
        $this->is_bottom = (count($result)==$length  ) ? 0:1;
        return $result;
    }
    
    /**
     * 品牌馆详情。
     * 
     * @param unknown $uid
     * @param unknown $brandshop
     * @return number[]|string[][]|NULL[][]
     */
    public function info($uid, $brandshop_id)
    {
        $uid = intval($uid);
        $brandshop_id = intval( $brandshop_id );
        
        $buser = Buser::find($brandshop_id);
        if (!$buser) {
            return ['code'=>0,'message'=>'id error'];
        }
        
        $new =[];
        $new['pic'] =$buser->get_userpic();
        $new['nickname'] =$buser->get_nickname();
        $new['fans_count'] =$buser->fans_count();
      //  $new['act_count'] =$buser->act_count();
        $new['id'] = $buser->id;
        $new['uid'] = $buser->uid;
        
        
        $user2 = \BBExtend\model\User::find( $buser->uid );
        $new['role'] = $user2->role;
        $new['frame'] = $user2->get_frame();
        $new['badge'] = $user2->get_badge();
        
        
        $new['level'] = $buser->get_level();
        $new['address'] = $buser->address;
        $new['info'] = $buser->info;
        
        //当前用户是否关注该品牌馆。
        $new['is_focus'] = $buser->is_focus( $uid );
        $new['fans_count'] =$buser->fans_count();
        // 前6个粉丝
        $db = Sys::get_container_db_eloquent();
        
        $time=time();
        $sql="
        select * from
        (select id,'act', start_time  from bb_task_activity
                where brandshop_id=?
                 and is_remove=0
                 and is_show=1
                 and start_time is not null
                 and start_time < '{$time}'
        
                ) a
        union all
        select * from
        (select id,'race', start_time  from ds_race
                where brandshop_id=?
                
        ) b
        order by start_time desc
        limit 5
        ";
        $hunhe_result = DbSelect::fetchAll($db, $sql,[$brandshop_id, $brandshop_id ]);
        $demo_arr=null;
        if ($hunhe_result) {
            $demo_arr=[];
          foreach ($hunhe_result as $result ) {
        
              if ($result['act'] =='act' ) {
                  $sql="select * from bb_task_activity where id=".$result['id'];
                $row= DbSelect::fetchRow($db, $sql);
                $temp =  $this->act_detail($row, $buser, $uid);
            }else {
                $sql="select * from ds_race where id=".$result['id'];
                $row= DbSelect::fetchRow($db, $sql);
                $temp =  $this->race_detail($row, $buser, $uid);
            }
            $demo_arr[]= $temp;
          }
        }
        $new['demo_arr'] = $demo_arr;
        
//         $FocusDB_Array = $db::table('bb_focus')
//            ->where('focus_uid',$buser->uid )
//            ->orderBy('time','desc')
//            ->offset(0)
//            ->limit(6)
//            ->get();
//         $UserArray = array();
//         foreach ($FocusDB_Array as $FocusDB) {
//             $User_UID = $FocusDB->uid;
//             $user = \app\user\model\UserModel::getinstance($User_UID);
//             $a_user = [];
//             $a_user['uid'] = $User_UID;
//             $a_user['pic'] = $user->get_userpic();
//             $a_user['nickname'] = $user->get_nickname();
//             array_push($UserArray,$a_user);
//         }
//         $new['fans_list'] = $UserArray; 
        
        // 导师
       
        $sql="select uid from bb_users_starmaker
where brandshop_id=?
 and is_show=1
limit 6
";
        $ids = DbSelect::fetchCol($db, $sql,[ $brandshop_id ]);
        $new2 = [];
        foreach ($ids as $id) {
            $obj = \BBExtend\model\UserStarmaker::where('uid', $id)->first();
            $new2[]= $obj->get_info();
        }
        $new['tutor_list'] = $new2;
        
        $pics = $db::table('bb_toppic')->where('sort_id',3)->where('activity_id',$brandshop_id)->get();
        // 这里处理轮播图
        $new['bigpic_list']=[];
        foreach ( $pics as $v2 ) {
                $new['bigpic_list'][]= [
                        'picpath' => \BBExtend\common\Image::geturl( $v2->picpath),
                        'title' =>$v2->title,
                        'linkurl' =>$v2->linkurl,
                ];
        }
        $new['xiangqing_url'] = \BBExtend\common\BBConfig::get_server_url_https(). 
        "/index/brandshop/info/id/".$brandshop_id ;
        
        return ['code'=>1,'data'=>$new];
        
    }
    
    /**
     * 得到某个大赛的群信息
     *
     * 查微信群
     * type=1 微信群  ，type=2 qq群
     *
     * @param unknown $id
     */
    private function get_ds_groups($id)
    {
        $db = Sys::get_container_db_eloquent();
        $sql = "select summary,title,pic,qrcode_pic,type from bb_group
                 where bb_type= 2 and ds_id=?";
        $groups = DbSelect::fetchAll($db, $sql, [$id]);
        $wx_group = null;
        $qq_group = null;
        if ($groups) {
            foreach ($groups as $group) {
                if ($group['type']==1) {
                    $wx_group = $group;
                    unset($wx_group['type']);
                    $wx_group['pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['pic'], 1);
                    $wx_group['qrcode_pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['qrcode_pic'], 1);
                }
                if ($group['type']==2) {
                    $qq_group = $group;
                    unset($qq_group['type']);
                    $qq_group['pic'] = PicPrefixUrl::add_pic_prefix_https($qq_group['pic'], 1);
                    $qq_group['qrcode_pic'] = PicPrefixUrl::add_pic_prefix_https($qq_group['qrcode_pic'], 1);
                }
            }
            
        }
        return ['qq_group'=> $qq_group, 'wx_group'=>$wx_group];
    }
    
    
}