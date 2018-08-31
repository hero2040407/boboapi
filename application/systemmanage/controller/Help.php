<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;

class Help {
    
    private function output_css(){
        $s=<<<html
<style>
  body {font-family: Consolas, Monaco, monospace;

}
</style>
html;
        echo $s;
    }
    
    private function output_debug($filename) {
        
        $this->output_css();
        
        $line = 0;
        if (isset ( $_GET ['line'] )) {
            $line = intval ( $_GET ['line'] );
        } else {
            $line = 100;
        }
        // $time = strval(time()) .mt_rand(100000, 999999).".txt";
        $filename = realpath($filename);
        echo " =======================&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;     ".$filename.
        "     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;======================= <br><br>\n";
        // $temp_file = "{$data_path}/temp/{$time}";
        
        if (PHP_OS =="Linux") {
          $command = "cat -b  {$filename} | tail -n {$line}";
        }else {
            $command = "type  {$filename}";
        }
        
        $out = shell_exec ( $command );
        echo $this->beautiful( nl2br ( $out ));
    }
    
    
    private function beautiful($html)
    {
        $html = preg_replace('#\[ error \]#', '<font style="color:red">[ error ]</font>', $html);
        $html = preg_replace('#\[ 2018(.+?)\]#', '<font style="color:green">[ 2018$1 ]</font>', $html);
        
        return $html;
        
    }
    
    /**
     * 查看错误日志
     */
    public function phperror()
    {
       // echo "windows 主机暂不处理";return;
        
        if (PHP_OS =="Linux") {
            
            $err_file="/var/www/html/runtime/log/". date("y_m_d").".log" ;
            if (is_file($err_file)) {
                $this->output_debug($err_file);
            }
            
            // 固有日志。
            $err_file="/var/log/php-fpm/www-error.log";
            if (Sys::get_machine_name() =="245" ) {
                $err_file="/usr/local/php/var/log/php-fpm.log";
            }
            
            
         //  $this->output_debug($err_file);
           
           
           
       
           
           
        }else {
            echo "windows主机暂无查看php错误日志功能";
        }
    }
    
    /**
     * 查看表行数
     */
    public function mysqlrowcount()
    {
        // echo "windows 主机暂不处理";return;
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $arr = \BBExtend\common\MysqlTool::show_table_rows_html();
//         if (PHP_OS =="Linux") {
//             $this->output_debug("/var/log/php-fpm/www-error.log");
//         }else {
//             echo "windows主机暂无查看php错误日志功能";
//         }
        echo $arr;
    }
    
    /**
     * 查看错误日志
     */
    public function phpslow()
    {
        // echo "windows 主机暂不处理";return;
    
        if (PHP_OS =="Linux") {
            $this->output_debug("/var/log/php-fpm/www-slow.log");
        }else {
            echo "windows主机暂无查看php错误日志功能";
        }
    }
    
    
    /**
     * 查看错误日志
     */
    public function mysqlslow()
    {
        // echo "windows 主机暂不处理";return;
    
        if (PHP_OS =="Linux") {
            $this->output_debug("/var/lib/mysql/master-slow.log");
        }else {
            echo "windows主机暂无查看php错误日志功能";
        }
    }
    
    
    /**
     * 查看debug
     */
    public function debug()
    {
        $this->output_debug(LOG_PATH . date('y_m_d').'.log');
    }
    
    /**
     * 查看debug
     */
    public function debugxieye()
    {
        $this->output_debug(LOG_PATH . 'xieyedebug.log');
    }
    
    public function nginx()
    {
        $time='';
        if (isset($_GET['time'])) {
            $time = strval($_GET['time']);
        }
         
        if (!preg_match('#^\d\d:\d\d$#', $time)) {
            echo "time参数应该类似?time=14:03";
            return;
        }
        $command = "cat -n /var/log/nginx/access.log |grep {$time} ";
        $out = shell_exec($command);
        echo nl2br( $out);
    }
    
    public function cp()
    {
        $command  = '';
    }
    
    public function view_data()
    {
        \BBExtend\user\Remove::getinstance(0)->query_bad_database();
    }
    
    
}