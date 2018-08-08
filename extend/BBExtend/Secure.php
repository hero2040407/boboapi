<?php

namespace BBExtend;

use think\Config;
use think\Request;

class Secure
{
    
    const code_ip_blacklist = -205;       // ip进入ip黑名单
    const code_token_need_login = -206;   // token错误，需登录
  //  const code_token_err = -207;          // token错误，需进入人机交互页面。
    const code_token_too_much = -208;          // token过于频繁，需进入人机交互页面。
    
    
    
    
    const  key_ip_blacklist = "limit:ip:week"; // ip黑名单key
    const key_prefix_ip_request_count='limit:ip:request_count:'; // ip请求次数
    const key_prefix_token_request_count='limit:ip:request_count:token'; // token请求次数
    const key_prefix_token='limit:ip:token:';    // token，值是 uid。
    
    const allow_request_count_per_minute=60; // 允许的无token 的ip每分钟访问次数。
    const allow_request_count_per_token_minute=130; //允许的 token 的每分钟访问次数。
    
    public $redis;
    public $ip;
    public $code;
    public $message;
    public $flush_token;
    
    // token生存时间，暂定半小时。
    private function get_token_live(){
        return 0.5 * 3600;
    }
    
    public function __construct($ip)
    {
        $this->redis = Sys::getredis2();
        $this->ip = $ip;
    }
    
    
    private function output($arr){
        $aa = "Content-Type: application/json; charset=utf-8";
        header($aa);
        echo json_encode($arr);
        exit;
    }
    
    private function add_ip_to_blacklist(){
        $redis = $this->redis;
        $redis->sadd( self::key_ip_blacklist, $this->ip );
    }
    
    
    public function is_secure_api($url)
    {
        if (preg_match('#^/user/info/get_public_addi_video#', $url )) {
            return true;
        }
        if (preg_match('#^/user/user/get_userallinfo#', $url )) {
            return true;
        }
        if (preg_match('#^/user/user/get_user_info#', $url )) {
            return true;
        }
        return false;
    }
    
    public function set_http_header_temptoken($temptoken)
    {
        header("Cache-Contro1:: {$temptoken}");
    }
    
    public function set_new_http_header_temptoken()
    {
        $ip = $this->ip;
        $random = mt_rand(100000,999999);
        $time =  time();
        $time = strval( $time );
        $random = strval( $random );
        
        $value = $ip . $time. $random;
        $temptoken = md5( $value );
        
        $redis = $this->redis;
        $key = self::key_prefix_token . $temptoken;
        $redis->setEx( $key,$this->get_token_live() , '1'  );
        
        $this->set_http_header_temptoken($temptoken);
    }
    
    
    //返回真，表示通过，返回假表示
    public function check()
    {
//         最前面是，ip白名单，接口白名单。
//         然后是ip黑名单校验。最好返回特定code,-205
        
//         人机校验页面
//         token没有。
//         首先，频次检查，不通过则 ip黑名单。
//         然后，关键接口错误，跳登录页面（token错误）
//         非关键接口，则直接返回临时token,谢烨注意，这里，总是返回新token。
        
//         token有并正确，但访问过于频繁。暂定1分钟超过130次，则跳转人机页面（访问过于频繁）
        
//         token有但错误，则跳转人机页面（token错误）
        $ip = $this->ip;
        if (IS_CLI === true) {
            return true;
        }
        
        if ( $ip == '127.0.0.0' || $ip == '0.0.0.0' ) {
            return true;
        }
        $request = Request::instance();
        $module_name = $request->module();
//         echo "当前模块名称是" . $request->module();
//         echo "当前控制器名称是" . $request->controller();
//         echo "当前操作名称是" . $request->action();
        // 根据模块忽略白名单。
        if ( in_array( $module_name,[ 'apptest','backstage','shop','thirdparty','sytemmanage',
                'command',
        ] ) ) {
            return true;
        }
        
        $key_ip_blacklist = self::key_ip_blacklist;
        
        $redis = $this->redis;
        // 如果查到哪个ip是在封禁ip列表内，禁止访问。
        $has_limit = $redis->sIsMember( $key_ip_blacklist, $ip );
        if ($has_limit === true  ) {
            
            $this->output(['code' =>self::code_ip_blacklist, 'message'=> $ip  ]);
        }
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////
        
        // 查token
        $token = '';
        if (isset( $_SERVER['HTTP_TOKEN'] )) {
            $token = SERVER['HTTP_TOKEN'];
        }
        $temptoken = '';
        if (isset( $_SERVER['HTTP_TEMPTOKEN'] )) {
            $temptoken = SERVER['HTTP_TEMPTOKEN'];
        }
        
        // 这是请求没有token的情况。
        if ( !$temptoken ) {
            $key_minute = self::key_prefix_ip_request_count.$ip; 
            $count = $redis->incr( $key_minute );
            if ($count == 1 ) {
                $redis->setTimeout( $key_minute, 60 ); // 仅能存活1分钟
            }
            
            if ( $count > self::allow_request_count_per_minute ) {
                
                $this->add_ip_to_blacklist();
                $this->output(['code' =>self::code_ip_blacklist, 'message'=> $ip  ]);
            }
            // 如果他请求关键接口。
            if ( $this->is_secure_api($request->url( )) ) {
                $this->output(['code' =>self::code_token_need_login, 'message'=> '需要登录'  ]);
            }
            $this->set_new_http_header_temptoken();
        }
        
        if ( $temptoken )  {
            $redis_key_temptoken = self::key_prefix_token.$temptoken;
            $result = $redis->get( $redis_key_temptoken );
            if ( $result === false ) {
                // 假如redis里没有，则说明 传来的是错的。重新登录。
                $this->output(['code' =>self::code_token_need_login , 'message'=> '校验错误'  ]);
            }
            // 下面的逻辑全部是 token正确的情况
            $key = self::key_prefix_token_request_count.$temptoken;
            $count = $redis->incr( $key );
            if ($count == 1 ) {
                $redis->setTimeout( $key_minute, 60 ); // 仅能存活1分钟
            }
            if ($count > self::allow_request_count_per_token_minute) {
                $this->output(['code' =>self::code_token_too_much , 'message'=> '检查错误'  ]);
            }
            $this->set_http_header_temptoken($temptoken);
        }
        
        
        
        
        
    }
    
    public function get_flush_token(){
        
    }
   
   
}
