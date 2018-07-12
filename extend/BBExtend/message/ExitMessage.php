<?php

namespace BBExtend\message;

use BBExtend\Sys;
use think\Db;
use BBExtend\BBUser;

/**
 *
 * 主播推出房间。
 * 
 * @author Administrator
 *        
 */
class ExitMessage extends MessageMethod {
    function send(Message $m) {
        $uid = $m->uid;
        // $db = Sys::get_container_db();
        // $db->insert("bb_msg", [
        // 'uid' => $m->uid,
        // 'type' => $m->type,
        // 'title' => $m->title,
        // 'info' => json_encode($m->get_message_array() ,JSON_UNESCAPED_UNICODE ),
        // 'img' => '',
        // 'time' => time(),
        // 'is_read' => 0,
        // 'overdue_time' => time() + 30 * 24 * 3600 ,
        // ]);
        
        $temp = get_cfg_var ( 'guaishou.username' );
        if (in_array ( $temp, [ 
            '200',
            'xieye' 
        ] )) {
            return true;
        }
        
        $redis = Sys::getredis11 ();
        $key = "exit:message:{$uid}";
        // $redis->setEx($key, 60*1, 1);
        $exists = $redis->get ( $key );
        // if ($exists==1) {
        // return;
        // }
        $redis->setEx ( $key, 60 * 1, 1 ); // 如果不存在，则需要加入到redis
                                      
        // nodejs推送新消息未读
        // $no_read = Db::table('bb_msg')->where(['uid'=>$m->uid,'is_read'=>0])->count();
        $user = BBUser::get_user ( $uid );
        
        if ($user) {
            // $user['is_online'] =0;
            
            try {
                
                $node_service = Sys::get_container_node ();
                $result = $node_service->http_Request ( 'http://127.0.0.1:19631/phone_api', [ 
                    'data' => json_encode ( [ 
                        "room_id" => $uid . "push",
                        'uid' => $uid 
                    ] ),
                    'code' => 1,
                    'type' => 50 
                ] );
            } catch ( \Exception $e ) {
            }
        }
    }
}

