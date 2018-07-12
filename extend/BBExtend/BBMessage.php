<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/18
 * Time: 14:48
 */

namespace BBExtend;


use think\Db;





class BBMessage
{
    public static function AddMsg($ContentDB,$content,$Color = '',$URL = '')
    {
        $Data = array();
        $Data['content'] =$content;
        if ($Color)
        {
            $Data['color'] = $Color;
        }
        if ($URL)
        {
            $Data['url'] = $URL;
        }
        array_push($ContentDB,$Data);
        return $ContentDB;
    }
    public static function AddMsgGoToView($ContentDB,$content,$Color = '',$View = '')
    {
        $Data = array();
        $Data['content'] =$content;
        if ($Color)
        {
            $Data['color'] = $Color;
        }
        if ($View)
        {
            $Data['view'] = $View;
        }
        array_push($ContentDB,$Data);
        return $ContentDB;
    }
    public static function SendMsg($type,$title,$ContentDB,$uid,$img = '')
    {
        //如果在线则发送内部消息传输
        $MsgDB = array();
        $MsgDB['type'] = $type;
        $MsgDB['title'] = $title;
        $MsgDB['info'] = json_encode($ContentDB,JSON_UNESCAPED_UNICODE );
        $MsgDB['uid'] = $uid;
        $MsgDB['img'] = $img;
        $MsgDB['time'] = time();
        $MsgDB['overdue_time'] = time() + 259200;//30天后失效
        Db::table('bb_msg')->insert($MsgDB);
        BBNodeJS::Send_new_msg_count($uid);
        //如果不在线则发送推送消息
        
    }
}