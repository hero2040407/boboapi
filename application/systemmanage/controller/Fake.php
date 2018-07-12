<?php

/**
 * 制作假数据用的。
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;

class Fake { 
    
    // 加关注给机构。
    public function index2()
    {
        $db_zend = Sys::get_container_db();
        $dbe = Sys::get_container_db_eloquent();
        $sql='select uid from bb_users
where bb_users.permissions=4';
        $jigou_arr = $db_zend->fetchCol($sql);
       // $jigou_arr =[7049564];
        $i=0;
        foreach ($jigou_arr as $jigou_uid) {
            $rand = mt_rand(300,1500);
            $sql="select uid from bb_users
where bb_users.permissions=99
order by rand()
limit {$rand}";
            $robot_arr = $db_zend->fetchCol($sql);
            foreach ($robot_arr as $robot_uid) {
                $i++;
                $help = new \BBExtend\user\FocusEasy($robot_uid);
                $help->focus_guy($jigou_uid, time() - mt_rand(1, 3*30*24*3600 ) );
                echo $i." : ". $jigou_uid.' -- '. $robot_uid ."\n";
                ob_flush();
                flush();
            }
        }
        
    }
    
    // 加机构点击量，视频的。
    public function index() 
    {
        return;
        $db_zend = Sys::get_container_db();
        $dbe = Sys::get_container_db_eloquent();
        $sql="
                select * from  bb_record
where audit=1 and is_remove=0
and exists (
 select 1 from bb_users 
  where bb_users.permissions=4
    and bb_users.uid = bb_record.uid
)
                ";
        $query = $db_zend->query($sql);
        $i=0;
        while ($row=$query->fetch()) {
            $rand = mt_rand(5000, 15000);
            $dbe::update("update bb_record set look=look+{$rand} where id={$row['id']} "  );
            echo $i."\n";
            ob_flush();
            flush();
            $i++;
        }  
    }
    
    
}