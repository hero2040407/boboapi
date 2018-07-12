<?php
/**
 * 视频点赞类
 * 
 * User: 谢烨
 */
namespace BBExtend\video\help;


use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Date;
use BBExtend\model\Record;
use BBExtend\model\User;

use BBExtend\user\exp\Exp;

use BBExtend\message\Message;
use BBExtend\fix\MessageType;
use BBExtend\Currency;



class Like 
{
    protected  $err = null;
    
    protected  $table; // 这是like表
    protected  $video;
    protected  $room_id;
    protected  $user;
    protected  $uid;
    protected  $video_table;// 这是视频表
    
    public function __construct( $room_id, $uid)
    {
        $this->room_id =strval( $room_id);
        $this->uid = intval( $uid);
        

        $this->user = User::find($uid);
        if (!$this->user) {
            $this->err = '用户不存在';
        }
        
    }
    
    /**
     * 错误检查，对象生成后必须调用
     */
    public function has_error()
    {
        return $this->err;
    }
    
    
    /**
     * 工厂方法，决定子类
     * 
     * @param unknown $type
     * @param unknown $room_id
     * @param unknown $uid
     * @return 
     */
    public static function factory($type, $room_id,$uid)
    {
        switch ($type)
        {
            case 'push':
                return new PushLike( $room_id, $uid);
            case 'record':
                return new RecordLike( $room_id, $uid);
            case 'rewind':
                return new RewindLike( $room_id, $uid);
            default:
                return null;
        }
    }
    
    // 注意哦，最多20个。
    private function record_redis_like($uid,$room_id)
    {
        $key ="record:like:room_id:". $room_id ;
        // 谢烨，我打算用list。
        $db = Sys::get_container_db_eloquent();
        $sql="select nickname from bb_users where uid=?";
        $name = DbSelect::fetchOne($db, $sql,[$uid]);
        $redis = Sys::get_container_redis();
        $redis->lRem($key, $name, 1000);//去除重复
        $redis->lPush($key, $name);
        $redis->lTrim($key, 0, 19);// 修剪
        
        $key ="record:like:room_id:display_id:". $room_id ;
        // 谢烨，我打算用list。
        $redis = Sys::get_container_redis();
        $redis->lRem($key, $uid, 1000);//去除重复
        $redis->lPush($key, $uid);
        $redis->lTrim($key, 0, 19);// 修剪
        
    }
    
    /**
     * 点赞公共逻辑，适合3种表
     */
    public  function run()
    {
        $db = Sys::get_container_db_eloquent();
        $time1 = Date::pre_day_start();
        $time2 = Date::pre_day_end();
        
        $table = $this->table;
        $uid = $this->uid;
        $room_id = $this->room_id;
        $video = $this->video;
    
        $sql = "select * from {$table}
                     where uid =?
                       and room_id=?
                       and (time between ? and ?)
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
                    'buy'=>null,
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
        $db::table($table )->insert([
            'uid' => $uid,
            'room_id' => $room_id,
            'time' => time(),
            'count' => 1,
        ]);
        
        
        // xieye 201803修改。
        
        if ($this->video_table =='bb_record' || $this->video_table =='bb_rewind' ) {
            // xieye,这是点赞逻辑。
            $this->record_redis_like($uid, $room_id);
        }
        
        
        if ($this->video_table =='bb_record') {
        
            // 谢烨，2018 0 6 改变 短视频点赞，冗余某个点赞字段，但是有条件，大赛结束后，就不那个了。
            // 先检查是否属于大赛，如果大赛，是否未过期，如果未过期，就 
            \BBExtend\model\DsRecord::like($video->id);
            
            $sql ="update {$this->video_table} 
                set `like`=`like` +1,real_like=real_like+1 where room_id=? ";
        }else {
            $sql ="update {$this->video_table}
               set `like`=`like` +1 where room_id=? ";
        }
        
        
        $db::update($sql, [ $room_id ]);
        // 统计
        \BBExtend\user\Tongji::getinstance($uid)->zan($video->uid  );
        // 经验
        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_ACTIVITY_LIKE )->add_exp();
    
