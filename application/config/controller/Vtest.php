<?php
namespace app\config\controller;
//仅限自机（其实是200） 和 200
in_array(\BBExtend\Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');

use think\Config;
/**
 * 
 * 测试 sign方案的正确性
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Vtest  extends \UnitTestCase
{
    
    const uid=1;
    const token='12345678901234567890';
    public function index() 
    {
    }
    
    public function test_v()
    {
        
        $param =['v'=> 1, ];
        $url ='http://127.0.0.1/shop/vv2/t1?'.http_build_query($param);
        
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 0);
        
        
        $url ='http://127.0.0.1/shop/vv2/t3';
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        
        $url ='http://127.0.0.1/shop/vv2/t1';
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        
        $time=time();
        $s = strval(self::uid) . strval($time) . substr(self::token, 0, 10) . 'C0W509' ;
        $sign =  strtolower( substr(md5($s), 0, 12) );
        $sign_err = $sign ."err";

        
        $param =['v'=>2, 'time'=>$time, 'uid'=>self::uid, 'sign'=>$sign_err ];
        $url ='http://127.0.0.1/shop/vv2/t1?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 0);
        
        
        $param =['v'=>2, 'time'=>$time, 'uid'=>self::uid, 'sign'=>$sign ];
        $url ='http://127.0.0.1/shop/vv2/t1?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        
        
    }
    
    
    
    
    
    
    
    function setUp() {
    
         $redis = $this->getredis();
        $redis->flushAll();
        $this->add_record();
    }
    function setDown() {
    }
    
    private function getredis()
    {
        $redis = new \Redis();
        $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
        $redis->auth(Config::get('REDIS_AUTH'));
        return $redis;
    }
    
    public function add_record()
    {
        $db = \BBExtend\Sys::get_container_db();
        
        $db->query("delete from bb_users");
        
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
            'userlogin_token'=>self::token,
        ]);
        
//         \BBExtend\BBRedis::getInstance('user')->hMset(self::uid, 
//                 ['userlogin_token'=>self::token,
//                     'uid'=>1,
//                     'sex'=>1,
//                     'vip'=>1,
//                     'min_record_time'=>1,
//                     'max_record_time'=>1,
//                 ]);
    
    }
    
    
}

