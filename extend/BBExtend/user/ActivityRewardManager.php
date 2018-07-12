<?php
namespace BBExtend\user;

/**
 * 
 * 
 * User: 谢烨
 */

use BBExtend\Sys;
use think\Db;
use BBExtend\BBRedis;

use BBExtend\user\ActivityReward;

/**
 * 
 * 特别说明，为什么要
 * 
 * @author Administrator
 *
 */
class ActivityRewardManager
{
   // public $redis;
    public $uid;
    public $activity_id;
    
    public $zong_price; // 谢烨，这是总金额。
    public $paiming;     // 谢烨，这是排名
    public $act_type;    //活动的分类，type0,pk擂台，type1，小记者，type2，悬赏。
    
    public $act_name;
    
    public $record_id;
    public $room_id;
    public $like_count;
    
    private $pk_statistics_json='';// pk统计数据，
    
    
    /**
     *   谢烨，redis最佳形式，集合，因为这是无序的。
     键名：relation_lahei_{$uid}
     类型集合。
     */
    public function  __construct($activity_id=0) {
        $this->activity_id= intval( $activity_id);
    }
    
    
    public static function getinstance($activity_id=0)
    {
        return new self($activity_id);
    }
    /**
     * 如果统一调用，则用这个方法。
     */
    public function process()
    {
       $result = $this->reward();
        $this->send_message();
        return $result;
    }
  
    public function send_message()
    {
        $act_id = $this->activity_id;
        $act = Db::table('bb_task_activity')->where('id',$act_id)->find();
        if (!$act) {
            return  ['code'=>0,'message' => '活动不存在' ];
        }
        
        // type=3 不发信息。
        if ($act['type']==3) {
            return ;
        }
        
        
        $db = Sys::get_container_db();
        $sql ="
        select * from bb_user_activity_reward
        where activity_id = {$act_id}
        and has_reward =1
        and has_message =0
        and exists(select 1 from bb_users where bb_users.uid =bb_user_activity_reward.uid )
        order by paiming asc
        limit 1000
        ";
        $result = $db->fetchAll($sql);
        
        
        
        
        foreach ($result as $v) {
            
            $client = new \BBExtend\service\pheanstalk\Client();
            $client->add(
                    new \BBExtend\service\pheanstalk\Data($v['uid'],
                            114,
                            [
                                    'act_id' => $act_id,
                                    'act_name' => $act['title'],
                                    'act_type' => $act['type'],
                                    'like_count' => $v['like_count'],
                                    'paiming' => $v['paiming'],
                                    'record_id' => $v['record_id'],
                                    'room_id' => $v['room_id'],
                                    'gold_type' => $act['gold_type'],
                            ], time()  )
                    
                    );
//             ActivityReward::getinstance($v['uid'],  $act_id )->set_act_name($act['title'])
//             ->set_act_type($act['type'])
//             ->set_like_count($v['like_count'])
//             ->set_paiming($v['paiming'])
//             ->set_record_id($v['record_id'])
//             ->set_room_id($v['room_id'])
//             ->set_gold_type($act['gold_type'])
//             ->send_message();
        }
        
    }
    
    /**
     * 判断红方还是蓝方胜利。
     */
    private function red_blue()
    {
        $activity_id = $this->activity_id;
        $db = Sys::get_container_db();
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
        $temp = [
                'red_score' =>$red_score,
                'red_count' =>$red_count,
                'red_like'  =>$red_like,
                'blue_score' => $blue_score,
                'blue_count' => $blue_count,
                'blue_like'  => $blue_like,
                
        ];
        $this->pk_statistics_json = json_encode($temp  );
        if ($red_score > $blue_score  ) {
            return 11;
        }
        return 12;
    }
    
    
    public function reward()
    {
        $act_id = $this->activity_id;
        $act = Db::table('bb_task_activity')->where('id',$act_id)->find();
        if (!$act) {
            return  ['code'=>0,'message' => '活动不存在' ];
        }
        if ($act['is_send_reward']==1) {
            return  ['code'=>0,'message' => '该活动已经发放过奖励' ];
        }
        
        $db = Sys::get_container_db();
        $sql ="
                select * from bb_record 
                 where type=2
                   and activity_id = {$act_id}
                   and audit=1
                   and is_remove=0
                   and exists(select 1 from bb_users where bb_users.uid =bb_record.uid )
                  order by `like` desc, `time` asc
                  limit 500
                ";
       // echo $sql;
        //$count = $db->f
        $list = $db->fetchAll($sql);
        
        // 谢烨，如果是pk，则list重新获取。
        if ($act['type']==3) {
            // xian 查红方胜，还是蓝方胜利。
            $team = $this->red_blue();
            $sql ="
                select * from bb_record
                 where type=2
                   and activity_id = {$act_id}
                   and audit=1
                   and is_remove=0
                   and usersort = {$team}
                   and exists(select 1 from bb_users where bb_users.uid =bb_record.uid )
                  order by `like` desc, `time` asc
                  limit 1000
                ";
            // echo $sql;
            //$count = $db->f
            $list = $db->fetchAll($sql);
            
            
        }
        
        
        $count = count($list);
        
        $paiming=0;
        foreach ($list as $v ) {
            $paiming++;
            $uid = intval( $v['uid']);
            $sql ="select count(*)  from bb_user_activity_reward
                    where uid = {$uid} 
                      and activity_id = {$act_id}
                    ";
            $result = $db->fetchOne($sql);
            if (!$result) { //极其重要，保障发奖成功。
                $db->insert('bb_user_activity_reward', [
                    'uid' => $uid,
                    'activity_id' => $act_id,
                    'create_time' => time(),
                ]);
            }
            // xieye, 这里就开始发奖励了。
          //  echo 1;
          
            // 谢烨，暂时屏蔽。
            
            ActivityReward::getinstance($uid,  $act_id )->set_act_name($act['title'])
               ->set_act_type($act['type'])
               ->set_like_count($v['like'])
               ->set_paiming($paiming)
               ->set_record_id($v['id'])
               ->set_room_id($v['room_id'])
               ->set_zong_price($act['reward'])
               ->set_gold_type($act['gold_type'])
               ->set_reward_people_coun($count)
               ->lingjiang(); //增加钱，同时，记录到bb_user_activity_reward表。
               
            
        }
        
      //  BBRedis::getInstance('bb_task')->hSet($act_id.'activity','is_send_reward',1);
        Db::table('bb_task_activity')->where('id',$act_id)->update(['is_send_reward'=>1,
               // 'pk_statistics_json'=> $this->pk_statistics_json
        ]);
        if ($act['type']==3  ) {
            Db::table('bb_task_activity')->where('id',$act_id)->update([
                     'pk_statistics_json'=> $this->pk_statistics_json
            ]);
        }
        
        
        return ['code'=>1, 
            'data'=>['count' => $count ]
            
        ];
        
    }
    
    

}