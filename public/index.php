<?php

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 开启调试模式
define('APP_DEBUG', true);
define('APP_AUTO_BUILD',true);
// 加载框架引导文件
require_once __DIR__.'/../extend/lib/vendor/autoload.php';
require __DIR__ . '/../thinkphp/start.php';
