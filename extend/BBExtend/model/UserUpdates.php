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
    
    public static function insert_record($record_arr)
    {
        $db = Sys::get_container_db_eloquent();
        
        $updates = new self();
        $updates->uid = $record_arr['uid'];
        $updates->create_time = time();
        $updates->is_remove = 0;
        if ($updates->title) {
            $updates->style = 6;
        }else {
            $updates->style = 4;
        }
        $updates->save();
        
        $media = new UserUpdatesMedia();
        $media->bb_users_updates_id = $updates->id;
        
        
        
        $sql="select * from bb_users_info where uid=?";
        $result = DbSelect::fetchRow($db, $sql, [$uid]);
        if (!$result) {
            $db::table("bb_users_info")->insert(['uid'=>$uid ]);
        }
        $temp = UserInfo::where('uid', $uid)->first();
        return $temp;
        
    }

   

}
