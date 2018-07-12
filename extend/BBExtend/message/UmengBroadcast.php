<?php
namespace BBExtend\message;




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
class UmengBroadcast
{
    
    const appkey_android           = '57515249e0f55a25dc0019c5';
    const appMasterSecret_android     = 'mwdsdwsa7gqapynj2jouapxqdgqgj7by';
    
    const appkey_ios           = '576e5785e0f55ad8e50032b9';
    const appMasterSecret_ios     = 'zwgkxiq3soesdl6aphmfffmejidttmfk';
    
    private $title;
    public $content;
    public $time;
    public $production_mode; // true表示产品模式，false表示测试模式。
    
    function __construct()
    {
        $this->title ='怪兽BoBo'; // 谢烨特别注意，兼容苹果，苹果只有固定标题。
        $this->time = strval( time() ) ;
        $this->production_mode = "true"; // 要点：是字符串，且默认真，表示是产品模式，
                                         // false 
    }
    
    public static function  getinstance()
    {
        return new self();
    }
    
   
    
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
   
    /**
     * 全体推送
     */
   public function send_online()
   {
       $node_service = \BBExtend\Sys::get_container_node ();
       
       $url_for_all = \BBExtend\common\BBConfig::get_touchuan_url_for_sendall();
       
       $result = $node_service->http_Request ( $url_for_all, [
           'data' => $this->content,
           'type' => 1000
       ] );
   }
    
    public function send_all()
    {
        
//         $node_service->http_Request('http://127.0.0.1:19631/phone_api',
//                 ['data'=>$m->get_message_string (),'uid'=>$uid,'type'=>1000]);
        
        
        $this->sendAndroidBroadcast();
        $this->sendIOSBroadcast();
    }
    
    
   
    /**
     * 安卓广播
     */
    function sendAndroidBroadcast() {
        try {
            $brocast = new \AndroidBroadcast();
            $brocast->setAppMasterSecret(self::appMasterSecret_android);
            $brocast->setPredefinedKeyValue("appkey",        self::appkey_android );
            $brocast->setPredefinedKeyValue("timestamp",        $this->time );
            $brocast->setPredefinedKeyValue("ticker",           "您有新的短消息");
            $brocast->setPredefinedKeyValue("title",            $this->title);
            $brocast->setPredefinedKeyValue("text",            $this->content);
            $brocast->setPredefinedKeyValue("after_open",       "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", $this->production_mode);
            // [optional]Set extra fields
       //     $brocast->setExtraField("test", "helloworld");
       //     print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
       //     print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
       //     print("Caught exception: " . $e->getMessage());
        }
    }
    
    /**
     * 苹果广播
     */
    function sendIOSBroadcast() {
        try {
            
            $brocast = new \IOSBroadcast();
            $brocast->setAppMasterSecret(self::appMasterSecret_ios);
            $brocast->setPredefinedKeyValue("appkey",        self::appkey_ios);
            $brocast->setPredefinedKeyValue("timestamp",        $this->time);
    
            $brocast->setPredefinedKeyValue("alert", $this->content);
            $brocast->setPredefinedKeyValue("badge", 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", $this->production_mode);
            // Set customized fields
            $brocast->setCustomizedField("test", "helloworld");
      //      print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
      //      print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
            print("Caught exception: " . $e->getMessage());
        } 
    }
    

    
  
}

