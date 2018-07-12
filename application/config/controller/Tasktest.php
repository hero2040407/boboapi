<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;
use BBExtend\user\Task;
use BBExtend\user\TaskManager;
//仅限自机（其实是200） 和 200
in_array(Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
class Tasktest extends \UnitTestCase
{
    public function index()
    {
      Sys::display_all_error();   
    }
    
    public function test_create1()
    {
        $temp =TaskManager::getinstance(1);
        $temp1 = $temp->get_task(0);
        $temp2 = $temp->get_task(1);
        $temp3 = $temp->get_task(2);
        
        $this->assertEqual($temp1->title, '编辑资料');
        $this->assertEqual($temp1->task_id, 1);
        $this->assertEqual($temp2->task_id, 2);
        $this->assertEqual($temp3->task_id, 3);
     
        //因为时间未到，验证刷新无效
        $temp->refresh_list();
        $this->assertEqual($temp->has_refresh, 0);
        //人为修改数据库，使得时间改变
        
        //本方法只改1个。
        $temp->set_complete(0,1)->set_reward(0,2);
        $temp->save();
        
        $this->assertEqual($temp->get_complete(0), 1);
        $this->assertEqual($temp->get_complete(1), 0);
        $this->assertEqual($temp->get_complete(2), 0);
        $this->assertEqual($temp->get_reward(0), 2);
        $this->assertEqual($temp->get_id(0), 1);
        $this->assertEqual($temp->get_id(1), 2);
        $this->assertEqual($temp->get_id(2), 3);
        
        $temp =TaskManager::getinstance(1);
        $this->assertEqual($temp->get_complete(0), 1);
        $this->assertEqual($temp->get_reward(0), 2);
        
        
        
        $db = Sys::get_container_db();
        $sql ='update bb_task_user set refresh_time =1 where uid =1';
        $db->query($sql);
        
        
        // 谢烨，把第1项任务，改成已完成，且领取了奖励。
        //谢烨，把第2项任务，改成已完成，且领取了奖励。
        $temp =TaskManager::getinstance(1);
        $temp->refresh_list();
        //
        $this->assertEqual($temp->has_refresh, 1);
        //断言数据有修改。
        $this->assertEqual($temp->get_complete(0), 0);
        $this->assertEqual($temp->get_complete(1), 0);
        $this->assertEqual($temp->get_complete(2), 0);
        
        //断言 20被接受
        $this->assertEqual($temp->get_id(0), 20);
        $this->assertEqual($temp->get_id(1), 2);
        $this->assertEqual($temp->get_id(2), 3);
        $task = $temp->get_task(0);
        $this->assertEqual($task->task_id, 20);
        
        $arr = $temp->get_list(); 
        $temp1 = $arr[0];
        $temp2 = $arr[1];
        $temp3 = $arr[2];
        
        $this->assertEqual($temp1['activity_id'], 0);
    }
    
    public function test_create2()
    {
        $temp =TaskManager::getinstance(1);
        $temp->set_complete(0,1)->set_reward(0,2);
        $temp->set_complete(1,1)->set_reward(1,2);
        
        $temp->save();
    
       
        $db = Sys::get_container_db();
        $sql ='update bb_task_user set refresh_time =1 where uid =1';
        $db->query($sql);
        $temp =TaskManager::getinstance(1); //必须这么写，因为时间未更新到对象！！
        // 谢烨，把第1项任务，改成已完成，且领取了奖励。
        //谢烨，把第2项任务，改成已完成，且领取了奖励。
        $temp->refresh_list();
        //
        $this->assertEqual($temp->has_refresh, 1);
        //断言数据有修改。
        $this->assertEqual($temp->get_complete(0), 0);
        $this->assertEqual($temp->get_complete(1), 0);
        $this->assertEqual($temp->get_complete(2), 0);
        $this->assertEqual($temp->get_reward(0), 0);
        $this->assertEqual($temp->get_reward(1), 0);
        $this->assertEqual($temp->get_reward(2), 0);
        
        
        //断言 11被接受
        $this->assertEqual($temp->get_id(0), 20);
        $this->assertEqual($temp->get_id(1), 55);
        $this->assertEqual($temp->get_id(2), 3);
        $task = $temp->get_task(0);
        $this->assertEqual($task->task_id, 20);
        $this->assertEqual($temp->get_complete_history(), '0,1,2');
        
        $list = $temp->sort_list();
        $this->assertEqual($list[0]->task_id, 3);
        $this->assertEqual($list[1]->task_id, 20);
        $this->assertEqual($list[2]->task_id, 55);
        
        $arr = $temp->get_list();
        $temp1 = $arr[0];
        $temp2 = $arr[1];
        $temp3 = $arr[2];
        $this->assertEqual($temp1['activity_id'], 0);
        $this->assertEqual($temp2['activity_id'], 18);
        $this->assertEqual($temp3['activity_id'], 13);
        $this->assertEqual($temp1['title'], "我的怪兽我创造");
        $this->assertEqual($temp2['title'], "你来搞怪，我卖萌");
//         $this->assertEqual($temp3['title'], 13);
        
        
        
        $this->assertTrue($temp->db_data['refresh_time'] >  time());
        
     }
    
    
      public function test3()
      {
          $temp =TaskManager::getinstance(1);
          $temp->set_complete(2,1)->set_reward(2,2);
          $temp->set_complete(1,1)->set_reward(1,2);
          
          $temp->save();
          
           
          $db = Sys::get_container_db();
          $sql ='update bb_task_user set refresh_time =1 where uid =1';
          $db->query($sql);
          $temp =TaskManager::getinstance(1); //必须这么写，因为时间未更新到对象！！
          // 谢烨，把第1项任务，改成已完成，且领取了奖励。
          //谢烨，把第2项任务，改成已完成，且领取了奖励。
          $temp->refresh_list();
          //
          $this->assertEqual($temp->has_refresh, 1);
          //断言数据有修改。
          $this->assertEqual($temp->get_complete(0), 0);
          $this->assertEqual($temp->get_complete(1), 0);
          $this->assertEqual($temp->get_complete(2), 0);
          $this->assertEqual($temp->get_reward(0), 0);
          $this->assertEqual($temp->get_reward(1), 0);
          $this->assertEqual($temp->get_reward(2), 0);
          
          
          //断言 11被接受
          $this->assertEqual($temp->get_id(0), 1);
          $this->assertEqual($temp->get_id(1), 20);
          $this->assertEqual($temp->get_id(2), 55);
          $task = $temp->get_task(0);
          $this->assertEqual($temp->get_complete_history(), '0,2,3');
          $this->assertTrue($temp->db_data['refresh_time'] >  time());
       
          
          //
          $temp =TaskManager::getinstance(1);
         
          $temp->set_complete(1,1)->set_reward(1,0);
          
          $temp->save();
          
           
          $db = Sys::get_container_db();
          $sql ='update bb_task_user set refresh_time =1 where uid =1';
          $db->query($sql);
          $temp =TaskManager::getinstance(1); //必须这么写，因为时间未更新到对象！！
          // 谢烨，把第1项任务，改成已完成，且领取了奖励。
          //谢烨，把第2项任务，改成已完成，且领取了奖励。
          $temp->refresh_list();
          //
          $this->assertEqual($temp->has_refresh, 1);
          $this->assertEqual($temp->get_id(0), 1);
          $this->assertEqual($temp->get_id(1), 57);
          $this->assertEqual($temp->get_id(2), 0);// 就算没完成，强制重新生成。
          
          $this->assertEqual($temp->get_complete(0), 0);
          $this->assertEqual($temp->get_complete(1), 0);
          $this->assertEqual($temp->get_complete(2), 0);
          $this->assertEqual($temp->get_reward(0), 0);
          $this->assertEqual($temp->get_reward(1), 0);
          $this->assertEqual($temp->get_reward(2), 0);
      }
    
    
      public function test_reward()
      {
          $uid=1;
          $temp =TaskManager::getinstance(1);
          $temp->set_complete(2,1)->set_reward(2,1);
//           $temp->set_complete(1,1)->set_reward(1,2);
          
          $temp->save();
          
          $temp =TaskManager::getinstance(1);
          $result= $temp->success_reward(3);
          //断言，奖励2状态
          //断言，用户钱数是30
//           dump($result['message']);

          $this->assertEqual(2, $temp->get_reward(2) );
          $this->assertEqual(1, $result['code']);
          
          $gold = \BBExtend\Currency::get_currency($uid);
          $this->assertEqual($gold['gold'], 31);
          
      }
      
      
      public function test_complete()
      {
          $uid=1;
          $temp =TaskManager::getinstance(1);
          $this->assertEqual(0, $temp->get_complete(2)  );
          $this->assertEqual(0, $temp->get_reward(2)  );
      
          $result= $temp->success_complete(3);
          //断言，奖励2状态
          //断言，用户钱数是30
          //           dump($result['message']);
          $this->assertEqual(1, $result['code']);
          $this->assertEqual(1, $temp->get_complete(2)  );
          $this->assertEqual(1, $temp->get_reward(2)  );
      
           $arr = $temp->success_state(3);
          $this->assertEqual(1, $arr['code']);
          $this->assertEqual(1, $arr['data']['complete']  );
          $this->assertEqual(1, $arr['data']['reward']  );
          
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
        
        
        $sql ="delete from bb_users_exp";
        $db->query($sql);
        $sql ="delete from bb_task_user";
        $db->query($sql);
        $sql ="delete from bb_task";
        $db->query($sql);
        $sql ="delete from bb_task_activity";
        $db->query($sql);
        
    
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
            'birthday' =>'2014-02',
        ]);
        $db->insert("bb_currency", [
            'uid'=>1,
            'gold' =>1 ,
        ]);
        $db->insert("bb_users_exp", [
            'uid'=>1,
            'level'=>'2',
        ]);
        
        
        $bb_user_task = ['uid'=> 1,'time'=> time(),'complete_task_group'=>'0',
            'complete'=>'0,0,0','reward'=>'0,0,0','task_group'=>'1,2,3',
            'refresh_time'=>strtotime(date('Ymd')) + 104400];
        $db->insert('bb_task_user', $bb_user_task);
        
        
        
        $db->insert("bb_task", [
            'id' =>1,
            'title' => '编辑资料',
            'is_remove'=>0,
            'level' =>   0,
            'min_age'=>  0,
            'max_age'=>  0,
            'reward_count' => 30,
            'reward_type'  => 0 ,
            'label' =>'[]' ,
            'state' =>1,
        ]);
        $db->insert("bb_task", [
            'id' =>2,
            'title' => '上传头像',
            'is_remove'=>0,
            'level' =>   0,
            'min_age'=>  0,
            'max_age'=>  0,
            'reward_count' => 30,
            'reward_type'  => 0 ,
            'label' =>'[]' ,
            'state' =>1,
        ]);
        $db->insert("bb_task", [
            'id' =>3,
            'title' => '个人认证',
            'is_remove'=>0,
            'level' =>   0,
            'min_age'=>  0,
            'max_age'=>  0,
            'reward_count' => 30,
            'reward_type'  => 1 ,
            'label' =>'[]' ,
            'state' =>1,
        ]);
        
        $db->insert("bb_task", [
            'id' =>11,
            'title' => '盛夏梦想，我才我秀',
            'is_remove'=>0,
            'level' =>   0,
            'min_age'=>  0,
            'max_age'=>  0,
            'reward_count' => 30,
            'reward_type'  => 0 ,
            'label' =>'[]' ,
            'state' =>0,
        ]);
        
        $db->insert("bb_task", [
            'id' =>20,
            'title' => '我的怪兽我创造',
            'is_remove'=>0,
            'level' =>   0,
            'min_age'=>  0,
            'max_age'=>  0,
            'reward_count' => 30,
            'reward_type'  => 0 ,
            'label' =>'[]' ,
            'state' =>0,
        ]);
        $db->insert("bb_task", [
            'id' =>55,
            'title' => '你来搞怪，我卖萌',
            'is_remove'=>0,
            'level' =>   0,
            'min_age'=>  0,
            'max_age'=>  0,
            'reward_count' => 30,
            'reward_type'  => 0 ,
            'label' =>'[]' ,
            'state' =>0,
        ]);
        $db->insert("bb_task", [
            'id' =>57,
            'title' => '动物模仿秀',
            'is_remove'=>0,
            'level' =>   0,
            'min_age'=>  0,
            'max_age'=>  0,
            'reward_count' => 30,
            'reward_type'  => 0 ,
            'label' =>'[]' ,
            'state' =>0,
        ]);
        
        
        
        $db->insert("bb_task_activity", [
            'id' =>13,
            'title' => '个人认证',
            'reward_id' => '0,0,0',
            'type' =>0,
            'bigpic_list'  => '[{"picpath":"123.jpg","title":"","linkurl":""}]',
            'reward'       =>50,
            'task_id'      => 3,
            'min_age'      =>0,
            'max_age'      =>12,
            'level'        =>0,
            'start_time'   => time(),
            'end_time'     => time()+ 23* 3* 3600,
            'sex'          =>2,
            'is_remove'=>0,
        ]);
        
        // xieye,这里人为调大年龄。
        $db->insert("bb_task_activity", [
            'id' =>14,
            'title' => '盛夏梦想，我才我秀',
            'reward_id' => '23,24,29,0,0',
            'type' =>0,
            'bigpic_list'  => '[{"picpath":"123.jpg","title":"","linkurl":""}]',
            'reward'       =>500,
            'task_id'      => 11,
            'min_age'      =>11,
            'max_age'      =>12,
            'level'        =>0,
            'start_time'   => time(),
            'end_time'     => time()+ 23* 3* 3600,
            'sex'          =>2,
            'is_remove'=>0,
        ]);
        
        //这是无效的数据
        $db->insert("bb_task_activity", [
            'id' =>15,
            'title' => '我是运动小健将',
            'reward_id' => '0,0,0',
            'type' =>0,
            'bigpic_list'  => '[{"picpath":"123.jpg","title":"","linkurl":""}]',
            'reward'       =>500,
            'task_id'      => 0,
            'min_age'      =>0,
            'max_age'      =>12,
            'level'        =>0,
            'start_time'   => time(),
            'end_time'     => time()+ 23* 3* 3600,
            'sex'          =>2,
            'is_remove'=>0,
        ]);
        
        
        $db->insert("bb_task_activity", [
            'id' =>18,
            'title' => '你来搞怪，我卖萌',
            'reward_id' => '0,0,0',
            'type' =>0,
            'bigpic_list'  => '[{"picpath":"123.jpg","title":"","linkurl":""}]',
            'reward'       =>5000,
            'task_id'      => 55,
            'min_age'      =>0,
            'max_age'      =>12,
            'level'        =>0,
            'start_time'   => time(),
            'end_time'     => time()+ 23* 3* 3600,
            'sex'          =>2,
            'is_remove'=>0,
        ]);
        
    }
   
}
