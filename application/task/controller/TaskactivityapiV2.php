<?php
namespace app\task\controller;

/**
 * 邀约活动任务
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/2
 * Time: 18:00
 */

use think\Db;

use BBExtend\BBRedis;
use BBExtend\Level;
use BBExtend\BBRecord;
use BBExtend\BBUser;
use BBExtend\user\Activity;
use BBExtend\user\ActivityRewardManager;
use BBExtend\Focus;
use BBExtend\Sys;
use think\Config;

class TaskactivityapiV2 
{
    
    /**
     * 通过活动id得到一个活动
     */
    public static function get_activity($activity_id)
    {
        $ActivityDB = Db::table('bb_task_activity')->where('id',$activity_id)->find();
        if ($ActivityDB)  {
            BBRedis::getInstance('bb_task')->hMset($activity_id.'activity',$ActivityDB);
        }
        return $ActivityDB;
    }
    
    /**
     * 得到活动列表 中的用户列表~默认排序方式为 like 跟look 一次请求20个
     */
    public function get_user_list()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $activity_id = input('?param.activity_id') ? (int)input('param.activity_id') : 0;
        $sort = input('?param.sort') ? (int)input('param.sort') : 0;
        if ( !in_array( $sort,[0,11,12] ) ) {
            return ['code'=>0,'message' =>'param error' ];
        }
        
        if (input('?param.min_page')) {
            $mini_page=(int)input('param.min_page');
        }elseif (input('?param.startid')  ) {
            $mini_page=(int)input('param.startid');
        }else {
            $mini_page=0;
        }
        
        
        // $mini_page = input('?param.min_page') ? (int)input('param.min_page') : 0;
        $max_page = input('?param.max_page') ? (int)input('param.max_page') : 20;
        $type =  input('?param.type') ? (int)input('param.type') : 0;
        $moviesDB_Array = array();
        $moviesDB_list = BBRecord::get_activity_movies_v2($uid,$activity_id, $mini_page, 
                $max_page,$type,$sort);
    //    Sys::debugxieye($moviesDB_list);
        
