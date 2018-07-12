<?php
namespace app\config\controller;
//仅限自机（其实是200） 和 200
in_array(\BBExtend\Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');

use think\Config;
use BBExtend\user\Activity;
use think\Db;
use BBExtend\user\RecordCheck;
use BBExtend\Sys;
/**
 * 
 * 测试 sign方案的正确性
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Activitychecktest  extends \UnitTestCase
{
    public function index()
    {
        
    }
    
   public function test1()
   {
//        $this->asserttrue(1);
       $check_obj = new RecordCheck(100,1);
      $result=  $check_obj->check();
     // dump($result);
      $this->assertEqual($result, true);
      
      // 断言，视频 的audit被改1.
      $db=Sys::get_container_db();
      $sql="select audit from bb_record where id=100";
      $this->assertEqual(1, $db->fetchOne($sql));
      
      $result=  $check_obj->check();
      $this->assertEqual($result, false);
      $this->assertEqual($check_obj->message, '该用户已经有认证视频');
        
     // $url='http://127.0.0.1/'
      
   }
   
   public function test2()
   {
       Sys::display_all_error();
       $url='http://127.0.0.1/record/recordmanager/cer_movies/id/100/audit/1';
       $result = file_get_contents($url);
      // dump($result);
       $result = json_decode($result,1);
      // dump($result);
       $this->assertEqual($result['code'], 1);
       $url='http://127.0.0.1/record/recordmanager/cer_movies/id/100/audit/1';
       $result = file_get_contents($url);
       $result = json_decode($result,1);
       
       $this->assertEqual($result['code'], 0);
       $this->assertEqual($result['message'], '该用户已经有认证视频');
        
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
        
        $db->query("delete from bb_record");
        $db->query("delete from bb_user_activity");
        $db->query("delete from bb_user_activity_reward");
        $db->query("delete from bb_task_user");
        $db->query("delete from bb_task");
        $db->query("delete from bb_task_activity");
        
        $db->query("delete from bb_users");
        $db->query("delete from bb_currency");
        $db->query("delete from bb_users_exp");
        
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
        ]);
        $db->insert("bb_users_exp", [
            'uid'=>1,
            'level'=>'1',
            'exp'  =>0,
        ]);
        $db->insert("bb_currency", [
            'uid'=>1,
            'gold'=>'0',
        ]);
        // 这是个人任务。
        $bb_user_task = ['uid'=> 1,'time'=> time(),'complete_task_group'=>'0',
            'complete'=>'0,0,0','reward'=>'0,0,0','task_group'=>'1,2,3',
            'refresh_time'=>strtotime(date('Ymd')) + 104400];
        Db::table('bb_task_user')->insert($bb_user_task);
        
        // 活动添加
        $db->insert('bb_task_activity', [
            'id'=>2,
            'sex' =>0,
            'is_send_reward' =>0,
            'start_time' => time()-10,
            'end_time' => time()+ 24*3600,
            'level' => 0,
            'type' =>0 , //活动擂台赛。
            'task_id' => 0,
            'title'=>'测试活动',
        ]);
        
        // 视频
        $db->insert('bb_record', [
            'type'=>2,
            'audit' => 0,
            'title' => '测试视频',
            'activity_id' => 2,
            'room_id' =>'2room',
            'id' => 100,
            'uid' => 1,
        ]);
        
        $db->insert('bb_task', [
            'id'=>1,
            'title'=>'编辑资料',
        ]);
        $db->insert('bb_task', [
            'id'=>2,
            'title'=>'上传头像',
        ]);
        $db->insert('bb_task', [
            'id'=>3,
            'title'=>'个人认证',
        ]);
        
    }
    
    
}

