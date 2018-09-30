<?php
namespace BBExtend\message;

use BBExtend\Sys;
use BBExtend\fix\MessageType;


/**
 * 关于对个人推送的限定
 * 
 * 首先，短视频上传，有很多小类。有一个大类。
 * 所有短视频发送通知，是在后台审核过某短视频后，
 * \Resque::enqueue('jobswork', '\app\command\controller\Workjob', $args);
 * 后台，Workjob任务；
 * 接受一个有粉丝的通知，查出700条记录，每个记录调用
 *    Message::get_instance()->set_type(123)->send();
 *    123类型是半小时合并发送，MergePushMessage
 *    在线直接发送，不在线看情况添加到某表。
 *        查出短视频label，然后对比某个人的接受情况。接受则填写到bb_msg_cache表
 * 
 * 系统定时任务，每半小时查bb_msg_cache表。
 * 程序：/application/systemmanage/controller/Live.php - function push_message()
 * 然后发送，包括123短视频上传通知。
 * 
 * 
 * 
 * 1000 后台发送。
 * 1001  报名缴费
 * 1010 测试用
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
 * 分享 131
 * 
 * 150 大赛自动注册成新用户，送波币。
 * 151 大赛报名支付成功，再送波币。
 * 
 * 152 好友成就。
 * 154 本人成就
 * 153 视频标记热门。
 * 
 * 160 主播推出直播房间。
 * 
 * 170 幸运转盘抽奖信息
 * 171 邀请他人注册成功
 * 172 被邀请人注册成功。
 * 
 * 
 * 173 邀请星推官点评。
 * 174 导师点评，发给被点评人信息。
 * 175 被邀请人注册成功后认证成功。
 * 176，发给星推官审核失败的消息
 * 177 删除审核过的短视频后，给星推官发送消息
 * 
 * 178 用于活动游戏奖励消息
 *     2018年2月   用于天降红包奖励信息
 *     2018年10月  用于国庆马拉松游戏奖励信息
 * 180 比赛信息
 * 
 * 190 通告报名成功
 * 
 * @author Administrator
 *
 */
class Message
{
   public $title;
   public $content; // 是数组，内含对象
   public $type;
   public $uid;
   
   
   public $col1=0;
   public $col2=0;
   public $sort=0;
   
   
   public $img=''; // 2017 05 xieye
   public $time=0; // 201706 假关注。
   
   public $other_uid=0; // 201706 合并推送消息。
   public $other_record_id=0;// 201706 ,短视频id，
   
   public $bb_msg_id=0;
   
   public $newtype=1; // 201806,4大分类。
   public $pic_uid=0; // 201806 头像uid
   
   public function __construct()
   {
       $this->content=[];
   }
   
   function set_title($title){
       $this->title = $title;    
       return $this;
   }
   
   function set_img($img){
       $this->img = $img;
       return $this;
   }
   
   function set_time($time){
       $this->time = $time;
       return $this;
   }
   function set_other_uid($other_uid){
       $this->other_uid = $other_uid;
       return $this;
   }
   function set_other_record_id($other_record_id){
       $this->other_record_id = $other_record_id;
       return $this;
   }
   
   function set_col1($id){
       $this->col1 = $id;
       return $this;
   }

   function set_col2($id){
       $this->col2 = $id;
       return $this;
   }

   function set_sort($id){
       $this->sort = $id;
       return $this;
   }
    
   
   
   function get_message_array()
   {
       $temp = [];
       
       foreach ($this->content as $obj) {
           $temp[]= $obj->get();
       }
       return $temp;
   }
   
   function get_message_string()
   {
       $temp = '';
        
       foreach ($this->content as $obj) {
           $temp.= $obj->content;
       }
       return $temp;
   }
   
     function add_content($obj){
         $this->content []= $obj;
         return $this;
     }
    
     /**
      * 谢烨，这里是真正的调用
      * 委托给MessageMethod子类调用。
      */
     function send()
     {
         $send_method = self::get_message_method($this->type);
         
         //2017 04 任务已经完全取消。
         if ($this->type==118) {
             return;
         }
         
         $user = \app\user\model\UserModel::getinstance($this->uid);
         if (in_array($user->get_permission(), [1,2,3,4,5,6])) {
         
             $send_method->send($this);
         }
     }
     
     function save_message()
     {
        
       $db = Sys::get_container_db();
       $db->insert("bb_msg", [
           'uid' => $this->uid,
           'type' => $this->type,
           'title' => $this->title,
           'info' => json_encode($this->get_message_array() ,JSON_UNESCAPED_UNICODE ),
           'img' => '',
           'time' => time(),
           'is_read' => 0,
           'overdue_time' => time() + 30 * 24 * 3600 ,
       ]); 
       
     }
   
   function set_type($type){
       $this->type = intval( $type);
       
       if ($type==MessageType::shipin_beizan) {
           $this->newtype=2;
       }
       if ($type==MessageType::beiguanzhu ) {
           $this->newtype=3;
       }
       
       
       
       return $this;
   }
   
   public function set_newtype($type){
       $this->newtype = intval( $type);
       
       return $this;
   }
   public function set_pic_uid($uid){
       $this->pic_uid = intval( $uid);
       
       return $this;
   }
   
   // 谢烨，201806，新的title
   public function get_my_title(){
//        $db = Sys::get_container_dbreadonly();
       // xieye,假设type==119
       $arr = $this->get_message_array();
      // Sys::debugxieye("message:uid:".$this->uid);
       $type = $this->type;
       if ($type==MessageType::shipin_beizan || $type==MessageType::beiguanzhu) {
       //    Sys::debugxieye("message:isguanzhu:".$type);
           // 查发起人。
           foreach ( $arr as $v ) {
               
//                Sys::debugxieye($v);
               
               if (isset( $v['url'])   ) {
                   
                   $temp = json_decode($v['url'],1 );
                   if (isset ( $temp['other_uid'] )) {
                   
         //              Sys::debugxieye("message".$temp['other_uid']);
                       $user = \BBExtend\model\User::find($temp['other_uid']);
                       if ($user) {
                           return $user->get_nickname();
                       }
                   }
               }
           }
           
           //return $title;
       }
       
       return '';
   }
   
   
   function set_uid($uid){
       $this->uid = intval( $uid);
       return $this;
   }
   
   //获得发消息实例
   /**
    * 
    * 
    */
   public static function get_instance()
   {
       return new self();
   }
   
   // 获得简单消息对象
   public static function simple()
   {
       return new Simple();
   }
   
   // 得到发消息的方法对象。
   /**
    * 
    * @param unknown $type
    * 
    * @return MessageMethod
    */
   public static function get_message_method($type)
   {
      // return new PushMessage();
       if ($type==1000) {
           return new HoutaiMessage();
       }
       
      
       
//        if (in_array($type, [119,])) {  //合并发送
//            return new MergeSystemMessage();
//        }
       if (in_array($type, [119,121,122,123,])) {  //合并发送
           return new MergePushMessage();
       }
       
       // 排行榜上榜，活动发奖，视频通过审核，完成任务，商城可兑换消息。好友成就，直播，需友盟。
       if (in_array($type, [113,114,115,116, 131, 118,124, 125,150,151,152,153,1010,171,172,
           173,174,175,176, 177,178
           
       ])) { 
           return new PushMessage();
       }
       if (in_array($type, [129,130,154,1001,180,190])) {
           return new PushMustMessage();
       }
       if (in_array($type, [160])) {
           return new ExitMessage();
       }
       return new SystemMessage();
   }
   
}

