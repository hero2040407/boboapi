<?php
namespace app\command\controller;

//use think\Controller;
// use BBExtend\Sys;

// 以下两行重要！！，所有的任务都要include进来！！

include __DIR__ . '/Job23.php'; 
 
/**
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */


class Worker23  
{

    
    /**
     * 守护进程
     * 启动方式
     * QUEUE=default REDIS_BACKEND=127.0.0.1:6380  php /var/www/html/public/index.php /command/message/worker
     * QUEUE=jobs REDIS_BACKEND=127.0.0.1:6380  php /var/www/html/public/index.php /command/message/worker
     */
    public function start()
    {
       
        require __DIR__ ."/../../../extend/lib/vendor/chrisboulton/php-resque/resque.php"; 
        
    }
    
   
    
}



