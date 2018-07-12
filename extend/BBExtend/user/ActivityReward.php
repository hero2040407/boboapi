<?php
namespace BBExtend\user;

/**
 * 
 * 
 * User: 谢烨
 */

use BBExtend\Sys;
use think\Db;
use BBExtend\Currency;
use BBExtend\BBUser;
use BBExtend\BBMessage;
use BBExtend\message\Message;
/**
 * 
 * 特别说明，为什么要设置这么多的字段，而不是读一条活动记录。
 * 是因为这是批量处理的，速度非常重要，开始查一次活动，然后传参进来，这样比较快。
 * 
 * @author Administrator
 *
 */
class ActivityReward
{
   // public $redis;
    public $uid;
    public $activity_id;
    
    public $zong_price; // 谢烨，这是总金额。
    public $paiming;     // 谢烨，这是排名
    public $act_type;    //活动的分类，type0,擂台，type1，小记者，type2，悬赏。type=3 pk
    
    public $act_name;
    
    public $record_id;
    public $room_id;
    public $like_count;
    
    public $gold_type;
    
    // type=3 专用
    public $reward_people_count=0;
    
    
    public function set_zong_price($price)
    {
        $this->zong_price = intval($price);
        return $this;
    }
    
    public function set_reward_people_coun($count)
    {
        $this->reward_people_count = intval($count);
        return $this;
    }
    
    public function set_gold_type($type)
    {
        $this->gold_type = intval($type);
        return $this;
    }
    
    public function set_paiming($paiming)
    {
        $this->paiming = intval($paiming);
        return $this;
    }
    
    public function set_act_type($type)
    {
        $this->act_type = intval($type);
        return $this;
    }
    
    public function set_act_name($name)
    {
        $this->act_name = strval($name);
        return $this;
    }
    
    public function set_record_id($record_id)
    {
        $this->record_id = intval($record_id);
        
     //   echo $this->record_id;
        
        return $this;
    }
    
    public function set_room_id($room_id)
    {
        $this->room_id = strval($room_id);
        return $this;
    }
    
    public function set_like_count($count)
    {
        $this->like_count = intval($count);
        return $this;
    }
    
    
    /**
     *   谢烨，redis最佳形式，集合，因为这是无序的。
     键名：relation_lahei_{$uid}
     类型集合。
     */
    public function  __construct($uid=0,$activity_id=0) {
//         $redis = \BBExtend\BBRedis::connectionRedis();
//         $redis->select(11);
//         $this->redis = $redis;
        $this->uid = intval($uid);
        $this->activity_id= intval( $activity_id);
    }
    
    
    public static function getinstance($uid=0, $activity_id=0)
    {
        return new self($uid, $activity_id);
    }
    
    public function lingjiang()
    {
        //先查type012，不是，则根本不处理。
        $type = $this->act_type;
        if (!in_array($type, [0,1,2,3])) {
            return;
        }
      //  echo 4;
        if ($this->zong_price ==0) {
          //  return;
        }
        //echo 5;
        // 先查是否领奖，领过就不能再领了！！
        $result = $this->has_reward();
        if ($result) {
            return false;
        }
       // echo 6;
        $zong_price = $this->zong_price;
        $paiming = $this->paiming;
        
//         擂台，擂主是500其他参与的都是6 type= 0
//         type = 1 小记者团
//         type = 2 悬赏是 50% 30% 10%  ，第四名之后参与的全是6波币 type=2
//         type = 3，PK，所有人平分。
        
        $price=0;
        // 根据排名，type换得金额数量 
        //type0,pk擂台，type1，小记者，type2，悬赏。
        if ($type==0) {
            if ($paiming== 1) {
                $price = $zong_price;
            }else {
                $price = 6;
            }
        }
        if ($type==1) {
            $price= $zong_price;
        }
        if ($type==2) {
            if ($paiming ==1) {
                $price = $zong_price * 0.5;
                $price = intval($price);
            }elseif ($paiming ==2) {
                $price = $zong_price * 0.3;
                $price = intval($price);
            }elseif ($paiming ==3) {
                $price = $zong_price * 0.1;
                $price = intval($price);
            }else {
                $price = 6;
            }
            
        }
        if ($type==3) {
            $price= $zong_price / $this->reward_people_count;
            if ($price <1) {
                $price=1;
            }else {
                $price = intval($price);
            }
        }
        
        
        $uid = $this->uid;
        $act_id = $this->activity_id;
        
        // xieye 201803 pk不发奖
        if ($type!=3) {
        
        if ($this->gold_type==1) {
        
           Currency::add_currency($this->uid, CURRENCY_GOLD,  
                $price, '参加['. $this->act_name .']活动奖励');
        
        }
        if ($this->gold_type==2) {
        
            Currency::add_bean($this->uid, $price, '参加['. $this->act_name .']活动奖励');
        
        }
        
        }
     //   echo 12;
        
        $db = Sys::get_container_db();
        $db->update("bb_user_activity_reward",[
            'has_reward' => 1,
            'reward_count' => $price,
            'reward_time' => time(),
            'paiming' => $paiming,
            'record_id' => $this->record_id,
            'room_id'   => $this->room_id,
            'like_count' => $this->like_count,
        ], "uid = {$uid} and activity_id = {$act_id} ");
        
    }
    
    
    
