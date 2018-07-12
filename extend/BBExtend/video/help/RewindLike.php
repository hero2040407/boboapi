<?php
/**
 * 视频点赞类
 * 
 * User: 谢烨
 */
namespace BBExtend\video\help;

use BBExtend\model\Rewind;
use BBExtend\model\User;

use BBExtend\video\help\Like;

class RewindLike extends Like 
{
    
    
    public function __construct( $room_id, $uid)
    {
        parent::__construct( $room_id, $uid);
        
        $this->table = 'bb_rewind_like';
        $this->video_table='bb_rewind';
        
        $this->video = Rewind::where( "room_id",$room_id )->first() ; 
        if (!$this->video) {
            $this->err = '短视频不存在';
        }
      
    }
    
   
   
}