<?php
/**
 * Node调用的接口
 * 
 */

namespace app\push\controller;


use BBExtend\Sys;
use BBExtend\DbSelect;
class Chat
{
    /**
     * 开始一个伪直播
     */
    public function sham_push($use_test=0,$duration_min = 30, $duration_max = 90 )
    {
        // 查询条件
        // 找一个3个月没人用过的user用户。login_time < time() - 1* 30 * 24 * 3600
        $duration_min = intval( $duration_min );
        $duration_max = intval( $duration_max );
        $db = Sys::get_container_db_eloquent();
        if ($use_test) {
            $sql="select uid  from bb_users_test";
        }else {
            $month3 = time() - 3 * 30 * 24 * 3600;
            $sql="select uid from bb_users where login_time < '{$month3}' and login_time is not null 
              and not_login=0
            limit 100";
        }
        $ids = DbSelect::fetchCol($db, $sql);
        $uid = $ids[ array_rand( $ids ) ];
        
        // 下面随机抽取回播
        $time=time();
//        $url='http://pushall.oss-cn-shanghai.aliyuncs.com/record/bobo/SII9U30S-7893247push.m3u8';
        $time1 = time() - 4 * 30 * 24 * 3600;
        $time2 = time() - 1 * 30 * 24 * 3600;
        $sql ="
        select id
          from bb_rewind
         where  (end_time - start_time) between {$duration_min} and {$duration_max}
           and end_time between {$time1}  and {$time2}
         limit 100
        ";
        $ids = DbSelect::fetchCol($db, $sql);
        $rewind = $db::table( 'bb_rewind' )->where('id', $ids[ array_rand( $ids ) ] )->first();
        
        
        $db::table('bb_push')->where( 'uid', $uid )->update([
                'create_time'=>$time,
                'time' => $time,
                'end_time' => $time + ( $rewind->end_time - $rewind->start_time ),
                'event' =>'publish',
                'price_type' => 2,
                'pull_url' => $rewind->rewind_url,
        ]);
        return ['code'=>1,'data'=>[
                'start_time' =>$time,
                'duration' =>  $rewind->end_time - $rewind->start_time ,
                'uid' =>$uid,
        ]];
        
    }
    
   
    
    /**
     * 结束一个伪直播
     */
    public function sham_push_end($uid=0)
    {
        $uid = intval($uid);
         $db = Sys::get_container_db();
         $db::table('bb_push')->where( 'uid', $uid )->where('price_type',2)->update([
                'event' =>'publish_done',
                'price_type' => 1,
        ]);
        return ['code'=>1];
    }
    
    
    //聊天log
    public function save_info($room_id=0,$uid=0,$info='')
    {
        $obj = new \BBExtend\model\ChatLog();
        $obj->room_id  =intval($room_id);
        $obj->uid  =intval($uid);
        $obj->content  = trim($info);
        $obj->create_time = time();
        $obj->save();
        return ["code"=>1];
        
    }
}