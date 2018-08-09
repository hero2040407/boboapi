<?php

/**
 * 本类是给管理员，手动设置面试通过流程。 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;

use BBExtend\Sys;
use BBExtend\Secure;


class Token
{
    
   public function index()
   {
       $redis = Sys::getredis2();
       $key = Secure::key_ip_all;
       echo "
<h2><a href='/systemmanage/token/ip_list'>看所有ip</a></h2>
<h2><a href='/systemmanage/token/token_list'>看所有token</a></h2>


";
       
       
              
   }
       
   public function ip_list(){
       $redis = Sys::getredis2();
       $key = Secure::key_ip_all;
       $list = $redis->lrange($key ,0,-1);
       echo "<h2>全部ip列表</h2>";
        echo "<ol>";
       foreach ($list as $ip) {
           echo "

<li><a href='/systemmanage/token/ip_detail/ip/{$ip}'>{$ip}</a></li>
                   
                   
";
           
           
       }
       echo "</ol>";
   }
   
   public function ip_detail($ip){
       $redis = Sys::getredis2();
       $key_count = Secure::key_prefix_ip_request_count . $ip;
       $count = $redis->get( $key_count );
       
       $key = Secure::key_prefix_ip_token_set . $ip;
       
       $list = $redis->lrange($key ,0,-1);
       echo "<h2>单个ip详情：ip{$ip}</h2>";
       echo "<h2>1分钟内请求次数：{$count}</h2>";
       echo "<h2>下面是该ip拥有的token列表：</h2>";
       echo "<ol>";
       foreach ($list as $token) {
           
           $val = $redis->get( Secure::key_prefix_token.$token );
           
           //过期
//            $is_exists=1;
//            $result = $redis->get( Sec );
           
           
           echo "
           
<li><a href='/systemmanage/token/token_detail/token/{$token}'>{$token}</a> ,uid :{$val}  </li>


";
           
           
       }
       echo "</ol>";
       
       
   }
   
   
    
}



