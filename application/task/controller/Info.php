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

class Info extends Level
{
//得到活动列表 每个活动列表中的用户只有第一名的数据 如果没有则代表没有人参加这个活动
//task/taskactivityapi/get_activity_list
//传参 ：无

    
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
        $sql = "select summary,title,pic,qrcode_pic from bb_group
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
            if ($activityDB['bigpic_list']) {
                $bigpic_list_image = json_decode($activityDB['bigpic_list'], true);
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
//得到活动列表 中的用户列表~默认排序方式为 like 跟look 一次请求20个
//task/taskactivityapi/get_user_list
//传参 activity_id
//min_page 默认为0 开始标识
//max_page 默认为20 结束标识
//回参 如果data为空则表示没有数据了。或者data中的数据不足结束标识的数量
    public function get_user_list()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $activity_id = input('?param.activity_id') ? (int)input('param.activity_id') : 0;
        $mini_page = input('?param.min_page') ? (int)input('param.min_page') : 0;
        $max_page = input('?param.max_page') ? (int)input('param.max_page') : 20;
        $type =  input('?param.type') ? (int)input('param.type') : 0;
        $moviesDB_Array = array();
        $moviesDB_list = Recordmanager::get_activity_movies($uid,$activity_id, $mini_page, $max_page,$type);
        $ActivityDB = self::get_activity($activity_id);
        foreach ($moviesDB_list as $moviesDB) {
            //审核过的并且不能等于当前官方指定擂主的
            if ($moviesDB['room_id'] != $ActivityDB['room_id']) {
                $pic = $moviesDB['big_pic'];
                //如果没有http://
                if (!(strpos($pic, 'http://') !== false)) {
                    $ServerURL = \BBExtend\common\BBConfig::get_server_url();
                    $moviesDB['big_pic'] = $ServerURL . $pic;
                }
                $moviesDB['is_like'] = Recordmanager::get_is_like($uid,$moviesDB['room_id']);
                $moviesDB['nickname'] = User::get_nickname($moviesDB['uid']);
                
                //谢烨20160922，加vip返回字段
                $moviesDB['vip'] = \BBExtend\common\User::is_vip($moviesDB['uid']) ;
                
                $moviesDB['pic'] = User::get_userpic($moviesDB['uid']);
                $moviesDB['age'] = User::get_userage($moviesDB['uid']);
                $moviesDB['sex'] = User::get_usersex($moviesDB['uid']);
                $moviesDB['type'] = (int)$moviesDB['type'];
                $moviesDB['look'] = (int)$moviesDB['look'];
                
                //谢烨20160926，强制成字符串型。
                if (array_key_exists('uid', $moviesDB)) {
                    $moviesDB['uid'] = strval($moviesDB['uid']);
                }
                
                array_push($moviesDB_Array, $moviesDB);
            }
        }
        if (count($moviesDB_Array) == $max_page) {
            return ['data' => $moviesDB_Array, 'is_bottom' => 0, 'code' => 1];
        } else {
            return ['data' => $moviesDB_Array, 'is_bottom' => 1, 'code' => 1];
        }
    }

    //加入活动
    public function join()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $activity_id = input('?param.activity') ? (int)input('param.activity') : 0;
        return self::join_activity($uid, $activity_id);
    }

    //点赞
    public function like()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $room_id = input('?param.room_id') ? (string)input('param.room_id') : 0;
        return Recordmanager::record_like($uid, $room_id);
    }

    //取消点赞
    public function unlike()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $room_id = input('?param.room_id') ? (string)input('param.room_id') : 0;
        return Recordmanager::record_un_like($uid, $room_id);
    }

