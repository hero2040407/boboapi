<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Date;
use BBExtend\fix\MessageType;
use think\Db;




class Phonecheck 
{
   
    
    
    
    public function index($phone){
//         $pass='11aab2';
//         echo $pass."的分数是". \BBExtend\common\Pwd::check_amdin($pass);
    //    \BBExtend\Session::index();
      
        $url = "https://bobo.yimwing.com/user/login/send_login_message/phone/".$phone;
        file_get_contents($url);
        
        echo "
<h1>请填写您收到的验证码：当前手机号{$phone}</h1>
<form action='/apptest/phonecheck/check'>
手机号：<input type=text name=phone value='{$phone}'>
验证码：<input type=text name=code>
<input type=submit value='提交'>
</form>
";
        
        
    }
    
    public function check($phone,$code)
    {
        $url="https://bobo.yimwing.com/user/login/check_phone_code/phone/".$phone."/code/".$code;
        $content = file_get_contents($url);
        
        return $content;
        
    }
   
   
   
}






