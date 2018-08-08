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
ini_set ( 'error_reporting', 6143 );
ini_set('display_errors', 1);

require APP_PATH  .'common.php';//zend 加载

require  APP_PATH  .'../extend/lib/vendor/autoload.php';



$uid = 5313683;
$user = \app\user\model\UserModel::getinstance($uid);
echo $user->get_nickname();





$db = \BBExtend\Sys::getdb();
$sql="select * from bb_users limit 1";
var_dump($db->fetchAll($sql) );

\BBExtend\Sys::test();


$db = database1();
$result = $db->fetchAssoc($sql);
var_dump($result);

 function database1(){
    $config = new \Doctrine\DBAL\Configuration();
    //..
    $connectionParams = array(
        'dbname' => 'bobo',
        'user' => 'root',
        'password' => 'ChenyueAbc.123',
        'host' => '127.0.0.1',
        'driver' => 'pdo_mysql',
    );
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    return $conn;
}


echo 111;
