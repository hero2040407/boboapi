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

class Taskactivityapi 
{
    
    
    const css_version=2;
    
    /**
     * 得到活动列表 每个活动列表中的用户只有第一名的数据 如果没有则代表没有人参加这个活动
     * 
     * 已废止。
     */
    public function get_activity_list()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $data = array();
        $activity = array();
        $index = 0;
        $ActivityDB_array = Db::table('bb_task_activity')
            ->where(['is_remove' => 0, 'is_show' => 1])
            ->where("start_time is not null")
            ->where("start_time < " . time())
      //      ->order('has_end','asc')
            ->order('has_end asc, start_time desc')
        //    ->limit(0,2)
            ->select();

        foreach ($ActivityDB_array as $ActivityDB) {
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
            
            // xieye Activity
            $activityDB['join_people'] = Activity::getinstance(0,$activityDB['id'] )->get_act_count();
            
            $bigpic_list = array();
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            //增加轮播图片
            if ($activityDB['bigpic_list']) {
                $bigpic_list_image = json_decode($activityDB['bigpic_list'], true);
                foreach ($bigpic_list_image as $image) {
                    if ($image['picpath'] == 'default.jpg') {
                        $image['picpath'] = $ServerURL . '/uploads/activity_pic/default.jpg';
                    } else {
                        $image['picpath'] = $ServerURL . $image['picpath'];
                    }
                    array_push($bigpic_list, $image);
                }
                
                foreach ($bigpic_list_image as $image) {
                    $activityDB['gray_pic'] = \BBExtend\common\Image::get_grayurl($image['picpath']);
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
        return ['data' => $data, 'code' => 1];
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
        $ActivityDB_array = Db::table('bb_task_activity')
            ->where(['is_remove' => 0, 'is_show' => 1])
            ->where("start_time is not null")
            ->where("start_time < " . time())
            ->order('has_end asc, start_time desc')
            ->limit($startid,$length)
            ->select();
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
            if ($activityDB['bigpic_list']) {
                $bigpic_list_image = json_decode($activityDB['bigpic_list'], true);
                
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
        $moviesDB_list = BBRecord::get_activity_movies($uid,$activity_id, $mini_page, $max_page,$type,$sort);
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
     * 加入一个活动每个用户只能加入一次活动
     */
    public function join()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $activity_id = input('?param.activity') ? (int)input('param.activity') : 0;
         $activityDB = self::get_activity($activity_id);
        if ($activityDB)
        {
            $user_list = array();
    
            $result = Activity::getinstance($uid)->canjia($activity_id);
            if ($result) {
                
//                 $ach = new \BBExtend\user\achievement\Huodong($uid);
//                 $ach->update(1);
                
                return ['message'=>'参加成功,请耐心等待客服审核!','code'=>1];
            }else {
                return ['message'=>'你已经参加过这个活动了','code'=>0];
            }
        }
        return ['message'=>'is not activity','code'=>0];
    }

    /**
     * 从活动列表中删除用户
     * @param unknown $uid
     * @param unknown $activity_id
     */
    public  function del_join($uid,$activity_id)
    {
        $activityDB = self::get_activity($activity_id);
        if ($activityDB) {
            
//             $ach = new \BBExtend\user\achievement\Huodong($uid);
//             $ach->update(-1);
            
            
            return Activity::getinstance($uid)->un_canjia($activity_id);
        }
        return false;
    }
    
    //点赞
    public function like()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $room_id = input('?param.room_id') ? (string)input('param.room_id') : 0;
        return BBRecord::record_like($uid, $room_id);
    }

    //取消点赞
    public function unlike()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $room_id = input('?param.room_id') ? (string)input('param.room_id') : 0;
        return BBRecord::record_un_like($uid, $room_id);
    }

    
    /**
     * 查询任务活动是否参加 code 0为未参加 1为参加
     * 
     */
    public function query_activity()
    {
        $uid = input('?param.uid') ? (int)input('param.uid') : 0;
        $activity_id = input('?param.activity_id') ? (int)input('param.activity_id') : 0;
        $ActivityDB = self::get_activity($activity_id);
        if ($ActivityDB) {
            return Activity::getinstance($uid, $activity_id)->check_canjia($activity_id);
        }
        return ['message' => '没有这个活动，或者活动已经关闭', 'code' => 0,];
    }
    

    /**
     * 结束的活动手动，后台发奖励，被后台http调用
     * @param unknown $activity_id
     */
    public  function reward_task_activity($activity_id)
    {
        $activityDB = self::get_activity($activity_id);
        if (!$activityDB) {
            return false;
        }
        
        $manager = new ActivityRewardManager($activity_id);
        return $manager->process();
    }

    
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
     * 活动详情 的html富文本，给客户端
     * @param number $id
     */
    public function detail($id=0)
    {
        $db = Sys::get_container_db();
        
        $type = Config::get("http_head_mobile_type"  );
        $css = ($type=='android')? "/html5/css/style.css" : "/html5/css/style_ios.css";
        $css.= "?v=" . self::css_version;
    
        $id = intval($id);
        $sql="select * from bb_task_activity where id={$id}";
        $detail_arr = $db->fetchRow($sql);
        $detail=strval($detail_arr['html_info']);
        $title = strval($detail_arr['title']);
        $s=<<<html
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1.0"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name='apple-mobile-web-app-status-bar-style' content='black'>
    <meta name='format-detection' content='telephone=no'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$title}-详情</title>
    <link rel="stylesheet" type="text/css" href="{$css}">
    <script type="text/javascript" src="/share/js/jquery.min.js"></script>
    <script type="text/javascript" src="/share/js/Adaptive.js"></script>
</head>
    
<body>
<div class="main" id="main">
{$detail}
</div>
</body>
</html>
    
html;
    echo $s;
    }
    
    
    /**
     * 活动详情 的html富文本，给客户端
     * @param number $id
     */
    public function h5_detail($id=0)
    {
        $db = Sys::get_container_db();
        
        $type = Config::get("http_head_mobile_type"  );
        $css = "/html5/css/style.css";
     //   $css = ($type=='android')? "/html5/css/style.css";// : "/html5/css/style_ios.css";
       // $css.= "?v=" . self::css_version;
        
       $css_content = file_get_contents(ROOT_PATH.'public' .   $css);
        
        $id = intval($id);
        $sql="select * from bb_task_activity where id={$id}";
        $detail_arr = $db->fetchRow($sql);
        $detail=strval($detail_arr['html_info']);
        $title = strval($detail_arr['title']);
        
        return [
                'code'=>1,
                'data' =>[
                        'css_content' => $css_content,
                        'detail' =>$detail,
                ]
        ];
    }
    
    
    
    
    
    
    
    
    
    
    
    
}