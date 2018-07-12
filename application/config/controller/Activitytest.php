<?php
namespace app\config\controller;
//仅限自机（其实是200） 和 200
in_array(\BBExtend\Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');

use think\Config;
use BBExtend\user\Activity;
/**
 * 
 * 测试 sign方案的正确性
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Activitytest  extends \UnitTestCase
{
    public function index()
    {
        
    }
    
   public function test1()
   {
//        $this->asserttrue(1);
       
       $help = Activity::getinstance(11);
       $act_id=12;
       $result =  $help->canjia($act_id);
       $this->assertTrue($result);
       // 验证参加后的数据 
       $db = \BBExtend\Sys::get_container_db();
       $sql="select * from bb_user_activity";
       $row = $db->fetchRow($sql);
       $this->assertEqual($row['uid'], 11);
       $this->assertEqual($row['activity_id'], $act_id);
       
       //验证成功
       $this->assertTrue($help->has_canjia($act_id));
       
       // 验证 重复参加失败
       $result =  $help->canjia($act_id);
       $this->assertfalse($result);
       
       //验证取消成功
       $result =  $help->un_canjia($act_id);
       $this->assertTrue($result);
        
       
       $sql="select * from bb_user_activity";
       $row = $db->fetchRow($sql);
        $this->assertFalse($row);
      //验证重复取消失败,
        $result =  $help->un_canjia($act_id);
        $this->assertfalse($result);
        
        //验证成功
        $this->assertfalse($help->has_canjia($act_id));
        
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
        
        $db->query("delete from bb_user_activity");
        
//         $db->insert("bb_users", [
//             'uid'=>1,
//             'platform_id'=>'1',
           
//         ]);
        
        
        
    
    }
    
    
}

