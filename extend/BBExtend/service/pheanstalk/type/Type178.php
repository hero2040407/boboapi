<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

/**
 * 天降红包奖励。
 *
 */
class Type178 extends WorkerJobPush
{
    public function excute()
    {
//         echo 'uid:'.$this->uid."\n".
//                 'info:'. var_export( $this->info,1)."\n".
//                 'type:'.$this->type."\n".
//                 'time:'.$this->time."\n";
        
        $type = $this->type ;
        $time = $this->time;
        $target_uid = $this->uid;
        $info = $this->info;
        
        
        $datestr = $info['datestr'];
        $money = $info['money'];
        //   【用户昵称】删除了视频#视频名称#，对您的邀请已经撤销！
        
        $content = " 恭喜您取得了天降红包{$datestr}日的排名，获得{$money}个BO币奖励!";
        
//         $user = \app\user\model\UserModel::getinstance($other_uid);
//         $nickname = $user->get_nickname();
//         $pic = $user->get_user_pic_no_http();
        
        Message::get_instance()
            ->set_title('系统消息')
            ->set_time($time)
            ->add_content(Message::simple()->content( $content ))
            ->set_type($type)
            ->set_uid($target_uid)
            ->send();
        
    }
   
    
}


