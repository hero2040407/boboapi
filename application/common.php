<?php
use think\Config;
use BBExtend\Sys;
// xieye ，定义时间常量，定义zend类库加载路径。
define( 'APP_TIME', time( ) );

// 支付宝邮箱，千万勿删除，xieye 201803
define( 'ALIPAY_SELLER_ID', '201457175@qq.com' );
// 大赛报名推送
define( 'DASAI_PUSH_QUDAO_ID', 156 );
// 缺省图片
define( 'const_default_pic', "https://bobo.yimwing.com/public/toppic/topdefault.png" );
// 是否使用通告的值显示通告短视频。
define( 'USE_UPDATES_RECORD', 1 );
// 1分钟限制次数-个人信息页面。
define( 'REQUEST_LIMIT_USERINFO_PER_MINUTE', 200 );
// 10分钟限制次数-个人信息页面。
define( 'REQUEST_LIMIT_USERINFO_TEN_MINUTE', 800 );
// 1分钟限制次数- 所有页面。
define( 'REQUEST_LIMIT_ALL_PER_MINUTE', 400 );
// 10分钟限制次数 - 所有页面。
define( 'REQUEST_LIMIT_ALL_TEN_MINUTE', 2000 );



\think\Debug::remark( 'begin_bobo' );

// debug('begin_bobo');
// 2017 07 加载composer，位于如下地址
require_once __DIR__ . '/../extend/lib/vendor/autoload.php';

// 下面这句千万不删，因为elequent的模型类必须要初始化。201708
$dbe = \BBExtend\Sys::get_container_db_eloquent( );

// 谢烨：下面的代码：把请求保存到表里，可以删除insert那句代码，但一般保留，每周清空一次bb_request表
$request = \think\Request::instance( );
$version = '';
if (isset( $_SERVER['HTTP_VERSION'] )) {
    $version = $_SERVER['HTTP_VERSION'];
}
$user_agent = $request->header( "user-agent" );
if (! $user_agent) {
    $user_agent = '';
}

$url = $request->url( );
if (preg_match( '#^/(uploads|vue)#', $url )) {
    exit( );
}

$bb_request_arr = [
        'create_time' => APP_TIME,
        'url' => $request->url( ),
        'ip' => $request->ip( ),
        'version' => $version,
        'user_agent' => $user_agent,
        'domain' => $request->domain( ),
        'datestr' => date( "Ymd" )
];

$post = '';
if ($request->method( ) == "POST") {
    $temp = (array) $_POST;
    
    $post = json_encode( $temp, JSON_UNESCAPED_UNICODE );
    if (strlen($post) > 1500 ) {
        $post = \BBExtend\common\Str::substr($post, 0, 500);
    }
}

$dbe::table( "bb_request" )->insert( 
        [
                'create_time' => APP_TIME,
                'url' => $request->url( ),
                'ip' => $request->ip( ),
                'version' => $version,
                'user_agent' => $user_agent,
                'domain' => $request->domain( ),
                'datestr' => date( "Ymd" ),
                'post' => $post
        ] );
$dbe::table( "bb_request2" )->insert(
        [
                'create_time' => APP_TIME,
                'url' => $request->url( ),
                'ip' => $request->ip( ),
                'version' => $version,
                'user_agent' => $user_agent,
                'domain' => $request->domain( ),
                'datestr' => date( "Ymd" ),
                'post' => $post
        ] );


// 谢烨，下面的代码不可删除，因为程序中用到了，会读取这几个配置！
Config::set( "http_head_version", $version );
Config::set( "http_head_ip", $request->ip( ) );
Config::set( "http_head_url", $request->url( ) );
Config::set( "http_head_mobile_type", preg_match( '#android#i', $user_agent ) ? "android" : "ios" );
Config::set( "http_head_user_agent", $user_agent );
Config::set( "bb_request_arr", $bb_request_arr );

//定义请求白名单。
Config::set( "bb_request_white_list_ip", [
        '127.0.0.1','0.0.0.0','122.224.90.210',
        
] );
$ip = Config::get( "http_head_ip" );




// 只有http请求，且 不在白名单内，才有以下处理。
if (IS_CLI === false && ( !in_array($ip,  Config::get( 'bb_request_white_list_ip' ) ) ) ) { // 谢烨，确保是http请求，必须放过本机shell
    $user_agent = Config::get( "http_head_user_agent" );
    
    // 以下条件判断语句，严格检查user-agent信息，可以注释掉。
//     if (preg_match('#MicroMessenger#', $user_agent) ||
//             preg_match('#^BoBo/4\.\d\.\d \((iPhone|iPad)#', $user_agent) ||
//             preg_match( '#^\(BoBo\)/\(4\.\d\.\d\) \(android#', $user_agent )
            
//             ){
//         // 这些正确的user-agent不做任何处理
//     }else {
//         // 错误的 agent 直接退出。
        
//         exit;
//     }
    
    $redis = Sys::get_container_redis( );
    
    $key = "limit_index:ip:{$ip}";
    $key_hour = "limit_index:ip:hour:{$ip}";
    $key_list = "limit:ip:week";
    
    // 如果查到哪个ip是在封禁ip列表内，禁止访问。
    $has_limit = $redis->sIsMember( $key_list, $ip );
    if ($has_limit === true  ) {
        sleep( 30 );
        exit( );
    }
}

//if ( !\BBExtend\Sys::is_product_server() ) {
    $obj = new \BBExtend\Secure();
    $obj->check();
//}



























