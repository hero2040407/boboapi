<?php
/**
 * 视频点赞类
 * 
 * User: 谢烨
 */
namespace BBExtend\video\help;

use BBExtend\model\Push;
use BBExtend\model\User;

use BBExtend\video\help\Like;

class PushLike extends Like 
{
    
    
    public function __construct( $room_id, $uid)
    {
        parent::__construct( $room_id, $uid);
        
        $this->table = 'bb_push_like';
        $this->video_table='bb_push';
        
        $this->video = Push::where( "room_id",$room_id )->first() ; 
        if (!$this->video) {
            $this->err = '直播视频不存在';
        }
      
    }
    
   
   
}