        // 成就
        $ach = new \BBExtend\user\achievement\Dianzan($uid);
        $ach->update(1);
        $ach = new \BBExtend\user\achievement\Zhubo($video->uid);
        $ach->update(1);
    
        $user22 = \app\user\model\UserModel::getinstance($uid);
        $pic = $user22->get_userpic();
        
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->set_pic_uid($uid)
            ->add_content(Message::simple()->content( $user22->get_nickname()  )->color(0xf4a560)
                ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                )
            ->add_content(Message::simple()->content('赞了你的视频'))
            ->add_content(Message::simple()->content($video->title)->color(0xf4a560)  )
            ->set_type( MessageType::shipin_beizan )
            ->set_uid($video->uid)
            ->set_other_uid($uid)
            ->set_other_record_id($video->id)
            ->send();
        return [
            'code'=>1,
            'data'=>[
                'status' => 1,//点赞成功，赞数可能加1，可能加5，看count字段
                'count'  => 1,
                'message' =>"",
                'buy'=>null,
            ]
        ];
    }
    
    
    /**
     * 大赞逻辑
     *
     * @param \BBExtend\model\User $user
     * @param unknown $room_id
     */
    public function big_run(\BBExtend\model\User $user ,$room_id)
    {
        $db = Sys::get_container_db_eloquent();
        $time1 = Date::pre_day_start();
        $time2 = Date::pre_day_end();
        
        $table = $this->table;
        $uid = $this->uid;
        $room_id = $this->room_id;
        $video = $this->video;
    
        $sql = "select * from {$table}
                     where uid =?
                       and room_id=?
                       and time between ? and ?
                       and count > 1
                    ";
        $rlike = DbSelect::fetchRow($db, $sql,[ $uid, $room_id, $time1, $time2 ]);
    
        // 以现在的逻辑看，一天内最多只有两条，一条普通，一条大赞。
        if ($rlike) {
            return [
                'code'=>1,
                'data'=>[
                    'status' => 3,//表示当日您已经大赞过。不能再有什么操作
                    'count'  =>0,
                    'message' =>"今天已经赞过了！\n明天再来吧 : )",
                    'buy'=>null,
                ]
            ];
        }
        //波币扣减
        $result =  Currency::add_bobi($uid, -10, '大赞', MessageType::shipin_beizan);
        if (!$result) {
            return ['code'=> \BBExtend\fix\Err::code_yuebuzu,'message' =>'您的余额不足' ];
        }
    
        // 如果都没大赞过
        $db::table($table)->insert([
            'uid' => $uid,
            'room_id' => $room_id,
            'time' => time(),
            'count' => 5,
        ]);
        $sql ="update {$this->video_table} 
            set `like`=`like` +5,real_like=real_like+5 where room_id=? ";
        $db::update($sql, [ $room_id ]);
        // 统计
        \BBExtend\user\Tongji::getinstance($uid)->zan($video->uid  );
        // 经验
        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_ACTIVITY_LIKE )->add_exp();
    
        // 成就
        $ach = new \BBExtend\user\achievement\Dianzan($uid);
        $ach->update(1);
        $ach = new \BBExtend\user\achievement\Zhubo($video->uid);
        $ach->update(1);
    
        $user22 = \app\user\model\UserModel::getinstance($uid);
        $pic = $user22->get_userpic();
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content( $user22->get_nickname()  )
                ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                )
            ->add_content(Message::simple()->content('赞了你的视频'))
            ->add_content(Message::simple()->content($video->title)  )
            ->set_type( MessageType::shipin_beizan )
            ->set_uid($video->uid)
            ->set_other_uid($uid)
            ->set_other_record_id($video->id)
            ->send();
        return [
            'code'=>1,
            'data'=>[
                'status' => 1,//点赞成功，赞数加1
                'count' =>5,
                'message' =>"",
                'buy'=>null,
            ]
        ];
    }
    

}