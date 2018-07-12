<?php
namespace BBExtend\user;

/**
 * 打赏类
 * 
 * User: 谢烨
 */

use BBExtend\Sys;
use think\Db;


/**
 * 
 * 
 * @author Administrator
 *
 */
class MovieDashang
{
    public $record_id;
    public $room_id;
    
    public function __construct($record_id, $audit)
    {
//         $this->audit = $audit;
//         $this->record_id = intval($record_id);
//         $this->record = Db::table('bb_record')->where('id', $this->record_id)->find();
//         if (!$this->record) {
//             throw  new \Exception('record not found');
//         }
        
//         $this->type = $this->record['type'];
//         $this->uid = $this->record['uid'];
        
        
    }
    
    public static function getinstance($room_id)
    {
        return new self($room_id);
    }
    
    public function get_dashang_all_count()
    {
        
    }

}