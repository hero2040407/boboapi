<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

/**
 * 被邀请，注册成功
 *
 */
class Type172 extends WorkerJobPush
{
    public function excute()
    {
        echo 'uid:'.$this->uid."\n".
                'info:'. var_export( $this->info,1)."\n".
                'type:'.$this->type."\n".
                'time:'.$this->time."\n";
        
        $type = $this->type ;
        $time = $this->time;
        $target_uid = $this->uid;
        $info = $this->info;
        
        $bonus = $info['bonus'];
        
//         $user = \app\user\model\UserModel::getinstance($other_uid);
//         $nickname = $user->get_nickname();
//         $pic = $user->get_user_pic_no_http();
        
        Message::get_instance()
            ->set_title('系统消息')
            ->set_time($time)
            ->add_content(Message::simple()->content('您被邀请注册成功，得到系统奖励'.$bonus))
            ->set_type($type)
            ->set_uid($target_uid)
            ->send();
        
    }
   
    
}


