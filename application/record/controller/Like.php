<?php
namespace app\record\controller;


use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Date;
use BBExtend\model\Record;
use BBExtend\user\exp\Exp;

use BBExtend\message\Message;
use BBExtend\fix\MessageType;
use BBExtend\Currency;
use app\push\controller\Pushmanager;
use app\push\controller\Rewindmanager;

/**
 * 视频点赞控制器
 * 
 * @author xieye
 *
 */
class Like
{
    /**
     * 大赞
     * @param unknown $uid
     * @param unknown $room_id
     * @param string $type
     * @return number[]|string[]|string[]|number[]
     */
    public function biglike($uid, $room_id, $type='push')
    {
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0, 'message'=>'uid error'];
        }
    
        switch ($type)
        {
            case 'push':
                $result = Pushmanager::_like($uid,$room_id);
                if ($result['code']==1) {
                    $result['data']=['status'=>1];
                }
                
                return $result;
            case 'record':
                return $this->record_big_like($user, $room_id);
            case 'rewind':
                $result = Rewindmanager::like($uid,$room_id);
                if ($result['code']==1) {
                    $result['data']=['status'=>1];
                }
                return $result;
        }
        return ['message'=>'传入类型错误','code'=>0];
    }
    
    
    /**
     * 点赞
     * 
     * @param unknown $uid
     * @param unknown $room_id
     * @param string $type
     */
    public function like($uid, $room_id, $type='push')
    {
        Sys::display_all_error();
        $obj = \BBExtend\video\help\Like::factory($type, $room_id,$uid);
        if ($obj) { 
           if ( $err = $obj->has_error() ) {
               return ['message'=>$err, 'code'=>0];
           }else {
               $result = $obj->run();
               if ($type!='record') {
                   $result['data']['status'] =1;
               }
               return $result;
           }
        } else {
           return ['message'=>'传入类型错误','code'=>0];
        }
    }

    
    /**
     * 短视频点赞逻辑
     * 
     * @param \BBExtend\model\User $user
     * @param unknown $room_id
     */
    private function record_like(\BBExtend\model\User $user ,$room_id)
    {
        $record = Record::where( "room_id",$room_id )->first() ;
        $uid = $user->uid;
        if (!$record) {
            return ['code'=>0,'message'=>'room_id error'];
        }
        $db = Sys::get_container_db_eloquent();
        $time1 = Date::pre_day_start();
        $time2 = Date::pre_day_end();
        
        $sql = "select * from bb_record_like
                     where uid =?
                       and room_id=?
                       and time between ? and ?
                    ";
        $rlike = DbSelect::fetchAll($db, $sql,[ $uid, $room_id, $time1, $time2 ]);
        
        // 以现在的逻辑看，一天内最多只有两条，一条普通，一条大赞。
        $count5=$count1 = 0;
        foreach ($rlike as $like) {
            if ($like['count']==5) {
                $count5=1;
            }
            if ($like['count']==1) {
                $count1=1;
            }
        }
        if ($count5) {
            return [
                'code'=>1,
                'data'=>[
                    'status' => 3,//表示当日您已经大赞过。不能再有什么操作
                    'count'  =>0,
                    'message' =>"今天已经赞过了！\n明天再来吧 : )",
                    'buy'=>[],
                ]
            ];
        }
        if ($count1) {
            return [
                'code'=>1,
                'data'=>[
                    'status' => 2,//表示当日您已经点过赞，但未点过大赞。
                    'count' =>0,
                    'message' =>"今天已经赞过了！\n明天再来吧 : )",
                    'buy' =>[
                        'buy_message' =>"倾情大赞+5 ( 50BO币 )",
                        'count' => 5,
                        'pay'   => 50,
                    ],
                ]
            ];
        }
        // 如果一次都没赞过
        $db::table('bb_record_like')->insert([
            'uid' => $uid,
            'room_id' => $room_id,
            'time' => time(),
            'count' => 1,
        ]);
        $sql ="update bb_record set `like`=`like` +1,real_like=real_like+1 where room_id=? ";
        $db::update($sql, [ $room_id ]);
        // 统计
        \BBExtend\user\Tongji::getinstance($user->uid)->zan($record->uid  );
        // 经验
        Exp::getinstance($user->uid)->set_typeint(Exp::LEVEL_ACTIVITY_LIKE )->add_exp();
        
        // 成就
        $ach = new \BBExtend\user\achievement\Dianzan($user->uid);
        $ach->update(1);
        $ach = new \BBExtend\user\achievement\Zhubo($record->uid);
        $ach->update(1);
        
        $user22 = \app\user\model\UserModel::getinstance($user->uid);
        $pic = $user22->get_user_pic_no_http();
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content($user['nickname'])->color(0xf4a560)
            ->url(json_encode(['type'=>2, 'other_uid'=>$user->uid ]) )
            )
            ->add_content(Message::simple()->content('赞了你的视频'))
            ->add_content(Message::simple()->content($record->title)->color(0xf4a560)  )
            ->set_type( MessageType::shipin_beizan )
            ->set_uid($record->uid)
            ->set_other_uid($user->uid)
            ->set_other_record_id($record->id)
            ->send();
        return [
            'code'=>1,
            'data'=>[
                'status' => 1,//点赞成功，赞数可能加1，可能加5，看count字段
                'count'  => 1,
                'message' =>"",
                'buy'=>[],
            ]
        ];
    }
    
    /**
     * 短视频大赞逻辑
     *
     * @param \BBExtend\model\User $user
     * @param unknown $room_id
     */
    private function record_big_like(\BBExtend\model\User $user ,$room_id)
    {
        $record = Record::where( "room_id",$room_id )->first() ;
        if (!$record) {
            return ['code'=>0,'message'=>'room_id error'];
        }
        $db = Sys::get_container_db_eloquent();
        $time1 = Date::pre_day_start();
        $time2 = Date::pre_day_end();
        $uid = $user->uid;
        $sql = "select * from bb_record_like
                     where uid =?
                       and room_id=?
                       and time between ? and ?
                       and count > 1
                    ";
        $rlike = DbSelect::fetchRow($db, $sql,[ $uid, $room_id, $time1, $time2 ]);
    
        // 以现在的逻辑看，一天内最多只有两条，一条普通，一条大赞。
        if ($rlike) {
            return [
                'code'=>0,
                'data'=>[
                    'status' => 3,//表示当日您已经大赞过。不能再有什么操作
                    'count'  =>0,
                    'message' =>"今天已经赞过了！\n明天再来吧 : )",
                    'buy'=>null,
                ]
            ];
        }
        $uid = $user->uid;
        //这里必须检查波币。
        if ($user->currency->gold < 50 ){
            return ['code'=> \BBExtend\fix\Err::code_yuebuzu,'message' =>'您的余额不足' ];
        }
      
        $dbzend = Sys::get_container_db();
        $sql="insert into bb_record_like(uid,room_id, time, count)
select ?,?,?,? from  bb_record_like
where not exists (select 1 from bb_record_like b2
  where b2.uid =?
    and b2.room_id=?
    and b2.time > ?
    and b2.count>1            
)
limit 1";
        $statement= $dbzend->query($sql,  [$uid,$room_id,time(), 5,$uid,$room_id,$time1,  ]);
        if ($statement->rowCount() == 0) {
            return [
                'code'=>0,
                'data'=>[
                    'status' => 3,//表示当日您已经大赞过。不能再有什么操作
                    'count'  =>0,
                    'message' =>"今天已经赞过了！\n明天再来吧 : )",
                    'buy'=>null,
                ]
            ];
        }
        //波币扣减，50，
        $result =  Currency::add_bobi($uid, -50, '大赞', 0);
        if (!$result) {
            return ['code'=> \BBExtend\fix\Err::code_yuebuzu,'message' =>'您的余额不足' ];
        }
        
        
//         // 如果都没大赞过
//         $db::table('bb_record_like')->insert([
//             'uid' => $uid,
//             'room_id' => $room_id,
//             'time' => time(),
//             'count' => 5,// 增加的赞数
//         ]);
        $sql ="update bb_record set `like`=`like` +5,real_like=real_like+5 where room_id=? ";
        $db::update($sql, [ $room_id ]);
        // 统计
        \BBExtend\user\Tongji::getinstance($user->uid)->zan($record->uid  );
        // 经验
        Exp::getinstance($user->uid)->set_typeint(Exp::LEVEL_ACTIVITY_LIKE )->add_exp();
    
        // 成就
        $ach = new \BBExtend\user\achievement\Dianzan($user->uid);
        $ach->update(1);
        $ach = new \BBExtend\user\achievement\Zhubo($record->uid);
        $ach->update(1);
    
        $user22 = \app\user\model\UserModel::getinstance($user->uid);
        $pic = $user22->get_user_pic_no_http();
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content($user['nickname'])->color(0xf4a560)
                ->url(json_encode(['type'=>2, 'other_uid'=>$user->uid ]) )
                )
            ->add_content(Message::simple()->content('赞了你的视频'))
            ->add_content(Message::simple()->content($record->title)->color(0xf4a560)  )
            ->set_type( MessageType::shipin_beizan )
            ->set_uid($record->uid)
            ->set_other_uid($user->uid)
            ->set_other_record_id($record->id)
            ->send();
        return [
            'code'=>1,
            'data'=>[
                'status' => 1,//点赞成功，赞数加1
                'count' =>5,// 增加的赞数。
                'message' =>"",
                'buy'=>null,
            ]
        ];
    }
    
 
   
}