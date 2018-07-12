<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\aliyun\Common;

class Live 
{
     // 谢烨，直播流测试。
     /**
      * 
      * 谢烨，你先查出不符合条件的。然后查播放时间。
      * 如果大于两分钟的，则修改之。
      * 
      * 
      */
    public function index()
    {
//         Sys::display_all_error();
//       $result = Common::describeLiveStreamsOnlineList();
       
//         $db = Sys::get_container_db();
        
//         $limit_time = time() - 2* 60; // 只查小于此时间的。
//         $sql ="update bb_push set event='publish_done'  where create_time < {$limit_time}
// and event= 'publish' ";
//         if ($result) {
//             $sql .= " and stream_name not in (?) ";
//             $sql = $db->quoteInto($sql, $result);
//         }
//         $db->query($sql);
//         echo "ok";
    }
    
    public function test()
    {
        echo Common::accessKeyId;
    }
    
   
}
