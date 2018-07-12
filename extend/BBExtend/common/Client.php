<?php
namespace BBExtend\common;

use think\Config;
/**
 * 获取客户端信息
 *  
 * @author 谢烨
 */
class Client
{
  
    /**
     * 是否安卓
     * @return boolean
     */
    public static function is_android()
    {
        $type = Config::get("http_head_mobile_type");
        if ($type) {
            if ($type=='android') {
                return true;
            }else {
                return false;
            }
            
        }else {
            //自己写
            $request = \think\Request::instance();
            $user_agent =$request->header("user-agent");
            if (!$user_agent){
                $user_agent='';
            }
            return preg_match('#android#i', $user_agent)? true : false ;
        }
        
    }
    
    public static function is_web()
    {
        $request = \think\Request::instance();
        $user_agent =$request->header("bobo-version");
        if (!$user_agent){
            return false;
        }
        return true;
        //return !(self::is_android());
    }
    
    
    public static function web_version()
    {
        $request = \think\Request::instance();
        $user_agent =$request->header("bobo-version");
        if (!$user_agent){
            return '0.0.1';
        }
        return preg_replace('#^.+/(.+)$#', '$1', $user_agent );
        
        //return !(self::is_android());
    }
    
    
    
    
    public static function is_ios()
    {
        return !(self::is_android());
    }
    
    public static function version()
    {
        $version = Config::get("http_head_version");
        if ($version) {
            return $version;
        
        }else {
            $version='1.0.0';
            if (isset($_SERVER['HTTP_VERSION'])) {
                $version = $_SERVER['HTTP_VERSION'];
            }
            
            
            return $version;
        }
        
    }
    
    /**
     * 用户代理
     */
    public static function user_agent()
    {
        $user_agent = \think\Request::instance()->header('User-Agent');
        $user_agent = strval($user_agent);
        return $user_agent;
    }
    
    
    /**
     * ip
     */
    public static function ip()
    {
        $user_agent = \think\Request::instance()->ip();
        $user_agent = strval($user_agent);
        return $user_agent;
    }
    
    
    public static function big_than_version($version)
    {
        $current = self::version();
        return version_compare($current, $version, ">");
    }
    
    public static function small_than_version($version)
    {
        $current = self::version();
        return version_compare($current, $version, "<");
    }
    
    
       
}//end class

