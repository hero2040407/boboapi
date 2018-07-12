<?php
namespace app\command\controller;
use BBExtend\Sys;
use BBExtend\BBPush;

use BBExtend\message\Message as me;
use BBExtend\message\Umeng;
use BBExtend\fix\MessageType;

/**
 * 常规队列，
 * 直播后立刻进入这里!!
 * 每一下，就是推送一次，目前仅直播开始124。
 * 
 * 
 * 注意循环已经在外面循环过了,这里每执行一次任务,向一个用户发送好友正在直播的消息.
 * 
 * @author xieye
 *
 */

class Job22  
{
    const cha =  1800;
    const hebing_cha = 7200;
    
    /**
     * 发一条大消息，直接推送
     * @param unknown $args
     */
    public function fu_push($args,$message_type) 
    {
        $target_uid = $args["target_uid"];
        $info = $args["info"];
        Umeng::getinstance()
           ->set_content($info)
           ->set_uid($target_uid)
           ->set_message_type($message_type)
           ->send_one();
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
        
        if (in_array($type, [
            MessageType::shipin_beizan,
            MessageType::shipin_beidashang,
            MessageType::beiguanzhu,
            MessageType::idol_upload_video,
            ])) { //这几个合并消息发送。
          $this->fu_push($this->args, $type);
          return;
        }
        
        if ($type == MessageType::idol_zhibo) { //直播
           $this->zhibo($this->args, $db);
        }
        if ($type == MessageType::idol_chengjiu) { //成就
            $this->ach($this->args, $db);
        }
    }
    
    /**
     * 成就推送
     */
    public function ach($args,$db)
    {
        // 下面都是单次直播推送的代码。
        $target_uid = $args['target_uid'];
        $nickname = $args['nickname'];
        $pic = $args['pic'];
        $uid = $args['uid'];
        $time = $args['time'];
        
        $event_name  = $args['event_name'];
        $level       = $args['level'];
        $bonus_count = $args['bonus_count'];
        
        $db->insert("bb_alitemp", [
            'url' => "ach_duilie",
            'create_time' => date("Y-m-d H:i:s"),
            'uid' => $uid,
            'test1' => $target_uid,
        ]);
        me::get_instance()
            ->set_title('系统消息')
            ->add_content(me::simple()->content('你的好友'))
            ->add_content(me::simple()->content($nickname)
                    ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
            )
            ->add_content(me::simple()->content(" ".
                $event_name." LV " . $level." 达成，获得 {$bonus_count} BO币奖励！"    
            ))
            ->set_type(MessageType::idol_chengjiu)
            ->set_img($pic)
            ->set_uid($target_uid)
            ->send();
    }
    
    /**
     * 直播推送
     */
    public function zhibo($args,$db)
    {
        // 下面都是单次直播推送的代码。
        $target_uid = $args['target_uid'];
        $nickname = $args['nickname'];
        $pic = $args['pic'];
        $uid = $args['uid'];
        $time = $args['time'];
        if (time() - $time > self::cha  ) {
            return;
        }
    
        $db->insert("bb_alitemp", [
            'url' => "zhibo_duilie",
            'create_time' => date("Y-m-d H:i:s"),
            'uid' => $uid,
            'test1' => $target_uid,
        ]);
    
        $sql="select * from bb_push where uid = {$uid}";
        $push_row = $db->fetchRow($sql);
        $temp =  BBPush::get_detail_by_row($push_row, $target_uid);
        me::get_instance()
            ->set_title('系统消息')
            ->add_content(me::simple()->content('你的好友'))
            ->add_content(me::simple()->content($nickname)
               ->url(json_encode(['type'=>9, 'other_uid'=>$uid,"push_info"=>$temp, ],
                    JSON_UNESCAPED_UNICODE ) )
            )
            ->add_content(me::simple()->content('，开启了直播，点击进入直播间'))
            ->set_type(MessageType::idol_zhibo)
            ->set_img($pic)
            ->set_uid($target_uid)
            ->send();
    }
    
    
    public function test()
    {
        echo time() .":  test Job2 ok!\n";
        $db = Sys::get_container_db();
        $db->insert("bb_alitemp", [
            'url' => "test Job22 ok!",
            'create_time' => date("Y-m-d H:i:s"),
        ]);
    }
    
    
}