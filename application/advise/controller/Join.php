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
    public function index($advise_id=0,$role_id=0         )
    {
        $advise_id=intval( $advise_id );
        $role_id = intval( $role_id );
        
        $advise  = Advise::find($advise_id);
        
        
        $info_arr=[
             '童星信息采集审核',
               '根据童星形象定角色，发放脚本',
                '试镜基地现场发放试镜卡，观摩试镜实况',
                '场景服装搭配，按角色定服装',
                '3-4人一组定妆',
                '专业顶级试镜体验（拍照、视频、场景展现）',
                '试镜全程花絮录制',
                '拍摄立档，视频录制',
                
        ];
        $record = \BBExtend\model\Record::find(51115);
        $record_arr=[
                'video_path' =>$record->video_path,
                'pic' =>$record->big_pic,
        ];
        return [
                'code'=>1,
                'data'=>[
                        'info_arr'=>$info_arr,
                        'record' => $record_arr,
                        'money_fen' => $advise->money_fen,
                        'advise_id' =>$advise_id,
                        'role_id'   =>$role_id,
                ],
                
                
        ];
        
    }
    
    public function progress($uid, $advise_id)
    {
        $db = Sys::get_container_db();
        $advise= \BBExtend\model\Advise::find($advise_id);
        if (!$advise) {
            return ['code'=>0,'message'=>'id err'];
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
                'date'  => "请联系客服" ,
                'address'  => "请联系客服" ,
                
        ];
        $info['progress']=[
            [
                'title' => '已申请试镜',
                'time'  => $row['create_time'],
            ]    
                
        ];
        $sql="select serial from bb_audition_card where id=?";
        $card_row = $db->fetchRow($sql,[ $row['audition_cart_id'] ]);
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
        $tips=<<<html
请提前做好准备，使自己能在试镜时展现自己最好的一面！请提前做好准备，使自己能在试镜时展现自己最好的一面！
html;
        $info['tips']=$tips;
        $info['agent'] = $advise->get_agent_info();
        return $info;
        
    }
    
    
  
}





