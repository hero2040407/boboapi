<?php
namespace app\systemmanage\controller;
/**
 * 每10分钟统计。
 * 
 * @author 谢烨
 */


use BBExtend\Sys;
use BBExtend\common\Date;
use BBExtend\DbSelect;
use BBExtend\user\ActivityRewardManager;

class Pk
{ 
    /**
     * 每10分钟linux定时任务，统计pk活动，要求为已结束，但未领奖！！
     */
    public function index ()
    {
        $db = Sys::get_container_db_eloquent();
        $time =time();
        $sql="select * from bb_task_activity
               where end_time < '{$time}'
                 and is_send_reward=0
                 and type=3
                 limit 1
";
        $result = DbSelect::fetchRow($db, $sql);
        if ($result) {
            $manager = new ActivityRewardManager($result['id']);
            $manager->process();
        }
        echo "ok";
    }
    
    
}

