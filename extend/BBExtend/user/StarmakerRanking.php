<?php
namespace BBExtend\user;

/**
 * 导师排行类
 * 
 * @author 谢烨
 */

use BBExtend\Sys;
use BBExtend\message\Message;
use BBExtend\DbSelect;

class StarmakerRanking
{
    
    
    public $list;
    
    /**
     * @param int $uid
     */
    public function  __construct() 
    {
        
        $db = Sys::get_container_db_eloquent();
        $sql="select uid , income from bb_users_starmaker where is_show=1";
        $result = DbSelect::fetchAll($db, $sql);
        
        $sort_arr=[];
        foreach ($result as $k =>$v) {
            $sort_arr[$k]= $v['income']; 
        }
        array_multisort($sort_arr, SORT_DESC,  $result);
        $this->list = $result;
    }
    
    public static function getinstance(){
        return new self();
    }
    
    
   // 注意，从1开始！！
    public function get_rank($uid)
    {
        $k = 0;
        foreach ($this->list as $v) {
            $k++;
            if ($v['uid']==$uid) {
                return $k;
            }
        }
        return 999;
        
    }

}