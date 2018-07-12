<?php
namespace BBExtend\message;


use BBExtend\Sys;
use think\Db;
use BBExtend\BBUser;
use BBExtend\fix\MessageType;

/**
 * 
 * 
 * @author Administrator
 *
 */
class MergePushMessage extends MessageMethod 
{
    
   
    
   function send(Message $m)
   {
      $uid = $m->uid; 
      $db = Sys::get_container_db();
      $img = $m->img;
      if ($img) {
          $img = \BBExtend\common\Image::geturl($img);
      }
      
      
      $msg_arr =[
              'uid' => $m->uid,
              'type' => $m->type,
              'pic_uid' =>$m->pic_uid,
              'title' =>$m->get_my_title(),
              'info' => json_encode($m->get_message_array() ,JSON_UNESCAPED_UNICODE ),
              'img' => $img,
              'time' => time(),
              'is_read' => 0,
              'overdue_time' => time() + 30 * 24 * 3600 ,
              'newtype' => $m->newtype,
      ];
      
//       if (  $m->type == MessageType::shipin_beizan ) {
//           $msg_arr['newtype']=2;
//       }
      
      $db->insert("bb_msg", $msg_arr );
      
      $temp = get_cfg_var('guaishou.username');
      if (in_array($temp, ['200', 'xieye',])){
          return true;
      }
      // nodejs推送新消息未读
      $no_read = Db::table('bb_msg')->where(['uid'=>$m->uid,'is_read'=>0])->count();
      $user =     BBUser::get_user($uid);
   //   $user['is_online']=0;
      if ($user ) {
   // $user['is_online'] =0;
          if ($user['is_online']) {
              
              try {
                  $node_service = Sys::get_container_node();
                  $url = \BBExtend\common\BBConfig::get_touchuan_url();
                  $node_service->http_Request($url,
                          ['data'=>$no_read,'uid'=>$uid,'type'=>1]);
              } catch (\Exception $e) {
              }
          }else {
              $send=1;
              //这里对123，短视频上传，要查用户是否接受，用户不接受，则不发消息。
              // 消息目标用户$m->uid，
              $temps = $m->uid;
              
       //       Sys::debugxieye($temps);
              
              if ($m->type==123) {
                  $sql="select label from bb_record where id= ". intval( $m->other_record_id);
                  $record_label = intval($db->fetchOne($sql));
                  // 查出该人的对该label的接受程度。
                  $user_config = \BBExtend\message\MessageConfig::get_instance($m->uid);
                  $config = $user_config->get_one_config($m->type, $record_label); 
                      
                  $send = $config? 1:0;
              }elseif ( in_array($m->type, [119, 122]) ) {
                  $user_config = \BBExtend\message\MessageConfig::get_instance($m->uid);
                  $config = $user_config->get_one_big_config($m->type);
                  $send = $config? 1:0;
              }
              if ($send) {
                  $db->insert("bb_msg_cache", [
                      'uid' => $m->uid,
                      'type' => $m->type,
                      'title' => $m->title,
                      'info' => json_encode($m->get_message_array() ,JSON_UNESCAPED_UNICODE ),
                      'img' => $img,
                      'time' => time(),
                      'is_read' => 0,
                      'other_uid'=>$m->other_uid,
                      'other_record_id'=> $m->other_record_id,
                      
                      //'overdue_time' => time() + 30 * 24 * 3600 ,
                  ]);
              }
              
              
//               Umeng::getinstance()
//               ->set_content($m->get_message_string())
//               ->set_uid($uid)
//               ->send_one();
              
          }
          
      }
      
      

     
   }
   
}

