<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

/**
 * 被邀请，注册成功后7日内认证成功，奖励20积分
 *
 */
class Type175 extends WorkerJobPush
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
            ->add_content(Message::simple()->content('您注册成功后7日内认证成功，得到系统奖励'.$bonus))
            ->set_type($type)
            ->set_uid($target_uid)
            ->send();
        
    }
   
    
}


