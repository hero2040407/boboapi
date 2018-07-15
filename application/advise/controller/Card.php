<?php

namespace app\advise\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\Advise;
/**
 * 通告列表
 * @author xieye
 *
 */
class Card
{
    public function bind($uid,$token, $serial )
    {
        
        $redis = Sys::get_container_redis();
        $key = "check_card:uid:".$uid.":date:".date("Ymd");
        $result = $redis->get($key);
        if ($result !== false && $result>10 ) {
           return ['code'=>0, 'message'=>'每天的绑定次数有限，您今日的次数已经使用完，不可以绑定'];
        }
        
        
        $db = Sys::get_container_db();
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0, 'message'=>'uid err'];
        }
        if (!$user->check_token( $token )) {
            return ['code'=>0, 'message'=>'id err'];
        }
        
        if (empty( $serial )) {
            return ['code'=>0, 'message'=>'serial err'];
        }
        
        $db = Sys::get_container_db();
        $sql="select * from bb_audition_card where serial =?";
        $card_row = $db->fetchRow($sql,[ $serial ]);
        if (!$card_row) {
            $redis->incr($key);
            $redis->setTimeout($key, 3* 24 * 3600);
        }
        if ($card_row->status<4 ) {
            return ['code'=>0, 'message'=>'该卡片不能绑定'];
        }
        if ($card_row->status >4 ) {
            return ['code'=>0, 'message'=>'不可重复操作'];
        }
        if ($card_row->status == 4 ) {
            $sql ="update bb_audition_card set status=5 where id=?";
            
            
            return ['code'=>1, 'message'=>'绑定成功'];
        }
        
    }
    
    
  
}





