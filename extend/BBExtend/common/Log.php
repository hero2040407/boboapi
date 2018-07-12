<?php
namespace BBExtend\common;
//use think\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;



/**
 * 获取db对象，代码中请勿直接使用此类，应使用BBExtend\Sys::get_container_db()调用。
 * 
 * 使用单件模式，允许在单元测试时注入。
 *  
 * @author 谢烨
 */
class Log
{
    public static $log;// 常规日志
    public static $log2; // sql日志
    
    
    private function __construct(){}
    
    /**
     * 返回普通日志
     * @return \Monolog\Logger
     */
    public static function getlog()
    {
        if (self::$log == null) {
            $log = new Logger('JK');
            // 谢烨，StreamHandler的最后一个参数，越严重，则实际记录日志越少。
            
            $dateFormat = "Y-m-d H:i:s";
            // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
            //$output = "[%datetime%] %message% %context% %extra%\n";
            $output = "\n-----  [%datetime%]  -----\n%message%\n";
            $formatter = new  LineFormatter($output, $dateFormat);
            $stream = new StreamHandler(LOG_PATH. date("Ymd"). ".log", Logger::INFO);
            $stream->setFormatter($formatter);
            
            $log->pushHandler($stream);
            
            self::$log = $log;
        }
        return self::$log;
    }
    
    /**
     * 返回sql日志
     * @return \Monolog\Logger 
     */
    public static function getsql_log()
    {
        if (self::$log2 == null) {
            $log = new Logger('JK_SQL');
            // 谢烨，StreamHandler的最后一个参数，越严重，则实际记录日志越少。
            
            $dateFormat = "Y-m-d H:i:s";
            // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
            //$output = "[%datetime%] %message% %context% %extra%\n";
            $output = "\n-----  [%datetime%]  -----\n%message%\n";
            $formatter = new LineFormatter($output, $dateFormat);
            $stream = new StreamHandler(LOG_PATH. date("Ymd"). ".sql.log", Logger::INFO);
            $stream->setFormatter($formatter);
            
            $log->pushHandler($stream);
            
            self::$log2 = $log;
        }
        return self::$log2;
    }
    
     
}//end class

