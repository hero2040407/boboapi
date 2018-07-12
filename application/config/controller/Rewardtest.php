<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;

use BBExtend\user\ActivityReward;
use BBExtend\user\ActivityRewardManager;

// use BBExtend\user\Task;
// use BBExtend\user\TaskManager;
//仅限自机（其实是200） 和 200
in_array(Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
class Rewardtest extends \UnitTestCase
{
    public function index()
    {
      Sys::display_all_error();   
    }
    
    public function test_create1()
    {
        $uid = 1;
        $act_id =13;
        
        $db = Sys::get_container_db();
        $sql='update bb_task_activity set is_send_reward=1';
        $db->query($sql);   
        $manager = new ActivityRewardManager($act_id);
        $result = $manager->reward();
        $this->assertEqual($result['code'], 0);
        $this->assertEqual($result['message'], '该活动已经发放过奖励');
        $sql='update bb_task_activity set is_send_reward=0';
        $db->query($sql);
        
        $manager = new ActivityRewardManager($act_id);
        $result = $manager->reward();
        
        // 断言bb_user_activity表有2行
        $this->assertEqual($result['code'], 1);
        $this->assertEqual($result['data']['count'], 2);
        // 谢烨，测试type=0，pk擂台，则第一名500， 
        $sql ="select * from bb_user_activity_reward order by paiming asc";
        $result = $db->fetchAll($sql);
        $obj1 = $result[0];
        $obj2 = $result[1];
        $this->assertEqual($obj1['uid'], 3);
        $this->assertEqual($obj1['activity_id'], 13);
        $this->assertEqual($obj1['has_reward'], 1);
        $this->assertEqual($obj1['reward_count'], 100);
        $this->assertEqual($obj1['paiming'], 1);
        $this->assertEqual($obj1['record_id'], 3);
        $this->assertEqual($obj1['room_id'], '3room');
        $this->assertEqual($obj1['like_count'], 30);
        
        $this->assertEqual($obj2['uid'], 2);
        $this->assertEqual($obj2['activity_id'], 13);
        $this->assertEqual($obj2['has_reward'], 1);
        $this->assertEqual($obj2['reward_count'], 6);
        $this->assertEqual($obj2['paiming'], 2);
        $this->assertEqual($obj2['record_id'], 2);
        $this->assertEqual($obj2['room_id'], '2room');
        $this->assertEqual($obj2['like_count'], 20);
        
    }
    
    //测试  type =1
    public function test2()
    {
        $uid = 1;
        $act_id =13;
        $db = Sys::get_container_db();
        $sql='update bb_task_activity set type=1';
        $db->query($sql);
        
        $manager = new ActivityRewardManager($act_id);
        $result = $manager->reward();
        
        // 断言bb_user_activity表有2行
        $this->assertEqual($result['code'], 1);
        $this->assertEqual($result['data']['count'], 2);
        // 谢烨，测试type=0，pk擂台，则第一名500，
        $sql ="select * from bb_user_activity_reward order by paiming asc";
        $result = $db->fetchAll($sql);
        $obj1 = $result[0];
        $obj2 = $result[1];
        $this->assertEqual($obj1['uid'], 3);
        $this->assertEqual($obj1['activity_id'], 13);
        $this->assertEqual($obj1['has_reward'], 1);
        $this->assertEqual($obj1['reward_count'], 100);
        $this->assertEqual($obj1['paiming'], 1);
        $this->assertEqual($obj1['record_id'], 3);
        $this->assertEqual($obj1['room_id'], '3room');
        $this->assertEqual($obj1['like_count'], 30);
        
        $this->assertEqual($obj2['uid'], 2);
        $this->assertEqual($obj2['activity_id'], 13);
        $this->assertEqual($obj2['has_reward'], 1);
        $this->assertEqual($obj2['reward_count'], 100);
        $this->assertEqual($obj2['paiming'], 2);
        $this->assertEqual($obj2['record_id'], 2);
        $this->assertEqual($obj2['room_id'], '2room');
        $this->assertEqual($obj2['like_count'], 20);
    }
      
    //测试  type =2
    public function test3()
    {
        $uid = 1;
        $act_id =13;
        $db = Sys::get_container_db();
        $sql='update bb_task_activity set type=2';
        $db->query($sql);
    
        $manager = new ActivityRewardManager($act_id);
        $result = $manager->reward();
    
        // 断言bb_user_activity表有2行
        $this->assertEqual($result['code'], 1);
        $this->assertEqual($result['data']['count'], 2);
        // 谢烨，测试type=0，pk擂台，则第一名500，
        $sql ="select * from bb_user_activity_reward order by paiming asc";
        $result = $db->fetchAll($sql);
        $obj1 = $result[0];
        $obj2 = $result[1];
        $this->assertEqual($obj1['uid'], 3);
        $this->assertEqual($obj1['activity_id'], 13);
        $this->assertEqual($obj1['has_reward'], 1);
        $this->assertEqual($obj1['reward_count'], 50);
        $this->assertEqual($obj1['paiming'], 1);
        $this->assertEqual($obj1['record_id'], 3);
        $this->assertEqual($obj1['room_id'], '3room');
        $this->assertEqual($obj1['like_count'], 30);
    
        $this->assertEqual($obj2['uid'], 2);
        $this->assertEqual($obj2['activity_id'], 13);
        $this->assertEqual($obj2['has_reward'], 1);
        $this->assertEqual($obj2['reward_count'], 30);
        $this->assertEqual($obj2['paiming'], 2);
        $this->assertEqual($obj2['record_id'], 2);
        $this->assertEqual($obj2['room_id'], '2room');
        $this->assertEqual($obj2['like_count'], 20);
        
        // 断言消息没有发。
        $sql ="select count(*) from bb_user_activity_reward where has_message=1";
        $this->assertEqual($db->fetchOne($sql), 0);
        //断言活动的发放数据设为1
        
        $sql = "select * from bb_task_activity ";
        $row = $db->fetchRow($sql);
        $this->assertEqual($row['is_send_reward'], 1);
        //再发放断言失败
        $manager = new ActivityRewardManager($act_id);
        $result = $manager->reward();
        $this->assertEqual($result['code'], 0);
        $this->assertEqual($result['message'], '该活动已经发放过奖励');
    }
    
    public function test_send_message()
    {
        $uid = 1;
        $act_id =13;
        $db = Sys::get_container_db();
       
        
        $manager = new ActivityRewardManager($act_id);
        $result = $manager->process();
        $sql ="select count(*) from bb_user_activity_reward where has_message=1";
        $this->assertEqual($db->fetchOne($sql), 2);
        //断言，
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
        
        $sql ="delete from bb_task_activity";
        $db->query($sql);
        $sql ="delete from bb_user_activity_reward";
        $db->query($sql);
        $sql ="delete from bb_record";
        $db->query($sql);
        
        
    
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
            'birthday' =>'2014-02',
        ]);
        $db->insert("bb_users", [
            'uid'=>2,
            'platform_id'=>'2',
            'birthday' =>'2014-02',
        ]);
        $db->insert("bb_users", [
            'uid'=>3,
            'platform_id'=>'3',
            'birthday' =>'2014-02',
        ]);
        $db->insert("bb_currency", [
            'uid'=>1,
            'gold' =>0 ,
        ]);
        $db->insert("bb_currency", [
            'uid'=>2,
            'gold' =>0 ,
        ]);
        $db->insert("bb_currency", [
            'uid'=>3,
            'gold' =>0 ,
        ]);
        
        $db->insert("bb_record", [
            'id' => 2,
            'uid'=>2,
            'is_remove'=>'0',
            'audit'=>'1',
            'like' => 20,
            'activity_id' => 13,
            'type'=>2,
            'room_id' => '2room',
        ]);
        $db->insert("bb_record", [
            'id' => 3,
            'uid'=>3,
            'is_remove'=>'0',
            'audit'=>'1',
            'like' => 30,
            'activity_id' => 13,
            'type'=>2,
            'room_id' =>'3room',
        ]);
        
        
        $db->insert("bb_task_activity", [
            'id' =>13,
            'title' => '个人认证',
            'reward_id' => '0,0,0',
            'type' =>0,
            'bigpic_list'  => '[{"picpath":"123.jpg","title":"","linkurl":""}]',
            'reward'       =>100,
            'task_id'      => 3,
            'min_age'      =>0,
            'max_age'      =>12,
            'level'        =>0,
            'start_time'   => time(),
            'end_time'     => time()+ 23* 3* 3600,
            'sex'          =>2,
            'is_remove'=>0,
            'is_send_reward' =>0,
        ]);
        
      
        
    }
   
}
