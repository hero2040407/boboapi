<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 
 * 
 * : 谢烨
 */
class Record extends Model 
{
    protected $table = 'bb_record';
    public $timestamps = false;
    
    private $view_cache_redis_time = 300;
    
    // 查关联的用户
    public function user()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\User', 'uid', 'uid');
    }
    
    // 查关联的通告
    public function updates()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\User', 'activity_id', 'id');
    }
    
    
    
    public function is_checked(){
        return $this->audit == \BBExtend\fix\TableType::bb_record__audit_yishenhe;
    }
    
    /**
     * 得到点击量
     */
    public function get_updates_view_count()
    {
        if ($this->type==6) {
            
        }
        return $this->good_get_views();
    }
    
    /**
     * 得到点赞
     */
    public function get_updates_like_count()
    {
        
    }
    /**
     * 得到评论数量
     */
    public function get_updates_like_count()
    {
        
    }
    
    
    
    
    
    
    
    private function views_key($id){
        return "record:view:{$id}";
        
    }
    
    private function views_key_by_room_id($room_id){
        return "record:view:room_id:{$room_id}";
    }
    
    
    public function add_views($id)
    {
        $redis = Sys::get_container_redis();
        $key = $this->views_key($id);
        // 先取，发现没有，就调用init，然后再取
        $result = $redis->get( $key );
        if ($result === false) {
            $this->initv($id);
        }
        $result = $redis->incr( $key );
//         $redis->incr( $this->views_key_by_room_id($room_id) );
        return intval( $result );
    }
    
    
    // get必须有两个方法，这是方法一
    public function good_get_views()
    {
        $redis = Sys::get_container_redis();
        $id = $this->id;
        $key = $this->views_key($id);
        // 先取，发现没有，就调用init，然后再取
        $result = $redis->get( $key );
        if ($result === false) {
            //             $db = Sys::get_container_db_eloquent();
            //             $sql="select room_id
            //                    from bb_record where id=".intval($id);
            //             $room_id = DbSelect::fetchOne($db, $sql);
            
            $result =  $this->initv($id);
            return $result;
            
        }else {
            return intval( $result );
        }
        
    }
    
    
   
    // get必须有两个方法，这是方法一
    public function get_views($id)
    {
        $redis = Sys::get_container_redis();
        $key = $this->views_key($id);
        // 先取，发现没有，就调用init，然后再取
        $result = $redis->get( $key );
        if ($result === false) {
//             $db = Sys::get_container_db_eloquent();
//             $sql="select room_id 
//                    from bb_record where id=".intval($id);
//             $room_id = DbSelect::fetchOne($db, $sql);
            
            $result =  $this->initv($id);
            return $result;
            
        }else {
            return intval( $result );
        }
        
    }
    
    // get必须有两个方法，这是方法二
//     public function get_views_by_room_id($room_id)
//     {
//         $redis = Sys::get_container_redis();
//         $key = $this->views_key_by_room_id($room_id);
//         // 先取，发现没有，就调用init，然后再取
//         $result = $redis->get( $key );
//         if ($result === false) {
//             $db = Sys::get_container_db_eloquent();
//             $sql="select id
//                    from bb_record where room_id=?";
//             $id = DbSelect::fetchOne($db, $sql,[$room_id]);
            
//             $result =  $this->initv($id, $room_id);
//             return $result;
            
//         }else {
//             return intval( $result );
//         }
        
//     }
    
    
    public function initv($id)
    {
        $redis = Sys::get_container_redis();
        $db = Sys::get_container_db_eloquent();
        $sql="select look from bb_record where id=".intval($id);
        $count = DbSelect::fetchOne($db, $sql);
        
        $key = $this->views_key($id);
        $redis->setEx($key, $this->view_cache_redis_time ,  intval( $count ) );
        
//         $key = $this->views_key_by_room_id($room_id);
//         $redis->setEx($key, $this->view_cache_redis_time ,  intval( $count ) );
        return intval( $count );
    }
    
//     public function initv($id, $room_id)
//     {
//         $redis = Sys::get_container_redis();
//         $db = Sys::get_container_db_eloquent();
//         $sql="select look from bb_record where id=".intval($id);
//         $count = DbSelect::fetchOne($db, $sql);
        
//         $key = $this->views_key($id);
//         $redis->setEx($key, $this->view_cache_redis_time ,  intval( $count ) );
        
//         $key = $this->views_key_by_room_id($room_id);
//         $redis->setEx($key, $this->view_cache_redis_time ,  intval( $count ) );
//         return intval( $count );
//     }
    
    
    
    
    

}
