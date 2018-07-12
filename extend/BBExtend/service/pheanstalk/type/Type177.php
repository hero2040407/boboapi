<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

/**
 * 删除审核过的短视频后，给星推官发送消息
 *
 */
class Type177 extends WorkerJobPush
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
        
        
        $record_id = $info['record_id'];
        //   【用户昵称】删除了视频#视频名称#，对您的邀请已经撤销！
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        //$db = \BBExtend\Sys::get_container_db_eloquent();
        $sql="select * from bb_record where id=" .intval( $record_id ) ;
        $row = $db->fetchRow($sql);
        $record = \BBExtend\BBRecord::get_detail_by_row($row,10000);
        $nickname = $record['nickname']; 
        $title = $record['title'];
        
        $content = " {$nickname} 删除了视频 {$title} ，对您的邀请已经撤销。";
        
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