//查询任务活动是否参加 code 0为未参加 1为参加
//task/taskactivityapi/query_activity
//传参 uid
//activity_id
//回参
    public function query_activity()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $activity_id = input('?param.activity_id') ? (int)input('param.activity_id') : 0;
        $ActivityDB = self::get_activity($activity_id);
        if ($ActivityDB) {
            // xieye Activity
            $result = Activity::getinstance($uid)->has_canjia($activity_id);
            if ($result) {
                return ['message' => '您已经参与过这个活动了！', 'code' => 0];
            } else {
                return ['message' => '可以参加!', 'code' => 1];
            }
            
//             $userlist = explode(',', $ActivityDB['user_list']);
//             if (in_array((string)$uid, $userlist, true)) {
//                 return ['message' => '您已经参与过这个活动了！', 'code' => 0];
//             } else {
//                 return ['message' => '可以参加!', 'code' => 1];
//             }
        }
        return ['message' => '没有这个活动，或者活动已经关闭', 'code' => 0];
    }

    //结束的活动手动发送奖励
    public static function reward_task_activity($activity_id)
    {
        $activityDB = self::get_activity($activity_id);
        if (!$activityDB) {
            return false;
        }
        $reward_count = $activityDB['reward'];
        $Record_array = Db::table('bb_record')->where(['type' => 2, 'activity_id' => $activity_id, 'audit' => 1, 'is_remove' => 0])->order(['like' => 'desc'])->select();
        $other_count = count($Record_array) - 3;

        for ($i = 0; $i < count($Record_array); $i++) {
            $RecordDB = $Record_array[$i];
            $ContentDB = array();
            $ContentDB = BBMessage::AddMsg($ContentDB,'活动完成:亲爱的');
            $ContentDB = BBMessage::AddMsg($ContentDB,'['.User::get_nickname($RecordDB['uid']).']',\BBExtend\fix\Color::ORANGE_MESSAGE_COL);
            $ContentDB = BBMessage::AddMsg($ContentDB,'您参加的');
            $ContentDB = BBMessage::AddMsg($ContentDB,$activityDB['title'],\BBExtend\fix\Color::BLUE_MESSAGE_COL);
            $ContentDB = BBMessage::AddMsg($ContentDB,'活动已经结束,恭喜您获得了'.$RecordDB['like'.'赞']);
            switch ($i) {
                case 0:
                    $send_reward = $reward_count * 0.5;//50%
                    $ContentDB = BBMessage::AddMsg($ContentDB,'排行第一名，获得');
                    $ContentDB = BBMessage::AddMsg($ContentDB,$send_reward.'Bo币',\BBExtend\fix\Color::ORANGE_MESSAGE_COL);
                    $ContentDB = BBMessage::AddMsg($ContentDB,'奖励');
                    BBMessage::SendMsg('系统消息','活动奖励发放',$ContentDB,$RecordDB['uid']);
                    self::add_currency($RecordDB['uid'], CURRENCY_GOLD,  (int)$send_reward, '参加['.$activityDB['title'].']活动第一名奖励');
                    break;
                case 1:
                    $send_reward = $reward_count * 0.3;//30%
                    $ContentDB = BBMessage::AddMsg($ContentDB,'排行第二名，获得');
                    $ContentDB = BBMessage::AddMsg($ContentDB,$send_reward.'Bo币',\BBExtend\fix\Color::ORANGE_MESSAGE_COL);
                    $ContentDB = BBMessage::AddMsg($ContentDB,'奖励');
                    BBMessage::SendMsg('系统消息','活动奖励发放',$ContentDB,$RecordDB['uid']);
                    self::add_currency($RecordDB['uid'], CURRENCY_GOLD,  (int)$send_reward, '参加['.$activityDB['title'].']活动第二名奖励');
                    break;
                case 2:
                    $send_reward = $reward_count * 0.1;//10%
                    $ContentDB = BBMessage::AddMsg($ContentDB,'排行第三名，获得');
                    $ContentDB = BBMessage::AddMsg($ContentDB,$send_reward.'Bo币',\BBExtend\fix\Color::ORANGE_MESSAGE_COL);
                    $ContentDB = BBMessage::AddMsg($ContentDB,'奖励');
                    BBMessage::SendMsg('系统消息','活动奖励发放',$ContentDB,$RecordDB['uid']);
                    self::add_currency($RecordDB['uid'], CURRENCY_GOLD,  (int)$send_reward, '参加['.$activityDB['title'].']活动第三名奖励');
                    break;
                default:

                    $send_reward = ($reward_count * 0.1) / $other_count;//10% 剩余的
                    $ContentDB = BBMessage::AddMsg($ContentDB,'参与奖，获得');
                    $ContentDB = BBMessage::AddMsg($ContentDB,$send_reward.'Bo币',\BBExtend\fix\Color::ORANGE_MESSAGE_COL);
                    $ContentDB = BBMessage::AddMsg($ContentDB,'奖励');
                    BBMessage::SendMsg('系统消息','活动奖励发放',$ContentDB,$RecordDB['uid']);
                    self::add_currency($RecordDB['uid'], CURRENCY_GOLD,   (int)$send_reward, '参加['.$activityDB['title'].']参与奖励');
                    break;
            }
        }
        BBRedis::getInstance('bb_task')->hSet($activity_id.'activity','is_send_reward',true);
        Db::table('bb_task_activity')->where('id',$activity_id)->update(['is_send_reward'=>true]);
        return true;
    }
    //加入一个活动每个用户只能加入一次活动
    public static function join_activity($uid,$activity_id)
    {
        //return ['message'=>$activity_id,'code'=>0];
        $activityDB = self::get_activity($activity_id);
        if ($activityDB)
        {
            $user_list = array();
            $result = Activity::getinstance($uid)->canjia($activity_id);
            if ($result) {
                return ['message'=>'参加成功,请耐心等待客服审核!','code'=>1];
            }else {
                return ['message'=>'你已经参加过这个活动了','code'=>0];
            }
            
//             $userlistDB = $activityDB['user_list'];
//             if ($userlistDB)
//             {
//                 $user_list = explode(',',$userlistDB);
//                 //$pos = strpos($user_list,$uid);
//                 if (in_array((string)$uid,$user_list,true))
//                 {
//                     return ['message'=>'你已经参加过这个活动了','code'=>0];
//                 }else
//                 {
//                     array_push($user_list,$uid);
//                     BBRedis::getInstance('bb_task')->hSet($activity_id.'activity','user_list',implode(',',$user_list));
//                     Db::table('bb_task_activity')->where('id',$activity_id)->update(['user_list'=>implode(',',$user_list)]);
// //                    if ($activityDB['is_show'])
// //                    {
// //                        self::add_currency($uid,CURRENCY_GOLD,$activityDB['reward'],'参加活动');
// //                    }

//                     return ['message'=>'参加成功,请耐心等待客服审核!','code'=>1];
//                 }
//             }else
//             {
//                 array_push($user_list,$uid);
//            //     BBRedis::getInstance('bb_task')->hSet($activity_id.'activity','user_list',implode(',',$user_list));
// //                if ($activityDB['is_show'])
// //                {
// //                    self::add_currency($uid,CURRENCY_GOLD,$activityDB['reward'],'参加活动');
// //                }
//                 return ['message'=>'参加成功,请耐心等待客服审核!!','code'=>1];
//             }
        }
        return ['message'=>'is not activity','code'=>0];
    }
    public static function del_join($uid,$activity_id)
    {
        $activityDB = self::get_activity($activity_id);
        if ($activityDB)
        {
            return Activity::getinstance($uid)->un_canjia($activity_id);
            
//             $userlistDB = $activityDB['user_list'];
//             if ($userlistDB) {
//                 $user_list = explode(',', $userlistDB);
//                 if (in_array((string)$uid,$user_list,true))
//                 {
//                     for ($i = 0; $i < count($user_list);$i++)
//                     {
//                         if ((int)$user_list[$i] == (int)$uid)
//                         {
//                             unset($user_list[$i]);
//                             break;
//                         }
//                     }
//                     BBRedis::getInstance('bb_task')->hSet($activity_id.'activity','user_list',implode(',',$user_list));
//                     Db::table('bb_task_activity')->where('id',$activity_id)->update(['user_list'=>implode(',',$user_list)]);
//                     return true;
//                 }
//             }
        }
        return false;
    }
    //通过活动id得到一个活动
    public static function get_activity($activity_id)
    {
        $ActivityDB = BBRedis::getInstance('bb_task')->hGetAll($activity_id.'activity');
        if (!$ActivityDB)
        {
            $ActivityDB = Db::table('bb_task_activity')->where('id',$activity_id)->find();
            if ($ActivityDB)
            {
                BBRedis::getInstance('bb_task')->hMset($activity_id.'activity',$ActivityDB);
            }
        }
        return $ActivityDB;
    }
    public function __construct()
    {
        return NULL;
    }
}