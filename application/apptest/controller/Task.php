<?php
namespace app\apptest\controller;

use BBExtend\BBRedis;
use BBExtend\Sys;
use BBExtend\BBUser;
use BBExtend\DbSelect;
/**
 * 该程序勿删，下面有注释。
 * 谢烨
 * @author Administrator
 *
 */
class Task
{
    private function can_cz($uid)
    {
        
        $cinfo = getImageSize($file['tmp_name']);
        if (!$cinfo) {exit;}
        switch ($cinfo[2]) {
            case IMAGETYPE_PNG:
                break;
            case IMAGETYPE_GIF:
                break;
            case IMAGETYPE_JPEG:
                break;
            default:
                exit;
        }
        
        
        if (!$this->can_del($uid)) {
            
            return false;
        }
        $db = \BBExtend\Sys::get_container_db();
        $sql =" select uid
                from bb_users_platform
                where platform_id in
                (
                md5('15062280508'),
                md5('15195988273'),
                md5('18679773750')
                )
                ";
        $result = $db->fetchCol($sql);
        if ( in_array($uid, $result) ){
            
            return true;
        }
        return false;
    }
    
    
    
    public function removetask($uid,$key) {
        if (PHP_OS == "Linux") {
            if ($key!='xieye') {
                return ;
            }
        }
        
      //  BBRedis::getInstance('bb_task')->hdel($uid.'user_task');
        $db = \BBExtend\Sys::get_container_db();
        $sql ="delete from bb_task_user where uid={$uid}";
      //  $db->query($sql);
      //  echo "删除用户{$uid}的任务完毕";
    }
    
   
    
    
    private function can_del($uid='')
    {
        $db = \BBExtend\Sys::get_container_db();
    
        $sql ="select nickname from bb_users where uid=?";
        $result = $db->fetchOne($sql,$uid);
        if ($result && $result=='测试帐号'){
            return true;
        }
    
        return false;
    }
    
    
    /**
     * 正式服和测试服删除测试帐号
     * @param string $uid
     */
   public function remove($uid=''){
       $can_del = $this->can_del($uid);
       if (!$can_del) {
           return "用户不存在";
       }
       \BBExtend\user\Remove::getinstance($uid)->del();
        
        echo "删除用户{$uid}完毕";
           return;
   }

   public function refresh_task($uid)
   {
       $uid = intval($uid);
       $user = BBUser::get_user($uid);
       if (!$user) {
           echo "用户不存在";
           return;
       }
       
       if (!Sys::is_product_server()) {
           $db = Sys::get_container_db();
           $sql ='update bb_task_user set refresh_time=0 where uid = '.$uid;
           $db->query($sql);
           echo "任务已刷新。";
           return;
       }
       echo "error";
   }
   
   public function random_robot()
   {
       $db = Sys::get_container_db();
       $db2 = Sys::getdb2();
       $sql = "select * from bb_users where permissions=99 order by uid asc";
       $query = $db->query($sql);
       $i=0;
       while ($row = $query->fetch()) {
           $i++;
           echo $i."----". $row['uid']."\n";
           $uid = $row["uid"];
           $sex = mt_rand(0,1);
           //$sql ="update bb_users set sex= '{$sex}' where uid = {$uid} ";
           //echo $sql."\n";
           // 1岁到12岁
           // 2016 到 2005
           $min_birthday = "2005-01-01 00:00:00";
           $min_birthday = strtotime($min_birthday);
           
           $max_birthday = "2017-01-01 00:00:00";
           $max_birthday = strtotime($max_birthday);
           $result = mt_rand($min_birthday, $max_birthday) ;
           $result = date("Y-m", $result);
          // $sql ="update bb_users set birthday= '{$result}' where uid = {$uid} ";
           $level = mt_rand(1,8);
           $sql ="update bb_users_exp set level= {$level} where uid = {$uid} ";
           
           echo $sql."\n";
          // $db2->query($sql);
       }
       
       echo "all ok\n";
       
   }
   
    
   /**
    * 今天是 2018 05 04
    * 要把点赞的redis昵称都改掉。
    * 别删。
    */
   public function task1(){
     //  exit;
       $db = Sys::get_container_db();
       $db2 = Sys::get_container_db_eloquent();
       
       $redis = Sys::get_container_redis();
       
       
       $sql="select * from bb_record where is_remove=0 order by id asc ";
       $query = $db->query($sql);
       while( $row = $query->fetch() ) {
           echo "== id:{$row['id']}  ==\n";
           $sql="select uid from bb_record_like where room_id=? and is_robot=0 order by time desc limit 40";
           $result = DbSelect::fetchCol($db2, $sql,[ $row['room_id'] ]);
           
           // 去重复
           $result = array_unique($result);
           
           $key ="record:like:room_id:display_id:". $row['room_id'] ;
           $redis->delete($key);
           
           foreach ( $result as $uid ) {
               $redis->rPush($key, $uid);
               echo "{$uid}||";
           }
           echo "\n\n";
           
//           echo $row['title']; 
       }
       
   }
   
   
   
}
