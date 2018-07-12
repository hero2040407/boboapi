<?php
namespace app\command\controller;
use BBExtend\Sys;
use BBExtend\message\Message;
use BBExtend\fix\MessageType;

/**
 * 偶像群发队列处理，
 * 
 * 每执行一次，就是给一个偶像的所有粉丝发送或者msg_cache表，或者推送一个消息个数
 * @author Administrator
 *
 */
class Workjob{
    
    public function test(){
        echo time().":test Workjob ok!\n";
        $db = Sys::get_container_db();
        $sql="select nickname from bb_users limit 1";
        echo $db->fetchOne($sql)."\n";
    
        $db = Sys::get_container_db();
        $db->insert("bb_alitemp", [
            'url' => "test Workjob ok!",
            'create_time' => date("Y-m-d H:i:s"),
        ]);
    }
    
    public function perform()
    {
        ini_set ( 'error_reporting', 6143 );
        ini_set('display_errors', 1);
        
        $db = Sys::get_container_db();
        $db->closeConnection();
        $db = Sys::get_container_db();
        
        $type = $this->args['type'];
        if ($type==10000) {
            $this->test();
            return ;
        }
        
        // 以下是type=123 的视频上传的情况。
        //         $args = array(
        //             'uid'  => $uid,
        //             'record_id' => $record_id,
        //             'title' =>$title,
        //             'type' => '123',
        //         );
        $type = $this->args['type'];
        $uid = $this->args['uid'];
        $uid = intval($uid);
        $record_id = $this->args['record_id'];
        $title = $this->args['title'];
        $db = Sys::get_container_db();
        $sql ="select uid from bb_users where permissions < 5
        and exists (select 1 from bb_focus
        where bb_users.uid = bb_focus.uid
        and bb_focus.focus_uid ={$uid}
        )
        order by is_online desc, permissions desc, login_time desc
        limit 700
        ";// 这句话在查粉丝。
        $ids = $db->fetchCol($sql);
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_user_pic_no_http();
        
        // 一下就累积几百个常规任务
        foreach ($ids as $target_uid) {
             
             //123 是某个偶像上传短视频，需要通知到个体。
             // 不在线需推送！会存msg_cache表（一下就累积几百个常规任务）
             // 定时任务刷新时，由常规队列处理推送消息。
             Message::get_instance()
                 ->set_title('系统消息')
                 ->set_img($pic)
                 ->add_content(Message::simple()->content("你的好友"))
                 ->add_content(Message::simple()->content($nickname)->color(0xf4a560)
                     ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                     )
                 ->add_content(Message::simple()->content('上传了新的短视频'))
                 ->set_type(MessageType::idol_upload_video)
                 ->set_uid($target_uid)
                 ->set_other_uid($uid)
                 ->set_other_record_id($record_id)
                 ->send();
        }
        
        
    }
   
}
