<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;



use BBExtend\message\Umeng;

class T1 {
    public $a=1;
    public function geta(){
        echo $this->a;
    }
}

class Temp extends T1  
{
   // const aa = \app\apptest\controller\abcd;
   
    public $a=232;
    
   public function index()
   {
       
       $a =floatval("0米");
       if ($a >0 ) {
           echo 1;
       }
       echo 2;
       
//        $arr=[
//            "2身高2"=>12.2,
//            "题主" => 2112,
//        ];
//        $key_arr = array_keys($arr);
//        foreach ( $key_arr as $key ) {
//            if ( preg_match('#身高#', $key) ) {
//                $value = $arr[$key];
//                if ($value > 0 && $value < 2) {
//                    $arr[$key] = intval( $value* 100);
//                }
//            }
//        }
//        dump($arr);
   }
   
   public function index55()  {
       echo 22;
   }
   
   
   
   
   // 推送测试
   public function tuisong($uid=10010) {
       $content ="你好，你好你好你好你好你！";
    //   $content="1233333333333333333333你好3333333333333333";
       
       Umeng::getinstance()
       ->set_content( $content )
       ->set_uid($uid)
       ->set_message_type(180)
       ->send_one();
       return ['code'=>1 ];
       
   }
   
   
   // 重定向测试
   public function test_redirect(){
       
     //  return ['code'=>2];
       $this->redirect('/apptest/temp/test2');
       echo 1;
   }
   
   public function test2(){
       return ['code'=>4];
   }
   
   public function test_redirect2(){
       
       //return ['code'=>2];
       $url = "http://192.168.99.100/apptest/temp/test_redirect";
       $content = file_get_contents($url);
//        dump($content);
        return json_decode( $content);
       
       //$this->redirect('/apptest/temp/test2');
   }
   
   
   
}






