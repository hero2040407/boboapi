<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/9/24
 * Time: 14:24
 */

namespace BBExtend;


use think\Db;
use BBExtend\BBUser;
use BBExtend\Sys;


class BBNodeJS
{

    /**
     * 发送新的短消息
     * @param int  $uid 用户id
     * @return array
     */
    public static function Send_new_msg_count($uid)
    {
        $temp = get_cfg_var('guaishou.username');
        if (in_array($temp, ['200', 'xieye',])){
            return;
        }
        
        //xieye count
         self::SendMessage($uid,\BBExtend\fix\Message::MSG_NEW_MESSAGE, Db::table('bb_msg')
                ->where(['uid'=>$uid,'is_read'=>0])->count());
    }
    /**
     * 发送公告
     * @param int  $uid 用户id
     * @return array
     */
    public static function Send_Announcement($uid,$Message)
    {
        $temp = get_cfg_var('guaishou.username');
        if (in_array($temp, ['200', 'xieye',])){
            return;
        }
        return self::SendMessage($uid,\BBExtend\fix\Message::MES_NEW_ANNOUNCEMENT,$Message);
    }
    /**
     * 发送消息
     * @param int  $uid 评论id
     * @param int  $type 消息类型
     * @param object  $data 发送数据
     * @return array
     */
    public static function SendMessage($uid,$type,$data)
    {
        $temp = get_cfg_var('guaishou.username');
        if (in_array($temp, ['200', 'xieye',])){
            return;
        }
        try {
           $user =     BBUser::get_user($uid);
           if ($user &&  $user['is_online'])  {
               $node_service = Sys::get_container_node();
               $node_service->http_Request('http://127.0.0.1:19631/phone_api/on_message',
                       ['data'=>$data,'uid'=>$uid,'type'=>$type]);
           }else {
               
                \BBExtend\PushMsg::Push_message($uid, $type);
           }
        
        }catch (\Exception $e) {
           return []; 
        }
        
        
    }
}