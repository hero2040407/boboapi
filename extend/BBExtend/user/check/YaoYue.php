<?php
namespace BBExtend\user\check;


use BBExtend\Sys;
use think\Db;
use BBExtend\user\check\Record;
use BBExtend\user\Activity;
use BBExtend\user\TaskManager;
/**
 * 
 * 
 * @author Administrator
 *
 */
class YaoYue extends Record
{
    public $task_id;
    public $activity;
    
    public function __construct($record_arr) {
        parent::__construct($record_arr);
        $this->type=2;
        
        $this->activity = Db::table('bb_task_activity')->where('id', $this->act_id)->find();
        if (!$this->activity) {
            throw  new \Exception('activity not found');
        }
        $this->task_id = $this->activity['task_id'];
        
    }
    
    public function success()
    {
//         echo "yaoyue_success_" . $this->record_arr['type'] .'_'.$this->record_arr['title'];  

        // 如果已经认证过，绝对不允许再做任何操作。
        //方法,
       
        if ($this->has_success()) { //重要啊，必须检查。
            $this->message ='该用户已经有认证视频';
            $this->result = false;
            return false;
        }
        // 否则，需要把视频的audit设为1
        $db = Sys::get_container_db();
        $sql ="update bb_record set audit=1 where id = " . $this->record_id;
        $db->query($sql);
        
        //然后，修改用户参加记录
        $user_activity = new Activity($this->uid, $this->act_id );
        $user_activity->canjia($this->act_id);
        $user_activity->checked();
        
        //把任务置为已完成
        $manager = TaskManager::getinstance($this->uid);
        $manager->success_complete($this->task_id); // 会自动设置个人认证的用户状态
        
        
        return true;
        
    }
    
    public function fail()
    {
        echo "yaoyue_fail_" . $this->record_arr['type'] .'_'.$this->record_arr['title'];
    }
    
    private function has_success()
    {
        $db = Sys::get_container_db();
        $sql ="select * from bb_record 
                where type = {$this->type} 
                  and audit=1 
                  and uid={$this->uid}
                  and activity_id =  {$this->act_id}
              " ;
        return $db->fetchOne($sql);
    }
    
}