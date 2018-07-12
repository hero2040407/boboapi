<?php

/**
 * 本类是给管理员，手动设置面试通过流程。 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;

use BBExtend\Sys;



class Vip 
{
    
   public function index($uid,$pass)
   {
       // 这是一个保险的设置
       $time = strtotime("2018-06-21 00:00:00");
       $time+= 30  *24*3600;
       if (time() > $time ) {
           return ['code'=>0];
       }
       
       
       
       if ($pass !='fMoIbHHsX0VZ5qG8UcdQ') {
           return ['code' =>0,'message' =>'pass error'  ];
       }
       
       $db = Sys::get_container_db();
       $user = \BBExtend\model\User::find( $uid );
       if (!$user) {
           return ['code' =>0,'message' =>'uid error'  ];
       }
       
       if ( $user->role==2 ||$user->role==3 ||$user->role==4   ) {
           return ['code' =>0,'message' =>'role error'  ];
       }
       
       $sql="select * from bb_vip_application_log where uid=? and status in ( 4,5,6 )";
       $row = $db->fetchRow($sql,[ $uid ]);
       if ($row) {
           return ['code' =>0,'message' =>'已有面试记录'  ];
       }
       
       $bind=[
               'uid' =>$uid,
               'create_time' => time(),
               'status' => 4,
       ];
       $db->insert("bb_vip_application_log", $bind);
       
       return ['code' =>0,'message' =>'添加面试记录成功'  ];
       
   }
       
       
   
   
   
   
    
}



