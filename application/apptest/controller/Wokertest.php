<?php
namespace app\apptest\controller;
use BBExtend\Sys;
class Wokertest
{
    
    /**
     * 这是测试队列是否可用的接口
     * 如果5313683的手机收到短信，则ok
     * bobo.yimwing.com/apptest/workertest/index
     */
//     public function index()
//     {
       
//         $target_uid = 5313683;
//         $uid = 10160;
//         $user = \app\user\model\UserModel::getinstance($uid);
//         $nickname = $user->get_nickname();
//         $pic = $user->get_userpic();
//         $time=time();
              
//         \Resque::setBackend('127.0.0.1:6380');
//         $args = array(
//                 'target_uid' => $target_uid,
//                 'uid'  => $uid,
//                 'time' => $time,
                
//                 'pic'      => $pic,
//                 'nickname' => $nickname,
//                 'type' => '124',
                
//             );
//         \Resque::enqueue('jobs2', '\app\command\controller\Job2', $args);
       
//    }
   
   /**
    * 代码修正短视频表，通告的id未设置的情况。
    */
   public function index ()
   {
       Sys::display_all_error();
       //echo 12;
       $db = Sys::get_container_db();
       $sql="select id from bb_users_updates
where style in(4,6)
 ";
       $updates_id_arr = $db->fetchCol($sql);
       foreach ( $updates_id_arr as $updates_id  ) {
          // echo "updates_id:{$updates_id}\n";
           $updates_obj = \BBExtend\model\UserUpdates::find($updates_id);
           if ($updates_obj) {
            //   echo "update_obj:you  \n";
           }
           
           $record_id = $updates_obj->get_record_id();
           
           if ($record_id) {
               echo "updates_id:{$updates_id} 22!\n";
               $db->update('bb_record',['type' =>7,'activity_id'=> $updates_id  ],'id='.$record_id);
               echo "updates_id:{$updates_id} success!\n";
           }
           
           
       }
       
   }
   
   // 
   public function index2()
   {
       Sys::display_all_error();
       //echo 12;
       $db = Sys::get_container_db();
       $sql="select * from bb_users_card where status=3
 ";
       $card_arr = $db->fetchAll($sql);
       
       foreach ( $card_arr as $card  ) {
           // echo "updates_id:{$updates_id}\n";
           $sql="select * from bb_users_updates_media where bb_users_card_id=?";
           
           $has = $db->fetchRow($sql,[ $card['id'] ]);
           if (!$has) {
               //
               $updates_obj = new \BBExtend\model\UserUpdates();
               $updates_obj->uid         = $card['uid'];
               
               $updates_obj->create_time = $card['create_time'] ;
               
               $updates_obj->style=1;//模块
               $updates_obj->status=1;//正常
               $updates_obj->save();
               
               $media_obj = new \BBExtend\model\UserUpdatesMedia();
               $media_obj->bb_users_updates_id = $updates_obj->id;
               $media_obj->type= 4;
               $media_obj->bb_users_card_id= $card['id'];
               $media_obj->save();
               
               echo "创建模块动态成功，动态id：{$updates_obj->id}";
           }
           
       }
       
   }
   
   
   
   
   
}

