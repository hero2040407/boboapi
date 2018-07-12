<?php
namespace app\message\controller;
use BBExtend\BBMessage;
use think\Db;
use BBExtend\PushMsg;
use BBExtend\Sys;



/**
 * Created by PhpStorm.
 * User: CY
 * 
 * 谢烨201706 设计思路
 * 有些消息半小时发送一次，我的思路是，
 * 不建另外的表了，当需要发送消息时，存在reids里面。
 * 做系统定时任务，
 * 每隔半小时，把这些信息收集起来，放到队列里，
 * 要点是，一旦放到队列，必须删除redis里面的信息。
 * 
 * redis类型，哈希表。存放到11库中。
 * 
 * 
 * 然后，队列和普通一样发送！
 * 
 * 
 * Date: 2016/7/13
 * Time: 18:27
 */
class Index
{
    /**
     * 用户的回复
     */
   public function add ()
   {
       $uid = intval(input('param.uid'));
       $msg_id = intval(input('param.msg_id'));
       $info = trim(input('param.info'));
       $db = Sys::get_container_db();
       $sql="select * from bb_users where uid = {$uid}";
       $result = $db->fetchRow($sql);
       if (!$result) {
           return ["code"=>0,'message'=>'uid error'];
       }
       $sql="select * from bb_msg where uid = {$uid} and id={$msg_id} and type=1000 ";
       $result = $db->fetchRow($sql);
       if (!$result) {
           return ["code"=>0,'message'=>'msg_id not exists'];
       }
       if (!$info) {
           return ["code"=>0,'message'=>'content is null'];
       }
       $db->insert("bb_msg_answer", [
           'uid' =>$uid,
           'msg_id' => $msg_id,
           'datestr' => date("Ymd"),
           'create_time' =>time(),
           'info' => $info,
           
       ]);
       
       return ["code"=>1, ];
   }
   
   /**
    * 得到某人的全部推送配置
    * @param unknown $uid
    */
   public function get_config($uid) {
       if (\app\user\model\Exists::userhExists($uid) !=1 ) {
           return ["code"=>0,'message'=>'user not exists'];
       }
       
       $tips_arr=[
          119=>'关闭点赞提醒，你无法收到其他人的点赞通知',
           122=>'关闭互粉提醒，你将无法收到被关注的通知',
           123=>'关闭好友新视频动态将无法收到好友上传新视频的通知',
           124=>'关闭好友直播动态将无法收到好友直播的通知',
           1100=> '关闭微信通知将无法收到微信的消息推送',
           152=>'关闭好友成就动态将无法收到好友成就的通知',
       ];
       
       $obj = \BBExtend\message\MessageConfig::get_instance($uid);
       $result = $obj->get_all_config();
       $result2=[];
       $bigtype_arr=[];
       foreach ($result as $v) {
           $temp=[];
           $temp['bigtype']=$v["bigtype"];
           $temp['type']=$v["type"];
           $temp['title']=$v["title"];
           $temp['value']=$v["value"];
           if ($v["bigtype"]==0) {
             $temp['tips']  =   $tips_arr[$v["type"]];// $v["value"];
             $temp['child']  =[];
           }else {
              // $temp['tips'] = '';
           }
           $result2[]= $temp;
           $bigtype_arr[]= $v["bigtype"];
       }
       $bigtype_arr = array_unique($bigtype_arr);
       
       $result=[];
       
       foreach ($result2 as $v) {
           if ($v['bigtype'] == 0) {
               $result []= $v;
           }
       }
       foreach ($result as $k=> $v) {
           
           foreach ($result2 as $v2) {
               if ($v2['bigtype']  == $v['type']) {
                   $result[$k]["child"] []= $v2;
               }
               
           }
       }
       
       
       //}
       
       return ["code"=>1,'data' => $result];
       
       
   }
   
   /**
    * 设置推送配置。
    * @param unknown $uid
    * @param unknown $type
    * @param unknown $value
    */
   public function set_config($uid,$bigtype,$type,$value,$parent_close=0) {
       if (\app\user\model\Exists::userhExists($uid) !=1 ) {
           return ["code"=>0,'message'=>'user not exists'];
       }

       $uid=intval($uid);
       $bigtype=intval($bigtype);
       $type=intval($type);
       $value=intval($value);
       $parent_close=intval($parent_close);
       
       $obj = \BBExtend\message\MessageConfig::get_instance($uid);
       $result = $obj->set_one_config($bigtype, $type, $value);
       
       if ($parent_close) {
           $result = $obj->set_one_config(0, $bigtype, 0);
       }
       
       if ($bigtype==0 && $value==1) {
           //检查子类全是0，如果全是，则改为全1.
           $db = Sys::get_container_db();
           $sql="select count(*) from bb_msg_user_config where value=1 and uid={$uid} and bigtype={$type}";
           $count = $db->fetchOne($sql);
           if ($count==0) {
               $sql="update bb_msg_user_config set value=1 where uid={$uid} and bigtype={$type}";
               $db->query($sql);
           }
           
       }
       
       return $this->get_config($uid);
   }
    
}
