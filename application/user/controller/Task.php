<?php
/**
 * Created by PhpStorm.
 * User: 
 * 
 * Time: 15:00
 */

namespace app\user\controller;


use BBExtend\Sys;
use BBExtend\BBRecord;


class Task 
{
    /**
     * 在客户端轮询时，我随机让用户点赞一个短视频。
     * @param number $uid
     */
    public function polling($uid=0)
    {
        $db = Sys::get_container_db();
        $redis = Sys::getredis11();
        $uid = intval($uid);
        $key = "/user/task/polling:{$uid}:".date("Ymd");
        
        $count = $redis->incr($key);
        $redis->setTimeout($key, 48 * 3600);
        if ($count>10) { //随机点赞设置上限10
            return ["code"=>1 ];
        }
        
        $sql="
           select room_id from bb_record
where   bb_record.audit=1
  and bb_record.is_remove=0
  and bb_record.type in (1,2)
and not exists (select 1 from bb_record_like
  where bb_record_like.uid = {$uid}
    and bb_record.room_id = bb_record_like.room_id
)
order by bb_record.id desc limit 50     
                
                ";
        $col = $db->fetchCol($sql);
        shuffle($col);
        BBRecord::record_like($uid, array_pop($col), 1 );
        return ["code"=>1 ];
        
    }
    
    
}