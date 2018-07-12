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


require_once  APP_PATH  .'../extend/lib/vendor/autoload.php';

include __DIR__ . "/Job2.php";
require __DIR__ ."/../../../extend/lib/vendor/chrisboulton/php-resque/resque.php";

