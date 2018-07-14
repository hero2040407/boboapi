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
class UserUpdatesComment extends Model 
{
    protected $table = 'bb_users_updates_comment';
//     protected $primaryKey="uid";
    
    public $timestamps = false;
    public static $err='';
    
    public function add_like($uid)
    {
        //$this->
        $dbread = Sys::get_container_dbreadonly();
        $sql="select * from bb_users_updates_like_log where uid=? and updates_id=? and type=2";
        $row = $dbread->fetchRow($sql, [ $uid, $this->id ] );
        if ($row) {
            return false;
        }
        
        
        $db = Sys::get_container_db();
        $bind=[
                "create_time" =>time(),
                "uid" =>$uid,
                "type" =>2,
                "updates_id"=> $this->id,
        ];
        $db->insert("bb_users_updates_like_log", $bind);
        
        $sql="update bb_users_updates_comment set like_count = like_count+ 1
               where id = ". $this->id;
        $db->query($sql);
        return true;
    }

   

}
