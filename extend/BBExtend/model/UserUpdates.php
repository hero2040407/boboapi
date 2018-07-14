<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 
 * 
 * 
 */
class UserUpdates extends Model 
{
    protected $table = 'bb_users_updates';
//     protected $primaryKey="uid";
    
    public $timestamps = false;
    public static $err='';
    
    
    public function info()
    {
        
        
    }
    
    
    public function add_like($uid)
    {
        //$this->
        
        $db = Sys::get_container_db();
        $sql="update bb_users_updates set like_count = like_count+ 1 
               where id = ". $this->id;
        $db->query($sql);
        
    }
    
    
    
    /**
     * 图片，这时，未审核。
     * @param unknown $card_id
     */
    public static function insert_pic($uid,$word,$pic_arr)
    {
        $db = Sys::get_container_db_eloquent();
        
        $updates = new self();
        $updates->uid = $uid;
        $updates->create_time = time();
        $updates->is_remove = 0;
        $updates->status = 0; // 因为审核过，再调用此接口，所以固定为完成状态。
        if ( $word ){
          $updates->style = 5;
        }  else {
            $updates->style = 3;
        }
        $updates->save();
        
        if ( $word ) {
            
            $media = new UserUpdatesMedia();
            $media->bb_users_updates_id = $updates->id;
            $media->type = 1;
            $media->word = $word ;
            $media->save();
            
        }
        
        foreach ($pic_arr as $pic) {
            $media = new UserUpdatesMedia();
            $media->bb_users_updates_id = $updates->id;
            $media->type = 2;
            $media->url = $pic['arr'];
            $media->pic_width = $pic['pic_width'];
            $media->pic_height = $pic['pic_height'];
            
            $media->save();
        }
    }
    
    
    /**
     * 文字，这时，未审核。
     * @param unknown $card_id
     */
    public static function insert_word($uid,$word)
    {
        $db = Sys::get_container_db_eloquent();
        
        $updates = new self();
        $updates->uid = $uid;
        $updates->create_time = time();
        $updates->is_remove = 0;
        $updates->status = 0; // 未审核
        
        $updates->style = 2;
        $updates->save();
        
        $media = new UserUpdatesMedia();
        $media->bb_users_updates_id = $updates->id;
        $media->type = 1;
        $content = strip_tags($word);
        $media->word = $content;
        $media->save();
    }
    
    
    
    /**
     * 模卡审核成功。
     * @param unknown $card_id
     */
    public static function insert_card($card_id)
    {
        $db = Sys::get_container_db_eloquent();
        
        $sql="select * from bb_users_card where id=?";
        $row = DbSelect::fetchRow($db, $sql,[ $card_id ]);
        
        
        $updates = new self();
        $updates->uid = $row['uid'];
        $updates->create_time = $row['create_time'];
        $updates->is_remove = 0;
        $updates->status = 1; // 因为审核过，再调用此接口，所以固定为完成状态。
        
        $updates->style = 1;
        $updates->save();

        $media = new UserUpdatesMedia();
        $media->bb_users_updates_id = $updates->id;
        $media->type = 4;
        $media->bb_users_card_id = $card_id;
        $media->save();
    }
    
    
    /**
     * 短视频审核成功
     * 
     * @param unknown $record_arr
     */
    public static function insert_record($record_arr)
    {
        $db = Sys::get_container_db_eloquent();
        
        $updates = new self();
        $updates->uid = $record_arr['uid'];
        $updates->create_time = $record_arr['time'];
        $updates->is_remove = 0;
        $updates->status = 1; // 因为审核过，再调用此接口，所以固定为完成状态。
        
        if ($record_arr['title']) {
            $updates->style = 6;
        }else {
            $updates->style = 4;
        }
        $updates->save();
        
        if ( $record_arr['title'] ) {
        
          $media = new UserUpdatesMedia();
          $media->bb_users_updates_id = $updates->id;
          $media->type = 1;
          $media->word = $record_arr['title'] ;
          $media->save();
          
        }
        
        $media = new UserUpdatesMedia();
        $media->bb_users_updates_id = $updates->id;
        $media->type = 3;
        $media->url = $record_arr['video_path'];
        $media->bb_record_id = $record_arr['id'];
        $media->time_length =  \BBExtend\common\Date::time_length_display( 
                $record_arr['time_length_second'] );
        $media->save();
        
        
    }

   

}
