<?php
namespace app\config\controller;
// use BBExtend\BBShop;
use think\Controller;
use BBExtend\Sys;
use think\Config;
use BBExtend\message\MessageConfig;

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');


/**
 * 
 * 商城单元测试类
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Msgconfigtest  extends \UnitTestCase
{
    public function index() 
    {
         $temp = get_cfg_var('guaishou.username');
    
         if ($temp && $temp=='xieye') {
              
         }else {
             exit();
         }
    }
    // 测试接口
    public function test_1()
    {
        //$this->assertTrue(1);
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_msg_user_config";
        $count=$db->fetchOne($sql);
        $this->assertEqual($count, 0);
        
         $param =['uid'=> 1, ];
        $url ='http://127.0.0.1/message/index/get_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        $data = $json['data'];
        // 断言有15 + 4 =19行的数据
        $sql ="select count(*) from bb_msg_user_config";
        $count=$db->fetchOne($sql);
        $this->assertEqual($count, 15+4);
        
        
    }
    
    // 直接测试类方法1.
    public function test_2()
    {
        //$this->assertTrue(1);
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_msg_user_config";
        $count=$db->fetchOne($sql);
        $this->assertEqual($count, 0);
    
        $temp = MessageConfig::get_instance(1);
        $result = $temp->get_one_big_config(119);
        
        // 断言有15 + 4 =19行的数据
        $sql ="select count(*) from bb_msg_user_config";
        $count=$db->fetchOne($sql);
        $this->assertEqual($count, 15+4);
    
    
    }
    // 直接测试类方法2.
    public function test_3()
    {
        //$this->assertTrue(1);
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_msg_user_config";
        $count=$db->fetchOne($sql);
        $this->assertEqual($count, 0);
    
        $temp = MessageConfig::get_instance(1);
        $result = $temp->get_one_config(0,11900); // 故意传错误参数
        $sql ="select count(*) from bb_msg_user_config";
        $count=$db->fetchOne($sql);
        $this->assertEqual($count, 0 ); // 错误的参数，不应该加行到表里。
        
        $result = $temp->get_one_config(0,119);
        // 断言有15 + 4 =19行的数据
        $sql ="select count(*) from bb_msg_user_config";
        $count=$db->fetchOne($sql);
        $this->assertEqual($count, 19 ); // 错误的参数
    }
    
    // 测试设置值是否正确。
    public function test_4()
    {
        $db = Sys::get_container_db();
        
        MessageConfig::get_instance(1)->get_all_config();
        
        //测试修改
        $param =['uid'=> 1,'bigtype'=>0,'type'=>123,'value'=>0, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        
        $sql="select value from bb_msg_user_config where uid=1 and bigtype=0 and type=123 ";
        $value=$db->fetchOne($sql);
        $this->assertEqual($value, 0);
        
        //测试修改
        $param =['uid'=> 1,'bigtype'=>0,'type'=>123,'value'=>1, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        
        $sql="select value from bb_msg_user_config where uid=1 and bigtype=0 and type=123 ";
        $value=$db->fetchOne($sql);
        $this->assertEqual($value, 1);
        
        //测试修改
        $param =['uid'=> 1,'bigtype'=>123,'type'=>3,'value'=>0, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        
        $sql="select value from bb_msg_user_config where uid=1 and bigtype=123 and type=3 ";
        $value=$db->fetchOne($sql);
        $this->assertEqual($value, 0);
        
        $param =['uid'=> 1,'bigtype'=>123,'type'=>3,'value'=>1, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        
        $sql="select value from bb_msg_user_config where uid=1 and bigtype=123 and type=3 ";
        $value=$db->fetchOne($sql);
        $this->assertEqual($value, 1);
        
        //
        
       
        
        
    }
    
    //测试parent_close参数
    public function test_5()
    {
        $db = Sys::get_container_db();
        
        MessageConfig::get_instance(1)->get_all_config();
        // 先断言打开
        $sql="select value from bb_msg_user_config where uid=1 and bigtype=0 and type=123 ";
        $value=$db->fetchOne($sql);
        $this->assertEqual($value, 1);
        
        $param =['uid'=> 1,'bigtype'=>123,'type'=>3,'value'=>0,'parent_close'=>1 ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        
        $sql="select value from bb_msg_user_config where uid=1 and bigtype=123 and type=3 ";
        $value=$db->fetchOne($sql);
        $this->assertEqual($value, 0);
        // 断言父类关闭
        $sql="select value from bb_msg_user_config where uid=1 and bigtype=0 and type=123 ";
        $value=$db->fetchOne($sql);
        $this->assertEqual($value, 0);
        
        
    }
    
    // 测试设置值是否正确。
    public function test_6()
    {
        $db = Sys::get_container_db();
    
        MessageConfig::get_instance(1)->get_all_config();
    
        //测试修改
        $param =['uid'=> 1,'bigtype'=>0,'type'=>123,'value'=>0, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
    
        $temp= MessageConfig::get_instance(1);
        $result = $temp->get_one_config(0,123); // 故意传错误参数
        $this->assertTrue($result===0 );
        
        $result = $temp->get_one_config(123,2); // 误参数,,重要的测试，测试父亲是主要的！
        $this->assertTrue($result===0 );
        
        
        //测试修改
        $param =['uid'=> 1,'bigtype'=>0,'type'=>123,'value'=>1, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        
        $param =['uid'=> 1,'bigtype'=>123,'type'=>2,'value'=>0, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        
        $result = $temp->get_one_config(123,2); // 误参数,,重要的测试，测试父亲是主要的！
        $this->assertTrue($result===0 );
        
        $param =['uid'=> 1,'bigtype'=>123,'type'=>2,'value'=>1, ];
        $url ='http://127.0.0.1/message/index/set_config?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
         
        $result = $temp->get_one_config(123,2); // 误参数,,重要的测试，测试父亲是主要的！
        $this->assertTrue($result===1 );
         
    }
    
    
    function setUp() {
    
         $redis = $this->getredis();
       // $redis->flushAll();
        $this->add_record();
    }
    function setDown() {
    }
    
    private function getredis()
    {
        $redis = new \Redis();
//         echo Config::get('REDIS_HOST');
//         echo Config::get('REDIS_PORT');
         $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
//         $redis->auth(Config::get('REDIS_AUTH'));
        return $redis;
    }
    
    public function add_record()
    {
        $db = \BBExtend\Sys::get_container_db();
        $sql ="truncate table bb_msg_user_config";
        $db->query($sql);
        $sql ="truncate table bb_users";
        $db->query($sql);
        
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
            'birthday' =>'2014-02',
        ]);
        
    }
    
}

