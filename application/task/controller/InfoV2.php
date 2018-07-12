<?php
/**
 * 邀约活动任务
 * 
 * 
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/7/2
 * Time: 18:00
 */

namespace app\task\controller;
use app\record\controller\Recordmanager;
use BBExtend\BBMessage;
use think\Db;
use app\user\controller\User;
use BBExtend\BBRedis;
use BBExtend\Level;
use BBExtend\user\Activity;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\PicPrefixUrl;

class InfoV2 extends Level
{
   
    
    private function get_pk_info($activity_id,$uid,$red_viewpoint,$blue_viewpoint,$pk_statistics_json)
    {
        $db = Sys::get_container_dbreadonly();
        $sql = "select count(*) from bb_user_activity where uid=?
          and activity_id = ?";
        // $result=0未参加，11，红方，12蓝方
        $result = $db->fetchOne($sql,[ $uid, $activity_id ]);
        if ( $result ) {
            $sql="select usersort from bb_record where uid=? and
           type=2 and
           activity_id=? order by id desc limit 1";
            $result = $db->fetchOne($sql,[$uid, $activity_id]);
            
        }
        $join_status = intval($result );
        
        if ($pk_statistics_json) {
            $temp = json_decode($pk_statistics_json , 1);
            $red_count = $temp['red_count'];
            $red_like = $temp['red_like'];
            $red_score = $temp['red_score'];
            
            $blue_count = $temp['blue_count'];
            $blue_like = $temp['blue_like'];
            $blue_score = $temp['blue_score'];
            
            
        }else {
            
            
            $sql = "
select count(*) from bb_record
where type=2 and activity_id=?
and is_remove=0 and audit=1
and usersort=11
";
            $red_count = $db->fetchOne($sql,[ $activity_id ]);
            $sql = "
select count(*) from bb_record
where type=2 and activity_id=?
and is_remove=0 and audit=1
and usersort=12
";
            $blue_count = $db->fetchOne($sql,[ $activity_id ]);
            
            $sql="
select sum(`like`) from bb_record
where type=2 and activity_id=?
and is_remove=0 and audit=1
and usersort=11
";
            $red_like = $db->fetchOne($sql,[ $activity_id ]);
            $red_like = intval($red_like);
            
            $sql="
select sum(`like`) from bb_record
where type=2 and activity_id=?
and is_remove=0 and audit=1
and usersort=12
";
            $blue_like = $db->fetchOne($sql,[ $activity_id ]);
            $blue_like = intval($blue_like);
            
            $red_score = $red_count* 100 + $red_like * 5;
            $blue_score = $blue_count* 100 + $blue_like * 5;
        }
        
        return [
                'red_viewpoint' =>$red_viewpoint,
                'blue_viewpoint' =>$blue_viewpoint,
                'red_count' => $red_count,
                'blue_count' => $blue_count,
                'red_like' => $red_like,
                'blue_like' => $blue_like,
                
                'red_score' =>$red_score,
                'blue_score' => $blue_score,
                'join_status'=>$join_status,
        ];
    }
    
    
    /**
     * 只显示单个信息。
     */
    public function info()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $activity_id = input('?param.activity_id') ? (int)input('param.activity_id') : 0;
        
        $data = array();
        $activity = array();
        $index = 0;
        $db = Sys::get_container_db();
        $ActivityDB_array = Db::table('bb_task_activity')
            ->where(['is_remove' => 0, 'is_show' => 1])
            ->where(" start_time < '". strval(time()) ."' ")
            ->where("id", $activity_id)
            ->order('start_time', 'desc')
            ->select();

