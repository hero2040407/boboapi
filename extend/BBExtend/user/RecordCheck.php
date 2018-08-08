<?php
namespace BBExtend\user;

/**
 * 
 * 
 * User: 谢烨
 */

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Db;
// use BBExtend\fix\Message;

use BBExtend\message\Message;

use BBExtend\user\Activity;
use BBExtend\user\TaskManager;
use BBExtend\BBUser;
use BBExtend\BBRedis;

use BBExtend\fix\MessageType;
use BBExtend\fix\TableType;
use BBExtend\common\Date;

use BBExtend\Currency;

/**
 * 
 * 特别说明，为什么要设置这么多的字段，而不是读一条活动记录。
 * 是因为这是批量处理的，速度非常重要，开始查一次活动，然后传参进来，这样比较快。
 * 
 * 
 * 有哪些改变：2016 11 10
 * 1、redis保险起见加。
 * 2、加入type=1处理。
 * 3、全部要发消息，无论审核成功失败。
 * 4、多了保存失败原因。
 * 
 * @author Administrator
 *
 */
class RecordCheck
{
    public $record_id;
    public $record;
    public $audit;
    public $message;
    public $act_id;
    public $activity;
    public $uid;
    
    public $type;// 2 邀约，3，个人认证。
    
    public $task_id; //注意，可能空
    public $fail_reason; // 失败原因
    public $title;
    
    public function __construct($record_id, $audit,$fail_reason='')
    {
        $this->audit = $audit;
        $this->record_id = intval($record_id);
        $this->record = Db::table('bb_record')->where('id', $this->record_id)->find();
        if (!$this->record) {
            throw  new \Exception('record not found');
        }
        if (!in_array($audit, [1,2,])) {
            throw  new \Exception('参数audit错误');
        }
        
        
        $this->type = $this->record['type'];//1秀场，2邀约，3个人认证
        $this->uid = $this->record['uid'];
        $this->title = $this->record['title'];
        $this->fail_reason = $fail_reason;
        if (!$this->fail_reason) {
            $this->fail_reason='检查';
        }
    }
    
    public static function getinstance($record_id, $audit)
    {
        return new self($record_id, $audit);
    }
    
    
    private function starmaker(){
        
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_record_invite_starmaker
               where record_id = " .$this->record_id ;
        $result = DbSelect::fetchRow($db, $sql);
        if ($result) {
            if ($result['push_type'] !=3 ) {
                \BBExtend\Currency::add_bobi( $result['uid'] ,$result['gold'], '点评邀请退回');
            }

            $sql="delete from bb_record_invite_starmaker
                   where record_id = " .$this->record_id ;
            $db::delete($sql);
            $sql="delete from bb_record_invite_starmaker_log
                   where record_id = " .$this->record_id ;
            $db::delete($sql);
        }
    }

