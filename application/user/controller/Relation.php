<?php
/**
 * Created by PhpStorm.
 * User: 谢烨
 */

namespace app\user\controller;

use BBExtend\user\Relation as Re;
class Relation 
{
   public function lahei($uid=0,$target_uid=0)
   {
       $help = new Re();
       $result = $help->lahei($uid, $target_uid);
       if ($result) {
           return ['code'=>1];
       }
       return ['code'=>0, 'message'=>'您已拉黑过对方'];
   }
   
   /**
    * 检查一个人是否拉黑另一个人
    * @param unknown $uid
    * @param unknown $target_uid
    */
   public function lahei_check($uid, $target_uid)
   {
       $help = new Re();
       $result = $help->has_lahei($uid, $target_uid);
       return ['code'=>1,'data'=> ['check_result'=>(bool)$result] ];
   }
   
   
   
   public function un_lahei($uid=0,$target_uid=0)
   {
       $help = new Re();
       $result = $help->un_lahei($uid, $target_uid);
       if ($result) {
           return ['code'=>1];
       }
       return ['code'=>0, 'message'=>'您从未拉黑过对方'];
   }
   
   public function jubao($uid=0,$target_uid=0,$small_type=1)
   {
       $help = new Re();
       $result = $help->jubao($uid, $target_uid,$small_type);
       if ($result) {
           return ['code'=>1];
       }
       return ['code'=>0, 'message'=>'您已举报过对方'];
   }
   
   
   public function lahei_list($uid=0, $startid=0, $length=8)
   {
        $length = intval($length);
        $length = ($length> 50)?50:$length;
        $uid = intval($uid);
        $startid = intval($startid);
        
        $db = \BBExtend\Sys::get_container_db();
        $sql ="select * from bb_lahei 
where type=1 and uid = {$uid}
order by target_uid
                limit {$startid},{$length}
                ";
        
        $list = $db->fetchAll($sql);
        $arr=[];
        foreach ($list as $v) {
            $arr[] = $v['target_uid'];
        }
        
        $is_bottom = (count($list) == $length)?0:1;
        //$data= \BBExtend\user\Common::get_userlist2($arr);
        $data=[];
        foreach ($arr as $uid) {
            $user = \app\user\model\UserModel::getinstance($uid);
            
            $ADDUser_DB = array();
            $ADDUser_DB['uid'] = (int)$uid;
            
            
            $user_detail = \BBExtend\model\User::find( $uid );
            
            $ADDUser_DB['role'] = $user_detail->role;
            $ADDUser_DB['frame'] = $user_detail->get_frame();
            $ADDUser_DB['badge'] = $user_detail->get_badge();
            
            
            
            //谢烨20160922，加vip返回字段
            $ADDUser_DB['vip'] = 0 ; // 纯粹为了兼容老版本啊。
            
            $ADDUser_DB['age'] = $user->get_userage(); // 1
            $ADDUser_DB['pic'] = $user->get_userpic();
            $ADDUser_DB['nickname'] = $user->get_nickname();
            $ADDUser_DB['address'] = $user->get_user_address();
            
           // $ADDUser_DB['signature'] = $UserDB['signature'];
            $ADDUser_DB['level'] = $user->get_user_level();  // 2
            $ADDUser_DB['sex'] = $user->get_usersex();       // 3
            $ADDUser_DB['specialty'] = $user->get_hobbys();  // 4
            
            $data[]=$ADDUser_DB;
        }
        
        
        return ['code'=>1, 'is_bottom'=>$is_bottom ,'data'=>$data];
   }
      
}
