<?php

require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidCustomizedcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSCustomizedcast.php');

define('APP_KEY','576e5785e0f55ad8e50032b9');
define('APP_MASTER_SECRET','zwgkxiq3soesdl6aphmfffmejidttmfk');
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/22
 * Time: 16:46
 */
class UmengPushAndroid
{
    
    const appkey           = '57515249e0f55a25dc0019c5';
    const appMasterSecret     = 'mwdsdwsa7gqapynj2jouapxqdgqgj7by';
    
    public $token;
    
    function __construct($token)
    {
        $this->token = $token;
    }
    
    function sendAndroidUnicast() { 
        try {
            $unicast = new AndroidUnicast();
            $unicast->setAppMasterSecret(self::appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           self::appkey);
            $unicast->setPredefinedKeyValue("timestamp",       strval(time()));
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $this->token);
            $unicast->setPredefinedKeyValue("ticker",           "Android unicast ticker");
            $unicast->setPredefinedKeyValue("title",            "Android unicast title");
            $unicast->setPredefinedKeyValue("text",             "Android unicast text");
            $unicast->setPredefinedKeyValue("after_open",       "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $unicast->setPredefinedKeyValue("production_mode", "true");
            // Set extra fields
            $unicast->setExtraField("test", "helloworld");
            print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }
    
}