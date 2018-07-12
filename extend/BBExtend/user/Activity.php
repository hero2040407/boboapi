<?php
namespace BBExtend\user;

/**
 * 
 * 
 * User: 谢烨
 */

use BBExtend\Sys;
use think\Db;
use BBExtend\BBUser;

class Activity
{
    public $redis;
    public $uid;
    public $activity_id;
    
    /**
     *   谢烨，redis最佳形式，集合，因为这是无序的。
     键名：relation_lahei_{$uid}
     类型集合。
     */
    public function  __construct($uid=0,$activity_id=0) {
        $redis = \BBExtend\BBRedis::connectionRedis();
        $redis->select(11);
        $this->redis = $redis;
        $this->uid = intval($uid);
        $this->activity_id=$activity_id;
    }
    
    
    public static function getinstance($uid=0, $activity_id=0)
    {
        return new self($uid, $activity_id);
    }
    
    public function get_act_count()
    {
        $act_id = $this->activity_id;
        if (!$act_id) {
            return 0;
        }
        $db = Sys::get_container_db();
        //$sql="select count(*) from bb_user_activity where activity_id = {$act_id}";
        
        $count = Db::table('bb_record')->where(['type'=>2,'activity_id'=>$act_id,
            'audit'=>1,'is_remove'=>0])->count();
        return $count;
//         return $db->fetchOne($sql);
        
    }
   
    public function un_canjia($act_id)
    {
        $uid = $this->uid;
        $act_id = intval($act_id);
        if ((!$uid) || ( !$act_id )) {
            return false;
        }
        
        $db = Sys::get_container_db();
        $sql="select count(*) from bb_user_activity where uid={$uid}
        and activity_id = {$act_id}
        ";
        $result = $db->fetchOne($sql);
        if (!$result) {
            return false;
        }
        
        $sql ="delete from bb_user_activity where uid={$uid}
          and activity_id = {$act_id}
                ";
       $result = $db->query($sql);
        return true;
        
    }
  
    //参加活动,加数据,这里带参数写的不好，不改了，防止错误。
    public function canjia($act_id)
    {
        $uid = $this->uid;
        $act_id = intval($act_id);
        if ((!$uid) || ( !$act_id )) {
            return false;
        }
        
        $db = Sys::get_container_db();
        $sql="select count(*) from bb_user_activity where uid={$uid}
          and activity_id = {$act_id}
        ";
        $result = $db->fetchOne($sql);
        if ($result) {
            return false;
        }
        $db->insert('bb_user_activity', [
            'uid' => $uid,
            'activity_id' => $act_id,
            'create_time' => time(),
        ]);
        return true;
        

    }
    
   
    
    /**
     * 是否参加
     * @param unknown $uid
     * @param unknown $room_id
     */
    public function has_canjia( $act_id)
    {
        $uid = $this->uid;
         $act_id = intval($act_id);
         if ((!$uid) || ( !$act_id )) {
             return false;
         }
      //   $act = Db::table("bb_task_activity")->where("id", $act_id)->find();
         
        
       $db = Sys::get_container_db();
        $sql="select count(*) from bb_user_activity where uid={$uid}
          and activity_id = {$act_id}
        ";
        return $db->fetchOne($sql);
    }
    
    /**
     * 最全面的核查
     * @param unknown $act_id
     * @return boolean|number[]|string[]|string
     */
    public function check_canjia($act_id)
    {
        $uid = $this->uid;
        $act_id = intval($act_id);
        if ((!$uid) || ( !$act_id )) {
            return ['code'=>0,'message'=>'活动不存在'];
        }
        $act = Db::table("bb_task_activity")->where("id", $act_id)->find();
        if (!$act) {
             return ['code'=>0,'message'=>'活动不存在'];
        }
        
        if ($act['is_send_reward']  ) {
            return ['code'=>0,'message'=>'活动已领奖，参加无意义啊'];
        }
        
        $user = BBUser::get_user($uid);
        //首先，查是否参加过
        $db = Sys::get_container_db();
        $sql="select count(*) from bb_user_activity where uid={$uid}
        and activity_id = {$act_id}
        ";
        if ($db->fetchOne($sql)) {
            return ['code'=>0, 'message' => '您已经参加过这个活动了'];
        }

        $sql = "select level from bb_users_exp where uid ={$uid}";
        $user_level = $db->fetchOne($sql);
        $user_age = BBUser::get_userage($uid);
        $user_sex = BBUser::get_usersex($uid);
        
        if ($act_id !=13) {
            //进行活动时间校验，年龄和级别校验
            $time =time();
            $time_start = intval($act['start_time']);
            $time_end = intval($act['end_time']);
            if(($time_start < $time) && ( $time_end > $time )) {
                
            }else {
                return ['code'=>0, 'message' => '活动已过期，不能参加'];
            }
            //进行级别校验。
            //$act_level = intval();
            if ($act['level'] && ( $act['level'] > $user_level ) ) {
                return ['code'=>0, 'message'=> '您的级别不够，请继续努力！'];
            }
            //年龄
            if ($act['min_age']  && ( $act['min_age'] > $user_age  ) ) {
                return ['code'=>0, 'message'=> '您的年龄不符合条件'];
            }
            if ($act['max_age']  && ( $act['max_age'] < $user_age  ) ) {
                return ['code'=>0, 'message'=> '您的年龄不符合条件'];
            }
            
            if ($act['sex'] ==0  && ( $user_sex==1  ) ) {
                return ['code'=>0, 'message'=> '您的性别不符合条件'];
            }
            if ($act['sex'] ==1  && ( $user_sex==0  ) ) {
                return ['code'=>0, 'message'=> '您的性别不符合条件'];
            }
        }
        return ['code'=>1,'message'=>'可以参加'];
    }
    
    
    
    /**
     * 谢烨，强行活动审核通过
     * 
     * 前置条件：如果已经认证失败，必须是未参加状态，才能改成已认证。防止有两条 audit=1的视频。
     *         如果
     * 
     */
    public function checked()
    {
        $db = Sys::get_container_db();
        $db->update('bb_user_activity', [
            'has_checked' => 1,
        ],  "uid = ".$this->uid." and activity_id =" . $this->activity_id  );
        return true;
    }
   

}