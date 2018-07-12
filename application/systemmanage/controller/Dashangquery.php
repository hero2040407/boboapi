<?php

/**
 * 
 *  
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Db;

class Dashangquery 
{
      
     /**
      * 临时性给后台管理员查看的
      */
     public function index($uid=0,$month=1)
     {
         $user = \BBExtend\model\User::find($uid);
         $uid = intval($uid);
         $month = intval($month);
         
         if (!$user) {
             return ['code'=>0, 'message' =>'用户不存在' ];
         }

         $db = Sys::get_container_dbreadonly();
         $time1 = time() - $month * 30 * 24 * 3600;
         $sql="select id,room_id,is_robot,bean,uid,create_time,ip,agent from bb_dashang_log
where target_uid = ?
and create_time > ?
order by id desc
";
         $result = $db->fetchAll($sql,[ $uid, $time1 ]);
         $sum=0;
         $sum2=0;
         $sum3_arr=[];
         
         foreach ($result as $k=> $v) {
             $uid2 = $v['uid'];
             $sum += $v['bean'];
             $sum2++;
             
             $sum3_arr[]= $uid2;
             
             $result[$k]['create_time'] = date('Y-m-d H:i', $result[$k]['create_time']);
             $result[$k]['is_robot'] = $result[$k]['is_robot']?'机器人打赏':'';
             
             $user2 = \BBExtend\model\User::find($uid2);
             if ($user2) {
               $result[$k]['nickname'] = $user2->get_nickname();
               $db22 = Sys::get_container_db_eloquent();
               $sql="select  platform_id,original from bb_users_platform
where type=3 and uid =?";
               $phone_row = DbSelect::fetchRow($db22, $sql,[ $uid2 ]);
               
               $result[$k]['phone'] = $phone_row['platform_id']." ( ". $phone_row['original'] .' ) ';
               
               
             }else {
                 $result[$k]['nickname'] = '';
                 $result[$k]['phone'] = '';
             }
         }
         
         
         $title_arr = ['id','视频room_id' ,'是否机器人打赏' ,'收到波豆数','打赏人uid','打赏时间','ip','手机型号','打赏人昵称','注册手机'];
         $table = new \BBExtend\common\HtmlTable($title_arr, $result);
         $name = $user->get_nickname();
         $sum3_arr = array_unique($sum3_arr);
         $sum3 = count($sum3_arr);
         
         $html = "<center><h1>用户 {$name} 的最近{$month}个月被打赏情况，总计波豆:{$sum}，总次数：{$sum2}，打赏bobo号个数（一个bobo号多次只算1个）：{$sum3}</h1></center>";
         echo $html;
         echo $table->to_html();
         //foreach 
        
     }
    
   
}