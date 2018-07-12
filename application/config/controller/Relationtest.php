<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\config\controller;




//仅限自机（其实是200） 和 200
in_array(\BBExtend\Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');

use think\Config;
use BBExtend\Sys;
class Relationtest extends  \UnitTestCase
{
    public function index()
    {
    }
    
    public function test_lahei()
    {
        //先测试金钱数量
        $param =['uid'=> 1,'target_uid'=>2 ];//type2 扣钱10
        $url ='http://127.0.0.1/user/relation/lahei?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
     
        $sql ='select * from bb_lahei where uid=1 and type=1';
        $db = Sys::get_container_db();
        $row = $db->fetchRow($sql);
        $this->assertEqual($row['target_uid'], 2);
        $redis = $this->getredis();
        $key = "user:lahei:1";
        $this->assertTrue($redis->sismember($key, 2) );//证明确实有2的存在
        
        
        //顺便测试拉黑list
        
        $param =['uid'=> 1 ];
        $url ='http://127.0.0.1/user/relation/lahei_list?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
         
        $datas = $json['data'];
        $this->assertEqual(count($datas), 1);
        
        
        
    //重复传同样参数，错误
        $param =['uid'=> 1,'target_uid'=>2 ];//type2 扣钱10
        $url ='http://127.0.0.1/user/relation/lahei?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 0);
         
        
        //测试取消拉黑
        $param =['uid'=> 1,'target_uid'=>2 ];//type2 扣钱10
        $url ='http://127.0.0.1/user/relation/un_lahei?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
         
        $sql ='select * from bb_lahei where uid=1';
        $db = Sys::get_container_db();
        $row = $db->fetchRow($sql);
        $this->assertfalse($row );
        $redis = $this->getredis();
        $key = "relation_lahei_1";
        $this->assertfalse($redis->sismember($key, 2) );//证明确实没有2的存在
        
        
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
        $redis->select(11);
        return $redis;
    }
    
    public function add_record()
    {
         $db = Sys::get_container_db();
        $sql='delete from bb_lahei';
        $db->query($sql);
    
    }
    
    
}