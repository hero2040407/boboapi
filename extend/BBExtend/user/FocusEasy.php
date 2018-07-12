<?php
namespace BBExtend\user;

/**
 * 关注类
 * 
 * 数量用最简单的set单变量！
 * 关注人列表用集合
 * 
 * @author 谢烨
 */

use think\Db;
use BBExtend\Sys;
use BBExtend\Level;
use BBExtend\message\Message;
use BBExtend\user\exp\Exp;
use BBExtend\user\Focus;

class FocusEasy extends Focus 
{
    
    /**
     * @param int $uid
     */
    public function  __construct($uid=0) 
    {
        parent::__construct($uid);
    }
    
    
    /**
     * 加关注, 注意，可能失败
     * 
     * @param int $target_uid
     * @param int $focus_time
     * @return bool
     */
    public function focus_guy($target_uid,$focus_time=0)
    {
        $uid = $this->uid;
        $target_uid = intval($target_uid);
        if ((!$uid) || ( !$target_uid )) {
            return false;
        }
        if ( $uid == $target_uid ) {
            $this->message='您不可以关注自己';
            return false;
        }
        if ($focus_time==0) {
            $focus_time = time();
        }
        /*
         * 先查redis中有无，如无，先插入redis，带默认值0
         *
         * 好，在有的情况下，直接sadd，根据返回数量，我也返回给接口
         * 成功还是失败！！
         */
        $key = $this->get_my_focus_key();
        $this->create_list();
        $result = $this->redis->sAdd($key, $target_uid);
        if ($result) {
            
            //重要，这句话必须放在最前面写，比数据库前，否则逻辑错误！
            $target_obj = new self($target_uid);
            $target_obj->add_fensi_count();
            
            $db = Sys::get_container_db();
            $db->insert("bb_focus",[
                'uid'=>$uid,
                'focus_uid'=>$target_uid,
                'time'=>$focus_time,
            ]);
            
           
            $this->redis->delete("focuson:new_index:{$uid}");
            
          
            
        }
        return $result;
    }
    
  

}