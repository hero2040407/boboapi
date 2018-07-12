<?php
namespace BBExtend\user;

/**
 * 关
 * 
 * 谢烨
 */

use BBExtend\Sys;
use think\Db;
use BBExtend\user\Task;
use BBExtend\BBUser;

use BBExtend\Currency;
use BBExtend\Level;
use BBExtend\BBMessage;

use BBExtend\message\Message;
use BBExtend\user\exp\Exp;
// use BBExtend\Level;

class TaskManager
{
    
   
    public $user;
    public $uid;
 //   public $message;
    public $level;     //有用啊。
    public $user_sex;
    
    public $db_data;   // 一行数据，概览
    
    public $task_list; // 当前列表，数组。//这个不固定啊
    
    public $has_refresh=0;//测试用参数，勿删
    
    
    public $select_arr =[]; // 这个数组也固定。
    public $select_obj_arr =[]; // 这个数组也固定。
    
    /**
     * 
     * @param number $uid
     */
    public function  __construct($uid=0) 
    {
        $uid = $this->uid = intval($uid);
       // $this->redis = Sys::getredis11();
        $this->user = BBUser::get_user($uid);
        $this->user_sex = $this->user['sex'];
        
        $db = Sys::get_container_db();
        $sql = "select level from bb_users_exp where uid = {$uid}";
        $this->level = $db->fetchOne($sql);
        $sql ="select * from bb_task_user where uid ={$uid}";
        $row = $db->fetchRow($sql);
        if (!$row) {
            throw new  \Exception('uid not found');
        }
        $this->db_data = $row;
        
        $this->task_list =[
            new Task( $this->get_id(0)),
            new Task( $this->get_id(1)),
            new Task( $this->get_id(2)),
        ];
    }
    
    //个人认证是否可以领奖。
    public function can_renzheng_lingjiang()
    {
        foreach(range(0,2) as $index) {
            $task_id = $this->get_id($index);
            if ($task_id == 3) {
                $reward = $this->get_reward($index);
                $complete = $this->get_reward($index);
                if ($complete==1 && $reward==1) {
                    return 1;
                }
            }
        }
        return 0;
    }
    
    public function success_state($task_id)
    {
        $arr = explode(',', $this->db_data['task_group']);
        if (in_array($task_id, $arr)) { // 任务确实存在
            foreach ($arr as $key => $v) {
                if ($v == $task_id) {
                    $index = $key;
                    break;
                }
            }
            return ['code'=>1, 'data'=>[
                'complete' => $this->get_complete($index),
                'reward' => $this->get_reward($index),
            ]];
            
        }else {
            return ['code' =>0 , 'message' => '任务不存在'];
        }
    }
    
    // 完成任务的接口
    public function success_complete($task_id)
    {
        $arr = explode(',', $this->db_data['task_group']);
        $uid = $this->uid;
        if (in_array($task_id, $arr)) { // 任务确实存在
            foreach ($arr as $key => $v) {
                if ($v == $task_id) {
                    $index = $key;
                    break;
                }
            }
            // 完成状态时已完成， 领奖状态时 1 可领奖。
            if ($this->get_complete($index) == 0 && $this->get_reward($index) == 0 ) {
                $this->set_reward($index,1);
                $this->set_complete($index,1);
                
                // xieye,不管了，只要活动完成，立刻把其加入到已完成任务表中。
                $this->add_complete_history($this->get_id($index) );
                
                
                $this->save();
                $task = $this->get_task($index);
                if ($task->state==0) { // 子线任务需要加经验
                   // Level::add_user_exp($this->uid, LEVEL_COMPLETE_TASK);
                    Exp::getinstance($uid)->set_typeint(Exp::LEVEL_COMPLETE_TASK)->add_exp();
                }else {
                    if ($task_id == 1) { //编辑资料
                        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_COMPLETE_CHANG_USERINFO)->add_exp();
                      //  Level::add_user_exp($uid,LEVEL_COMPLETE_CHANG_USERINFO);
                    }
                    if ($task_id == 2) { // 上传头像
                        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_COMPLETE_UPLOAD_PIC)->add_exp();
                 //       Level::add_user_exp($uid,LEVEL_COMPLETE_UPLOAD_PIC);
                    }
                    if ($task_id == 3) { // 个人认证
                    
                        BBUser::set_attestation($uid, 2);
                        \BBExtend\user\Tongji::getinstance($uid)->geren_renzheng_success();
                   //     Level::add_user_exp($uid, LEVEL_COMPLETE_ATTESTAION);
                        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_COMPLETE_ATTESTAION)->add_exp();
                    }
                    
                    
                }
                
