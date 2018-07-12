<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;
require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
use BBExtend\user\Focus;

/**
 * 关注测试，不要删除！！！
 * @author Administrator
 *        
 */
class Focustest extends \UnitTestCase{
    
    public function index() {
        if (PHP_OS=='Linux'){
            exit();
        }
    }
    
    public function test_1()
    {
        Sys::display_all_error();
        
        $uid =1;
        $target_uid=33;
        $obj = new Focus(1);
        
        $obj->focus_guy($target_uid);
        //断言数据库，和缓存。
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_focus where uid=1 and focus_uid={$target_uid}";
        $result = $db->fetchOne($sql);
        $this->assertTrue($result);
        
        $obj33 = new Focus($target_uid);
        
        $this->assertEqual(1, $obj33->get_fensi_count());
        $this->assertEqual(0, $obj->get_fensi_count());
        
        $newobj = new Focus(2);
        $newobj->focus_guy($target_uid);
        $this->assertEqual(2, $obj33->get_fensi_count());
        $this->assertEqual(0, $obj->get_fensi_count());
        
        $newobj->un_focus_guy($target_uid);
        $this->assertEqual(1, $obj33->get_fensi_count());
        $this->assertEqual(0, $obj->get_fensi_count());
        
        $newobj->un_focus_guy($target_uid);//第2次取消关注无效
        $this->assertEqual(1, $obj33->get_fensi_count());
        $this->assertEqual(0, $obj->get_fensi_count());
        
        $sql ="select count(*) from bb_focus";
        $result = $db->fetchOne($sql);
        $this->assertEqual(1,$result);
        
        $newobj->focus_guy($target_uid);
        $newobj->focus_guy($target_uid); // 第2次关注无效
        $newobj->focus_guy($target_uid); // 第3次关注无效
        
        $sql ="select count(*) from bb_focus ";
        $result = $db->fetchOne($sql);
        $this->assertEqual(2,$result);
        
        $this->assertEqual(2, $obj33->get_fensi_count());
        $this->assertTrue($newobj->has_focus($target_uid));
        $this->assertfalse($obj33->has_focus(2));
        
        
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
        $db = \BBExtend\Sys::get_container_db();
        $sql ="delete from bb_users";
        $db->query($sql);
        $sql ="delete from bb_focus";
        $db->query($sql);
        
    
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
        ]);
        $db->insert("bb_users", [
            'uid'=>33,
            'platform_id'=>'12',
        ]);
        $db->insert("bb_users", [
            'uid'=>2,
            'platform_id'=>'1',
        ]);
    
    }
        
}
