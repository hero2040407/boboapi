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
$include_path = ini_get("include_path").
    PATH_SEPARATOR . realpath( realpath( APP_PATH)."/../extend");
ini_set('include_path',$include_path);

// xieye，只能是false，只加载zend类库，不扩展加载路径
require_once  "Zend/Loader/Autoloader.php";
\Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(false);

require  APP_PATH  .'../extend/lib/vendor/autoload.php';

include __DIR__ . "/Jobjuhe.php";
require __DIR__ ."/../../../extend/lib/vendor/chrisboulton/php-resque/resque.php";

