<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/6
 * Time: 10:17
 */

namespace app\show\controller;


use BBExtend\BBShow;
use think\Db;
use BBExtend\Sys;
use app\user\controller\User;
use BBExtend\BBRedis;
use BBExtend\Focus;
use BBExtend\BBPush;

class Query 
{
    public function movies($self_uid, $room_id)
    {
       $db = Sys::get_container_db();
       $self_uid=intval($self_uid);
       
       $sql ="select * from bb_push where event='publish' and  room_id=?
               and exists (
          select 1 from bb_users
            where bb_users.uid = bb_push.uid
              and bb_users.not_zhibo=0
        )
               
               ";
      // $sql ="select * from bb_push where  room_id=?";
       $row = $db->fetchRow($sql, $room_id);
       if (!$row) {
           return ['code'=>0, 'message'=>'视频已下线'];
       }
       
       $room_user_id = preg_replace('/push/', '', $room_id);
       $room_user_id=intval($room_user_id);
       
       $result = BBPush::get_detail_by_row($row, $self_uid);
       $sql = "select gold_bean from bb_currency where uid = {$room_user_id}";
       $bean = intval($db->fetchOne($sql));
       $result['owner_bean_count'] = $bean;
       
       
       
       return ['code'=>1,'data'=> $result ];
       
//        return BBPush::get_detail_by_row($row, $self_uid);
       
    }
}