    public function check()
    {
        $result = $this->check_all();
        if ($result) {
            // 发消息。
            $db = Sys::get_container_db();
            $sql = "select * from bb_record where id = ". $this->record_id;
            $recordDB = $db->fetchRow($sql);
            BBRedis::getInstance('record')->hMset($recordDB['room_id'].'record',$recordDB);
            
            $reason = strval( $this->fail_reason);
            if (!$reason) {
                $reason ='审核未通过';
            }
            $title=strval($this->title);
            if (!$title) {
                $title='视频';
            }
            
            // xieye 20-1807注释。
//             // 星推官处理
//             if ($this->audit==2 ) {
//                 $this->starmaker();
//             }
            
            
            
            // 谢烨20171101，短视频个人认证，审核通过，如果是被邀请的用户，且邀请7日内认证成功，有20积分奖励
            if ($this->audit==1 && $this->type == TableType::bb_record__type_yanzheng) {
                $dbe = Sys::get_container_db_eloquent();
                $sql ="select * from bb_users_invite_register where target_uid=? and
                       is_complete = ? 
                       and
                       create_time > ? ";
                $result2 =  DbSelect::fetchRow($dbe, $sql,[ $this->uid, 
                    TableType::bb_users_invite_register__is_complete_yizhuce,
                    Date::pre_day_start(7)
                ]);
                if ($result2) {
                    $yaoqing_uid = $result2['uid'];
                    
                    Currency::add_score($yaoqing_uid, 20, '被邀请人注册后认证成功奖励',175);
                    $client = new \BBExtend\service\pheanstalk\Client();
                    $client->add(
                            new \BBExtend\service\pheanstalk\Data($yaoqing_uid,175,['bonus' => ' 20 积分',], time())
                    );
                }
            }
            
            
            // 审核成功，结果如下
            if ($this->audit==1) {
                
                if ($this->type==1 || $this->type==\BBExtend\fix\TableType::bb_record__type_updates ) {
                // 谢烨，201807 ，添加到动态表中去。
                   \BBExtend\model\UserUpdates::insert_record( $recordDB );
                }
                
                // 2 邀约活动，3，个人认证,4大赛，1才艺秀。
                if ($this->type==1 || $this->type==\BBExtend\fix\TableType::bb_record__type_updates ) {
                    $ach = new \BBExtend\user\achievement\Neirong($this->uid);
                    $ach->update(1);
                }
                if ($this->type==2) {
                    $ach = new \BBExtend\user\achievement\Huodong($this->uid);
                    $ach->update(1);
                }
                if ($this->type==4) {
                    $ach = new \BBExtend\user\achievement\Dasai($this->uid);
                    $ach->update(1);
                }
                
                // xieye 2016 11 22
                \BBExtend\user\Tongji::getinstance($this->uid)->renzheng_movie();
                
                $this->push_fensi($this->uid,$this->record_id,$title);
                
//                 // 谢烨，20180105，给星推官发消息
//                 $sql="select * from bb_record_invite_starmaker
//                        where record_id = {$this->record_id}
//                          and status=1
//                  ";
//                 $invite = $db->fetchRow($sql);
//                 if ($invite) {
//                     $client = new \BBExtend\service\pheanstalk\Client();
//                     $client->add(
//                             new \BBExtend\service\pheanstalk\Data($invite['starmaker_uid'],
//                                     MessageType::yaoqing_dianping, ['record_id' => $this->record_id ,], time()  )
//                             );
//                 }
                
                if ($this->type==4) { // 大赛
                    
                    // 谢烨特别设置，大赛的短视频，啊，审核通过时，要记录一条日志。
                    \BBExtend\backmodel\RaceLog::check($this->record['activity_id'], $this->uid, 1);
                    
                    Message::get_instance()
                        ->set_title('系统消息')
                        ->add_content(Message::simple()->content("恭喜您提交的"))
                        ->add_content(Message::simple()->content($title)->color(0xf4a560)  )
                        ->add_content(Message::simple()->content('已通过审核，请进入'))
                        ->add_content(Message::simple()->content('个人中心')->color(0x32c9c9)
                                ->url(json_encode(['type'=>1 ]) )
                                )
                        ->add_content(Message::simple()->content('->我的短视频查看。'))
                        ->set_type(129)
                        ->set_uid($this->uid)
                        ->send();
                }else {               //活动
                    
//                     $ach = new \BBExtend\user\achievement\Neirong($uid);
//                     $ach->update(1);
                    
                    Message::get_instance()
                    ->set_title('系统消息')
                    ->add_content(Message::simple()->content("恭喜您提交的"))
                    ->add_content(Message::simple()->content($title)->color(0xf4a560)  )
                    ->add_content(Message::simple()->content('已通过审核，请进入'))
                    ->add_content(Message::simple()->content('个人中心')->color(0x32c9c9)
                            ->url(json_encode(['type'=>1 ]) )
                            )
                            ->add_content(Message::simple()->content('->我的短视频查看。'))
                            ->set_type(115)
                            ->set_uid($this->uid)
                            ->send();
                    
                }
            } else {  // 失败消息
                
                if ($this->type==4) { // 大赛
                
                    // 谢烨特别设置，大赛的短视频，啊，审核不通过时，也要记录一条日志。
                    \BBExtend\backmodel\RaceLog::check($this->record['activity_id'], $this->uid, 2);
                    
                    
                Message::get_instance()
                    ->set_title('系统消息')
                    ->add_content(Message::simple()->content("很遗憾由于"))
                    ->add_content(Message::simple()->content( $reason )->color(0xf4a560)  )
                    ->add_content(Message::simple()->content('，您提交的'))
                    ->add_content(Message::simple()->content($title)->color(0xf4a560)  )
                    ->add_content(Message::simple()->content('没有通过审核，请重新提交，如有疑问请联系客服。'))
                    ->set_type(130)
                    ->set_uid($this->uid)
                    ->send();
                } else {
                    Message::get_instance()
                        ->set_title('系统消息')
                        ->add_content(Message::simple()->content("很遗憾由于"))
                        ->add_content(Message::simple()->content( $reason )->color(0xf4a560)  )
                        ->add_content(Message::simple()->content('，您提交的'))
                        ->add_content(Message::simple()->content($title)->color(0xf4a560)  )
                        ->add_content(Message::simple()->content('没有通过审核，请重新提交，如有疑问请联系客服。'))
                        ->set_type(116)
                        ->set_uid($this->uid)
                        ->send();
                }
            }
        }
        return $result;
    }
    
    
    private function push_fensi($uid, $record_id, $title) {
        //你的好友#玩家昵称#，开启了直播，点击进入直播间
        \Resque::setBackend('127.0.0.1:6380');
    
        $args = array(
            'uid'  => $uid,
            'record_id' => $record_id,
            'title' =>$title,
            'type' => '123',
        );
     //   \Resque::enqueue('jobs3', 'Jobjuhe', $args);
        \Resque::enqueue('jobswork', '\app\command\controller\Workjob', $args);
    }
    

