<?php
namespace BBExtend\user;

/**
 * 拉黑缓存类
 * 购买视频缓存类
 * 
 * User: 谢烨
 */

use BBExtend\Sys;
use think\Db;

class Relation 
{
    public $redis;
    public $uid;
    
    /**
     *   谢烨，redis最佳形式，集合，因为这是无序的。
     键名：relation_lahei_{$uid}
     类型集合。
     */
    public function  __construct($uid=0) {
        $redis = \BBExtend\Sys::get_container_redis();
      //  $redis->select(11);
        $this->redis = $redis;
        $this->uid = intval($uid);
    }
    
    
    public static function getinstance($uid)
    {
        return new self($uid);
    }
    
    private function get_lahei_key($uid) 
    {
        return "user:lahei:{$uid}";
    }
    
    private function get_buy_movie_key($uid)
    {
        return "buy_movie:{$uid}";
    }
    
    
    
    
    
    
    private function create_lahei_set($uid)
    {
        $redis = $this->redis;
        //dump(23232);
        $key = $this->get_lahei_key($uid);
        if ( !$redis->exists($key) ) {
            $redis->sAdd($key , '0'); // 重要，加默认值0
            $db = Sys::get_container_db();
            $sql ="select target_uid from bb_lahei where type=1 and uid={$uid}";
            $result = $db->fetchCol($sql);
            $result = (array)$result;
            foreach ($result as $v) {
                $redis->sAdd($key, $v);
            }
        }
    }
    
    private function create_buy_movie_set($uid)
    {
        $redis = $this->redis;
        $key = $this->get_buy_movie_key($uid);
        if ( !$redis->exists($key) ) {
            $redis->sAdd($key , '0'); // 重要，加默认值0
            $db = Sys::get_container_db();
            $sql ="select room_id from bb_buy_video  where uid={$uid}";
            $result = $db->fetchCol($sql);
            $result = (array)$result;
            foreach ($result as $v) {
                $redis->sAdd($key, $v);
            }
        }
    }
    
//     public function check($uid,$target_uid){
//         $uid = intval($uid);
//         $target_uid = intval($target_uid);
//         if ((!$uid) || ( !$target_uid )) {
//             return false;
//         }
//         if ( $uid == $target_uid ) {
//             return true;
//         }
//         $key = $this->get_lahei_key($uid);
//         //    dump($key);
//         $this->create_lahei_set($uid);
//         return $this->redis->sIsMember( $target_uid );
//     }
    
  
    //拉黑,加数据
    public function lahei($uid,$target_uid)
    {
        $uid = intval($uid);
        $target_uid = intval($target_uid);
        if ((!$uid) || ( !$target_uid )) {
            return false;
        }
        if ( $uid == $target_uid ) {
            return false;
        }
        
        /**
         * 先查redis中有无，如无，先插入redis，带默认值0
         * 
         * 好，在有的情况下，直接sadd，根据返回数量，我也返回给接口
         * 成功还是失败！！
         */
        $key = $this->get_lahei_key($uid);
     //    dump($key);
        $this->create_lahei_set($uid);
        $result = $this->redis->sAdd($key, $target_uid);
        if ($result) {
            $db = Sys::get_container_db();
            $db->insert("bb_lahei",[
                'uid'=>$uid,
                'target_uid'=>$target_uid,
                'type'=>1, //1 拉黑
                'create_time'=>time(),
            ]);
            $sql ="update bb_currency set lahei_count = lahei_count+1 where uid={$uid}";
            $db->query($sql);
        }
        return $result;
    }
    
    //取消拉黑，减数据
    public function un_lahei($uid,$target_uid)
    {
        $uid = intval($uid);
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
        $key = $this->get_lahei_key($uid);
        $this->create_lahei_set($uid);
        $result = $this->redis->srem($key, $target_uid);
        if ($result) {
            Db::table('bb_lahei')->where('uid', $uid)->where('type',1)
               ->where('target_uid', $target_uid)->delete();
            $db = Sys::get_container_db();
            $sql ="update bb_currency set lahei_count = lahei_count-1 where uid={$uid}";
            $db->query($sql);
            
            $user = \BBExtend\BBUser::get_user($uid);
            if ($user) {
              
              $msg =[
                  'nickname' => $user['nickname'],
                  'uid' => $uid,
                  'message' => $user['nickname']. "(bobo号：{$uid}) 取消了对你的拉黑",
              ]  ;
              //要给nodejs发消息
             // $msg =   $user['nickname']. "(bobo号：{$uid}) 取消了对你的拉黑";
              \BBExtend\BBNodeJS::SendMessage($target_uid, 3, $msg);
              
            }
            
        }
        return $result;
    }
    
    /**
     * has 拉黑
     * @param unknown $uid
     * @param unknown $room_id
     */
    public function has_lahei($uid, $target_uid)
    {
        $uid = intval($uid);
        $key = $this->get_lahei_key($uid);
        
        $this->create_lahei_set($uid);
        $result = $this->redis->sismember($key, $target_uid);
        return intval($result);
    }
    
    
    // 用户购买课程，只会加数据,只能波币购买
    // 要点：用户购买某个视频，只需一次，就能永久免费观看该视频。
    // 返回假，表示已购买。
    public function buy_video($uid, $room_id, $price)
    {
        $uid = intval($uid);
        $room_id = strval($room_id);
        $price = intval($price);
        
        if ((!$uid) || ( !$room_id ) || (!$price) ) {
            return false;
        }
        /**
         * 先查redis中有无，如无，先插入redis，带默认值0
         *
         * 好，在有的情况下，直接sadd，根据返回数量，我也返回给接口
         * 成功还是失败！！
         */
        $key = $this->get_buy_movie_key($uid);
        $this->create_buy_movie_set($uid);
        $result = $this->redis->sadd($key, $room_id);
        if ($result) { // 缓存中插入成功，意味着必须加入数据库
            $db = Sys::get_container_db();
            $db->insert("bb_buy_video",[
                'uid'=>$uid,
//                 'target_uid'=>$target_uid,
//                 'type'=>2, //1 拉黑 2举报
//                 'small_type' => intval($type),
                'create_time'=>time(),
                'room_id' => $room_id,
                'price' => $price,
                
            ]);
        }
        return $result; // 这里返回表示假，表示已购买
    
    }
    
    public function has_buy_video($uid, $room_id)
    {
        $uid = intval($uid);
        $key = $this->get_buy_movie_key($uid);
        $this->create_buy_movie_set($uid);
        $result = $this->redis->sismember($key, $room_id);
        return intval($result);
    }
    
    
    public function remove()
    {
        $redis = $this->redis;
        $uid = $this->uid;
        $key = $this->get_buy_movie_key($uid);
        $redis->delete($key);
        
        //下面做拉黑的处理。
        $key  = $this->get_lahei_key($uid);
        $redis->delete($key);
        
        //下面是被拉黑的处理
        //再删除，关注列表
        $db = Sys::get_container_db();
        $sql='select * from  bb_lahei where target_uid= '.$this->uid;
        $query = $db->query($sql);
        while ($row = $query->fetch()) {
            $key = $this->get_lahei_key($row['uid']);
            $redis->srem($key, $this->uid);
        }
    }
    

}