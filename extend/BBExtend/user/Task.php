<?php
namespace BBExtend\user;

/**
 * 任务抽象类，既可以有自己，也包括活动表。
 * 
 * 
 * 谢烨
 */

use BBExtend\Sys;
// use think\Db;
// use BBExtend\Level;
use app\record\controller\Recordmanager;

class Task 
{
    public $uid;
    
//     public $info;
//     public $title;  //任务名                      以任务表为准
    public $state; // 1主线任务，0子线   
    public $task_id;      // task_id       以任务表为准。
    
    public $min_age; //              活动以活动表为准
    public $max_age;                 //活动以活动表为准
    public $level;                   //活动以活动表为准
    
   public $act_id;  //活动，可能为0，
    
   public $sex; 
    
    public $complete;
    public $reward;
    public $id;
    public $title;
    public $info;
    public $activity_id;
    public $video_path;
    public $big_pic;
    public $reward_count;
    public $reward_type;
    public $send_type;
    public $label;
    public $type;
    //video_path
   
    /**
     * 
     * 谢烨，这张表有两个来源，1个是活动，1个是任务，虽然目前没有
     * 
     * @param number $uid
     */
    public function  __construct($task_id=0) {
     //  $this->uid = $uid;
       
        
        
        
       $this->task_id =  $task_id = $this->id = intval($task_id);
       if ($task_id==0) {
           $this->task_id=0;
           
           
           
           return;
       }
       
        
       $db = Sys::get_container_db();
       
       $sql ="select * from bb_task_activity where task_id ={$task_id}";
       $row = $db->fetchRow($sql);
       if ($row) {
           //此时进入活动
            $sql ="select * from bb_task where id ={$task_id}";
            $row2 = $db->fetchRow($sql);
           
           
           $this->title = $row['title'];
           $this->info = $row['info'];
           $this->reward_type = $row2['reward_type'];
           $this->reward_count = intval( $row2['reward_count']);
           $this->send_type = $row2['send_type'];
           $this->label = $row2['label'];
           $this->type = $row2['type'];
           
           
           if (in_array($task_id, [1,2,3])) {
               $this->state=1;// 强制主线。
           } else {
               $this->state=0;// 强制子线。
           }
           $this->act_id = $row['id'];
           $this->min_age = intval($row['min_age']);
           $this->max_age = intval($row['max_age']);
           $this->level = intval($row['level']);
           $this->sex = intval($row['sex']);
           //两个关键字段
           $RecodDB = Recordmanager::get_movieds_by_room_id($row['room_id']);
           if ($RecodDB)
           {
               $this->video_path = $RecodDB['video_path'];
               // 谢烨20160927
               $Pic = $RecodDB['big_pic'];
               $serverUrl = \BBExtend\common\BBConfig::get_server_url();
               
               $this->big_pic =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                       $Pic, $RecodDB['thumbnailpath'] );
               
              
           
           }
           
           return;
       }
       
       
       $sql ="select * from bb_task where id ={$task_id}";
       $row = $db->fetchRow($sql);
       if (!$row) {
         //  echo $sql;
           throw new  \Exception('task_id not found');
       }
       $this->title = $row['title'];
       $this->info = $row['info'];
       $this->reward_type = $row['reward_type'];
       $this->reward_count = intval($row['reward_count']);
       $this->send_type = $row['send_type'];
       $this->label = $row['label'];
       $this->type = $row['type'];
       
       
       $this->state = $row['state'];
       $this->act_id=0;// 表示这是任务。
       
       $this->min_age = intval($row['min_age']);
       $this->max_age = intval($row['max_age']);
       $this->level = intval($row['level']);
       $this->sex = 2;
      // $this->id = $task_id;
       
    }
    
    public function output()
    {
        
    }
    
    
    
}