    /**
     * 退波币
     */
    private function tuibobi()
    { 
        if ($this->audit==2) {
            $db = Sys::get_container_db_eloquent();
            $sql ="select * from bb_record_invite_starmaker where gold>0 and record_id = ?";
            $row = DbSelect::fetchRow($db, $sql,[ $this->record_id ]);
            if ($row) {
                // 这里要退还波币
                Currency::add_bobi($this->uid, $row['gold'], '邀请退还');
                $sql = "update bb_record_invite_starmaker set gold=0 where id=?";
                $db::update($sql,[ $row['id'] ]);
            }
        }
    }
    
    /**
     * 审核操作
     * @throws \Exception
     */
    public function check_all()
    {
//         if (!in_array($this->type, [2,3]) ) {
//             $this->message='type参数错误，非邀约';
//             return  false;
//         }
        $audit = $this->audit;
        $db = Sys::get_container_db();
        
    //    $this->tuibobi();
        
        if ($this->type == 6) { // 普通短视频。//1秀场，2邀约，3个人认证
            
            $sql = "select * from bb_record where id = ". $this->record_id;
            $recordDB = $db->fetchRow($sql);
            $change=['audit' => $audit ];
            if ($audit == $recordDB['audit']) {
                $this->message='重复审核错误';
                return false;
            }
            if ($recordDB['usersort']==0) {
                $change['usersort'] =2;// type=1 秀场， usersort=2 才艺秀。
            }
            if ($audit == 2) {
                $change['fail_reason'] = $this->fail_reason;
            }
            
            $db->update("bb_record", $change,"id=". $this->record_id);
            if ($audit==1) {
                $db->update("bb_record", ['audit_success_time' => time() ],"id=". $this->record_id);
            }
            
            
            return true;
        }

        if ($this->type == 4) { // 普通短视频。//1秀场，2邀约，3个人认证，4大赛短视频。
            $sql = "select * from bb_record where id = ". $this->record_id;
            $recordDB = $db->fetchRow($sql);
            $change=['audit' => $audit ];
            if ($audit == $recordDB['audit']) {
                $this->message='重复审核错误';
                return false;
            }
           
            if ($audit == 2) {
                $change['fail_reason'] = $this->fail_reason;
            }
        
            $db->update("bb_record", $change,"id=". $this->record_id);
            if ($audit==1) {
                $db->update("bb_record", ['audit_success_time' => time() ],"id=". $this->record_id);
            }
            return true;
        }
        
//         $this->act_id = $this->record['activity_id'];
//         $this->activity = Db::table('bb_task_activity')->where('id', $this->act_id)->find();
//         if (!$this->activity) {
//             throw  new \Exception('activity not found');
//         }
//         $this->task_id = $this->activity['task_id'];
        
//         if ($this->audit==1) {
//            return $this->success();
//         }
        
//         if ($this->audit==2) {
//             if ($this->type==3) {
//                 return $this->fail_type3();
//             }
//            return $this->fail();
//         }
         $this->message='参数错误';
        return  false;
        
    }
    
