<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

/**
 * 消息队列。pheanstalk
 * @author Administrator
 *
 * xieye: 20171016
 * 这是客户端代码，使用方法如下，只有两句话。专用于添加消息到队列。
 * 
 *  $client = new \BBExtend\service\pheanstalk\Client();  
 *  $client->add(['type'=>1,'msg' =>'hello!' ]);  
 *
 */
class Type1010 extends WorkerJobPush
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
        
        $other_uid = $info['other_uid'];
        
        $user = \app\user\model\UserModel::getinstance($other_uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_user_pic_no_http();
        
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->set_time($time)
            ->add_content(Message::simple()->content($nickname)->color(0xf4a560)
                ->url(json_encode(['type'=>2, 'other_uid'=>$other_uid ]) )
                )
            ->add_content(Message::simple()->content('成为了您的新粉丝。'))
            ->set_type(1010)
            ->set_uid($target_uid)
            ->set_other_uid($other_uid)
            ->send();
        
    }
   
    
}


