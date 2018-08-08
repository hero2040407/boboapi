<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\common\Image;

class Dsmigrate
{
   
   /**
    * 这是demo页面，同时包括了get和处理post的逻辑
    * 
    * 1复选框，2文本框，3上传，4简介
    */
   public function index()
   {
       Sys::display_all_error();
    $db = Sys::get_container_db();
        $act_id_arr =[83,84,90];
        $zong_ds_map = [
            83=>17,
            84=> 9,
            90 => 59,
        ];
        $qudao_map = [
            83=>18,
            84=> 11,
            90 => 60,
        ];
        
        foreach ($act_id_arr as $act_id) {
            $zong_ds_id = $zong_ds_map[$act_id];
            $qudao_id = $qudao_map[$act_id];
            //先查bb_record
            $sql ="select * from bb_record where type=2 and 
                      is_remove=0 and audit=1 and activity_id={$act_id}
                     and exists(select 1 from bb_users where bb_users.uid = bb_record.uid)
                      ";
            $record_arr =$db->fetchAll($sql);
            // 谢烨，先确保给register_ar注册
            // 先查有没有
            foreach ($record_arr as $record) {
                $uid = $record['uid'];
                $record_id = $record['id'];
                $sql="select * from ds_register_log where zong_ds_id={$zong_ds_id}
                   and uid = {$uid}
                ";
                $has_join = $db->fetchRow($sql);
                if (!$has_join) {
                    $ss = "给uid:{$uid}自动报名，大赛id:{$zong_ds_id}";
                //    Sys::debugxieye($ss);
                 //   echo $ss ."\n";
                    $sql ="select * from ds_race where id = {$zong_ds_id}";
                    $ds_row = $db->fetchRow($sql);
                    $sql ="select * from bb_users where uid = {$uid}";
                    $user_row = $db->fetchRow($sql);
                   // var_dump($user_row);
                    $has_dangan = intval( $ds_row['has_dangan']);
                    $has_dangan= 1- $has_dangan;
//                     $db->insert('ds_register_log', [
//                         'ds_id' => $qudao_id,
//                         'zong_ds_id' => $zong_ds_id,
//                         'create_time' =>time(),
//                         'has_join' =>1,
//                         'money' => 0,
//                         'phone' => strval($user_row['phone']),
//                         'sex' =>1,
//                         'birthday' => '2007-05',
//                         'name' => strval($user_row['nickname']),
//                         'has_pay' =>0,
//                         'has_dangan' => $has_dangan,
//                         'uid' =>$uid,
//                     ]);
//                     echo $db->lastInsertId();
                    
                    /////..自动报名
                }
                $sql="select * from ds_record where ds_id={$zong_ds_id}
                and uid = {$uid}
                ";
                $has_join = $db->fetchRow($sql);
                if (!$has_join) {
                    $info = "给uid:{$uid}上传视频，视频id:{$record_id}，大赛id:{$zong_ds_id}";
                    echo $info."\n";
             //       Sys::debugxieye($info);
//                     $db->insert("ds_record", 
//                             [
//                                 'ds_id' => $zong_ds_id,
//                                 'uid' =>$uid,
//                                 'record_id' => $record_id,
//                                 'create_time' => time(),
//                             ]);
//                     // Sys::debugxieye("给uid:{$uid}上传视频，视频id:{$record_id}，大赛id:{$zong_ds_id}\n");
//                     /////..自动上传视频
//                     //$db->iun
//                     $sql ="update bb_record set activity_id=0,type=4 where id={$record_id}";
//                     $db->query($sql);
//                     $sql="delete from bb_user_activity where uid={$uid} and activity_id={$act_id}";
//                     $db->query($sql);
//                     $db->insert('bb_alitemp', [
//                         'create_time'=>date("Y-m-d H:i:s"),
//                         'uid'=>$uid,
//                         'test1' => $zong_ds_id,
//                         'content' => $act_id,
//                     ]);
                    
                    /// 改视频id，
                    // 去除此人在此活动的记录select count(*) from bb_user_activity where uid={$uid}
       //and activity_id = {$act_id}
                    /// 加入到alitemp表           
                    
                }
            }
        
       }// end foreach wai
    
   
    }// end function
    
    
    
}