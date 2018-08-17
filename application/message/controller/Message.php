<?php
namespace app\message\controller;
use BBExtend\BBMessage;
use think\Db;
use BBExtend\PushMsg;
use BBExtend\Sys;
use BBExtend\fix\MessageType;

include_once( realpath( EXTEND_PATH)."/umeng/UmengPush.php");

/**
 * Created by PhpStorm.
 * User: CY
 * 
 * 谢烨201706 设计思路
 * 有些消息半小时发送一次，我的思路是，
 * 不建另外的表了，当需要发送消息时，存在reids里面。
 * 做系统定时任务，
 * 每隔半小时，把这些信息收集起来，放到队列里，
 * 要点是，一旦放到队列，必须删除redis里面的信息。
 * 
 * redis类型，哈希表。存放到11库中。
 * 
 * 
 * 然后，队列和普通一样发送！
 * 
 * 
 * Date: 2016/7/13
 * Time: 18:27
 */
class Message extends BBMessage
{
    /**
     * 调试用接口
     * 
     * /message/message/node_push/uid/111/type/3/data/记住在get中传递需先urlencode
     * 
     * $uid,如在正式服上测试，请一定使用自己的测试帐号
     * $type,    const MSG_NEW_MESSAGE = 1; // 有新的消息
    const MES_NEW_ANNOUNCEMENT = 2; // 有新的公告
    const MES_CANCEL_LAHEI = 3; //取消拉黑
    const MES_CANCEL_ZHIBO = 4; // 解禁直播
     * 
     * data 是标准的json，记住在get中传递需先urlencode，例如取消拉黑是
     *   {"nickname":"谢烨","uid":1111,"message":"有人取消拉黑"}
     */
    public function node_push($uid,$type,$data)
    {
        \BBExtend\BBNodeJS::SendMessage($uid, $type, $data);
        
    }
    
