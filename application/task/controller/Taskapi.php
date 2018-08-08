<?php
namespace app\task\controller;

use think\Db;

use BBExtend\BBUser;
use BBExtend\user\TaskManager;
use BBExtend\user\Task;

/**
 * 任务api
 * Created by PhpStorm.
 * 
 * @author xieye
 */
class Taskapi 
{

    /**
     * 获取每天3个的任务列表
     */
    public function get_list($uid=0)
    {
        $user_taskDB = self::get_user_task($uid);
        if ($user_taskDB)
        {
            $manager = TaskManager::getinstance($uid);
            $manager->refresh_list();
            $data=[];
            $data['task_list'] = $manager->get_list();
            return ['data'=>$data,'code'=>1];
        }
        return['message'=>'没有这个用户的任务信息','code'=>0];
    }
    
    
    /**
     * 获取认证信息，专为个人信息页面的个人认证点击判断用，
     * @param unknown $uid
     */
    public function get_renzheng($uid)
    {
        $uid = intval($uid);
        $user = BBUser::get_user($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'用户不存在'];
        }
        
        $task = new Task( 3 );
        $data= [
            'attestation'   =>  $user['attestation'],
            'title' => $task->title,
            'info'  => $task->info,
            'reward_count' => strval( $task->reward_count),
            'need_reward'  => 0,
            'activity_id'  => (int)($task->act_id),
            'big_pic'   => strval($task->big_pic),
            'video_path'=> strval($task->video_path),
            'label'     => strval($task->label ),
        ];
        return ['data'=> $data, 'code'=>1 ];
    }
        
    
    /**
     * 用户手动领取奖励
     */
    public function get_reward()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $task_id = input('?param.task_id')?(int)input('param.task_id'):0;
        $user_taskDB = self::get_user_task($uid);
        if ($user_taskDB) {
            $manager = TaskManager::getinstance($uid);
            return $manager->success_reward($task_id);
        }
        return['message'=>'当前用户没有这个任务','code'=>0];
    }

    
    /**
     * 返回任务状态
     * 
     * 结果如下：
     * return ['code'=>1, 'data'=>[
     *           'complete' => $this->get_complete($index),
     *           'reward' => $this->get_reward($index),
     *       ]];
     */
    public function get_taskstate($uid=0, $task_id=0)
    {
        $user_taskDB = self::get_user_task($uid);
        if ($user_taskDB) {
            $manager = TaskManager::getinstance($uid);
            return $manager->success_state($task_id);
        }
        return['message'=>"没有找到任务数据！",'code'=>0];
    }
    
    
    private static function get_user_task($uid)
    {
        return Db::table('bb_task_user')->where('uid',$uid)->find();
    }
   
}
