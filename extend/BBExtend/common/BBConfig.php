<?php
namespace BBExtend\common;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 获取bbconfig表数据。
 * 
 * 使用单件模式确保不重复查询sql，允许在单元测试时注入。
 *  
 * @author 谢烨
 */
class BBConfig
{
    private static $config;
    private function __construct(){}
    
    /**
     * 连接
     */
    public static function get_bbconfig()
    {
        if (self::$config == null) {
            $db =Sys::get_container_db_eloquent();
            $sql = "select * from bb_config limit 1";
            self::$config = DbSelect::fetchRow($db, $sql); 
        }
        return self::$config;
    }
    
    //获得服务器连接地址
    public static function get_server_url()
    {
        $config = self::get_bbconfig();
        return $config["picserver"];
    }
    
    
    /**
     * 获取nodejs透传接口 主机地址，末尾有斜杠
     */
    private static function get_touchuan_host()
    {
        $config = self::get_bbconfig();
        return $config["nodejs"];
    }
    
    /**
     * 获取nodejs透传接口 网址。
     */
    public static function get_touchuan_url()
    {
        $url = self::get_touchuan_host() ; 
        return $url . 'phone_api/on_message'  ;
    }
    
    
    /**
     * 获取nodejs透传接口 网址。
     * 但这是发送给所有人的
     */
    public static function get_touchuan_url_for_sendall()
    {
        $url = self::get_touchuan_host() ;
        return $url . 'phone_api'  ;
    }
    
    

    public static function get_share_server_url()
    {
        $config = self::get_bbconfig();
        return $config["share_server"];
    }
    
    /**
     * 谢烨201709
     * 先获取地址，再人为把http改成https
     */
    public static function get_server_url_https()
    {
        $server = self::get_server_url();
        if (preg_match('/https/', $server)) {
            return $server;
        }else  {
            return preg_replace('/http/', 'https', $server);
        }
    }
    
  
}//end class

