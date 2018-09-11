<?php

namespace BBExtend;

use think\Config;

class Sys
{
    
    
    public static $pimple_container; // 这是pimple容器
      //
    /**
     * 这是pimple容器的实际注册过程。
     * @return \Pimple\Container
     */
    private static function register_pimple()
    {
        $container = new \Pimple\Container();
        $container['dbe']= function($c) {
            return self::getdb_eloquent();
        };
        $container['db_readonly']= function($c) {
            return self::getdb_read();
        };
        $container['db']= function($c) {
            return self::getdb();
        };
        $container['redis']= function($c) {
            return self::getredis11();
        };
        $container['log']= function($c) {
            return self::getlog();
        };
        $container['sql_log']= function($c) {
            return self::getsql_log();
        };
        $container['node']= function($c) {
            return new \BBExtend\service\Node();
        };
        return $container;
    }
    
    /**
     *
     * @return \Pimple\Container
     */
    public static function get_container()
    {
        if (self::$pimple_container == null) {
            self::$pimple_container = self::register_pimple();
        }
        return self::$pimple_container;
    }
    
    /**
     * 
     * @return \Illuminate\Database\Capsule\Manager
     */
    public static function get_container_db_eloquent()
    {
        $c = self::get_container();
        return $c['dbe'];
    }
    
    /**
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function get_container_dbreadonly()
    {
        $c = self::get_container();
        return $c['db_readonly'];
    }
    
    /**
     * @return \BBExtend\service\NodeInterface
     */
    public static function get_container_node()
    {
        $c = self::get_container();
        return $c['node'];
    }
    
     /**
     * 数据库连接
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function get_container_db()
    {
        $c = self::get_container();
        return $c['db'];
    }
    /**
     * 得到redis11
     * @return \Redis
     */
    public static function get_container_redis()
    {
        $c = self::get_container();
        return $c['redis'];
    }
     
    
    
    /**
     * 获取微信公众号token
     */
    public static function get_wx_gongzhong_token()
    {
        $redis = self::getredis11();
        
        $key = 'get_wx_gongzhong_token';
        $result = $redis->get($key);
        if (!$result) {
        
            $appid = 'wx190ef9ba551856b0';
            $secret = '55a4e4aa42e36a3691ee242c967ffd5f';
            $url ='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.
                 $appid.'&secret='.$secret;
            $result = file_get_contents($url);
            $redis->set($key, $result); //保存在redis里的是一个json字符串，包括token和失效时间。
            $json = json_decode($result, true);
            $redis->setTimeout($key, $json['expires_in']);
            
        }
        $json = json_decode($result, true);
        if(isset($json['access_token'])){
            return $json['access_token'];
        }else{
            return '';
        }
    }
    
    /**
     * 获取微信公众号ticket
     */
    public static function get_wx_gongzhong_ticket()
    {
        $redis = self::getredis11();
        
        $key = 'get_wx_gongzhong_ticket';
        $result = $redis->get($key);
        if (!$result) {
            
            $token = self::get_wx_gongzhong_token();
            
            $url ='https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='. $token. 
                  '&type=jsapi';
            $result = file_get_contents($url);
            $redis->set($key, $result); //保存在redis里的是一个json字符串，包括token和失效时间。
            $json = json_decode($result, true);
            $redis->setTimeout($key, $json['expires_in']);
                    
        }
        $json = json_decode($result, true);
        if(isset($json['ticket'])){
            return $json['ticket'];
        }else{
            return '';
        }
    }
    
    
    
    private static function baidu_post($url = '', $param = '')
    {
       
            if (empty($url) || empty($param)) {
                return false;
            }
            
            $postUrl = $url;
            $curlPost = $param;
            $curl = curl_init();//初始化curl
            curl_setopt($curl, CURLOPT_URL,$postUrl);//抓取指定网页
            curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
            curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
            $data = curl_exec($curl);//运行curl
            curl_close($curl);
            
            return $data;
    }
    
     
    
