<?php
namespace BBExtend\common;
use think\Config;
/**
 * 获取db对象，代码中请勿直接使用此类，应使用BBExtend\Sys::get_container_db()调用。
 * 
 * 使用单件模式，允许在单元测试时注入。
 *  
 * @author 谢烨
 */
class ConfigTest
{
    
    public static function init()
    {
        \think\Config::set(include APP_PATH . "config.php" );
        $database_arr = include APP_PATH . "database.php" ;
        $database_arr = ["database" => $database_arr ];
        \think\Config::set($database_arr );
    }
    
  
}//end class

