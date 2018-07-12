<?php
namespace app\config\controller;
//仅限自机（其实是200） 和 200
in_array(\BBExtend\Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');

use think\Config;
use BBExtend\Currency;

/**
 * 
 * 测试 sign方案的正确性
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Dashangtest  extends \UnitTestCase
{
    
    public function index() 
    {
    }
    
    public function test_dashang_and_list()
    {
        //先测试金钱数量
        $gold1 = Currency::get_currency(1)['gold'];
        $gold2 = Currency::get_currency(2)['gold'];
        $this->assertEqual($gold1, 10000);
        $this->assertEqual($gold2, 0);
        $param =['uid'=> 1,'room_id'=>11, 'type'=>2 ];//type2 扣钱10
        $url ='http://127.0.0.1/shop/dashang/index?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
     //测试一下金钱数量   
        $gold1 = Currency::get_currency(1)['gold'];
        $gold2 = Currency::get_currency(2)['gold'];
        $this->assertEqual($gold1, 9990);
        $this->assertEqual($gold2, 10);
        
        
        //测试list函数
        $param =['room_id'=> 11, ];//
        $url ='http://127.0.0.1/shop/dashang/list_people?'.http_build_query($param);
        dump($url);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        $this->assertEqual(count( $json['data']), 1);
//         $data = $json['data'][0];
//         $this->assertEqual($data['uid'], 1);
        
        
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
        $sql ="delete from bb_users";
        $db->query($sql);
        $sql ="delete from bb_currency";
        $db->query($sql);
        $sql ="delete from bb_record";
        $db->query($sql);
        $sql ="delete from bb_dashang_log";
        $db->query($sql);
        
        
        
        //谢烨，建立2个人，
        
        
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
        ]);
        $db->insert("bb_currency", [
            'uid'=>1,
            'gold'=>'10000',
        ]);
        $db->insert("bb_users", [
            'uid'=>2,
            'platform_id'=>'1',
        ]);
        $db->insert("bb_record", [
            'id' =>2,
            'uid'=>2,
            
            'room_id' =>'11',
            
        ]);
    
    }
    
    
}

