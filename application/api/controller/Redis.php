<?php
namespace app\api\controller;
use BBExtend\BBRedis;

/**
 * 按行删除缓存，方便后台调用。
 * 
 * 20171019，实际并无使用
 * 
 * @author xieye
 *
 */
class Redis
{
    /**
     * 按行删除缓存。
     * @param string $type
     * @param string $id
     */
    public function remove($type,$id)
    {
        if ($type == 'activity') { // 删除 bb_activity表的id行
            BBRedis::getInstance('activity')->Del($id);
        }
        if ($type == 'config') { //删除config，id可能是其中一个['bb_config', 具体请看陈岳代码
          // 'bb_role', 'bb_usersort', 'bb_label', 'bb_label_activity', 'bb_label_learn',   ]
            BBRedis::getInstance('activity')->hDel($id);
        }
        if ($type == 'record') { // id 是短视频的room_id
            BBRedis::getInstance('record')->Del( $id.'record');
        }
        
        
        if ($type == 'push') {   //id 是 用户id，即uid
            BBRedis::getInstance('push')->Del($id."push");
        }
        
        if ($type == 'user') {   //id 是 用户id，即uid
            BBRedis::getInstance('user')->Del($id);
        }
        
        if ($type == 'bb_task_user') {  //id 是用户id，删除该用户的所有任务。bb_task_user表
        //    BBRedis::getInstance('bb_task')->Del($id.'user_task');
        }
        
        if ($type == 'bb_task_activity') {  //id 是活动id，。bb_task_activity表的id行
            BBRedis::getInstance('bb_task')->Del($id.'activity');
        }
        
        if ($type == 'bb_task') {  //id 。bb_task表的id行
          //  BBRedis::getInstance('bb_task')->Del($id.'task_list');
        }
        
        
        if ($type == 'bb_monster_animation') { // id 是 bb_monster_animation表 的id
            BBRedis::getInstance('monster')->Del($id.'ani');
        }
        
        if ($type == 'bb_monster_list') { // id 是 bb_monster_list表 的id
            BBRedis::getInstance('monster')->Del($id.'monster');
        }
        
        
        
        if ($type == 'comments') { // id是表名+ comments_id + type
            BBRedis::getInstance('comments')->Del($id);
        }
      
        
        
        return ["code"=>1];
    }
   
}