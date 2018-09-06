<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

use BBExtend\Sys;

/**
 * 比赛自动公告。
 *
 */
class Type180 extends WorkerJobPush
{
    
    
    private function msg20180904(){
        
    }
    
    
    
    public function excute()
    {
        
        $type = $this->type ;
        $time = $this->time;
        $target_uid = $this->uid;
        $info = $this->info;
        
        $small_type = $info['small_type'];
        
        // 谢烨，根据small_type不同来确定。
//         键 race_msg_register 报名成功发送的公告
//         键 race_msg_promote 晋级成功发送的公告
//          键 hand 表示 手动发送公告。
//          键 race_log 表示记录一个事件，耗时长，所以放入 队列里。
        
        
        // 谢烨特别说明：我姑且认为是线下的。  线上咱不考虑。
        
        // 自动替换： 【大赛名称】 【赛区名称】 【用户昵称】 【轮次】
        // 
        
        
        $db = \BBExtend\Sys::get_container_db();
        
        if ( $small_type=='race_log' ) {
            $success =  $info['success'];//1成功，2失败。
            $ds_id = $info['ds_id'];
            \BBExtend\backmodel\RaceLog::upgrade($ds_id, $target_uid, $success);
        }
        
        
        
        if ( $small_type=='race_msg_register' ) {
            
            $sql=" select val from bb_config_str where type=13 and config=?";
            $content = $db->fetchOne($sql,[ $small_type ]);
            if (!$content) {
                return false;
            }
            $field_id =  $info['field_id'];
            $ds_id = $info['ds_id'];
            
            // 谢烨，这里区分线上和线下。
            
            if ($field_id) { // 线下的。
                $field = \BBExtend\backmodel\RaceField::find( $field_id );
                
                $user = \BBExtend\model\User::find( $target_uid );
                
                $race = \BBExtend\backmodel\Race::find( $ds_id );
                
                
                $content = $this->convert_message( $content,[
                        '【大赛名称】' => $race->title,
                        '【赛区名称】' => $field->title,
                        '【用户昵称】' => $user->get_nickname() ,
                        
                ] );
            }  else {
                
//                 $field = \BBExtend\backmodel\RaceField::find( $field_id );
                
                $user = \BBExtend\model\User::find( $target_uid );
                
                $race = \BBExtend\backmodel\Race::find( $ds_id );
                
                
                $content = $this->convert_message( $content,[
                        '【大赛名称】' => $race->title,
                        '【赛区名称】' => '线上',
                        '【用户昵称】' => $user->get_nickname() ,
                        
                ] );
                
            }
            Message::get_instance()
                ->set_title('系统消息')
                ->set_time($time)
                ->add_content(Message::simple()->content( $content ))
                ->set_type($type)
                ->set_newtype(4)
                ->set_uid($target_uid)
                ->send();
        }
        
        
        if ( $small_type=='race_msg_promote' ) {
            
            $sql=" select val from bb_config_str where type=13 and config=?";
            $content = $db->fetchOne($sql,[ $small_type ]);
            if (!$content) {
                return false;
            }
            $field_id =  $info['field_id'];
            $round = $info['round'];
            
            $field = \BBExtend\backmodel\RaceField::find( $field_id );
            
            $user = \BBExtend\model\User::find( $target_uid );
            
            $race = \BBExtend\backmodel\Race::find( $field->race_id );
            
            
            $content = $this->convert_message( $content,[
                    '【大赛名称】' => $race->title,
                    '【赛区名称】' => $field->title,
                    '【用户昵称】' => $user->get_nickname() ,
                    '【轮次】'   => $round,
            ] ); 
            Message::get_instance()
                ->set_title('系统消息')
                ->set_time($time)
                ->add_content(Message::simple()->content( $content ))
                ->set_type($type)
                ->set_newtype(4)
                ->set_uid($target_uid)
                ->send();
        }
        
        
        // 手动发送消息，
        if ( $small_type=='hand' ) {
            
            
            $content = $info['content'];
            
            Message::get_instance()
              ->set_title('系统消息')
              ->set_time($time)
              ->add_content(Message::simple()->content( $content ))
              ->set_type($type)
              ->set_newtype(4)
              ->set_uid($target_uid)
              ->send();
        }
        
    }
    
    private function convert_message( $message, $arr )
    {
        $old = array_keys($arr);
        $new = array_values($arr);
        return str_replace($old, $new, $message);
    }
   
    
}


