<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

/**
 * 活动后台发奖
 *
 */
class Type114 extends WorkerJobPush
{
    
    public function get_message()
    {
        $type = $this->type ;
        $time = $this->time;
        $target_uid = $this->uid;
        $info = $this->info;
        
        
        //先查type012，不是，则根本不处理。
        $type = $info['act_type'];
        
        $act_id = $info['act_id'];
        $uid = $this->uid;
        if (!in_array($type, [0,1,2,3])) {
            return false;
        }
        
        // 先查是否领奖，领过就不能再领了！！
        $db = \BBExtend\Sys::get_container_db();
        $sql="select *
           from bb_user_activity_reward where uid={$uid}
          and activity_id = {$act_id}
        ";
        $result = $db->fetchRow($sql) ;
        $sql="select nickname from bb_users where uid = ?";
        
        $nickname = $db->fetchOne($sql,[ $this->uid ] );
        
       // $user = BBUser::get_user($uid);
        
        if ($result &&  $result['has_reward'] && $result['has_message']==0 ) {
            $price = $result['reward_count'];
            
           // $user = BBUser::get_user($uid);
            
            $ContentDB=[
                    'nickname' => $nickname,
                    'act_name' => $info['act_name'],
                    'zan_count' => $info['like_count'],
                    'paiming'   => $result['paiming'],
                    'jiangli'   => $price,
                    
            ];
            
            $sql="update bb_user_activity_reward set   has_message=1
            where uid={$uid}
            and activity_id = {$act_id}
            ";
            $db->query($sql) ;
            //   echo 55;
            return $ContentDB;
            
            
        }
        //    echo 22;
        return  false;
        
    }
    
    
    
    
    
    
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
        
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        $result = $this->get_message();
        
        
      //  $record_id = $info['record_id'];
        
        $gold_type = ($info['gold_type']==1) ? "BO币":"BO豆";
        
        if ($result) {
            
            Message::get_instance()
            ->set_title('系统消息')
            ->set_time($time)
            ->add_content(Message::simple()->content("亲爱的"))
            ->add_content(Message::simple()->content($result['nickname'])->color(0x32c9c9)  )
            ->add_content(Message::simple()->content('，您参加的'))
            ->add_content(Message::simple()->content($result['act_name'])->color(0xf4a560)
                    ->url(json_encode(['type'=>4, 'activity_id'=>$info['act_id'] ]) )
                    )
            ->add_content(Message::simple()->content("已结束，恭喜您获得了".
                    "{$result['zan_count']}赞，排行第{$result['paiming']}名，获得"))
                    ->add_content(Message::simple()->content("{$result['jiangli']}{$gold_type}")->color(0xf4a560)  )
                    ->add_content(Message::simple()->content("奖励。")  )
                    ->set_type(114)
                    ->set_uid($this->uid)
                    ->send();
        }
      
        
//         Message::get_instance()
//             ->set_title('系统消息')
//             ->set_time($time)
//             ->add_content(Message::simple()->content( $content ))
//             ->set_type($type)
//             ->set_uid($target_uid)
//             ->send();
        
    }
   
    
}


