<?php
use think\Config;

// xieye ，定义时间常量，定义zend类库加载路径。
define('APP_TIME', time() );

// 支付宝邮箱，千万勿删除，xieye 201803
define('ALIPAY_SELLER_ID', '201457175@qq.com' );
// 大赛报名推送
define('DASAI_PUSH_QUDAO_ID', 156 );


\think\Debug::remark('begin_bobo' );


// debug('begin_bobo');
// 2017 07  加载composer，位于如下地址
require_once  __DIR__ .'/../extend/lib/vendor/autoload.php';

// 下面这句千万不删，因为elequent的模型类必须要初始化。201708
$dbe = \BBExtend\Sys::get_container_db_eloquent();

// 谢烨：下面的代码：把请求保存到表里，可以删除insert那句代码，但一般保留，每周清空一次bb_request表
$request = \think\Request::instance();
$version='';
if (isset($_SERVER['HTTP_VERSION'])) {
    $version = $_SERVER['HTTP_VERSION'];
}
$user_agent =$request->header("user-agent");
if (!$user_agent){
    $user_agent='';
}

$url = $request->url();
if (preg_match('#^/(uploads|vue)#', $url)) {
    exit;
}


$bb_request_arr =[
        'create_time' => APP_TIME,
        'url' => $request->url(),
        'ip'  => $request->ip(),
        'version' => $version,
        'user_agent' => $user_agent,
        'domain' => $request->domain(),
        'datestr' => date("Ymd"),
];

$post='';
if (  $request->method() =="POST") {
    $temp = (array)$_POST;
    
  //  $post= json_encode($temp, JSON_UNESCAPED_UNICODE   ) ;
}

$dbe::table("bb_request")->insert( [
    'create_time' => APP_TIME,
    'url' => $request->url(), 
    'ip'  => $request->ip(),
    'version' => $version,
    'user_agent' => $user_agent,
        'domain' => $request->domain(),
    'datestr' => date("Ymd"),
    'post' => $post,
]);

// 谢烨，下面的代码不可删除，因为程序中用到了，会读取这几个配置！
Config::set("http_head_version",$version);
Config::set("http_head_ip",$request->ip());
Config::set("http_head_url",$request->url());
Config::set("http_head_mobile_type", preg_match('#android#i', $user_agent)?"android":"ios" );
Config::set("http_head_user_agent",$user_agent);
Config::set("bb_request_arr",$bb_request_arr);