    // xieye 2018 02 该函数废止，用队列
    public function get_message()
    {
        //先查type012，不是，则根本不处理。
        $type = $this->act_type;
        $act_id = $this->activity_id;
        $uid = $this->uid;
        if (!in_array($type, [0,1,2])) {
            return false;
        }
      
        // 先查是否领奖，领过就不能再领了！！
        $db = Sys::get_container_db();
        $sql="select * 
           from bb_user_activity_reward where uid={$uid}
          and activity_id = {$act_id}
        ";
        $result = $db->fetchRow($sql) ;
        $user = BBUser::get_user($uid);
        
        if ($result &&  $result['has_reward'] && $result['has_message']==0 ) {
            $price = $result['reward_count'];
            
            $user = BBUser::get_user($uid);
            
            $ContentDB=[
                'nickname' => $user['nickname'],
                'act_name' => $this->act_name,
                'zan_count' => $result['like_count'],
                'paiming'   => $result['paiming'],
                'jiangli'   => $price,
               
            ];
            
            $sql="update bb_user_activity_reward set   has_message=1
            where uid={$uid}
            and activity_id = {$act_id}
            ";
            $db->query($sql) ;
         //   echo 55;
            return $ContentDB;
            
            
        }
    //    echo 22;
        return  false;
        
    }
   
    // xieye 2018 02 该函数废止，用队列
    public function send_message()
    {
        $result = $this->get_message();
        
        $gold_type = ($this->gold_type==1) ? "BO币":"BO豆"; 
        
        if ($result) {
            
            Message::get_instance()
                ->set_title('系统消息')
                ->add_content(Message::simple()->content("亲爱的"))
                ->add_content(Message::simple()->content($result['nickname'])->color(0x32c9c9)  )
                ->add_content(Message::simple()->content('，您参加的'))
                ->add_content(Message::simple()->content($result['act_name'])->color(0xf4a560)
                        ->url(json_encode(['type'=>4, 'activity_id'=>$this->activity_id ]) )
                        )
                ->add_content(Message::simple()->content("已结束，恭喜您获得了".
                        "{$result['zan_count']}赞，排行第{$result['paiming']}名，获得"))
                ->add_content(Message::simple()->content("{$result['jiangli']}{$gold_type}")->color(0xf4a560)  )
                ->add_content(Message::simple()->content("奖励。")  )
                ->set_type(114)
                ->set_uid($this->uid)
                ->send();
        }
    }
    
    /**
     * 谢烨，此函数非常特别，失败反而返回真。
     * @param unknown $uid
     * @param unknown $room_id
     */
    public function has_reward()
    {
        $uid = $this->uid;
         $act_id = $this->activity_id;
         if ((!$uid) || ( !$act_id )) {
             return 1;
         }
        
       $db = Sys::get_container_db();
        $sql="select * from bb_user_activity_reward where uid={$uid}
          and activity_id = {$act_id}
        ";
        $result = $db->fetchRow($sql) ;
        if (!$result) {
            return 1;
        }
        return $result['has_reward'];
        
    }
    
   

}