                Message::get_instance()
                    ->set_title('系统消息')
                    ->add_content(Message::simple()->content("恭喜您完成任务"))
                    ->add_content(Message::simple()->content($task->title)->color(0xf4a560)  )
                    ->add_content(Message::simple()->content('，获得'))
                    ->add_content(Message::simple()->content("{$task->reward_count}BO币")->color(0xf4a560)  )
                    ->add_content(Message::simple()->content('奖励，请进入'))
                    ->add_content(Message::simple()->content("邀约")->color(0x32c9c9)
                            ->url(json_encode(['type'=>3 ]) )
                            )
                    ->add_content(Message::simple()->content('->查看任务列表领取奖励。'))
                    ->set_type(118)
                    ->set_uid($this->uid)
                    ->send();
                
                //任务完成推送
//                 $ContentDB = array();
//                 $ContentDB = BBMessage::AddMsg($ContentDB,
//                         '恭喜您完成任务'.$task->title.'请前往邀约点击任务领取奖励');
//                 BBMessage::SendMsg(\BBExtend\fix\Message::PUSH_MSG_TASK,'邀约任务完成',$ContentDB,$this->uid);
                
                return ["code"=>1];
            }else {
    
                return ['code'=>0,'message'=>'任务已完成，不能再完成一遍'];
            }
        }else {
            return ['code' =>0 , 'message' => '任务不存在'];
        }
    }
    
    // 领奖接口。
    public function success_reward($task_id)
    {
        $arr = explode(',', $this->db_data['task_group']);
        if (in_array($task_id, $arr)) { // 任务确实存在
            foreach ($arr as $key => $v) {
                if ($v == $task_id) {
                    $index = $key;
                    break;
                }
            }
            // 完成状态时已完成， 领奖状态时 1 可领奖。
            if ($this->get_complete($index) == 1 && $this->get_reward($index) == 1 ) {
                $this->set_reward($index,2);
                $this->save();
                $task = $this->get_task($index);
                Currency::add_currency($this->uid, CURRENCY_GOLD,$task->reward_count ,'完成任务');
                $arr =[
                    'reward_type' =>0,
                    'reward_count' => $task->reward_count,
                ];
                return ["code"=>1, "data"=>$arr];
            }else {
                
                return ['code'=>0,'message'=>'任务未完成，不能领奖'];
            }
        }else {
            return ['code' =>0 , 'message' => '任务不存在'];
        }
    }
    
    
    /**
     * 看情况用，如果想调用多个方法，则不用此函数
     * 
     * @param unknown $uid
     */
    public static function getinstance($uid)
    {
        return new self($uid); 
    }
    
   // public function 
    
    
    public function get_list(){
        return [
          $this->get_juti(0),
            $this->get_juti(1),
            $this->get_juti(2),
            
        ];
    }
    
    public function get_juti($index) {
        $tid = $this->get_id($index);
        if (!$tid) {
            $task=[];
            $task['id'] = '0';
            $task['title'] = '暂时没有任务了';
            $task['info'] = '暂时没有任务了';
            return $task;
        }
        $task = new Task( $tid );
//         $complete = $this->get_complete($index);
//         $reward = $this->get_reward($index);
        return [
            'id' => strval($task->task_id),
            'title' => $task->title,
            'info'  => $task->info,
            'reward_type' => strval( $task->reward_type),
            'reward_count' => strval( $task->reward_count),
            'send_type' => strval( $task->send_type),
            'state'     => strval($task->state),
            'complete'  => strval($this->get_complete($index)),
            'reward'    => strval($this->get_reward($index)),
            'activity_id'  => (int)($task->act_id), 
            'type'      => strval($task->type),
            'min_age'   => strval($task->min_age),
            'max_age'   => strval($task->max_age),
            'big_pic'   => strval($task->big_pic),
            'video_path'=> strval($task->video_path),
            'label'     => strval($task->label ),
            'level'     => strval($task->level ),
            
        ];
        
    }
    
    public function sort_list()
    {
        // 谢烨，这里的函数只返回最终记录，但是对表不影响，只是输出顺序的改变。
        // 改变成什么样？
        $arr =[$this->get_id(0), $this->get_id(1), $this->get_id(2),   ];
        $new = [];
        foreach ($arr as $v) {
            if ($v==0) {
                $new[]= 10000;
            }else {
                $new[]= $v;
            }
        }
        // 现在排序
        sort($new);
        $arr =[];
        foreach ($new as $v) {
            if ($v==10000) {
                
                $temp = new  Task( 0);
                
                $arr[]= $temp;
            }else {
//                 $arr[] = new Task($v);
                $temp = new  Task( $v);
              //  $temp->complete = 
                $arr[] = $temp;
            }
        }
        return $arr;
    }
    
    //随机弹出一个数组中的对象。
    public function pop_task()
    {
        $temp = $this->select_arr;
        if (!$temp) {
            return null;
        }
        
        $key = array_rand($temp);
        
        $temp2 = array_keys($temp);
        $key=$temp2[0];
        
        $t = $this->select_obj_arr[$key];
        unset($this->select_arr[$key]);
        unset($this->select_obj_arr[$key]);
        
        return $t;
    }

    public function refresh_list()
    {
        $refresh_time = $this->db_data['refresh_time'];
        if ($refresh_time > time()) {
            return $this;
        }
        
        // 计算排除arr，
        $row = $this->db_data;
        $arr1 = explode(',', $row['complete_task_group']);
        $arr2 = explode(',', $row['task_group']);
        $paichu_arr = array_merge($arr1, $arr2);
        
        $this->has_refresh=1;
        
        //xieye,这里就把数组定义好，且最重要的！，每次刷新，把该数组中去除！！！！！
        // 先查活动表
        $not_in = implode(',', $paichu_arr);
        $uid = $this->uid;
        $db = Sys::get_container_db();
        $sql ="
                select task_id from bb_task_activity where is_remove=0
                and is_send_reward=0
and (UNIX_TIMESTAMP() between  start_time and end_time )
and exists (select 1 from bb_task where bb_task.id =
   bb_task_activity.task_id and bb_task.is_remove=0
 )
and  not exists (select 1 from bb_user_activity
where  bb_user_activity.activity_id = bb_task_activity.id
  and bb_user_activity.uid = {$this->uid}
) 
 
and not exists (select 1 from bb_record where 
 bb_record.type=2 
 and bb_record.activity_id = bb_task_activity.id
 and bb_record.audit=1
  and bb_record.uid = {$this->uid}
)
and task_id not in  ({$not_in})
                ";
        //Sys::debugxieye($sql);
        $arr1 = $db->fetchCol($sql);
        $sql = "select id from bb_task where is_remove=0
                and not exists (
  select 1 from bb_task_activity
   where bb_task_activity.task_id =
    bb_task.id
)
                and id not in  ({$not_in})
                ";
        $arr2 = $db->fetchCol($sql);
        
        $arr2=[]; // 暂时操作，2016 11 05
        
        $arr = array_merge($arr1, $arr2);
        $arr = array_unique($arr); // 新的数组
        sort($arr);
        $new_arr =[];
        $new_obj_arr=[];
        foreach ($arr as $v) { // 此时，删除和 起始时间都已经考虑到，正在进行，已完成都已经考虑到。
            $result = true;
            $task = new task($v);
            if ($task->level && $task->level > $this->get_level()) {
          //      Sys::debugxieye("level err");
                $result = false;
            }
            if ($task->min_age && $task->min_age > $this->get_age() ) {
         //       Sys::debugxieye("min_age err");
                $result = false;
            }
            if ($task->max_age && $task->max_age < $this->get_age() ) {
         //       Sys::debugxieye("max_age err");
                $result = false;
            }
            if ($task->sex ==0 &&  $this->user_sex==1 ) {
         //       Sys::debugxieye("sex1 err");
                $result = false;
            }
            if ($task->sex ==1 &&  $this->user_sex==0 ) {
           //     Sys::debugxieye("sex2 err");
                $result = false;
            }
            if ($result) {
                $new_arr[] = $v;
                $new_obj_arr[]= $task;
            }
        }
      //  dump($new_arr);
        
        $this->select_arr = $new_arr;
        $this->select_obj_arr = $new_obj_arr;
         $this->refresh(0);
     //    dump($this->db_data['task_group']);
        $this->refresh(1);
     //   dump($this->db_data['task_group']);
        $this->refresh(2);
     //   dump($this->db_data['task_group']);
        $this->db_data['refresh_time'] = strtotime(date('Ymd')) + 104400;//获得今天凌晨5点的时间戳
        $this->save();
        return $this->sort_list();
    }
    
    // 这个过程中，需要先排除，当前的成员，排除级别和年龄。
    public function refresh($index)
    {
       // dump( $index);
        // 先判断是否完成，如果完成，需添加到已完成
        if ($this->get_complete($index) ==1 && $this->get_reward($index) == 2 ) {
           
            $this->add_complete_history($this->get_id($index));
        }
        $task = $this->get_task($index);
       // dump($task);
        if ($task->state == 1 ) { //主线任务
            //如果完全完成，则刷新
           if ( $this->get_complete($index) ==1 && $this->get_reward($index) == 2) {
               $result = $this->pop_task();
               if ($result) {
                   $task_id = $result->task_id;
//                    echo $task_id;
                   $this->set_id($index, $task_id);
                   $this->set_task($index, $result);
                   $this->set_reward($index,0);
                   $this->set_complete($index,0);
               }else {
                   $task_id = 0;
                   $this->set_id($index, $task_id);
                   $this->set_task($index, new Task(0));
                   $this->set_reward($index,0);
                   $this->set_complete($index,0);
               }
           }
        }else { //子线任务
            $result = $this->pop_task();
            if ($result) {
                $task_id = $result->task_id;
                $this->set_id($index, $task_id);
                $this->set_task($index, $result);
                $this->set_reward($index,0);
                $this->set_complete($index,0);
            }else {
                $task_id = 0;
                $this->set_id($index, $task_id);
                $this->set_task($index, new Task(0));
                $this->set_reward($index,0);
                $this->set_complete($index,0);
            }
        }
        
        
    }
    
    
   
   
    public function save()
    {
        $db = Sys::get_container_db();
        $uid = $this->uid;
        $db->update('bb_task_user', $this->db_data, "uid = {$uid}");
        return $this;
    }
    
    
    public function add_complete_history($task_id)
    {
        $arr = explode(',', $this->db_data['complete_task_group']);
        $arr[]= $task_id;
        $arr = array_unique($arr);
        $this->db_data['complete_task_group'] = implode(',', $arr);
    }
    
    public function get_complete_history()
    {
        return $this->db_data['complete_task_group'];
    }
    
    
    
    
    //设置第index项已完成
    public function set_complete($index, $value=1)
    {
        $arr = explode(',', $this->db_data['complete']);
        $arr[$index] =$value;
    
        $this->db_data['complete'] = implode(',', $arr);
        return $this;
    }
    
    public function get_complete($index)
    {
        $arr = explode(',', $this->db_data['complete']);
        return $arr[$index];
    }
    
    
    //0不可领取，1可以领取，2已经领取。
    public function set_reward($index,$reward_type=2)
    {
        $arr = explode(',', $this->db_data['reward']);
        $arr[$index] =$reward_type;
    
        $this->db_data['reward'] = implode(',', $arr);
        return $this;
    }
    
    
    public function get_reward($index)
    {
        $arr = explode(',', $this->db_data['reward']);
        return $arr[$index];
    }
    
    
    
    public function set_id($index,$task_id)
    {
        $arr = explode(',', $this->db_data['task_group']);
        $arr[$index] =$task_id;
    
        $this->db_data['task_group'] = implode(',', $arr);
        return $this;
    }
    
    public function get_id($index)
    {
        $arr = explode(',', $this->db_data['task_group']);
        return $arr[$index];
    }
    
    
    
    
    
    
    //返回 Task对象，在同一级目录
    public function set_task($index,Task $task)
    {
        $this->task_list[$index] = $task;
        return $this;
    }
    
    //返回 Task对象，在同一级目录
    public function get_task($index)
    {
        $temp = $this->task_list;
        if (isset($temp[$index])) {
            return $temp[$index];
        }
    }
    
    public function get_age()
    {
        return date('Y') -  substr($this->user['birthday'] ,0, 4) ;
    }
    
    public function get_level()
    {
        return $this->level;
    }
    
    
}