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
class UmengPush
{
    function sendIOSBroadcast($title,$info) {
        try {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret(APP_MASTER_SECRET);
            $brocast->setPredefinedKeyValue("appkey", APP_KEY);
            $brocast->setPredefinedKeyValue("timestamp", strval(time()));

            $brocast->setPredefinedKeyValue("alert", "系统公告");
            $brocast->setPredefinedKeyValue("badge", 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields
            $brocast->setCustomizedField($title, $info);
            print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }
    function sendIOSCustomizedcast($uid,$token,$event,$title,$info = null) {
        try {
         //   var_dump($token);
            $customizedcast = new IOSCustomizedcast();
            $customizedcast->setAppMasterSecret(APP_MASTER_SECRET);
            $customizedcast->setPredefinedKeyValue("appkey",           APP_KEY);
            $customizedcast->setPredefinedKeyValue("timestamp",       strval(time()));
            $customizedcast->setPredefinedKeyValue("type", 'unicast');//单播
            //$customizedcast->setPredefinedKeyValue("type", 'listcast');//列播
            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("device_tokens",$token);
            $customizedcast->setPredefinedKeyValue("alias", "xx");
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "xx");
            $customizedcast->setPredefinedKeyValue("alert", $title);
            $customizedcast->setPredefinedKeyValue("badge", 0);
            $customizedcast->setPredefinedKeyValue("sound", "chime");
            if ($info)
            {
                $customizedcast->setCustomizedField("info",$info);
            }
            $customizedcast->setCustomizedField("uid",$uid);
            $customizedcast->setCustomizedField("event",$event);

            // Set 'production_mode' to 'true' if your app is under production mode
            $customizedcast->setPredefinedKeyValue("production_mode", "false");
            $customizedcast->send();
        } catch (Exception $e) {
            \BBExtend\Sys::debug($e->getMessage());
            
      //      print("Caught exception: " . $e->getMessage());
        }
    }

    function sendIOSUnicast($toens,$title,$info) {
        try {
            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret(APP_MASTER_SECRET);
            $unicast->setPredefinedKeyValue("appkey",           APP_KEY);
            $unicast->setPredefinedKeyValue("timestamp",         strval(time()));
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $toens);
            $unicast->setPredefinedKeyValue("alert", "IOS 单播测试");
            $unicast->setPredefinedKeyValue("badge", 0);
            $unicast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $unicast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields
            $unicast->setCustomizedField($title, $info);
            print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }
}