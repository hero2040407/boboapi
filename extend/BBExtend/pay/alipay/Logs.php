<?php

namespace BBExtend\pay\alipay;

/**
 * 日志处理类
 *
 * @since alpha 0.0.1
 * @date 2014.03.04
 * @author genialx
 *
 */

class Logs{

    //单例模式
    private static $instance    = NULL;
    //文件句柄
    private static $handle      = NULL;
    //日志开关
    private $log_switch     = NULL;
    //日志相对目录
    private $log_file_path      = NULL;
    //日志文件最大长度，超出长度重新建立文件
    private $log_max_len        = NULL;
    //日志文件前缀,入 log_0
    private $log_file_pre       = '';

        
    /**
     * 构造函数
     *
     * @since alpha 0.0.1
     * @date 2014.02.04
     * @author genialx
     */
    protected function __construct(){//注意：以下是配置文件中的常量，请读者自行更改

        $this->log_file    =  realpath( realpath( APP_PATH)."/../runtime/log/xieye.log");

     

    }

    /**
     * 单利模式
     *
     * @since alpha 0.0.1
     * @date 2014.02.04
     * @author genialx
     */
    public static function get_instance(){
        if(!self::$instance instanceof self){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     *
     * 日志记录
     *
     * @param int $type  0 -> 记录(THING LOG) / 1 -> 错误(ERROR LOG)
     * @param string $desc
     * @param string $time
     *
     * @since alpha 0.0.1
     * @date 2014.02.04
     * @author genialx
     *
     */
    public function log($desc){
        $time = date('Y-m-d H:i:s');
        
        file_put_contents($this->log_file, '[' .   $time."] " . $desc."\n",
                FILE_APPEND );
        
     
    }

   
}