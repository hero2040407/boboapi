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

class StarmakerRankingFans
{
    
    
    public $list;
    
    /**
     * @param int $uid
     */
    public function  __construct() 
    {
        
        $db = Sys::get_container_db_eloquent();
        $sql="select uid,level  from bb_users_starmaker where is_show=1";
        $result = DbSelect::fetchAll($db, $sql);
        
        foreach ($result as $k => $v) {
            $help = \BBExtend\user\Focus::getinstance($v['uid']);
            
            
            $result[$k]['fans_count'] = intval( $help->get_fensi_count() );
        }
        
        
        $sort_arr=[];
        foreach ($result as $k =>$v) {
            $sort_arr[$k]= $v['fans_count']; 
        }
        array_multisort($sort_arr, SORT_DESC,  $result);
        $this->list = $result;
    }
    
    public static function getinstance(){
        return new self();
    }
    
    
    public function get_list($startid, $length)
    {
        $index=-1;
        $new=[];
        $c_length=0;
        foreach ($this->list as $v) {
            $index++;
            if ($index >= $startid  && count( $new ) < $length  ) {
                $new[] = $v;
            }
        }
        return $new;
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
        return 200;
        
    }

}