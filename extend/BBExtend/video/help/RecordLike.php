<?php
/**
 * 视频点赞类
 * 
 * User: 谢烨
 */
namespace BBExtend\video\help;

use BBExtend\model\Record;
use BBExtend\model\User;

use BBExtend\video\help\Like;

class RecordLike extends Like 
{
    
    
    public function __construct( $room_id, $uid)
    {
        parent::__construct( $room_id, $uid);
        
        $this->table = 'bb_record_like';
        $this->video_table='bb_record';
        
        $this->video = Record::where( "room_id",$room_id )->first() ; 
        if (!$this->video) {
            $this->err = '回播视频不存在';
        }
      
    }
    
   
   
}