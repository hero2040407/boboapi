<?php
namespace BBExtend\user;

/**
 * 关注类
 * 
 * 数量用最简单的set单变量！
 * 关注人列表用集合
 * 
 * @author 谢烨
 */

use think\Db;
use BBExtend\Sys;
use BBExtend\Level;
use BBExtend\message\Message;
use BBExtend\user\exp\Exp;

class Focus 
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
    public $message;
    
    /**
     * @param int $uid
     */
    public function  __construct($uid=0) 
    {
        $uid = intval($uid);
        $this->redis = Sys::getredis11();
        $this->uid = $uid;
        $this->message = '您已经关注过这个用户了';
    }
    
    /**
     * 看情况用，如果想调用多个方法，则不用此函数
     * 
     * @param int $uid
     * @return Focus
     */
    public static function getinstance($uid)
    {
        return new self($uid); 
    }

    /**
     * 获取redis键：当前用户关注数量
     * @return string
     */
    public function get_focus_count_key()
    {
        return "user:focus_count:" . $this->uid;
    }
    
    /**
     * 获取redis键，当前用户关注列表
     * @return string
     */
    public function get_my_focus_key()
    {
        return $this->get_focus_key_by_userid($this->uid);
    }
    
    /**
     * 获取redis键，某个用户的关注列表
     * @param int $uid
     * @return string
     */
    private function get_focus_key_by_userid($uid)
    {
        return "user:focus_list:" . $uid;
    }
    
    /**
     * 返回当前用户关注人数，查那个redis的关注列表的长度即可。
     * 减1是因为有一个默认的0。
     * 
     * @return int
     */
    public function get_guanzhu_count()
    {
        $redis = $this->redis;
        $this->create_list();
        $key = $this->get_my_focus_key();
        return $redis->sSize($key) - 1 ; //去除默认0
    }
    
    
    /**
     * 获取关注列表，注意一定包含无用的0
     * @return unknown
     */
    public function get_guanzhu_list()
    {
        $redis = $this->redis;
        $this->create_list();
        $key = $this->get_my_focus_key();
        $result =  $redis->sMembers($key);
        
        return $result;
    }
    
    /**
     * 返回粉丝数量
     * 
     * @return int
     */
    public function get_fensi_count()
    {
        $redis = $this->redis;
        $key = $this->get_focus_count_key();
        $count = $redis->get($key);
        if ($count === false) {
            $this->create_count();
            $count = $redis->get($key);
        }
        return  $count;
    }
    
    /**
     * 增加一个粉丝数量
     * 
     * @return int 增加后的粉丝数量
     */
    public function add_fensi_count()
    {
        $redis = $this->redis;
        $key = $this->get_focus_count_key();
        $this->create_count();
        return $redis->incr($key);
    }
    
    /**
     * 减少一个粉丝数量
     *
     * @return int 减少后的粉丝数量
     */
    public function sub_fensi_count()
    {
        $redis = $this->redis;
        $key = $this->get_focus_count_key();
        $this->create_count();
        return $redis->decr($key);
    }
    
    /**
     * 在redis中创建当前用户的粉丝数量
     */
    public function create_count()
    {
        $uid = $this->uid;
        $key = $this->get_focus_count_key();
        $redis = $this->redis;
        if ( !$redis->exists($key) ) {
            $db = Sys::get_container_db();
            $sql ="select count(*) from bb_focus where focus_uid= {$uid}";
            $result = $db->fetchOne($sql);
            $redis->set($key, $result);
        }
    }
    
    /**
     * 在redis中，创建当前用户关注的人的列表
     */
    public function create_list()
    {
        $uid = $this->uid;
        $redis = $this->redis;
        $list_key = $this->get_my_focus_key();
        if ( !$redis->exists($list_key) ) {
            $redis->sAdd($list_key , '0'); // 重要，加默认值0
            $db = Sys::get_container_db();
            $sql ="select focus_uid from bb_focus where uid = {$uid}";
            $result = $db->fetchCol($sql);
            $result = (array)$result;
            foreach ($result as $v) {
                $redis->sAdd($list_key, $v);
            }
        }
    }
    
    
    /**
     * 加关注, 注意，可能失败
     * 
     * @param int $target_uid
     * @param int $focus_time
     * @return bool
     */
    public function focus_guy($target_uid,$focus_time=0)
    {
        $uid = $this->uid;
        $target_uid = intval($target_uid);
        if ((!$uid) || ( !$target_uid )) {
            return false;
        }
        if ( $uid == $target_uid ) {
            $this->message='您不可以关注自己';
            return false;
        }
        if ($focus_time==0) {
            $focus_time = time();
        }
        /*
         * 先查redis中有无，如无，先插入redis，带默认值0
         *
         * 好，在有的情况下，直接sadd，根据返回数量，我也返回给接口
         * 成功还是失败！！
         */
        $key = $this->get_my_focus_key();
        $this->create_list();
        $result = $this->redis->sAdd($key, $target_uid);
        if ($result) {
            
            //重要，这句话必须放在最前面写，比数据库前，否则逻辑错误！
            $target_obj = new self($target_uid);
            $target_obj->add_fensi_count();
            
            $db = Sys::get_container_db();
            $db->insert("bb_focus",[
                'uid'=>$uid,
                'focus_uid'=>$target_uid,
                'time'=>$focus_time,
            ]);
            
            //重要，加经验，没做呢！！！！
            Exp::getinstance($target_uid)->set_typeint(Exp::LEVEL_LIKE )->add_exp();
            
            //对目标用户修改排名,2017 04 xieye注释掉下行，勿删下行
    //        \BBExtend\user\Ranking::getinstance($target_uid)->set_fensi_ranking();
            \BBExtend\user\Tongji::getinstance($uid)->focus($target_uid);
            $this->redis->delete("focuson:new_index:{$uid}");
            
            $focus_send = new \BBExtend\message\send\Focus($target_uid, $uid, $focus_time);
            $focus_send->send();
            
        }
        return $result;
    }
    
    
    //取消关注，减数据
    public function un_focus_guy($target_uid)
    {
        $uid = $this->uid;
        $target_uid = intval($target_uid);
        if ((!$uid) || ( !$target_uid )) {
            return false;
        }
        /**
         * 先查redis中有无，如无，先插入redis，带默认值0
         *
         * 好，在有的情况下，直接sadd，根据返回数量，我也返回给接口
         * 成功还是失败！！
         */
        $key = $this->get_my_focus_key();
        $this->create_list();
        $result = $this->redis->srem($key, $target_uid);
        if ($result) {
           
            //重要，粉丝减一。
            $target_obj = new self($target_uid);
            $target_obj->sub_fensi_count();
            
            Db::table('bb_focus')->where('uid', $uid)
              ->where('focus_uid', $target_uid)->delete();
          // 2017 04
            $this->redis->delete("focuson:new_index:{$uid}");
              //对目标用户修改排名
         //     \BBExtend\user\Ranking::getinstance($target_uid)->set_fensi_ranking();
              
        }
        return $result;
    }
    
    /**
     * 我是否关注某人。
     * @param unknown $room_id
     */
    public function has_focus($target_uid)
    {
        $key = $this->get_my_focus_key();
        $this->create_list();
        $result = $this->redis->sismember($key, $target_uid);
        return intval($result);
    }
    
    public function remove()
    {
        $key = $this->get_focus_count_key();
        $redis = $this->redis;
        $redis->delete($key);
        //然后删除我关注的人
        $key = $this->get_my_focus_key();
        $redis->delete($key);
        
        //再删除，关注列表
        $db = Sys::get_container_db();
        $sql='select * from  bb_focus where focus_uid= '.$this->uid;
        $query = $db->query($sql);
        while ($row = $query->fetch()) {
            $key = $this->get_focus_key_by_userid($row['uid']);
            $redis->srem($key, $this->uid);
        }
    }
   
    /**
     * 2017 07 给某人增加机器人粉丝
     */
    public function add_robot_fensi()
    {
        $uid = $this->uid;
        $focusDB = \BBExtend\BBUser::get_user($uid);
        if ($focusDB['permissions']==3) { // 如果被关注用户是特邀用户，则需同时加3个机器人粉丝。
            $db = Sys::get_container_db();
            $count  = mt_rand(3,4);
            $sql = "select uid from bb_users where permissions = 99
            and not exists (select 1 from bb_focus where bb_focus.uid = bb_users.uid
            and bb_focus.focus_uid = {$uid}
            ) limit 50";
            $ids = $db->fetchCol($sql);
            $result_count = count($ids);
            if ($ids) {
                shuffle($ids);
                $time = time();
                for ($i=0;$i < $count && $i < $result_count ; $i++) {
                    // 现在用任务完成
                    $db->insert('bb_system_task', [
                        'datestr' => date("Ymd"),
                        'type' =>1,
                        'uid'  => array_pop( $ids ),
                        'target_uid' => $uid,
                        'created_at' => $time,
                        'has_finish'=>0,
                        'task_at'   => $time + mt_rand(2*60,  10 * 60),
                    ]);
                }
            }
        }
    }

}