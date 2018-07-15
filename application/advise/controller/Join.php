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
                ],
                
                
        ];
        
    }
    
}





