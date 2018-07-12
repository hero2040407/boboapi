<?php
namespace app\api\controller;

use BBExtend\Sys;

class Robotfans
{
    
    /**
     * 机器人加粉
     * 
     *  随机挑选100个类型为10,11的用户（批量导入的用户）作为机器人
     * 随机挑选100个视频（概率 1周内 30%   一月内50%  一个月前20%）
     * 遍历视频，对每个视频随机挑选10个机器人插入浏览记录（观看时间60s范围内随机）
     * 同时在选中的机器人中随机挑选1~3个人 插入点赞记录
     * 预留添加评论接口（概率5%~10%）
     * （上述记录最好有个标识，区别正常日志数据）
     * 
     * @return number[]|number[]
     */
    public function index($target_uid=0)
    {
     //   $db = Sys::get_container_db_eloquent();
        $dbzend = Sys::get_container_dbreadonly();
        
        if ($target_uid==0) {
          $sql="
select uid from bb_users where role=4
order by rand() limit 1";
          $target_uid = $dbzend->fetchOne($sql);
        }
        
        if (!$target_uid) {
            return ['code'=>0,'message'=>'err1'];
        }
        
        
        $time1 = time() - 3*30*24*3600;
        $sql="
          select uid from bb_users where permissions=1
and login_time < ?
and not exists (
  select 1 from bb_focus
   where bb_focus.uid = bb_users.uid 
    and bb_focus.focus_uid = ?
)
order by rand()
limit 1
";
        $uid = $dbzend->fetchOne($sql,[ $time1, $target_uid ]);
        if (!$uid) {
            return ['code'=>0,'message'=>'err2'];
        }
     //   return ['code'=>30,'message'=>'err3'];
        
        $self_uid = $uid;
        $focus_uid = $target_uid;
        
        $help = \BBExtend\user\Focus::getinstance($self_uid);
        if ( $help->focus_guy($focus_uid) ) {
            $ach = new \BBExtend\user\achievement\Hongren($focus_uid);
            $ach->update(1);
            return ['code'=>1,'data'=>['uid'=>$uid,'target_uid'=>$target_uid  ] ];
        } 
        return ['code'=>0,'message'=>'err3'];
        
        
        
    }
    
    
}
