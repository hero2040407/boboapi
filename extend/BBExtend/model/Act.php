<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;


/**
 * 
 * 
 * User: 谢烨
 */
class Act extends Model 
{
    protected $table = 'bb_task_activity';
    
    public $timestamps = false;
    
    
    
    /**
     * 视频状态，1未上传未审核，2上传审核中，3成功，4失败，
     * @param unknown $uid
     * @return string
     */
    public function record_status( $uid )
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_record
where uid=? and activity_id=? and type=2
order by id desc limit 1
                
";
        $record =$db->fetchRow($sql,[ $uid, $this->id ]);
        
        if (!$record) {
            return 1;
        }
        
        if ($record['audit']==0) {
            return 2;
        }
        if ($record['audit']==1) {
            return 3;
        }
        if ($record['audit']==2) {
            return 4;
        }
        
        
        return 1;
    }
    
    
    public function record_paiming( $uid )
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_user_activity
where uid=? and activity_id=? 
limit 1
                
";
        $record =$db->fetchRow($sql,[ $uid, $this->id ]);
        
        if (!$record) {
            return 100;
        }
        
        
        return $record['paiming_new'] ;
    }
    
    
    
}
