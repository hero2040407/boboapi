<?php
namespace BBExtend\message;

use BBExtend\Sys;



/**
 * 1000 后台发送。
 * 
 * 第一次登陆  110 o
 * 参加活动      111 o
 * 等级升级        112 o
 * 财富，等级，粉丝数排行榜上榜，  113 o
 * 活动完成（实际是后台发奖）。      114 o 
 * 视频通过审核。    115    o
 * 视频没有通过审核。 116  o
 * 被禁言，                117 o
 * 完成任务，     118 o
 * 视频被赞，     119 哦
 * 
 * 视频被评论     120 哦 
 * 视频被打赏     121 哦
 * 被关注。                      122 哦
 * 好友上传视频                  123
 * 好友开启直播                  124
 * 商城兑换推送。              125 哦
 * 充值成功           126 哦
 * 购买视频           127 哦
 * vip续费           128 哦
 *  * 大赛视频通过审核 129
 * 大赛视频没有通过审核 130
 * 
 * 150 大赛自动注册成新用户，送波币。
 * 151 大赛报名支付成功，再送波币。
 * 152
 * 160 主播推出直播房间。
 * 
 * 170 幸运转盘抽奖信息
 * 
 * @author Administrator
 *
 */
class MessageConfig
{
   public $uid;
   
   // 这是给外部类调用的。检查用
   private $message_type_arr=[119,122,123,124, 152,1100 ];
   
   //这是本类使用的有效的消息类型。
   private $valid_type_arr=[];
   
   public function __construct($uid)
   {
       $this->uid = intval($uid);
       $temp = $this->message_type_arr;// 先把外部类消息类型写上。
       foreach (range(1,15) as $v ) {
           $temp[] = $v;
       }
       $this->valid_type_arr = $temp;
       
       if ( \app\user\model\Exists::userhExists($uid) !=1 ) {
           throw new \Exception("uid 不存在");
       }
       
   }
   
   public static function get_instance($uid)
   {
       return new self($uid);
   }
   
   /**
    * 受控制的消息类型种类，目前只有4个。5
    */
   public function get_config_type(){
       return $this->message_type_arr;
   }
   
   public function check_type($type){
       return in_array($type, $this->message_type_arr);
   }
   
   
   /**
    * 得到单个配置
    * 
    * 特别注意，如果是有小类的大类，优先判断其父节点，
    * 
    * @param unknown $type
    */
   public function get_one_config($bigtype,$type){
       $db = Sys::get_container_db();
       $uid = $this->uid;
       $bigtype = intval($bigtype);
       $type = intval($type);
       if (in_array($type, $this->valid_type_arr )) {
       
           
           $sql ="select * from bb_msg_user_config where uid={$uid} and type={$type}
             and bigtype={$bigtype}
           ";
           $result = $db->fetchRow($sql);
           if (!$result) {
               $this->init();
               $result = $db->fetchRow($sql);
           }
           
           if ($bigtype > 0) {
               // 查出父类，且父类禁止的情况下，直接返回0！
               $config = $this->get_one_big_config($bigtype);
               if ($config ==0) {
                   return 0;
               }
           }
           
           return $result['value'];
       }
       return false;
   }
   
   /**
    * 得到单个大类的配置
    * @param unknown $type
    */
   public function get_one_big_config($type){
       $db = Sys::get_container_db();
       $uid = $this->uid;
  
       $type = intval($type);
       if (in_array($type, $this->message_type_arr )) {
            
           $sql ="select * from bb_msg_user_config where uid={$uid} and type={$type}";
           $result = $db->fetchRow($sql);
           if (!$result) {
               $this->init();
               $result = $db->fetchRow($sql);
           }
           return $result['value'];
       }
       return false;
   }
   
   
   /**
    * 得到所有配置
    */
   public function get_all_config()
   {
       $db = Sys::get_container_db();
       $uid = $this->uid;
       $sql ="select * from bb_msg_user_config where uid={$uid} order by bigtype  asc,sort asc, type asc ";
       $result = $db->fetchAll($sql);
       if (!$result) {
           $this->init();
           $result = $db->fetchAll($sql);
       }
       return $result;
       
   }
   
   /**
    * 用户改单个配置
    * @param unknown $type
    * @param unknown $value
    */
   public function set_one_config($bigtype,$type,$value)
   {
       $value = $value?1:0;
      // if (in_array($needle, $haystack))
       $type=intval($type);
       $bigtype = intval($bigtype);
       
       if (in_array($type, $this->valid_type_arr ) ) {
           $uid = $this->uid;
           $db = Sys::get_container_db();
           $sql="update bb_msg_user_config set value={$value} where uid={$uid} and type={$type}
             and bigtype={$bigtype}
           ";
           $db->query($sql);
           return true;
       }
       return false;
   }
   
   
   
   private function init()
   {
       $db = Sys::get_container_db();
       $uid = $this->uid;
       $sql="delete from bb_msg_user_config where uid = {$uid}";
       $db->query($sql);
       
       $arr = [
           [
           "bigtype" =>0,
           "uid" =>$uid,
           "value"=>1,
           "type" => 119,
           'title' => "点赞提醒",
           'sort'  => 10,
           ],
           [
           "bigtype" =>0,
           "uid" =>$uid,
           "value"=>1,
           "type" => 122,
           'title' => "互粉提醒",
               'sort'  => 20,
           ],
           [
           "bigtype" =>0,
           "uid" =>$uid,
           "value"=>1,
           "type" => 123,
           'title' => "好友新视频动态",
               'sort'  => 40,
           ],
           [
           "bigtype" =>0,
           "uid" =>$uid,
           "value"=>1,
           "type" => 124,
           'title' => "好友直播动态",
               'sort'  => 30,
           ],
               
               [
                       "bigtype" =>0,
                       "uid" =>$uid,
                       "value"=>1,
                       "type" => 1100,
                       'title' => "微信消息推送",
                       'sort'  => 38,
               ],
               
           
           [
           "bigtype" =>0,
           "uid" =>$uid,
           "value"=>1,
           "type" => 152,
           'title' => "好友成就动态",
           'sort'  => 35,
           ],
           
       ];
       $sql="select * from bb_label";
       $result = $db->fetchAll($sql);
       foreach ($result as $v) {
           $arr []=[
               "bigtype" =>123,
               "uid" =>$uid,
               "value"=>1,
               "type" => $v["id"],
               'title' => $v['name'],
               
           ];
       }
       foreach ($arr as $v) {
           $db->insert('bb_msg_user_config', $v);
       }
   }
   
}

