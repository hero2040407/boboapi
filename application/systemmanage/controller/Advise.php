<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\aliyun\Common;
class Advise 
{
      
    const maxid = 146;
    
     /**
      * 改活动名词
      */
     public function index()
     {
       //  Sys::debugxieye('Actpaiming');
         
         // 首先，查出所有大赛。
         //目前最高id 146 ，
        // 要查大于146的。且处于有效期的。
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_task_activity where is_remove=0 and is_show=1 and has_paiming=0
  and id =138

";
        $result = DbSelect::fetchAll($db, $sql,[  ]);
        foreach ($result as $v) {
            echo "process {$v['id']}  start \n";
            $this->process( $v['id'] );
            echo "process {$v['id']}  ok \n";
        }
        echo "all ok\n";
     }
    
     
     private function process($act_id) 
     {
         $db = Sys::get_container_db_eloquent();
//          $sql="update bb_task_activity set has_paiming=1 where id=?";
         $db::table('bb_task_activity')->where( 'id', $act_id )->update( [ 'has_paiming' =>1 ] );
         
         $sql="select * from bb_record where 
type = 2 and activity_id = ? and audit=1 and 
exists(
  select 1 from bb_user_activity
   where bb_user_activity.activity_id = ?
     and bb_user_activity.uid  = bb_record.uid

) 
order by `like` desc
";
         $record_arr = DbSelect::fetchAll($db, $sql,[ $act_id, $act_id ]);
         
         // 谢烨，现在根据
         $paiming = 0;
         foreach ($record_arr as $v) {
             $paiming++;
             $db::table('bb_user_activity')->where( 'uid', $v['uid'] )
               ->where('activity_id',$act_id  )->update( [
                       'zan' => $v['like'],
                       'record_id' => $v['id'],
                       'paiming_new' => $paiming,
               ] )  ;
             
             
         }
         
     }
    
    
   
}











