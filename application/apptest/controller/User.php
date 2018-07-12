<?php
namespace app\apptest\controller;

use BBExtend\BBRedis;
use  think\Db;
use BBExtend\Sys;
use BBExtend\BBUser;
use BBExtend\DbSelect;

class User
{
    private function can_cz($uid)
    {
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
    
    public function show_lahei($uid){
        $redis = \BBExtend\Sys::get_container_redis();
        $key = "user:lahei:{$uid}";
        $result = $redis->sMembers($key);
        dump($result);
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
    
    /**
     * 谢烨，充值接口
     * @param string $uid
     * @param string $mima
     */
    public function cz($uid='',$mima='') {
        $money = 2000;
        $time = strtotime("2016-10-10 00:00:00");
        $right_mima = "3iRnph";
        $time+= 7*24*3600;

        
        $uid = intval($uid);
        if (!$this->can_cz($uid)) {
            return "用户不存在.";
        }
        if (time() > $time ) {
            return "请提醒管理员修改";
        }
        if ($mima != $right_mima) {
            return "您输入的密码错误";
        }
        $db = \BBExtend\Sys::get_container_db();
        $sql="update bb_currency set gold=gold + {$money} where uid=? ";
        $db->query($sql,$uid);
        $db->insert("bb_currency_log", array(
            'type' => 1000,
            'count' => $money,
            'time' => date("Y-m-d"),
            'uid'  => $uid,
            'way'  => "测试人员充值",
        ));
        return "用户{$uid}充2000成功，当前时间".date("y-m-d H:i:s");
    }
    
    
    private function can_del($uid='')
    {
        $db = \BBExtend\Sys::get_container_db();
    
        $sql ="select nickname from bb_users where uid=?";
        $result = $db->fetchOne($sql,$uid);
        if ($result && $result=='x测试帐号2'){
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
   
   public function jisuan_pic()
   {
       $db = Sys::get_container_db_eloquent();
       $sql="select * from bb_users_card_template_material where pic_width=0 or pic_height=0";
       $result = DbSelect::fetchAll($db, $sql);
    //   dump($result);
       foreach ( $result as $v ) {
           $arr = getimagesize( $v['pic'] );
           $db::table('bb_users_card_template_material')->where('id',$v['id']  )->update([
                   'pic_width' =>$arr[0],
                   'pic_height' => $arr[1],
           ] )  ;
           echo $arr[0].'--'.$arr[1];
       }
   }
   
    
   
}
