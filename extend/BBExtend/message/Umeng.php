<?php
namespace BBExtend\message;


use BBExtend\Sys;
use think\Db;
use BBExtend\BBUser;

require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/android/AndroidBroadcast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/android/AndroidFilecast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/android/AndroidGroupcast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/android/AndroidUnicast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/android/AndroidCustomizedcast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/ios/IOSBroadcast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/ios/IOSFilecast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/ios/IOSGroupcast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/ios/IOSUnicast.php"));
require_once ( realpath( realpath( EXTEND_PATH)."/umeng/notification/ios/IOSCustomizedcast.php"));

/**
 * 
 * 
 * @author Administrator
 *
 */
class Umeng 
{
    
    const appkey_android           = '57515249e0f55a25dc0019c5';
    const appMasterSecret_android     = 'mwdsdwsa7gqapynj2jouapxqdgqgj7by';
    
    const appkey_ios           = '576e5785e0f55ad8e50032b9';
    const appMasterSecret_ios     = 'zwgkxiq3soesdl6aphmfffmejidttmfk';
    
    public $token;
    private $title;
    public $content;
    public $time;
    public $production_mode; // true表示产品模式，false表示测试模式。
    public $uid;
    
    
    public $message_type=0; //201707 
    public $record_label=0; // 201707
    public $message_obj=null;
    
    function __construct()
    {
//         $this->token = $token;
        $this->time = strval( time() ) ;
        $this->production_mode = "true"; // 要点：是字符串，且默认真，表示是产品模式，
                                         // false 
        $this->title ='怪兽BoBo'; // 谢烨特别注意，兼容苹果，苹果只有固定标题。
    }
    
    public static function  getinstance($message_obj=null)
    {
       // $this->message_obj=$message_obj;
        return new self();
    }
    
    /**
     * 要点，同时存token
     * @param unknown $uid
     */
    public function  set_uid($uid)
    {
        $this->uid = $uid = intval($uid);
        $db = Sys::get_container_db();
        $sql ="select token from bb_umeng_push_msg where uid={$uid}";
        $token = $db->fetchOne($sql);
        $this->token = strval($token);
        return $this;
        
    }
    
    /**
     * 设置message_type
     * @param unknown $type
     */
    public function  set_message_type($type)
    {
        $this->message_type =  intval($type);
        return $this;
    }
    
    
    /**
     * 设置record_label
     * @param unknown $
     */
    public function  set_record_label($record_label)
    {
        $this->record_label =  intval($record_label);
        return $this;
    }
    
//     public function set_title($title)
//     {
//         $this->title = $title;
//         return $this;
//     }
    
    public function set_content($content)
    {
        $this->content = $content;
        return $this;
    }
    
    public function set_production_mode($boo)
    {
        $this->production_mode = $boo;
        return $this;
    }
    
    public function send_one()
    {
        if ( $this->message_type==180 ){
            Sys::debugxieye("有一个友盟推送,uid:".$this->uid);
            $this->real_send_one();
            return;
        }
        
        if ($this->message_type>0) {
            // 这里，先做检查，如果是配置的特定type， 如果接受，才发送。
            // 不是配置type，都发送。
            //  是配置type，不接受，则不发送。
            $message_config = MessageConfig::get_instance($this->uid);
            
            // 这里，其实是几个特点的msg类型，119之类，目前5个。
            if ($message_config->check_type($this->message_type) ) {
                $value = $message_config->get_one_big_config($this->message_type);
                if ($value==1) {// xieye，目前119和人124使用中。
                    
                    $this->real_send_one();
                    return;
                }else {
                    // 只要表中不为1，则 不发送
                    return;
                }
                
            }else { // 不在配置列表，一律发送。
                $this->real_send_one();
                return;
            }
            
            
        }else { // 如忘了写type，一律发送。
            $this->real_send_one();
            return;
        }
        
        
    }
    
   public function real_send_one(){
       
       $db=Sys::get_container_db();
       $db->insert('bb_msg_push_log', [
           "uid" => $this->uid,
           "info" => $this->content,
           'create_time'=>time(),
           'datetimestr'=> date("Y-m-d H:i:s"),
           'type' =>$this->message_type, 
           
       ]);
       
       // 安卓，44位， 苹果 64位。
       if (strlen( $this->token ) ==  44 ) {
           //      Sys::debugxieye("start push..{$this->content} {$this->uid}");
           $this->sendAndroidUnicast();
       
       }
       if (strlen( $this->token ) ==  64 ) {
           $this->sendIOSUnicast();
       }
   }
    
    
    // 安卓发送单个消息。
    public function sendAndroidUnicast() {
        try {
            
            if (is_null($this->token) ||  is_null($this->title) ||  is_null($this->content) ) {
                throw new \Exception("cuowu");            
            }
            
            $unicast = new \AndroidUnicast();
            $unicast->setAppMasterSecret(self::appMasterSecret_android);
            $unicast->setPredefinedKeyValue("appkey",           self::appkey_android);
            $unicast->setPredefinedKeyValue("timestamp",      $this->time );
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $this->token);
            $unicast->setPredefinedKeyValue("ticker",           "您有新的短消息");
            $unicast->setPredefinedKeyValue("title",            $this->title);
            $unicast->setPredefinedKeyValue("text",             $this->content);
            $unicast->setPredefinedKeyValue("after_open",       "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $unicast->setPredefinedKeyValue("production_mode", $this->production_mode);
            // Set extra fields
  //          $unicast->setExtraField("test", "helloworld");
  //          print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
         //   print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
    //        print("Caught exception: " . $e->getMessage());
        }
    }
    
    
    
    public function sendIOSUnicast() {
        try {
      //      echo 2343;
            $customizedcast = new \IOSCustomizedcast();
            $customizedcast->setAppMasterSecret(self::appMasterSecret_ios);
            $customizedcast->setPredefinedKeyValue("appkey",    self::appkey_ios   );
            $customizedcast->setPredefinedKeyValue("timestamp", $this->time );
            $customizedcast->setPredefinedKeyValue("type", 'unicast');//单播
            //$customizedcast->setPredefinedKeyValue("type", 'listcast');//列播
            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("device_tokens",$this->token );
            $customizedcast->setPredefinedKeyValue("alias", "xx");
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "xx");
            $customizedcast->setPredefinedKeyValue("alert", $this->content);
            $customizedcast->setPredefinedKeyValue("badge", 0);
            $customizedcast->setPredefinedKeyValue("sound", "chime");
//              $customizedcast->setCustomizedField("uid",$this->uid);
//              $customizedcast->setCustomizedField("event",11);
// echo "wait";
            // Set 'production_mode' to 'true' if your app is under production mode
            $customizedcast->setPredefinedKeyValue("production_mode", $this->production_mode);
            $customizedcast->send();
        
        } catch (\Exception $e) {
        //   print("Caught exception: " . $e->getMessage());
       //     \BBExtend\Sys::debug($e->getMessage());
        }
    }
    
    
    
    
  
}

