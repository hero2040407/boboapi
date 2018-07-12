<?php
namespace BBExtend\video;



use BBExtend\Sys;
use BBExtend\DbSelect;
/**
 * 活动状态。
 * 
 * 
 * @author xieye
 *
 */
class ActList
{
//     public $race=null;    

    private $list=null;
    private $uid;
    private $act_id;
    private $startid;
    private $length;
    
    private $has_join;
    private $type;
    
    public function __construct($uid, $act_id, $startid=0, $length=10,$type)
    {
        $this->uid = intval( $uid );
        $this->act_id = intval( $act_id );
        $this->startid = intval( $startid );
        $this->length = intval( $length );
        $this->type = intval( $type );
        //if ( )
        
        
        $db = Sys::get_container_dbreadonly();
        $sql = "select count(*) from bb_user_activity
                 where uid = ? and activity_id=? and has_checked=1";
        $has_join = $db->fetchOne($sql,[  $this->uid, $this->act_id ]);
        $this->has_join = boolval($has_join);
    }
    
    
    /**
     * 返回 一句话，返回状态，返回按钮文字，返回颜色。
     * 
     * 
     * @param unknown $uid
     * @param unknown $act_id
     */
    public function list_arr()
    { 
        $uid = $this->uid;
        $act_id = $this->act_id;
        $startid = $this->startid;
        $length = $this->length;
        $has_join = $this->has_join;
        $type = $this->type;
        
        $db = Sys::get_container_dbreadonly();
        
        // type = 1表示按最新排名。
        if ($type==1) {
            
            $sql=" select * from bb_record 
   where type=2 and activity_id =?
     and audit=1
     and is_remove=0
     order by time desc
    limit ?,?
";
            $result =   $db->fetchAll($sql,[  $act_id, $startid, $length ]);
            return  $this->add_sort($result) ;
        }
        
        
        // type = 0 表示按排行榜排名。
        
        $sql = "select has_paiming from bb_task_activity 
                 where id=?";
        $is_reward = $db->fetchOne( $sql , $act_id);
        
       
        if ($is_reward) {
            return $this->has_end_process();
        }else {
            return $this->not_end_process();
            
        }
       
    }
    
    
    private function not_end_process()
    {
        
        $uid = $this->uid;
        $act_id = $this->act_id;
        $startid = $this->startid;
        $length = $this->length;
        $has_join = $this->has_join;
        
        // 谢烨，在没有发奖的时候，不需要关联 bb_user_activity这张表的排名字段。
        $db = Sys::get_container_dbreadonly();
        // 此时，分几种情况，我已参加，我未参加。第一页，后面几页。
        if ( $has_join ) {
            //  我参加的话会比较麻烦。
            $sql="

select 
CASE uid 
         WHEN ? THEN 1 
         else 0 
END as uid2,
bb_record.*
 from bb_record where type=2 and activity_id = ?
and audit=1 and is_remove=0
order by uid2 desc , `like` desc
 limit ?,?
";
            $result = $db->fetchAll($sql,[$uid,  $act_id, $startid, $length ]);
            return  $this->add_sort_search_all($result ) ;
            
        }
        if (!$has_join) {
            
            $sql="
  select * from bb_record 
   where type=2 and activity_id =?
     and audit=1
     and is_remove=0
     order by `like` desc
    limit ?,?

";
            
            $result =   $db->fetchAll($sql,[  $act_id, $startid, $length ]);
            return  $this->add_sort($result) ;
            
        }
        
    }
    
    
    private function has_end_process()
    {
        
        $uid = $this->uid;
        $act_id = $this->act_id;
        $startid = $this->startid;
        $length = $this->length;
        $has_join = $this->has_join;
       
        // 谢烨，在没有发奖的时候，不需要关联 bb_user_activity这张表的排名字段。
        $db = Sys::get_container_dbreadonly();
        // 此时，分几种情况，我已参加，我未参加。第一页，后面几页。
        if ( $has_join ) {
            //  我参加的话会比较麻烦。
             
            $sql="
                    
select 
case bb_user_activity.uid 
         WHEN ? THEN 1 
         else 0 
END as uid2,
paiming_new as paiming,bb_record.* from bb_user_activity
left join bb_record 
 on bb_record.id = bb_user_activity.record_id
where bb_user_activity.activity_id = ?
and bb_record.audit = 1
order by uid2 desc, paiming_new asc
limit ?,?
";
            $result = $db->fetchAll($sql,[$uid,  $act_id, $startid, $length ]);
            return  $result  ;
            
        }
        if (!$has_join) {
            
            $sql="
 select paiming_new as paiming,bb_record.* from bb_user_activity
left join bb_record 
 on bb_record.id = bb_user_activity.record_id
where bb_user_activity.activity_id = ?
  and bb_record.audit = 1
order by paiming asc
limit ?,?
    
                    
";
            
            $result =   $db->fetchAll($sql,[  $act_id, $startid, $length ]);
            return  $result ;
            
        }
        
    }
    
    private function add_sort($result )
    {
        $startid = $this->startid;
        
        $i=1;
        foreach ( $result as $k => $v ) {
            $result[$k]['paiming'] = $startid+ $i;
            $i++;
        }
        
        return $result;
    }
    
    
    private function add_sort_search_all($result )
    {
        $i=1;
        foreach ( $result as $k => $v ) {
            $result[$k]['paiming'] = $this->get_sort_by_uid($v['uid']  );
            $i++;
        }
        
        return $result;
    }
    
    
    
    private function get_sort_by_uid($uid)
    {
        $list = $this->get_list_not_end();
        if (isset( $list[$uid] )) {
            return $list[$uid] ;
        }else {
            return 100;
        }
        
    }
    
    
    
    private function get_list_not_end()
    {
        if ( $this->list !=null ) {
            return $this->list;
        }
        
        $act_id = $this->act_id;
        
        $db = Sys::get_container_dbreadonly();
        $sql="
select bb_record.uid
 from bb_record where type=2 and activity_id = ?
and audit=1 and is_remove=0
order by `like` desc
";
        $result = $db->fetchCol($sql,[ $act_id ]);
        $new=[];
        $i=0;
        foreach ( $result as $uid ) {
            $i++;
            $new[ $uid ] = $i;
        }
        
        $this->list = $new;
        return $this->list;
    }
    

    
    
}



