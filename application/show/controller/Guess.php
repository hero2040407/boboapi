<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/6
 * Time: 10:17
 */

namespace app\show\controller;


use BBExtend\BBShow;
use think\Db;
use BBExtend\Sys;
use BBExtend\BBRecord;
class Guess
{
   // public $is_bottom;
    
    
   /**
    * 谢烨，2017 03 ，猜你喜欢，同类型视频挑4个。
    * @param number $movie_id
    * @param number $uid
    */
    public function index($movie_id=0,$uid=0,$count=4) 
    {
        $count = intval($count);
        if ($count>10) {
            $count=4;
        }
        
        $movie_id = intval($movie_id);
        $uid=intval($uid);
        $db = Sys::get_container_db();
        $sql ="select label from bb_record where id={$movie_id}";
        $label = $db->fetchOne($sql);
        $label = intval($label);
        $start_time = time()- 2* 30 * 24 * 3600; // 2个月内。
        if (!$label) {
             $sql = "select * from bb_record where heat>0 
                and bb_record.audit=1
                 and `time` > '{$start_time}'
                and bb_record.is_remove=0
                and bb_record.id != {$movie_id}
                limit 40
                ";
                $result  = $db->fetchAll($sql);
        }else {
           
            $sql = "select * from bb_record where heat>0 and label='{$label}'
              and `time` > '{$start_time}'
              and bb_record.audit=1
              and bb_record.is_remove=0
              and bb_record.id != {$movie_id}
              limit 40
            ";
            $result  = $db->fetchAll($sql);
            if (count($result) < 4 ) {
                $sql = "select * from bb_record where heat>0 
                and bb_record.audit=1
                 and `time` > '{$start_time}'
                and bb_record.is_remove=0
                and bb_record.id != {$movie_id}
                limit 40
                ";
                $result  = $db->fetchAll($sql);
                
            }
        }
        
        
        shuffle($result);
        $result = array_slice($result, 0, $count);   
        $temp=[];
        foreach ($result as $v) {
            $temp []= BBRecord::get_detail_by_row($v,$uid);
        }
        return ['code'=>1,"data"=>$temp];
    }
}