        foreach ($ActivityDB_array as $ActivityDB) {
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            $activityDB = $ActivityDB;
            
            
            $sql="select count(*) from bb_user_activity where uid={$uid}
          and activity_id = {$activity_id}";
            $activityDB['has_join'] = $db->fetchOne($sql);
            $activityDB['has_join'] = boolval($activityDB['has_join'] );
        //}
            
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
            
            $activityDB['qq_group'] = $this->get_qq_group();
            $activityDB['wx_group'] = $this->get_wx_group();
            
            // xieye 2018 02
            $activityDB['pk'] =null;
            if ($activityDB['type']==3) {
              $activityDB['pk'] = $this->get_pk_info($activity_id, $uid, 
                    $activityDB['red_viewpoint'],$activityDB['blue_viewpoint'], 
                      $activityDB['pk_statistics_json']  );
            }
            
            unset($activityDB['red_viewpoint']);
            unset($activityDB['blue_viewpoint']);
            
            
            // xieye 201709,富文本链接
            $activityDB['detail_link'] =  $ServerURL . '/task/taskactivityapi/detail/id/'.
                    $activityDB['id'];
            
            // xieye Activity
           // $activityDB['join_people'] = count(explode(',', $activityDB['user_list']));
            $activityDB['join_people'] = Activity::getinstance(0,$activityDB['id'])->get_act_count();
            
            $bigpic_list = array();
           
            //增加轮播图片
            if ($activityDB['bigpic_list_bignew']) {
                $bigpic_list_image = json_decode($activityDB['bigpic_list_bignew'], true);
                foreach ($bigpic_list_image as $image) {
                    if ($image['picpath'] == 'default.jpg') {
                        $image['picpath'] = $ServerURL . '/uploads/activity_pic/default.jpg';
                    } else {
                        $image['picpath'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $image['picpath']);
                    }
                    array_push($bigpic_list, $image);
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
            if ($activityDB['is_show'] && $Time > $activityDB['start_time']) {
                if ($Time > $activityDB['end_time']) {
                    $activityDB['time_out'] = true;
                }else
                {
                    $activityDB['time_out'] = false;
                }
                $activityDB['system_time'] = time();
                // xieye Activity
                if (isset($activityDB['user_list']) ) {
                  unset($activityDB['user_list']);}
                $activity[$index] = $activityDB;
                $Demo_video = Recordmanager::get_activity_movies_by_room_id($activityDB['room_id']);
                if ($Demo_video && $activityDB['room_id']) {
//                    if ($Demo_video['audit'] == 1)//只显示通过审核的
//                    {
                    $pic = $Demo_video['big_pic'];
                    $moviesDB = array();
                    //如果没有http://
                    $demo_uid = $Demo_video['uid'];
                    if (!(strpos($pic, 'http://') !== false)) {
                        $moviesDB['big_pic'] = $ServerURL . $pic;
                    } else {
                        if (!$pic) {
                            $moviesDB['big_pic'] = User::get_userpic($uid);
                        } else {
                            $moviesDB['big_pic'] = $pic;
                        }
                    }
                    $moviesDB['title'] = $Demo_video['title'];
                    $moviesDB['room_id'] = $activityDB['room_id'];
                    $moviesDB['video_path'] = $Demo_video['video_path'];
                    $moviesDB['comments_num'] = Recordmanager::get_comments_count($activityDB['room_id']);
                    $moviesDB['comments_score'] = Recordmanager::get_score_avg($activityDB['room_id']);
                    $moviesDB['nickname'] = User::get_nickname($demo_uid);
                    
                    //谢烨20160922，加vip返回字段
                    $moviesDB['vip'] = \BBExtend\common\User::is_vip($demo_uid) ;
                    //谢烨20160926，加uid整型字段
                    $moviesDB['uid'] = intval($demo_uid) ;
                    
                    $moviesDB['pic'] = User::get_userpic($demo_uid);
                    $moviesDB['age'] = User::get_userage($demo_uid);
                    $moviesDB['address'] = strval($Demo_video['address']);
                    $moviesDB['sex'] = User::get_usersex($demo_uid);
                    $moviesDB['look'] = (int)$Demo_video['look'];
                    $moviesDB['like'] = (int)$Demo_video['like'];
                    $moviesDB['is_like'] = Recordmanager::get_is_like($uid,$Demo_video['room_id']);
                    $activity[$index]['demo_video'] = $moviesDB;
//                    }
                }
                $index++;
            }
        }
        $data['activity_list'] = $activity;
        if ($activity) {
            return ["data"=>$activity[0], "code"=>1];
        }else {
            return ["code"=>0,"message"=>'信息不存在'];
        }
        
//         return ['data' => $data, 'code' => 1];
    }
    
    
    // 查微信群
    private function get_wx_group()
    {
        $db = Sys::get_container_db_eloquent();
        $sql = "select summary,title,pic,qrcode_pic from bb_group
where bb_type= 1 and type=1 limit 1";
        $wx_group = DbSelect::fetchRow($db, $sql);
        if (!$wx_group) {
            $wx_group = null;
        }else {
            $wx_group['pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['pic'], 1);
            $wx_group['qrcode_pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['qrcode_pic'], 1);
        }
        return $wx_group;
    }
    
    // 查qq群
    private function get_qq_group()
    {
        $db = Sys::get_container_db_eloquent();
        $sql = "select summary,title,pic,qrcode_pic,code,group_or_person from bb_group
where bb_type= 1 and type=2 limit 1";
        $wx_group = DbSelect::fetchRow($db, $sql);
        if (!$wx_group) {
            $wx_group = null;
        }else {
            $wx_group['pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['pic'], 1);
            $wx_group['qrcode_pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['qrcode_pic'], 1);
        }
        return $wx_group;
    }
    
    
//得到活动列表 中的用户列表~默认排序方式为 like 跟look 一次请求20个
//task/taskactivityapi/get_user_list
//传参 activity_id
//min_page 默认为0 开始标识
//max_page 默认为20 结束标识
//回参 如果data为空则表示没有数据了。或者data中的数据不足结束标识的数量
   
}