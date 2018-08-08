<?php
namespace app\apptest\controller;

// use BBExtend\BBRedis;
use  think\Db;
use BBExtend\common\MysqlTool;
use BBExtend\Sys;
use BBExtend\BBRecord;
class Createact
{
    
    public function start2()
    {
        $db = Sys::get_container_db();
        $sql ="select * from bb_user_activity limit 50";
        $result = $db->fetchAll($sql);
        dump($result);
    }
    
    /**
     * 打赏新表
     */
    public function start5()
    {exit;
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        
        $sql ="delete from bb_dashang_ranking";
        $db->query($sql);
        $sql ="update  bb_record set dashang_all=0";
        $db->query($sql);
        $sql ="update  bb_rewind set dashang_all=0";
        $db->query($sql);
        $sql ="update  bb_push set dashang_all=0";
        $db->query($sql);
        $sql ="select * from bb_dashang_log order by id asc";
        $query = $db2->query($sql);
        while($row = $query->fetch()) {
           //uid, target_uid, create_time, gold, room_id
           // 谢烨，首先，把 表的     
            $uid = $row['uid'];
            $target_uid = $row['target_uid'];
            $gold = $row['gold'];
            $room_id = $row['room_id'];
            echo "{$uid}:{$room_id}\n";
            $this->log($uid, $gold, $room_id, $target_uid);
             
        }
    }
    
    
    private function log($uid,  $price, $room_id, $target_uid)
    {
        $table = BBRecord::get_table_name($room_id);
        $db = Sys::get_container_db();
        if ($table) {
            $sql ="update {$table} set dashang_all = dashang_all + {$price} where room_id=? ";
            $db->query($sql,$room_id);
        
            //
           
            $sql ="select count(*) from bb_dashang_ranking where uid = {$uid} and room_id=? ";
            $count = $db->fetchOne($sql, $room_id);
            if ($count) {
                //原有修改，数值。
                $sql ="update bb_dashang_ranking
                set gold_all = gold_all + {$price}
                where uid = {$uid}
                and  room_id = ?
                ";
                $db->query($sql, $room_id);
            }else {
                //直接添加
                $db->insert("bb_dashang_ranking", [
                    'uid' =>$uid,
                    'target_uid' =>$target_uid,
                    'update_time' =>time(),
                    'gold_all' =>$price,
                    'room_id' =>$room_id,
                ]);
        
            }
        }
        //把视频的表的打赏总数也要加啊。
       
    }
    
    
    
    public function start()
    {exit;
        Sys::display_all_error();
       $db = Sys::get_container_db();
       $db2 = Sys::getdb2();
       $sql ="select * from bb_task_activity order by id asc";
       $query = $db2->query($sql);
       while($row = $query->fetch()) {
           $user_list = $row['user_list'];
           echo $user_list."<br>";
           
           if ($user_list) {
               $user_list = trim($user_list);
               $arr= explode(',', $user_list) ;
               
               $sql ="delete from bb_user_activity where activity_id={$row['id']}";
               $db->query($sql);
               foreach ($arr as $v) {
                   $db->insert('bb_user_activity', ['uid'=>$v,'activity_id'=> $row['id']  ]);
               }
           }
           
       }
        
    }
    
    
    public function start4()
    {exit;
        $db = Sys::get_container_db();
        $sql="select * from bb_task_activity where is_send_reward = 1";
        $result = $db->fetchAll($sql);
        foreach ($result as $v) {
            $id = $v['id'];
            $sql="select count(*) from bb_user_activity_reward where activity_id = {$id}";
            $count = $db->fetchOne($sql);
            if ($count==0) {
                $sql ="select * from bb_record 
                 where type=2
                   and activity_id = {$id}
                   and audit=1
                   and is_remove=0
                   and exists(select 1 from bb_users where bb_users.uid =bb_record.uid )
                  order by `like` desc, `time` asc
                  limit 500";
                $result2 = $db->fetchAll($sql);
                $i=0;
                foreach ($result2 as $vv) {
                    $i++;
                    $db->insert("bb_user_activity_reward", [
                        'uid' => $vv['uid'],
                        'create_time' => time(),
                        'activity_id' => $id,
                        
                        'has_reward' => 1,
                        'reward_count' => 0,
                        'reward_time' => time(),
                        'paiming' => $i,
                        'record_id' => $vv['id'],
                        'room_id'   => $vv['room_id'],
                        'like_count' => $vv['like'],
                    ]);
                }
            }
            
        }
    }
    
    public function start3()
    {exit;
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql ="select * from bb_user_activity order by id asc";
        $query = $db2->query($sql);
        while($row = $query->fetch()) {
           
            // xieye ,对于每条记录，都要查 record表，的 acity字段，和 uid字段，和 type=2，和type=3，和audit=1
            $sql ="select count(*) from bb_record 
                    where type in (2,3)
                      and audit=1
                    and activity_id = {$row['activity_id']}
                     and uid = {$row['uid']}
                    ";
             $count = $db->fetchOne($sql);
             echo "{$row['uid']}:  {$row['activity_id']} : {$count} \n";
             if ($count) {
                 $sql ="update bb_user_activity set has_checked=1 where id = {$row['id']}";
                 $db->query($sql);
                 
             }
             
//             if ($user_list) {
//                 $user_list = trim($user_list);
//                 $arr= explode(',', $user_list) ;
                 
//                 $sql ="delete from bb_user_activity where activity_id={$row['id']}";
//                 $db->query($sql);
//                 foreach ($arr as $v) {
//                     $db->insert('bb_user_activity', ['uid'=>$v,'activity_id'=> $row['id']  ]);
//                 }
//             }
             
        }
        
    }
    
   
    
   
}
