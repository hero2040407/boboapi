<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\message\Umeng;
class Temp 
{
    
   public function index()
   {
       $file =<<<ss
212
~~~ 
v=5
~~~
ss;
     echo $this->display_version($file);
       
      
   }
   
   
   public function tuisong($uid=10010) {
       $content ="你好，怪兽BOBO欢迎您！".date("Y-m-d H:i:s");
       
       Umeng::getinstance()
       ->set_content( $content )
       ->set_uid($uid)
       ->set_message_type(180)
       ->send_one();
       return ['code'=>1 ];
       
   }
   
   
    
   
}






