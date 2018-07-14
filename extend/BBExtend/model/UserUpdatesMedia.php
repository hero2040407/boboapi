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
class UserUpdatesMedia extends Model 
{
    protected $table = 'bb_users_updates_media';
//     protected $primaryKey="uid";
    
    public $timestamps = false;
    public static $err='';
    
//     public static function insert_record($record_arr)
//     {
//         $db = Sys::get_container_db_eloquent();
        
        
        
        
//         $sql="select * from bb_users_info where uid=?";
//         $result = DbSelect::fetchRow($db, $sql, [$uid]);
//         if (!$result) {
//             $db::table("bb_users_info")->insert(['uid'=>$uid ]);
//         }
//         $temp = UserInfo::where('uid', $uid)->first();
//         return $temp;
        
//     }

   

}