    private function success()
    {
        // 如果已经认证过，绝对不允许再做任何操作。
        //方法,
        if ($this->has_success()) { //重要啊，必须检查。
            $this->message ='该用户已经有认证视频';
            return false;
        }
        // 否则，需要把视频的audit设为1
        $db = Sys::get_container_db();
        $sql ="update bb_record set audit=1 where id = " . $this->record_id;
        $db->query($sql);
        //2017 04
        $db->update("bb_record", ['audit_success_time' => time() ],"id=". $this->record_id);
        
        
        //然后，修改
        $help = new Activity($this->uid, $this->act_id );
        $help->canjia($this->act_id);
        $help->checked();
        
        //把任务置为已完成
        $manager = TaskManager::getinstance($this->uid);
        $manager->success_complete($this->task_id); // 会自动设置个人认证的用户状态
        
        
        return true;
    }
    
    /**
     * 个人认证，认证失败
     */
    private function fail_type3()
    {
//         if ($this->has_success()) { //重要啊，必须检查。
//             $this->message ='已认证成功的活动时不允许反向操作的。';
//             return false;
//         }
        // 否则，需要把视频的audit设为2
        $db = Sys::get_container_db();
        $sql ="update bb_record set audit=2 where id = " . $this->record_id;
        $db->query($sql);
    
        $sql ="update bb_record set fail_reason=? where id = " . $this->record_id;
        $db->query($sql, $this->fail_reason);
    
        // 如果这个人已经成功了，则忽略 
        $sql="select attestation from bb_users where uid=".intval($this->uid);
        $att = $db->fetchOne($sql);
        if ($att != 2){
            //然后，修改
            $help = new Activity($this->uid, $this->act_id );
            $help->un_canjia($this->act_id);
            if ($this->type ==3) {
                BBUser::set_attestation($this->uid,3); //user表状态3表示 审核失败。
            }
        }
    
        return true;
    }
    
    private function fail()
    {
        if ($this->has_success()) { //重要啊，必须检查。
            $this->message ='已认证成功的活动时不允许反向操作的。';
            return false;
        }
        // 否则，需要把视频的audit设为2
        $db = Sys::get_container_db();
        $sql ="update bb_record set audit=2 where id = " . $this->record_id;
        $db->query($sql);
        
        $sql ="update bb_record set fail_reason=? where id = " . $this->record_id;
        $db->query($sql, $this->fail_reason);
        
        
        //然后，修改
        $help = new Activity($this->uid, $this->act_id );
        $help->un_canjia($this->act_id);
        if ($this->type ==3) {
            BBUser::set_attestation($this->uid,3); //user表状态3表示 审核失败。
        }
        
        return true;
    }
    
    private function has_success()
    {
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_user_activity
                where has_checked=1
                  and uid = ". $this->uid ."
                  and activity_id = ". $this->act_id ;
        
        $sql ="
                select count(*) from bb_record where type in (2,3) and audit=1 and uid=".
                $this->uid
                ." and activity_id =  " . $this->act_id;
        
        return $db->fetchOne($sql);
    }
   

}