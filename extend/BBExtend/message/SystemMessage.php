<?php
namespace BBExtend\message;

use BBExtend\Sys;
use think\Db;
use BBExtend\BBUser;
/**
 * 
 * 
 * @author Administrator
 *
 */
class SystemMessage extends MessageMethod
{
  
    
   function send(Message $m)
   {
      $uid = $m->uid; 
      $db = Sys::get_container_db();
      
      $img = $m->img;
      if ($img) {
          $img = \BBExtend\common\Image::geturl($img);
      }
      
      $time = $m->time;
      if (!$time) {
          $time = time();
      }
      
      $db->insert("bb_msg", [
          'uid' => $m->uid,
              'pic_uid' =>$m->pic_uid,
          'type' => $m->type,
              'title' => $m->get_my_title(),
          'info' => json_encode($m->get_message_array() ,JSON_UNESCAPED_UNICODE ),
          'img' => $img,
          'time' => $time,
          'is_read' => 0,
          'overdue_time' => time() + 30 * 24 * 3600 ,
              'newtype' => $m->newtype,
      ]);
      
      $temp = get_cfg_var('guaishou.username');
      if (in_array($temp, ['200', 'xieye',])){
          return true;
      }
      // nodejs推送新消息未读
      $no_read = Db::table('bb_msg')->where(['uid'=>$m->uid,'is_read'=>0])->count();
      $user =     BBUser::get_user($uid);
      if ($user &&  $user['is_online'])  {
          // type 1表示新消息
          try {
              $node_service = Sys::get_container_node();
              $url = \BBExtend\common\BBConfig::get_touchuan_url();
              $node_service->http_Request($url,
                      
                      ['data'=>$no_read,'uid'=>$uid,'type'=>1]);
//           $result = json_decode($result,true);
//           Sys::debugxieye($result['code']);
          
          } catch (\Exception $e) {
     //         Sys::debugxieye($e->getMessage());
          }
      }
       
     
   }
   
   
   
}

