<?php
namespace BBExtend\user\achievement;
use BBExtend\model\AchievementSummary;
use BBExtend\model\Achievement;

use BBExtend\model\AchievementMsg;
use BBExtend\model\AchievementBonus;
use BBExtend\Currency;
use BBExtend\model\help\AchievementPic;
use BBExtend\Sys;

use BBExtend\message\Message as me;

/**
 * 
 * 
 * User: 谢烨
 */

abstract class Ach implements UpdateInterface
{
    protected  $uid;
    protected $ach_summary;
    protected $ach;
    
    protected $old=0;// 旧的成就等级，比如0
    protected $new=0;// 新的成就等级，比如1
    
    protected  $bonus_count=0; // 具体奖励数额，发消息用
    
    /**
     * 静态工厂
     * 201709
     * @param unknown $uid
     */
    public static function create_ach_by_event($event,$uid)
    {
        switch ($event) {
            case 'dengji':
                return new Dengji($uid);
            case 'zhibo':
                return new Zhibo($uid);
            case 'pinglun':
                return new Pinglun($uid);
            case 'dianzan':
                return new Dianzan($uid);
            case 'zhubo':
                return new Zhubo($uid);
            case 'hongren':
                return new Hongren($uid);
            case 'huodong':
                return new Huodong($uid);
            case 'dasai':
                return new Dasai($uid);
            case 'neirong':
                return new Neirong($uid);
        }
        return null;
    }
    
    public static function get_all_event()
    {
        return ['dengji', 'zhibo','pinglun','dianzan','zhubo','hongren','huodong','dasai','neirong' ];
    }
    
    
    public function __construct($uid)
    {
        $this->uid = intval($uid);
        $this->ach = Achievement::where("uid",$this->uid)->first();
        $this->ach_summary = AchievementSummary::where("uid",$this->uid)->first();
        
        
    }
    
    /**
     * 每次，Achievement表的变动，比如LV2-> LV3类似这样，就会进入此函数
     * 
     * 先检查bonus表，以前有无发过奖励？
     * 如有，直接忽略。
     * 
     * 如无，
     * 发bobi，记录日志，然后给ach_msg表添加记录
     * 
     * @param unknown $old
     * @param unknown $new
     * @param unknown $event 类似huodong，hongren这样
     */
    protected  function bonus($old,$new,$event)
    {
        // 现在的规定是，每升一次，都要加100波币
        if ($old==$new) {
            return false;
        }
        
        if ($new < 1 || $new > 3) {
            return false;
        }
        
        $obj = AchievementBonus::where("uid",$this->uid)->where("level",$new)
            ->where("event",$event)->first();
        if ($obj) { // 如果已经发过奖励了，则忽略
            return false;
        }
        //$count=100; //固定100波币奖励
        
        $count = $this->get_bonus_value($new);
        
        $this->bonus_count = $count;
      //  Currency::add_currency($this->uid,1,$count,'成就奖励');
        $bonus = new  AchievementBonus();
        $bonus->uid = $this->uid;
        $bonus->event = $event;
        $bonus->level = $new;
        $bonus->bonus = $count;
        $bonus->create_time = time();
        $bonus->get_time = 0;
        $bonus->save();
        
        //记录消息表
        $help = new AchievementPic();
        $msg = new AchievementMsg();
        $msg->uid = $this->uid;
        $msg->is_read = 0 ;
        $msg->event = $event;
        $msg->level = $new;
        $msg->event_name = AchievementPic::get_event_name($event);
        $msg->bonus = $count;
        $msg->create_time = time();
        $msg->pic = $help->get_pic_by_key($event, $new);
        $msg->save();
        return true;
    }
    
    /**
     * 这是模板方法，重要。
     * update_ach：更新两张表
     * bonus（）： 给奖励。
     * 
     * @param unknown $param
     */
    public function update($param)
    {
        $result = $this->update_ach($param);
        if ($result === true) {
            $result2 =  $this->bonus($this->old, $this->new, $this->get_event());
            if ($result2) {
                // 如果确实发奖励了，要推送消息给粉丝 he ziji .
                
                $this->send_msg_to_self(AchievementPic::get_event_name($this->get_event()),
                        $this->new, $this->bonus_count );
                
//                 $this->send_msg(AchievementPic::get_event_name($this->get_event()),
//                     $this->new, $this->bonus_count );
            }
        }
        
    }

    //
    public function send_msg_to_self($event_name,$level, $bonus_count)
    {
        me::get_instance()
            ->set_title('系统消息')
            ->add_content(me::simple()->content('你的'))
            ->add_content(me::simple()->content(
                $event_name." LV " . $level." 已达成，请至个人中心领取奖励！"    
                )->url(json_encode(['type'=>1,  ]))
              )
            ->set_type(154)
            ->set_uid($this->uid)
            ->send();
    }
    
    /**
     * 推送给粉丝
     */
    public function send_msg($event_name,$level, $bonus_count)
    {
        $db=Sys::get_container_db();
        $uid =$this->uid;
        $sql ="select uid,is_online from bb_users where permissions < 5
         
        and exists (select 1 from bb_focus
        where bb_users.uid = bb_focus.uid
        and bb_focus.focus_uid ={$uid}
        )
        order by is_online desc, permissions desc, login_time desc
        limit 500
        ";
        $ids = $db->fetchAll($sql);
    
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_userpic();
        $time=time();
    
        //你的好友#玩家昵称#，开启了直播，点击进入直播间
        \Resque::setBackend('127.0.0.1:6380');
    
        foreach ($ids as $v) {
            $args = array(
                'target_uid' => $v['uid'],
                'uid'  => $uid,
                'time' => $time,
                'pic'      => $pic,
                'nickname' => $nickname,
                'type' => '152',
                
                'event_name' => $event_name,
                'level' => $level,
                'bonus_count' => $bonus_count,
            );
            \Resque::enqueue('jobs22', '\app\command\controller\Job22', $args);
        }
    }
    
    /**
     * 可否领奖
     */
    public function can_award()
    {
        return  \BBExtend\model\AchievementBonus::where("uid",$this->uid )
        ->where('get_time',0)
        ->where('level','>',0)
        ->where('event',$this->get_event())
        ->count();
    }
    
    
    abstract  public function update_ach($param);
    abstract  public function get_event();
    abstract  public function get_bonus_value($new_level);
    
    
}
