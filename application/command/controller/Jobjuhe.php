<?php

use BBExtend\Sys;
use BBExtend\message\Message;

/**
 * 短视频上传推送。输入参数uid，视频id
 * @author xieye
 *
 */
class Jobjuhe  
{
    public function test(){
        echo time().":test Jobjuhe ok!\n";
        $db = Sys::getdb();
        $sql="select nickname from bb_users limit 1";
        echo $db->fetchOne($sql)."\n";
        
        $db = Sys::getdb();
        $db->insert("bb_alitemp", [
            'url' => "test Jobjuhe ok!",
            'create_time' => date("Y-m-d H:i:s"),
        ]);
    }
    
    
    /**
     * 本函数无时限
     */
    public function perform()
    {
        $type = $this->args['type'];
        Sys::display_all_error();
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
        //echo 11;
        $type = $this->args['type'];
        $uid = $this->args['uid'];
        $uid = intval($uid);
        $record_id = $this->args['record_id'];
        $title = $this->args['title'];
        //echo 22;
        $db = Sys::getdb();
        $sql ="select uid from bb_users where permissions < 5
            and exists (select 1 from bb_focus
            where bb_users.uid = bb_focus.uid
            and bb_focus.focus_uid ={$uid}
            )
            order by is_online desc, permissions desc, login_time desc
            limit 700
        ";// 这句话在查粉丝。  
        $ids = $db->fetchCol($sql);
       // var_dump($ids);
        
         foreach ($ids as $target_uid) {
             //$uid = $target_uid;
             //echo $target_uid."\n";
             
             $data = [
                 'target_uid' =>$target_uid,
                 'time' =>time(),
                 'uid' => $uid,
                 'record_id' =>$record_id,
                 'title' => $title,
             ];
             
             $response = \Requests::post('http://127.0.0.1/command/message/record', array(), $data);
                 
             
//              $user = \app\user\model\UserModel::getinstance($uid);
//              $nickname = $user->get_nickname();
//              $pic = $user->get_user_pic_no_http();
//              //echo $target_uid.":1:\n";
//              Message::get_instance()
//                  ->set_title('系统消息')
//                  ->set_img($pic)
//                  ->add_content(Message::simple()->content("你的好友"))
//                  ->add_content(Message::simple()->content($nickname)->color(0xf4a560)
//                      ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
//                      )
//                  ->add_content(Message::simple()->content('上传了新的短视频'))
//                  ->set_type(123)
//                  ->set_uid($target_uid)
//                  ->set_other_uid($uid)
//                  ->set_other_record_id($record_id)
//                  ->send();
//              //echo $target_uid.":2:\n";
         }
        
    }
    
}

