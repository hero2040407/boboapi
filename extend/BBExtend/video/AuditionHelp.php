<?php
namespace BBExtend\video;




/**
 * 大赛报名帮助类
 * 
 * 面向对象。
 * 
 * $reg = new RaceNew();
 * $result = $reg->register(....);
 * 
 * 
 * @author xieye
 *
 */
class AuditionHelp
{
    public static function index()
    {
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
        
        
        return [
                'info_arr' =>$info_arr,
                'record' =>[
                        'video_path' =>'http://bobo-sql.oss-cn-beijing.aliyuncs.com/shijing/20180831.mp4',
                        'pic' =>'http://bobo-sql.oss-cn-beijing.aliyuncs.com/shijing/20180831.png',
                        
                ],
        ];
        
    }
    
    
    
    
    

}