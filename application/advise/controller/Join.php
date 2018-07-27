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
class Join
{
    public function index($advise_id=0,$role_id=0 ,$uid,$token        )
    {
        $advise_id=intval( $advise_id );
        $role_id = intval( $role_id );
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if (!$user->check_token( $token )) {
            return ['code'=>0,'message'=>'id error'];
        }
        $advise  = Advise::find($advise_id);
        if (!$advise) {
            return ['code'=>0,'message'=>'advise_id error'];
        }
        
        
        // 谢烨，我同时查权限，已参加过，过期了，权限不足，卡片数量不够。
        // 谢烨，现在判断这个人付钱是否合适
        $message='';
        $err=0;
        if ($advise->has_join( $uid)) {
            $message='您已经参加此通告，不可重复报名';
            
        }
        
        if ( $advise->is_active==0  ) {
            $message='通告未激活';
        }
        if ( $advise->end_time < time()  ) {
            $message='通告已过期';
        }
        
        if (!$advise->can_join_by_auth( $uid )) {
            $message=$advise->get_msg();
        }
        
        
        if ( $advise->check_card_count() <3 ) {
            $message='卡片数量不足，暂时不能购买';
        }
        
        if ( !$advise->check_max_join_count() ) {
            $message='该通告参加人数已满，谢谢您的参与';
        }
        
        if ($message) {
            $err=1;
        }
       
//         if ( $advise->check_card_count() <3  ) {
//             return ['code'=>0,'message' =>'卡片数量不足，目前不可以参加' ];
//         }
        
        $info_arr= \BBExtend\video\AuditionHelp::index()['info_arr'] ;
       // $record = \BBExtend\model\Record::find(51115);
        $record_arr= \BBExtend\video\AuditionHelp::index()['record'] ;
        return [
                'code'=>1,
                'data'=>[
                        'info_arr'=>$info_arr,
                        'record' => $record_arr,
                        'money_fen' => $advise->money_fen,
                        
                        'money_yuan' => $advise->get_money_yuan(),
                        
                        'advise_id' =>$advise_id,
                        'role_id'   =>$role_id,
                        'is_sign'   => $user->is_sign(),
                        'err'  =>$err,
                        'err_message'=>$message, 
                ],
                
                
        ];
        
    }
    
    public function progress($uid, $advise_id)
    {
        $db = Sys::get_container_db();
        $advise= \BBExtend\model\Advise::find($advise_id);
        if (!$advise) {
            return ['code'=>0,'message'=>'advise_id err'];
        }
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'id err'];
        }
        
        $sql="select * from bb_advise_join where uid=? and advise_id=?";
        $row = $db->fetchRow($sql,[ $uid, $advise_id ]);
        if (!$row) {
            return ['code'=>0,'message'=>'您尚未参加此通告'];
        }
        $info=[];
        $info['advise'] = [
                'title' =>$advise->title,
                'date'  => $advise->audition_time ,
                'address'  => $advise->audition_address  ,
                
        ];
        $info['progress']=[
            [
                'title' => '已申请试镜',
                'time'  => $row['create_time'],
            ]    
                
        ];
        $sql="select serial from bb_audition_card where id=?";
        $card_row = $db->fetchRow($sql,[ $row['audition_card_id'] ]);
        $serial = $card_row['serial'];
        $info['serial']=$serial;
        
        $sql="select * from bb_baoming_order where uid=? and newtype=3 and ds_id=?";
        $order_row=$db->fetchRow($sql,[$uid, $advise_id]);
        if (!$order_row){
            $info['order']=null;
        }else {
            $info['order']=[
                    'order_no' => $order_row['serial'],
            'create_time' => $order_row['create_time'],
                    'uid'  => $order_row['uid'],
                    'money_fen' => $order_row['price_fen'],
            ];
        }
        $tips=$advise->audition_tips;
        
        $info['tips']=$tips;
        $info['agent'] = $advise->get_agent_info();
        return ['code'=>1,'data' =>$info ];
        
    }
    
    
  
}





