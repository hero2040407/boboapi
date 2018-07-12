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
class PushMustMessage extends MessageMethod 
{
    
   
    
   function send(Message $m)
   {
      $uid = $m->uid; 
      $db = Sys::get_container_db();
      $img = $m->img;
      if ($img) {
          $img = \BBExtend\common\Image::geturl($img);
      }
      $db->insert("bb_msg", [
          'uid' => $m->uid,
          'type' => $m->type,
              'pic_uid' =>$m->pic_uid,
              'title' => $m->get_my_title(),
          'info' => json_encode($m->get_message_array() ,JSON_UNESCAPED_UNICODE ),
          'img' => $img,
          'time' => time(),
          'is_read' => 0,
          'overdue_time' => time() + 30 * 24 * 3600 ,
          'sort' => $m->sort,
          'col1' => $m->col1,
          'col2' => $m->col2,
              'newtype' => $m->newtype,
      ]);
      $m->bb_msg_id = $db->lastInsertId();
      
      $temp = get_cfg_var('guaishou.username');
      if (in_array($temp, ['200', 'xieye',])){
          return true;
      }
      // nodejs推送新消息未读
      $no_read = Db::table('bb_msg')->where(['uid'=>$m->uid,'is_read'=>0])->count();
      $user =     BBUser::get_user($uid);
      
      if ($user ) {
   // $user['is_online'] =0;
          if ($user['is_online']) {
              
              try {
                  $url = \BBExtend\common\BBConfig::get_touchuan_url();
                  $node_service = Sys::get_container_node();
                  $node_service->http_Request($url,
                          ['data'=>$no_read,'uid'=>$uid,'type'=>1]);
              } catch (\Exception $e) {
              }
          }
      
          
          // 谢烨新增：重要！！
          
          Umeng::getinstance()
          ->set_content($m->get_message_string())
          ->set_uid($uid)
          ->set_message_type($m->type)
          ->send_one();
              
          
          
      }
      
      

     
   }
   
}

