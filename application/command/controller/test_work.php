<?php
// 定义应用目录

define('APP_PATH', __DIR__ . '/../../../application/');
// 开启调试模式
define('APP_DEBUG', true);
define('APP_AUTO_BUILD',false);
// 加载框架引导文件
//require __DIR__ . '/../../../thinkphp/start.php';
require __DIR__ . '/../../../thinkphp/base.php';
\think\Loader::addNamespace('app','/var/www/html/application/');
require APP_PATH  .'common.php';//zend 加载

require  APP_PATH  .'../extend/lib/vendor/autoload.php';

// 加任务
ini_set ( 'error_reporting', 6143 );
ini_set('display_errors', 1);
        \Resque::setBackend('127.0.0.1:6380');
        $args = array(
            'name' => '张三job3：',
            'type' => 10000,
        );
        \Resque::enqueue('jobswork', '\app\command\controller\Workjob', $args);
        