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


class Data 
{
    public function insert()
    {
        if (Sys::is_product_server()) {
            exit();
        }
         
        $db = Sys::get_container_db();
        $table="bb_subject";
        $db->delete($table);
        
        
        $db->insert($table,[
           'id'=>1,
           'title' =>'栏目1',
           'subtitle' =>'栏目1副题',
           'is_show' =>1,  
        ]);
        $db->insert($table,[
            'id'=>2,
            'title' =>'栏目2',
            'subtitle' =>'栏目2副题',
            'is_show' =>1,
        ]);
        $db->insert($table,[
            'id'=>3,
            'title' =>'栏目3',
            'subtitle' =>'栏目3副题',
            'is_show' =>1,
        ]);
        
        
        
        $table="bb_subject_movie";
        $db->delete($table);
        
        $sql="select room_id from bb_record where audit=1 and is_remove=0 limit 15";
        $rooms = $db->fetchCol($sql);
        
        $count = count($rooms);
        if ($count>=5) {
            for ($i=0;$i<5;$i++) {
                $db->insert($table, [
                    'subject_id'=>1,
                    'sort' => $i,
                    'room_id' => $rooms[$i],
                    'is_recommend'=>1,
                ]);
            }
        }
        
        
        if ($count>=10) {
            for ($i=5;$i<10;$i++) {
                $db->insert($table, [
                    'subject_id'=>2,
                    'sort' => $i,
                    'room_id' => $rooms[$i],
                    'is_recommend'=>1,
                ]);
            }
        }
        
        if ($count>=15) {
            for ($i=10;$i<15;$i++) {
                $db->insert($table, [
                    'subject_id'=>3,
                    'sort' => $i,
                    'room_id' => $rooms[$i],
                    'is_recommend'=>1,
                ]);
            }
        }
        
        echo "all ok";
    }
    
}


