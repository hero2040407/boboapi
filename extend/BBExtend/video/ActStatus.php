<?php
namespace BBExtend\video;



use BBExtend\Sys;

/**
 * 活动状态。
 * 
 * 
 * @author xieye
 *
 */
class ActStatus
{
    public $race=null;      
    
    
    /**
     * 返回 一句话，返回状态，返回按钮文字，返回颜色。
     * 
     * 
     * @param unknown $uid
     * @param unknown $act_id
     */
    public static function get_status($uid,$act_id)
    {
        // 先查主办方。
        // 查手机绑定。
        // 查大赛未开始前。
        //下面是查表。
        $time = time();
        $act = \BBExtend\model\Act::find( $act_id );
        if (!$act) {
            throw new \Exception("活动不存在");
        }
        
        
        $db = Sys::get_container_dbreadonly();
        
        // 现在是大赛报名中，或者之后的情况。
        $v1='活动报名中';
        if ($time >  intval( $act->end_time ) ) {
            $v1 ='活动结束';
        }
        
        
      
        $temp = '视频未上传';
        $v3 = $act->record_status($uid);
        if ($v3  == 2 ) {
            $temp = '视频上传审核中';
        }elseif ( $v3 == 3 ) {
            $temp = '视频上传审核成功';
        }elseif ( $v3 == 4 ) {
            $temp = '视频上传审核失败';
        }
        $v3=$temp;
        
      
        // 构建正则
        $pre = "\|{$v1}\|{$v3}";
        
        $list = self::status_list();
        $word='';
        foreach ($list as $v) {
            if ( preg_match('/'. $pre .'/', $v) ) {
                $arr = explode('|', $v);
                $new=[];
                foreach ( $arr as $vv ) {
                    $t = trim($vv);
                    if ($t) {
                        $new[]= $t;
                    }
                }
                
                $word= $new[ count($new)  -1 ] ;
                break;
            }
        }
        return [ 'describe' => trim($word),  ];
    }
    
    
    
    private static function status_list()
    {
       $s="|活动报名中|视频未上传|    |
|活动报名中|视频上传待审核| 上传视频审核中  |
|活动报名中|视频上传审核成功| 视频审核已通过，分享视频给好友  |
|活动报名中|视频上传审核失败| 视频审核未通过，请重新上传  |
|活动结束|视频未上传|   |
|活动结束|视频上传待审核| 上传视频审核中   |
|活动结束|视频上传审核成功| 视频审核已通过，分享视频给好友 |
|活动结束|视频上传审核失败|   |"; 
        
       return preg_split('/[\r\n]+/s', $s);
    }
    

}