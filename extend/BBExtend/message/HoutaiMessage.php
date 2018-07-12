<?php

namespace BBExtend\message;

use BBExtend\Sys;
use think\Db;
use BBExtend\BBUser;

/**
 *
 * @author Administrator
 *        
 */
class HoutaiMessage extends MessageMethod {
    function send(Message $m) {
        $uid = $m->uid;
        $db = Sys::get_container_db ();
        // 谢烨，201708 ，这里后台的消息无需插入表，谢峰已经加入到表里了。
        
        $temp = get_cfg_var ( 'guaishou.username' );
        if (in_array ( $temp, [ 
            '200',
            'xieye' 
        ] )) {
            return true;
        }
        // nodejs推送新消息未读
        $no_read = Db::table ( 'bb_msg' )->where ( [ 
            'uid' => $m->uid,
            'is_read' => 0 
        ] )->count ();
        $user = BBUser::get_user ( $uid );
        
        if ($user) {
            // 201708 
            if ($user['is_online']== 1) {
                $node_service = Sys::get_container_node();
                $url = \BBExtend\common\BBConfig::get_touchuan_url();
                $node_service->http_Request($url,
                        ['data'=>$m->get_message_string (),'uid'=>$uid,'type'=>1000]);
            }else {
               Umeng::getinstance ()->set_content ( 
                    $m->get_message_string () )->set_uid ( $uid )->send_one ();
            
            }
        }
    }
}

