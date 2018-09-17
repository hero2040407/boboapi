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
       echo 1/0;
//        $s="aa31";
//        echo $s[0]."<br>";
//        echo $s[1]."<br>";
//        echo $s[2]."<br>";
//        $all=0;
//        for ($i=0; $i < strlen($s);$i++  ) {
//            $all += ord( $s[$i] );
//        }
//        echo $all;

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






