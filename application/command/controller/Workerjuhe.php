<?php
namespace app\command\controller;

use think\Controller;
// use BBExtend\Sys;

// 以下两行重要！！，所有的任务都要include进来！！

// include __DIR__ . '/Myjob.php'; 
 include __DIR__ . '/Jobjuhe.php';
/**
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */


class Workerjuhe  extends Controller
{
//     public function add_task ()
//     {
//         ini_set ( 'error_reporting', 6143 );
//         ini_set('display_errors', 1);
//       \Resque::setBackend('127.0.0.1:6380');
//       $args = array(
//           'name' => '张三：'
//       );
//       \Resque::enqueue('default', 'Myjob', $args);
//     }
    
//     public function add_task2 ()
//     {
//         ini_set ( 'error_reporting', 6143 );
//         ini_set('display_errors', 1);
//         \Resque::setBackend('127.0.0.1:6380');
//         $args = array(
//             'name' => '张三job3：'
//         );
//         \Resque::enqueue('jobs', '\app\command\controller\Job2', $args);
//     }
    
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