    //api
    //推送消息
    public function push_message()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $type = input('?param.type')?(int)input('param.type'):0;
        if ($uid)
        {
            PushMsg::Push_message($uid,$type);
            return ['code'=>1,'message'=>'发送成功'];
        }
        return ['code'=>0,'message'=>'uid不能为0'];
    }
    //得到所有消息
    public function get_msg()
    {
     //   Sys::display_all_error();
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        
        if ($length> 200) {
            $length=200;
        }
        
        $use_json = input('?param.use_json')?input('param.use_json'):0; // 谢烨，2016 12 
        
        // xieye 2016 12 19,必须以id排序，否则bug
        $MsgDB = Db::table('bb_msg')->where('uid',$uid)
          ->where("info != ''")
          ->limit($start_id,$length)->order([ 'sort'=>'desc', 'id'=>'desc'])->select();
        $id_arr=[];
        foreach ($MsgDB as $v) {
            $id_arr[]= $v['id'];
        }
        $db = Sys::get_container_db();
        $sql = "update bb_msg set is_read=1 where id in (?)";
        $sql = $db->quoteInto($sql, $id_arr);
       // echo $sql;
        if ($id_arr) {
          $db->query($sql);
        }
          
//         foreach ($MsgDB as $v) {
//             Db::table('bb_msg')->where("id <= {$v['id']}")->where("uid",$uid)->update(['is_read'=>1]);
//             break; // 谢烨，此处不要修改，杨桦。
//         }
        
        // 2016 12 沈德志要求。全json返回。
        if ($use_json) {
            $new = [];
            foreach ($MsgDB as $v) {
                $temp = $v;
                $temp['info'] = json_decode($temp['info'], true);
                $new[]= $temp;
                
            }
            $MsgDB = $new;
        }
        
        $unread_count = Db::table('bb_msg')->where("uid",$uid )->where('is_read',0)->count();
        
        if (count($MsgDB) == $length)
        {
            return ['data'=>$MsgDB,'is_bottom'=>0,'code'=>1,'unread_count'=>$unread_count,];
        }
        else
        {
            return ['data'=>$MsgDB,'is_bottom'=>1,'code'=>1,'unread_count'=>$unread_count,];
        }
    }
    
    //删除消息
    public function del_msg()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):'';
        $id = input('?param.id')?(int)input('param.id'):'';
        $MsgDB = Db::table('bb_msg')->where('id',$id)->find();
        if ($MsgDB)
        {
            Db::table('bb_msg')->where('id',$id)->delete();
            return ['message'=>'删除成功','code'=>1];
        }
        return ['message'=>'没有这个消息','code'=>0];
    }
    //读取消息
    public function read_msg()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):'';
        $id = input('?param.id')?(int)input('param.id'):'';
        $MSGDB = Db::table('bb_msg')->where(['id'=>$id,'uid'=>$uid])->find();
        if ($MSGDB)
        {
       //     Db::table('bb_msg')->where('id',$id)->update(['is_read'=>1]);
            return ['code'=>1];
        }
        return ['code'=>0];
    }
    //得到当前是否有新的消息
    public function get_new_msg_num($uid)
    {
        $MsgDB = Db::table('bb_msg')->where(['uid'=>$uid,'is_read'=>0])->order(['time'=>'desc'])->select();
        return count($MsgDB);
    }
    
    public function houtai_msg($uid) {
        //\BBExtend\BBNodeJS::Send_new_msg_count($uid);
        $uid = intval($uid);
        $db = Sys::get_container_db();
        $sql ="select title,info from bb_msg where uid={$uid} 
        and type=1000
        order by id desc limit 1";
        $row = $db->fetchRow($sql);
        $temp = json_decode($row['info'] ,true);
        $title = $row['title'];
        $info = $temp[0]['content'];
      //  Sys::debugxieye($info);
        
        \BBExtend\message\Message::get_instance()
          ->set_title($title)
                        ->add_content(\BBExtend\message\Message::simple()->content($info))
                        ->set_type(MessageType::houtai_fasong)
                        ->set_uid($uid)
                        ->send();
    }
    
    // 153 视频标记热门
    public function record_hot_msg($record_id)
    {
        $record = \BBExtend\model\Record::find($record_id);
        if ($record) {
            $uid = $record->user->uid;
            $nickname = $record->user->nickname;
            $record_title = trim($record->title);
            $info="{$nickname}太棒了，你的短视频{$record_title}被评审员添加为今日热门视频！";
            
            \BBExtend\message\Message::get_instance()
                ->add_content(\BBExtend\message\Message::simple()->content($info)
                        ->url(json_encode(['type'=>1, ]))
                  )
                ->set_type(MessageType::video_to_hot)
                ->set_uid($uid)
                ->send();
            return ["code"=>1];
        }
    }
    
    
    /**
     * 给用户发消息，催用户缴款。
     * @param unknown $uid
     * @param unknown $ds_id
     */
    public function pay_msg($uid,$ds_id) 
    {
        $uid = intval($uid);
        $ds_id=intval($ds_id);
        
        $db = Sys::get_container_db();
        $sql ="select * from bb_baoming where uid={$uid} and ds_id={$ds_id}";
        $row = $db->fetchRow($sql);
        if ( !$row) {
            return ["code"=>0,"message"=>'id 不存在'];
        }
    
        $msg =  \BBExtend\message\Message::get_instance();
        $msg->set_title("新的短消息")
            ->add_content(\BBExtend\message\Message::simple()->content( $row['msg_content'] )
                    ->url(json_encode(['type'=>1001, 'ds_id'=>$ds_id ]))
                    )
            ->set_type(MessageType::baoming_jiaofei)
            ->set_sort(MessageType::baoming_jiaofei)
            ->set_col1($ds_id)
            ->set_uid($uid)
            ->send();
         $id = $msg->bb_msg_id;
         $db->update('bb_baoming', ["msg_id" =>$id ], " id = {$row["id"]}");
            
         return ["code"=>1];
    }
    
    
    
    public function gonggao_msg()
    {
        $info = input('post.info');
        //Sys::debugxieye($info);
        
       // exit;
        \BBExtend\message\UmengBroadcast::getinstance()
        ->set_content($info)
        ->set_production_mode("true")
        ->send_all();
        
//         $Umpush = new \UmengPush();
//         $Umpush->sendIOSBroadcast($title,$info);
        return ['code'=>1,  ];
    }
    
    /**
     * 在线通知。只给后台调用。
     * @return number[]
     */
    public function online_msg()
    {
        $info = input('post.info');
        \BBExtend\message\UmengBroadcast::getinstance()
        ->set_content($info)
        ->send_online();
        return ['code'=>1,  ];
    }
    
}