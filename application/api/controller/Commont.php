<?php
namespace app\api\controller;

use think\Db;
use BBExtend\Sys;
use BBExtend\message\Message;
use BBExtend\fix\MessageType;

class Commont
{
    /**
     * 谢峰后台需要此接口
     * @param unknown $table
     * @param unknown $id
     */
    public function check_success($table,$id) 
    {
        $id = intval($id);
        $db  = Sys::get_container_db();
        $sql ="select * from {$table} where id={$id}";
        $row = $db->fetchRow($sql);
        if (!$row) {
            return ["code"=>0];
        }
        if ($row['audit']!= 1) {
            return ["code"=>0];
        }
        $uid = $row['uid'];
        $activity_id = $row['activity_id'];
       
        
        if (in_array($table, ['bb_record_comments','bb_rewind_comments',])){
            $db = Sys::get_container_db();
            $sql="select nickname,pic from bb_users where uid=".intval($uid);
            $row1 = $db->fetchRow($sql);
            $nickname = $row1['nickname'];
            $pic = $row1['pic'];
        
            if ($table=='bb_record_comments') {
            $sql = "select title from bb_record where id = ".intval($activity_id);
            $title = $db->fetchOne($sql);
            $sql = "select uid from bb_record where id = ".intval($activity_id);
            $uid2 = $db->fetchOne($sql);
            }
            if ($table=='bb_rewind_comments') {
                $sql = "select title from bb_rewind where id = ".intval($activity_id);
                $title = $db->fetchOne($sql);
                $sql = "select uid from bb_rewind where id = ".intval($activity_id);
                $uid2 = $db->fetchOne($sql);
            }
            
            Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content($nickname)->color(0xf4a560)
                    ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                    )
                    ->add_content(Message::simple()->content('评论了你的视频'))
                    ->add_content(Message::simple()->content($title)->color(0xf4a560)  )
                    ->set_type(MessageType::shipin_beipinglun)
                    ->set_uid($uid2)
                    ->send();
        }
        
        return ["code"=>1];
    }
}