    /**
     * 获取百度公共token，只有一个，每30天百度自己会改值，所以redis缓存10天。
     */
    public static function get_baidu_token()
    {
        $redis = self::get_container_redis();
        
        $key = 'get_baidu_public_token';
        $result = $redis->get($key);
        if (!$result) {
            $app_id = '11227646';// 未用到。是百度 的应用id
            // 后台网址
            // https://cloud.baidu.com/?from=console
            $api_key='ehZPTAMqBxMex7KndsspwSyC';
            $secret_key='8X9hcDQ7iq8CnEOybLYQ7S5YG3Q33sGE';
            $url = 'https://aip.baidubce.com/oauth/2.0/token';
            $post_data['grant_type']       = 'client_credentials';
            $post_data['client_id']      = $api_key;
            $post_data['client_secret'] = $secret_key;
            
            $o = "";
            foreach ( $post_data as $k => $v )
            {
                $o.= "$k=" . urlencode( $v ). "&" ;
            }
            $post_data = substr($o,0,-1);
            
            $result = self::baidu_post($url, $post_data);
            
            $redis->set($key, $result); //保存在redis里的是一个json字符串，包括token和失效时间。
            $json = json_decode($result, true);
            $redis->setTimeout($key, $json['expires_in']);
        }
        $json = json_decode($result, true);
        if(isset($json['access_token'])){
            return $json['access_token'];
        }else{
            return '';
        }
    }
    
    
    /**
     * 数据库连接
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function getdb()
    {
        return \BBExtend\common\ZendDb::getdb();
    }

    /**
     * 数据库连接,同上面那个一样，遍历用
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function getdb2()
    {
        return \BBExtend\common\ZendDb::getdb2();
    }
    
    /**
     * eloquent 数据库连接
     * @return \Illuminate\Database\Capsule\Manager
     */
    public static function getdb_eloquent()
    {
        return \BBExtend\common\ZendDb::getdb_eloquent();
    }
    /**
     * eloquent 数据库连接
     * @return \Illuminate\Database\Capsule\Manager
     */
    public static function getdb_read()
    {
        return \BBExtend\common\ZendDb::getdb_read();
    }
    
    
    /**
     * 得到redis11
     * @return \Redis
     */
    public static function getredis11()
    {
        static $redis = null;
        if ($redis == null) {
            $redis = new \Redis();
            $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
            $redis->auth(Config::get('REDIS_AUTH'));
            $redis->select(11);
        }
        return $redis;
    }
    
    
    /**
     * 得到redis2
     * @return \Redis
     */
    public static function getredis2()
    {
        static $redis = null;
        if ($redis == null) {
            $redis = new \Redis();
            $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
            $redis->auth(Config::get('REDIS_AUTH'));
            $redis->select(2);
        }
        return $redis;
    }
    
    
    public static function getredis_paihangbang()
    {
        static $redis = null;
        if ($redis == null) {
            $redis = new \Redis();
            $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
            $redis->auth(Config::get('REDIS_AUTH'));
            $redis->select(12);
        }
        return $redis;
    }

//     /**
//      * 得到redis
//      */
//     public static function getredis()
//     {
//         static $redis = null;
//         if ($redis == null) {
//             $option = array(
//                 'host'     => '127.0.0.1',
//                 'port'     => 6379,
//                 'database' => 15
//             );
//             $redis = new \Predis\Client($option);
//         }
//         return $redis;
//     }

    /**
     * 显示所有错误
     */
    public static function display_all_error()
    {
        ini_set ( 'error_reporting', 6143 );
        ini_set('display_errors', 1);
    }



    /**
     * 判断是否正式服
     */
    public static function is_product_server()
    {
        //$url = \BBExtend\common\BBConfig::get_server_url();
        //return preg_match('#bobo.yimwing.com#', $url);
        return self::get_machine_name() =='production';
    }
    
    /**
     * 判断是否单元测试机器。
     */
    public static function is_phpunit_server()
    {
        //$url = \BBExtend\common\BBConfig::get_server_url();
        //return preg_match('#bobo.yimwing.com#', $url);
        return self::get_machine_name() =='xieye';
    }

    /**
     * 得到机器名称，由php.ini配置，在最后几行
     */
    public static function get_machine_name()
    {
        $temp = get_cfg_var('guaishou.username');
        if ($temp ) {
            return $temp;
        }
        return '';
    }

    /**
     * 得到机器配置文件，由php.ini配置，在最后几行
     */
    public static function get_machine_config()
    {
        $temp = get_cfg_var('guaishou.config');
        if ($temp ) {
            return $temp;
        }
        return '';
    }

    /**
     * 调试用重要工具
     * @param unknown $txt
     */
    public static function debug($txt)
    {
        
        if (is_array($txt) || is_object($txt) ) {
            $txt = var_export($txt, 1);
        }
        $txt= strval($txt);
        $log = self::getlog();
        $log->info($txt);
    }

    public static function debugxieye($txt)
    {
        static $logger = null;
        if ($logger == null) {
            $file = 'xieyedebug.log';
            $log = new \Zend_Log_Writer_Stream( LOG_PATH . $file);
            $logger = new \Zend_Log($log);
        }
        if (is_array($txt) || is_object($txt) ) {
            $txt = var_export($txt, 1);
        }
        $logger->info($txt);
    }
    
    public static function test(){
        return "test09";
    }
    
    
    /**
     * 返回sql日志
     * @return \Monolog\Logger
     */
    public static function getsql_log()
    {
        return \BBExtend\common\Log::getsql_log();
    }
    
    /**
     * 返回普通日志
     * @return \Monolog\Logger
     */
    public static function getlog()
    {
        return \BBExtend\common\Log::getlog();
    }

}
