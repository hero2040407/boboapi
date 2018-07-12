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
class Killpush 
{
      
     /**
      * 每晚10点禁止所有直播
      * 设置成每天晚上10：10执行一次。
      */
     public function index()
     {
         return;
         Sys::display_all_error();
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_push where event='publish'";
   //    $sql="select * from bb_push where uid=6700440";
        $list = DbSelect::fetchAll($db, $sql);
     //   echo 2;
        $help = new \BBExtend\aliyun\Common();
       // echo 3;
        foreach ($list as $row) {
            
            $domainName= preg_replace('#^[^/]+//([^/]+)/.+$#', '$1',  $row['pull_url']) ;
            $streamName=$row['stream_name'];
            $db::table('bb_aliyun_kill_log')->insert([
                'stream_name' => $streamName,
                'domain_name' =>$domainName,
                'create_time' =>date("Y-m-d H:i:s"),
                'uid' => $row['uid'],
                'is_huifu' => 0,
            ]);
            
            $result =  $help->kill($domainName,$streamName );
         //   dump($result);
        }
        
    }
    
    /**
     * 设置成每天早晨8点执行。
     */
    public function resume()
    {
        return;
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_aliyun_kill_log where is_huifu=0";
        $list = DbSelect::fetchAll($db, $sql);
    
        $help = new \BBExtend\aliyun\Common();
        foreach ($list as $row) {
    
            $domainName= $row['domain_name'];
            $streamName=$row['stream_name'];
            
            $result =  $help->resume($domainName,$streamName );
            $db::table('bb_aliyun_kill_log')->where('id', $row['id'])->update(['is_huifu'=>1]);
            
           // dump($result);
        }
    
    }
    
    
    
   
}