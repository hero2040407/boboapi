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
class Jubaotest extends  \UnitTestCase
{
    public function index()
    {
    }
    
    
    
    
    public function test_jubao()
    {
        $user = \BBExtend\BBUser::get_user(2);
        $this->assertTrue( array_key_exists('not_fayan', $user) );
        
        //测举报
        $param =['uid'=> 2,'type'=>1,'content'=>'呵呵', ];//type
        $url ='http://127.0.0.1/user/jubao/add?'.http_build_query($param);
       // echo "{$url}<br>";
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
         
        $sql ='select * from bb_jubao_log where uid=2 and type=1 ';
        $db = Sys::get_container_db();
        $row = $db->fetchRow($sql);
        $this->assertEqual($row['uid'], 2);
        
        //测禁言
        $param =['uid'=> 2,'type'=>2,'content'=>'呵呵', ];//type2 扣钱10
        $url ='http://127.0.0.1/user/jubao/add?'.http_build_query($param);
        // echo "{$url}<br>";
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
         
        $sql ='select * from bb_jubao_log where uid=2 and type=2 ';
        $db = Sys::get_container_db();
        $row = $db->fetchRow($sql);
        $this->assertEqual($row['uid'], 2);
        
        $user = \BBExtend\BBUser::get_user(2);
        $this->assertEqual( $user['not_fayan'], 1 );
        
        $sql ='select * from bb_users where uid=2  ';
        $db = Sys::get_container_db();
        $row = $db->fetchRow($sql);
        $this->assertEqual($row['not_fayan'], 1);
        
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
        $sql='delete from bb_jubao_log';
        $db->query($sql);
        $sql='delete from bb_users';
        $db->query($sql);
        
        

        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
        ]);
      
        $db->insert("bb_users", [
            'uid'=>2,
            'platform_id'=>'1',
        ]);
      
    }
    
    
}