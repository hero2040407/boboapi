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

// 谢烨，下面的代码不可删除，因为程序中用到了，会读取这几个配置！
Config::set( "http_head_version", $version );
Config::set( "http_head_ip", $request->ip( ) );
Config::set( "http_head_url", $request->url( ) );
Config::set( "http_head_mobile_type", preg_match( '#android#i', $user_agent ) ? "android" : "ios" );
Config::set( "http_head_user_agent", $user_agent );
Config::set( "bb_request_arr", $bb_request_arr );

//定义请求白名单。
Config::set( "bb_request_white_list_ip", [
        '127.0.0.1','0.0.0.0','122.224.90.210','218.72.27.194',
        
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
    
   
        
        
        // 谢烨，加请求前置判断。最少两个请求。
        $redis2 = Sys::getredis2();
        $requst_redis_key =  "limit_index:ip:request_list:{$ip}";
        
        if ( preg_match('#^/user/info/get_public_addi_video#',$url   ) || 
             preg_match('#^/user/user/get_userallinfo#',$url   ) 
                
                ) {
                    
        }else {
            $redis2->lPush( $requst_redis_key, $url );
            $redis2->lTrim( $requst_redis_key, 0,9 ); // 0,9 保留10个。
            $redis2->setTimeout($requst_redis_key, 5*60);// 存活5分钟。
            
        }
        
        
        
        
        // 每分钟最多60次。
        $new = $redis->incr( $key );
        $new2 = $redis->incr( $key_hour );
        if ($new < 3) {
            $redis->setTimeout( $key, 60 ); // 仅能存活1分钟
        }
        if ($new2 < 3) {
            $redis->setTimeout( $key_hour, 600 ); // 存活10分钟
        }
        
        if ($new2 > 600 ) { // 10分钟超过100次，永久限制。
            $redis->sadd( $key_list, $ip );
            Sys::debugxieye( "get_public_addi_video, 封禁ip成功，ip:{$ip},agent:{$user_agent}" );
            return [
                    'code' => 0
            ];
        }
        
        if ($new > 200 ) { // 每分钟超过20次，限制。
                         // Sys::debugxieye("get_public_addi_video,
                         // 每分钟30次限制，ip:{$ip},agent:{$user_agent}");
            sleep( 6 );
            // 限制每分钟每个ip最多访问30次这个接口。
            
            return [
                    'code' => 0
            ];
        }
        $batagent = 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Maxthon/4.3.2.1000 Chrome/30.0.1599.101 Safari/537.36';
//         if (strpos( $user_agent, $batagent ) !== false) {
//             return [
//                     'code' => 0
//             ];
//         }
        
        $weiagent = 'MicroMessenger';
        $weiphonagent = 'Windows Phone';
        
        // if(strpos($ua, 'MicroMessenger') == false || strpos($ua, 'Windows
    // Phone') == false){
        // if(strpos($user_agent,$batagent) !==false){
        // return ['code'=>0];
        // }
        
        // }
    
   
}





























