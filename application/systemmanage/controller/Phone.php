<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;

use BBExtend\Sys;

class Phone 
{ 
   public function query($uid)
   {
       
       if (time() > strtotime('2018-08-30 00:00:00') ) {
          exit;
       }
       $db = Sys::get_container_dbreadonly();
       $sql="select userlogin_token from bb_users where uid=?";
       $token = $db->fetchOne($sql,[ $uid ]);
       echo $uid." token : " . $token;
       
          
       $redis = Sys::getredis2();
        $key = 'limit:ip:uid:token:uid_str'.$uid;
        echo "<br>";
        echo "临时token： ". $redis->get($key);
           
   }
       
       
   
   
   
   
    
}