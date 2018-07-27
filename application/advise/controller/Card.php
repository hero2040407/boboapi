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
    public function bind($uid,$token, $serial, $advise_id, $role_id )
    {
        
        $uid= intval($uid);
        $advise_id= intval($advise_id);
        $role_id= intval($role_id);
        
        
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
        
        if ( \BBExtend\model\UserCheck::is_phone_renzheng($uid)===false ) {
            return ['code'=>0, 'message'=>'您需要先绑定手机号才可以使用此功能。'];
        }
        
        
        if (empty( $serial )) {
            return ['code'=>0, 'message'=>'serial err'];
        }
        
        // 重要的一句话。
        $serial = strtoupper($serial);
        
        $advise = \BBExtend\model\Advise::find( $advise_id );
        if ( !$advise ) {
            return ['code'=>0, 'message'=>'advise_id err'];
        }
        
        if ( $advise->has_join($uid) ) {
            return ['code'=>0, 'message'=>'您已参加此通告，不可以重复参加。'];
        }
        
        if ( $advise->is_active==0  ) {
            return ['message'=>'通告未激活','code'=>0];
        }
        if ( $advise->end_time < time()  ) {
            return ['message'=>'通告已过期','code'=>0];
        }
        
        if ( !$advise->check_max_join_count() ) {
            return ['message'=>'该通告参加人数已满，谢谢您的参与','code'=>0];
//             $message='该通告参加人数已满，谢谢您的参与';
        }
        
        // xieye ,现在查条件。
        if ( !$advise->can_join_by_auth( $uid ) ) {
            return ['message'=>$advise->get_msg() ,'code'=>0];
        }
        
        
        $db = Sys::get_container_db();
        $sql="select * from bb_audition_card where serial =? and online_type=2";
        $card_row = $db->fetchRow($sql,[ $serial ]);
        if (!$card_row) {
            $redis->incr($key);
            $redis->setTimeout($key, 3* 24 * 3600);
            
            return ['code'=>0, 'message'=>'卡号错误'];
            
        }
        $card_id = $card_row['id'];
        
        if ($card_row['status']<3 ) {
            return ['code'=>0, 'message'=>'该卡片不能绑定'];
        }
        if ($card_row['status'] >4 ) {
            return ['code'=>0, 'message'=>'不可重复操作'];
        }
        if ($card_row['status'] == 4 || $card_row['status'] == 3) {
            
            if (!$advise->check_relation_of_card( $card_row['id'] ) ) {
                return ['code'=>0, 'message'=>'您输入的试镜卡编号和当前您选择的通告不对应，请选择正确的通告绑定。'];
            }
            
            \BBExtend\model\Advise::public_advise_join($advise_id, $role_id, $uid, $card_id);
            
            return ['code'=>1, 'message'=>'绑定成功'];
        }
        
    }
    
    
  
}





