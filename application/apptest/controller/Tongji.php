<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\User;
use BBExtend\model\Achievement as Ach;

class Tongji
{
    /**
     * 统计总共需要发放的奖励有多少。
     * 
     * 首先，先查普通用户。
     * 
     * 对每个用户，有9个成就，
     * 
     * 对每个用户的每个成就，有3个等级。
     * 逐个查看
     * 
     */
    public function index()
    {
        $event_arr = \BBExtend\user\achievement\Ach::get_all_event();
        $sum=0;
        foreach ($event_arr as $event) {
            echo "event_name: {$event}\n";
            $result = $this->event($event);
            $sum+= $result;
            echo "all {$event}  : {$result}\n";
        }
        echo " \n\nall {$sum}\n";
    }
    
    private function event($event)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select uid from bb_users where permissions in (1,3,4) ";
        $uid_arr = DbSelect::fetchCol($db, $sql);
        $sum =0;
        foreach ($uid_arr as $uid) {
            $result = $this->one_user($event,$uid);
            if ($result) {
                $sum+=$result;
             //   echo $event." ". $uid ."  ".$result."\n";
            }
        }
        return $sum;
    }
    
    
  
    
    private function one_user($event, $uid)
    {
        $ach_real = \BBExtend\user\achievement\Ach::create_ach_by_event($event,$uid);
        $user = User::find($uid);
        $ach2 = new Ach();
        $ach = $ach2->create_default_by_user($user);
        $data = $ach->get_one_detail($event);
        $list = $data['list'];
        $sum = 0;
        foreach ($list as $v) {
            if ($v['complete_status'] == 3 && $v['bonus']==0) {
//                $sum+=100;
                $sum += $ach_real->get_bonus_value($v['level']);
            }
        }
        return $sum;
    }
    
    
}



