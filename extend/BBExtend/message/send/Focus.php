<?php
namespace BBExtend\message\send;

use BBExtend\message\Message;

/**
 * 
 * 发送关注消息
 * @author Administrator
 *
 */
class Focus 
{
    public $target_uid;
    public $fensi_uid;
    public $focus_time;
     public function __construct($target_uid, $fensi_uid, $focus_time)
     {
         $this->target_uid = intval($target_uid );
         $this->fensi_uid = intval($fensi_uid );
         
         
         $this->focus_time = intval($focus_time);
         if (!$this->focus_time) {
             $this->focus_time=time();
         }
     }
    
     public function send()
     {
         
         $target_uid = $this->target_uid;
         $uid = $this->fensi_uid;
         
         $user = \app\user\model\UserModel::getinstance($uid);
         $nickname = $user->get_nickname();
         $pic = $user->get_user_pic_no_http();
          Message::get_instance()
             ->set_title('系统消息')
             ->set_img($pic)
             ->set_pic_uid($uid)
             ->set_time($this->focus_time)
             ->add_content(Message::simple()->content($nickname)->color(0xf4a560)
                 ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
             )
             ->add_content(Message::simple()->content('成为了您的新粉丝。'))
             ->set_type(122)
             ->set_uid($target_uid)
             ->set_other_uid($uid)
             ->send();
     }
   
   
}