        $ActivityDB = self::get_activity($activity_id);
        foreach ($moviesDB_list as $moviesDB) {
            //审核过的并且不能等于当前官方指定擂主的
            
            $pic = $moviesDB['big_pic'];
            //如果没有http://
            if (!(strpos($pic, 'http://') !== false)) {
                $ServerURL = \BBExtend\common\BBConfig::get_server_url();
                $moviesDB['big_pic'] = $ServerURL . $pic;
            }
            $moviesDB['is_like'] = BBRecord::get_is_like($uid,$moviesDB['room_id']);
            $moviesDB['nickname'] = BBUser::get_nickname($moviesDB['uid']);
            
            
            $user_detail = \BBExtend\model\User::find( $moviesDB['uid'] );
            
            $moviesDB['role'] = $user_detail->role;
            $moviesDB['frame'] = $user_detail->get_frame();
            $moviesDB['badge'] = $user_detail->get_badge();
            
            
            
            //谢烨20160922，加vip返回字段
            $moviesDB['vip'] = \BBExtend\common\User::is_vip($moviesDB['uid']) ;
            
            $moviesDB['pic'] = BBUser::get_userpic($moviesDB['uid']);
            $moviesDB['age'] = BBUser::get_userage($moviesDB['uid']);
            $moviesDB['sex'] = BBUser::get_usersex($moviesDB['uid']);
            $moviesDB['type'] = (int)$moviesDB['type'];
            $moviesDB['look'] = (int)$moviesDB['look'];
            $moviesDB['uid'] = strval($moviesDB['uid']);
            // 谢烨，江浩池 12 16
            $moviesDB['is_focus'] = Focus::get_focus_state($uid,$moviesDB['uid']);
            
            // xieye ,20171016,类型bug
            if (isset($moviesDB['time'])) {
                $moviesDB['time'] = strval($moviesDB['time']);
            }
            
            array_push($moviesDB_Array, $moviesDB);
        }
        if (count($moviesDB_Array) == $max_page) {
            return ['data' => $moviesDB_Array, 'is_bottom' => 0, 'code' => 1];
        } else {
            return ['data' => $moviesDB_Array, 'is_bottom' => 1, 'code' => 1];
        }
    }
    
    
    
    /**
     * 谢烨 2017 04
     * 得到活动列表 每个活动列表中的用户只有第一名的数据 如果没有则代表没有人参加这个活动
     */
    public function newlist()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        
        $user = \app\user\model\UserModel::getinstance($uid);
        if ($user->error==1) {
            return ['code'=>0, 'message'=>'用户不存在'];
        }
        $user_level = $user->get_user_level();
        $user_age = $user->get_userage();
        $user_sex = $user->get_usersex();
        
        // 0 全部，1能参加 ，2已参加
        $range = input('?param.range') ? (int)input('param.range') : 0;
        $startid = input('?param.startid') ? (int)input('param.startid') : 0;
        $length = input('?param.length') ? (int)input('param.length') : 10;
        $time = time();
        
        $data = array();
        
        $activity = array();
        $index = 0;
        
        if (\BBExtend\model\User::is_test($uid)) {
        
        $ActivityDB_array = Db::table('bb_task_activity')
            ->where(['is_remove' => 0, 'is_show' => 1])
            ->where("start_time is not null")
            ->where("start_time < " . time())
            ->order('has_end asc, start_time desc')
            ->limit($startid,$length)
            ->select();
        } else {
            $ActivityDB_array = Db::table('bb_task_activity')
            ->where(['is_remove' => 0, 'is_show' => 1])
            ->where("start_time is not null")
            ->where("start_time < " . time())
            ->where("id != 138 " )
            
            ->order('has_end asc, start_time desc')
            ->limit($startid,$length)
            ->select();
        }
        if ($range == 2) { // 已参加
            $db = Sys::get_container_db();
            $sql="
               select * from bb_task_activity 
where is_remove=0 
and is_show=1 
and start_time is not null 
and start_time < {$time}
and exists (
 select 1 from bb_user_activity
 where bb_user_activity.uid={$uid} and 
  bb_user_activity.activity_id = bb_task_activity.id
)
order by start_time desc     
                    ";
            $ActivityDB_array = $db->fetchAll($sql);
        }
        
        //能参加
        if ($range==1) {
            //$select = $db->select();
            $db = Sys::get_container_db();
            $sql="
            select * from bb_task_activity
            where is_remove=0
              and is_show=1
              and start_time is not null
              and start_time < {$time}
              and end_time > {$time}
              and not exists (
            select 1 from bb_user_activity
            where bb_user_activity.uid={$uid} and
            bb_user_activity.activity_id = bb_task_activity.id
            )
              and ( level=0   or ( level > 0 and level <= {$user_level} ) )
              and ( min_age=0 or ( min_age >0 and min_age <= {$user_age}  ) )
              and ( max_age=0 or ( max_age >0 and max_age >= {$user_age}  ) )
              and ( sex=2 or  sex= {$user_sex}  )
            order by start_time desc
            ";
            $ActivityDB_array = $db->fetchAll($sql);
            
        }
            
        foreach ($ActivityDB_array as $ActivityDB) {
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            $activityDB = $ActivityDB;
            $activityDB['id'] = (int)$activityDB['id'];
            $activityDB['type'] = (int)$activityDB['type'];
            $activityDB['value'] = (int)$activityDB['value'];
            $activityDB['reward'] = (int)$activityDB['reward'];
            $activityDB['min_age'] = (int)$activityDB['min_age'];
            $activityDB['max_age'] = (int)$activityDB['max_age'];
            $activityDB['level'] = (int)$activityDB['level'];
            $activityDB['sex'] = (int)$activityDB['sex'];
            $activityDB['task_id'] = (int)$activityDB['task_id'];
            $activityDB['is_remove'] = (int)$activityDB['is_remove'];
            
            // xieye 201709,富文本链接
            $activityDB['detail_link'] =  $ServerURL . '/task/taskactivityapi/detail/id/'.
                $activityDB['id'];
    
            // xieye Activity
            $activityDB['join_people'] = Activity::getinstance(0,$activityDB['id'] )->get_act_count();
    
            $bigpic_list = array();
            
            //增加轮播图片
            if ($activityDB['bigpic_list_bignew']) {
                $bigpic_list_image = json_decode($activityDB['bigpic_list_bignew'], true);
                
                foreach ($bigpic_list_image as $image) {
                    $activityDB['gray_pic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https($image['picpath']);
                  //  $activityDB['first_pic'] = $image['picpath'];
                    break;
                }
                
                foreach ($bigpic_list_image as $image) {
                    if ($image['picpath'] == 'default.jpg') {
                        $image['picpath'] = $ServerURL . '/uploads/activity_pic/default.jpg';
                    } else {
                        $image['picpath'] = $ServerURL . $image['picpath'];
                    }
                    array_push($bigpic_list, $image);
                }
    
                foreach ($bigpic_list_image as $image) {
                    
                    $activityDB['first_pic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https($image['picpath']);
                    break;
                }
    
            }
            $activityDB['bigpic_list'] = $bigpic_list;
            //增加活动奖励
            if ($activityDB['reward_id']) {
                $activityDB['reward_list'] = array();
                $RewardList_id = explode(',', $activityDB['reward_id']);
                unset($activityDB['reward_id']);
                foreach ($RewardList_id as $Reward_id) {
                    if ($Reward_id) {
                        $RewardDB = Db::table('bb_task_reward')->where('id', $Reward_id)->find();
                        if ($RewardDB) {
                            $RewardDB['pic_icon'] = $ServerURL . $RewardDB['pic_icon'];
                            array_push($activityDB['reward_list'], $RewardDB);
                        }
                    }
                }
            } else {
                $activityDB['reward_list'] = array();
                $RewardDB = Db::table('bb_task_reward')->where('id', 1)->find();
                if ($RewardDB) {
                    $RewardDB['pic_icon'] = $ServerURL . $RewardDB['pic_icon'];
                    array_push($activityDB['reward_list'], $RewardDB);
                }
            }
            $Time = time();
    
            //只显示开始的活动以及显示的活动
            if ($Time > $activityDB['end_time']) {
                $activityDB['time_out'] = true;
            }else {
                $activityDB['time_out'] = false;
            }
            $activityDB['system_time'] = time();
             
            
            // 谢烨，新加一个东西。
            $user = \BBExtend\model\User::find( $uid );
            $activityDB['describe'] = $user->act_status(  $activityDB['id']);
            
            
            $activity[$index] = $activityDB;
            $Demo_video = BBRecord::get_activity_movies_by_room_id($activityDB['room_id']);
            if ($Demo_video && $activityDB['room_id']) {
                $pic = $Demo_video['big_pic'];
                $moviesDB = array();
                //如果没有http://
                $demo_uid = $Demo_video['uid'];
                if (!(strpos($pic, 'http://') !== false)) {
                    $moviesDB['big_pic'] = $ServerURL . $pic;
                } else {
                    if (!$pic) {
                        $moviesDB['big_pic'] = BBUser::get_userpic($uid);
                    } else {
                        $moviesDB['big_pic'] = $pic;
                    }
                }
                $moviesDB['title'] = $Demo_video['title'];
                $moviesDB['room_id'] = $activityDB['room_id'];
                $moviesDB['video_path'] = $Demo_video['video_path'];
                $moviesDB['comments_num'] = BBRecord::get_comments_count($activityDB['room_id']);
                $moviesDB['comments_score'] = BBRecord::get_score_avg($activityDB['room_id']);
                $moviesDB['nickname'] = BBUser::get_nickname($demo_uid);
    
                //谢烨20160922，加vip返回字段
                $moviesDB['vip'] = \BBExtend\common\User::is_vip($demo_uid) ;
                //谢烨20160926，加uid整型字段
                $moviesDB['uid'] = intval($demo_uid) ;
    
                $moviesDB['pic'] = BBUser::get_userpic($demo_uid);
                $moviesDB['age'] = BBUser::get_userage($demo_uid);
                $moviesDB['address'] = strval($Demo_video['address']);
    
                //              if ($demo_uid == 17040) {
                //       \BBExtend\Sys::debugxieye($demo_uid.'=='.BBUser::get_user_address($demo_uid));
                //              }
                $moviesDB['sex'] = BBUser::get_usersex($demo_uid);
                $moviesDB['look'] = (int)$Demo_video['look'];
                $moviesDB['like'] = (int)$Demo_video['like'];
                $moviesDB['is_like'] = BBRecord::get_is_like($uid,$Demo_video['room_id']);
                $activity[$index]['demo_video'] = $moviesDB;
            }
            $index++;
        }
        $data['activity_list'] = $activity;
        return ['data' => $data, 'code' => 1,'is_bottom' =>
            (count($activity) ==$length  )?0:1
            
        ];
    }
    
    
    
    
    
    
    
    
}