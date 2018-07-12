<?php
/**
 * 短视频星推官模块
 */

namespace app\record\controller;
use think\Config;
use BBExtend\model\User;
use BBExtend\model\Record;
use BBExtend\model\RecordInviteStarmaker;
use BBExtend\model\RecordInviteStarmakerFail;
use BBExtend\model\Starmaker as St;

use BBExtend\Currency;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\message\Message;
use BBExtend\fix\MessageType;
use BBExtend\fix\TableType;


class Starmaker
{
    /**
     * 邀请星推官点评
     */
    public function invite($record_id, $uid, $token,$starmaker_uid)
    {
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        $starmaker = St::where('uid', $starmaker_uid)->first();
        if (!$starmaker) {
            return ['code'=>0,'message'=>'starmaker_uid error'];
        }
        
        $record = Record::find( $record_id );
        if (!$record) {
            return ['code'=>0,'message'=>'record  not exists'];
        }
        // 查是否存在记录。
        $exists = RecordInviteStarmaker::where( "record_id",$record_id )->first();
        if ($exists) {
            return ['code'=>0,'message'=>'该视频已被邀请过，不可再次邀请点评'];
        }
        
        // 只有本人才能邀请。
        if ($uid != $record->uid) {
            return ['code'=>0,'message'=>'只能本人才能邀请导师点评 '];
        }
        
        
        $pay = 50;
        if ($user->currency->gold < 50 ) {
            return ['code'=>\BBExtend\fix\Err::code_yuebuzu ,'message'=>'您的BO币不足'];
        }
        
        $result=  Currency::add_bobi($uid, 0-$pay, '邀请点评',0);
        
        $invite = new RecordInviteStarmaker();
        $invite->uid = $uid;
        $invite->record_id = $record_id;
        $invite->status = TableType::bb_record_invite_starmaker__type_yiyaoqing;
        
        $invite->new_status=2;
        
        $invite->create_time = time();
        $invite->gold = $pay;
        $invite->starmaker_uid = $starmaker_uid;
        $invite->save();
        
        if ( $record->is_checked() ) {
            $client = new \BBExtend\service\pheanstalk\Client();
            $client->add(
                new \BBExtend\service\pheanstalk\Data($starmaker_uid,
                    MessageType::yaoqing_dianping, ['record_id' => $record_id ,], time()  )
            );
        }
        
        
        return ['code'=>1 ];
    }
    
    /**
     * 切换导师
     */
    public function change($uid=0,$last_uid=0)
    {
        $starmaker = new \BBExtend\model\UserStarmaker();
        return ['code'=>1, 'data'=>$starmaker->get_random($uid,$last_uid) ];
    }
    
    /**
     * 导师列表
     */
    public function get_starmaker_list_page($uid=0,$start=0,$length=10,$level=0, $hobby=0, $week=0 
            ,$name='')
    {
        $starmaker = new \BBExtend\model\UserStarmaker();
        $lists = $starmaker->get_list($uid,$start,$length,$level, $hobby, $week, $name);
        return ['code'=>1, 'data'=>[
                'list'=>  $lists,
                'is_bottom' => count($lists)== $length ?0:1,
               // 'sql' => $starmaker->sql,
                
        ], 
        ];
    }
    
    /**
     * 查询该视频是否上锁。
     * 
     * 判断资格：
                这个视频没有锁，或者锁过期。
                
                如果失败，不做什么。
                
                如果成功，
                当一个人开始点评时，取消这个用户的 所有其他视频锁。
                给这个视频加锁
                
                同时，完成点评，修改代码
                去除这个邀请的锁。
     * 
     * @param unknown $uid
     * @param unknown $token
     * @param unknown $record_id
     * @param unknown $count
     * @param unknown $info
     */
    public function query($uid, $token, $record_id)
    {
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->is_show_starmaker() ) {
            return ['code'=>0,'message'=>'您不是导师，无权限操作'];
        }
        
        $record = Record::find( $record_id );
        if (!$record) {
            return ['code'=>0,'message'=>'record  not exists'];
        }
        if ($record->audit != 1) {
            return ['code'=>0,'message'=>'短视频认证后才可以点评'];
        }
        
        // 查是否存在记录。
        $answer = RecordInviteStarmaker::where( "record_id",$record_id )
            ->where( 'status',1 )
            ->first();
        if (!$answer) {
            return ['code'=>0,'message'=>'该视频未被邀请过或已经点评过'];
        }
        // 检查锁。10分钟
        if ( $answer->lock_time >0 && ( (time() - $answer->lock_time) < 10 * 60  ) 
              &&  ( $answer->lock_uid != $uid)
                ) {
            return ['code'=>0,'message'=>'其他星推官正在点评此视频'];
        }
        // 现在成功了，给这个用户的其他锁全部去除
        $db = Sys::get_container_db_eloquent();
        $sql = "update bb_record_invite_starmaker set lock_time=0, lock_uid=0 where starmaker_uid = ?";
        $db::update( $sql,[$uid] );
        // 成功时，还需要把本次邀请加锁
        $answer->lock_uid = intval($uid);
        $answer->lock_time = time();
        $answer->save();
        return ['code'=>1, ];
    }
    
    
    /**
     * 导师点评。
     * 
     * 有几种出错情况，
     * 没人邀请。
     * 已解答过。
     * 
     * 
     * 
     */
    public function evaluate($uid, $token, $record_id, $count, $info, $answer_type=1,
        $media_url='',$media_duration=0, $media_pic='' )
    {
        if (!in_array( $answer_type, [
            TableType::bb_record_invite_starmaker__answer_type_duanshipin,
            TableType::bb_record_invite_starmaker__answer_type_wenzi,
            TableType::bb_record_invite_starmaker__answer_type_yuyin,
        ])) {
            return ['code'=>0,'message'=>'type error'];
        }
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->is_show_starmaker() ) {
            return ['code'=>0,'message'=>'您不是导师，无权限操作'];
        }
        
        
        $record = Record::find( $record_id );
        if (!$record) {
            return ['code'=>0,'message'=>'record  not exists'];
        }
        // 查是否存在记录。
        $answer = RecordInviteStarmaker::where( "record_id",$record_id )
           ->where( 'status',1 )
           ->where( 'starmaker_uid',$uid )
           ->first();
        if (!$answer) {
            return ['code'=>0,'message'=>'该视频未被邀请过或已经点评过'];
        }
        
        $count = intval($count);
        if ($count <1 || $count > 50) {
            return ['code'=>0,'message'=>'赞数错误'];
        }
        $info =  trim( strval( $info ));
        if ((!$info) && $answer_type== TableType::bb_record_invite_starmaker__answer_type_wenzi  ) {
            return ['code'=>0,'message'=>'评论内容不能为空'];
        }

        // 增加赞数
        $db = Sys::get_container_db_eloquent();
//         $sql ="update bb_record set `like`=`like`+{$count},
//            real_like=real_like+{$count} where id=? ";
//         $db::update($sql, [ $record_id ]);
        // 被点评人成就增加。
//         $ach = new \BBExtend\user\achievement\Zhubo($record->uid);
//         $ach->update($count);

        $answer->answer_time =time();
        // 1表示文字回复。
        $answer->answer_type =  $answer_type ;
        $answer->media_url = strval($media_url);
        $answer->media_pic = strval($media_pic);
        $answer->media_duration = intval($media_duration);
        
        $answer->answer = $info;
        $answer->starmaker_uid = $uid;
        // 2表示已正常回复过，未审核。3是审核过。
        $answer->status = TableType::bb_record_invite_starmaker__type_yidianping; 
        $answer->new_status = 3;
        
        $answer->zan_count = $count;
        
        // 谢烨 2017 1027
         $answer->lock_time =0;
         $answer->comment_time = time();
         $answer->new_status = 3;
//         $answer->lock_uid =0;
        $answer->save();
        
        
        $id = intval( $answer->id);
        $sql="select * from bb_record_invite_starmaker
               where id = {$id}
             ";
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $result = \BBExtend\DbSelect::fetchRow($db, $sql);
        $db::table('bb_record_invite_starmaker_log')->insert($result);
        
        // 发给被点评人消息
        $user22 = \app\user\model\UserModel::getinstance($user->uid);
        $pic = $user22->get_user_pic_no_http();
        /*
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content( "导师 ". $user['nickname'])->color(0xf4a560)
                ->url(json_encode(['type'=>2, 'other_uid'=>$user->uid ]) )
                )
            ->add_content(Message::simple()->content(' 评论了你的视频，快去看看吧！'))
            ->add_content(Message::simple()->content($record->title)->color(0xf4a560)  )
            ->set_type( MessageType::daoshi_dianping  )
            ->set_uid($record->uid)
            ->set_other_uid($user->uid)
            ->set_other_record_id($record->id)
            ->send();
            */
        return ["code"=>1,'data' =>[ 'status' => 
            TableType::bb_record_invite_starmaker__type_yidianping,
                'gold' => $answer->gold,
        ] ];
        
    }
    
    
    /**
     * 导师看到的点评列表。
     * 
     * @param unknown $uid
     * @param number $startid
     * @param number $length
     * @return number[]|string[]|number[]|number[][]|string[][][][]|unknown[][][][]|number[][][][][]|string[][][][][]|boolean[][][][][]|NULL[][][][][]|unknown[][][][][]|unknown[][][][][][]
     */
    public function lists($uid,$startid=0,$length=10)
    {
        $startid=intval($startid);
        $length=intval($length);
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->is_show_starmaker() ) {
            return ['code'=>0,'message'=>'您不是导师，无权限操作'];
        }
        
        $db = Sys::get_container_db_eloquent();
        $sql ="select * from bb_record_invite_starmaker  
                where  starmaker_uid=?
                  and exists(
                    select 1 from bb_record
                     where bb_record.id = bb_record_invite_starmaker.record_id
                       and bb_record.type in (?,?,?)
                       and bb_record.audit = 1
                       and bb_record.is_remove=0
                  )
                order by status asc,
                create_time asc
                limit {$startid},{$length}
                ";
        $result = DbSelect::fetchAll($db, $sql,
                [  
                    $uid,
                    
                  TableType::bb_record__type_dasai,
                  TableType::bb_record__type_yaoyue,
                  TableType::bb_record__type_xiuchang,
                    
                  
        ]);
        //dump($result);
        $new=[];
        foreach ($result as $v) {
            $temp=[];
            // 苹果想要字符串。
            $temp['invite_time'] = strval( $v['create_time']);
            
            $temp['status'] = $v['status'];
            
            $sql="select * from bb_record where id = ?";
           // dump($v);
            $video2 = DbSelect::fetchRow($db, $sql,[ $v['record_id'] ]);
            //dump($video2);
            $temp['video'] = \BBExtend\BBRecord::get_detail_by_row($video2,$uid) ;
            $temp['word'] = "【". $temp['video']['nickname'] ."】邀请您点评";
            // 已评 0xc1c1c1,进行中 0x8ce57d， 超时2天 0xff811d，超时3天 0xf03611
            $color_time = 0xc1c1c1;
            $word_time='';
            
            
            $time_display = new \BBExtend\common\Mygettime( $v['create_time']  ) ;
            $word_time = $time_display->display();
            $color_time=0xaaaaaa ; // 注意，要改成灰色
            if ($v['status'] == 1 ) {
                // 超过2天
                if ( time() - $v['create_time'] > 2* 24 * 3600 ) {
                    $color_time=0xff811d;
                }
                if ( time() - $v['create_time'] > 3* 24 * 3600 ) {
                    $color_time=0xf03611;
                }
                
            }
            // 下面是 右侧，未评，进行中，已评。
            $color_status=0xaaaaaa;
            $word_status='';
            if (  $v['status'] == 1 ) {
                if ( time() - $v['create_time'] > 2* 24 * 3600 ) {
                    $word_status='超时';
                    $color_status=0xff811d;
                }
                if ( time() - $v['create_time'] > 3* 24 * 3600 ) {
                    $word_status='超时';
                    $color_status=0xf03611;
                }
            }
            if (  $v['status'] == 2 ) {
                $word_status='进行中';
                $color_status=0x8ce57d;
            }
            
            if (  $v['status'] == 3 ) {
                $word_status='已评';
                $color_status=0xc1c1c1;
            }
            $temp['word_status'] = $word_status;
            $temp['color_status'] = $color_status;
            $temp['word_time'] = $word_time;
            $temp['color_time'] = $color_time;
            
            $new[] = $temp;
        }
        $is_bottom = count($result)==$length ? 0:1;
        
       
        
        return [
            'code'=>1,
            'data'=>[
                'list' => $new,
                'is_bottom' =>$is_bottom,
//                 'qrcode' => \BBExtend\common\BBConfig::get_share_server_url()."/",
                    
                  'qrcode' => 'http://www.guaishoubobo.com/skin/images/but_bjt_ewm.png',
                    'has_follow_wechat'   => intval(  $user->has_focus_wechat_official_accounts() ),
                    'has_open_wechat_push'   =>0,
                    //|   has_follow_wechat   | int | 1已关注我们公司微信公众号，0没有// |
            ],
            
        ];
        
    }
    
    /**
     * 获取单个视频的点评详情
     *
     * @param unknown $uid
     * @param number $startid
     * @param number $length
     * @return 
     */
    public function one_comment( $record_id )
    {
        $record = Record::find( $record_id );
        if (!$record) {
            return ['code'=>0,'message'=>'record  not exists'];
        }
        // 查是否存在记录。
        $answer = RecordInviteStarmaker::where( "record_id",$record_id )->first();
        if (!$answer    ) {
            // 状态为0，表示从未邀请过。
            return ['code'=>1,'data'=>['status' => 0,'info'=>null  ]];
        }
        
        if ( $answer->status== TableType::bb_record_invite_starmaker__type_yiyaoqing
            || $answer->status==TableType::bb_record_invite_starmaker__type_yidianping   ) {
            // 状态为0，表示从未邀请过。
            return ['code'=>1,'data'=>[ 'status'=> $answer->status,'info'=>null  ]];
        }
        
        $user = \app\user\model\UserModel::getinstance($answer->starmaker_uid);
        return [
            'code'=>1,
            'data' => [
                'status' => $answer->status,
                'info' =>[
                    'create_time'=> $answer->create_time,
                    'zan_count' =>$answer->zan_count,
                    'answer_time' => $answer->answer_time,
                    'answer_type' => $answer->answer_type,
                    'answer' => $answer->answer,
                    'uid' => $answer->starmaker_uid,
                    'nickname' => $user->get_nickname(),
                    'pic' => $user->get_userpic(),  
                    'media_url'     =>$answer->media_url,
                    'media_duration'=>$answer->media_duration,
                    'media_pic'     =>$answer->media_pic,
                ],
            ],
        ];
    }
    
    /**
     * 获取单个视频的点评详情,多个评论
     *
     * @param unknown $uid
     * @param number $startid
     * @param number $length
     * @return
     */
    public function comments( $record_id )
    {
        $record = Record::find( $record_id );
        if (!$record) {
            return ['code'=>0,'message'=>'record  not exists'];
        }
        
        $audit = $record->audit; // 0未审核，1成，2失败。
        
        $display_switch=1;// 显示，0开关禁止
        
        // 查是否存在记录。
        $answer = RecordInviteStarmaker::where( "record_id",$record_id )->first();
        if (!$answer    ) {
            // 状态为0，表示从未邀请过。
            return ['code'=>1,'data'=>[ 'display_switch'=>$display_switch, 'status' => 0,'audit'=>$audit,'info'=>null  ]];
        }
    
        if ( $answer->status== TableType::bb_record_invite_starmaker__type_yiyaoqing
                || $answer->status==TableType::bb_record_invite_starmaker__type_yidianping   ) {
                    // 状态为0，表示从未邀请过。
                    return ['code'=>1,'data'=>['display_switch'=>$display_switch,'audit'=>$audit, 'status'=> $answer->status,'info'=>null  ]];
                }
    
                $user = \app\user\model\UserModel::getinstance($answer->starmaker_uid);
                
                $t=[];
                $user_detail = \BBExtend\model\User::find( $answer->starmaker_uid);
                
                $t['role'] = $user_detail->role;
                $t['frame'] = $user_detail->get_frame();
                $t['badge'] = $user_detail->get_badge();
                
                return [
                    'code'=>1,
                    'data' => [
                        'status' => $answer->status,
                        'audit'=>$audit,
                        'display_switch'=>$display_switch,
                        'info' =>[
                            [
                            'create_time'=> $answer->create_time,
                            'zan_count' =>$answer->zan_count,
                            'answer_time' => $answer->answer_time,
                            'answer_type' => $answer->answer_type,
                            'answer' => $answer->answer,
                            'uid' => $answer->starmaker_uid,
                            'nickname' => $user->get_nickname(),
                            'pic' => $user->get_userpic(),
                            'media_url'     =>$answer->media_url,
                            'media_duration'=>$answer->media_duration,
                            'media_pic'     =>$answer->media_pic,
                                    
                                    "role" => $t['role'] ,
                                    "frame" =>  $t['frame'] ,
                                    "badge" => $t['badge']  ,
                                    
                        ],
                       ]
                    ],
                ];
    }
    
    /**
     * 给后台，调用，审核失败的
     */
    public function fail($invite_id,$comment )
    {
        $db = Sys::get_container_db_eloquent();
        $invite = RecordInviteStarmaker::find($invite_id);
        if (!$invite) {
            return ['code'=>0,'message' =>'邀请不存在' ];
        }
        // 谢烨，这里，必须是一个人有评论的，且必须处于待审核状态
        if ($invite->status != TableType::bb_record_invite_starmaker__type_yidianping  ){
            return ['code'=>0,'message' =>'必须是一个人有评论的，且必须处于待审核状态' ];
        }
        $comment = strval($comment);
        if (!$comment) {
            return ['code'=>0,'message' =>'必须填写审核失败的理由' ];
        }
        $fail = new RecordInviteStarmakerFail();
        $fail->uid = $invite->uid;
        $fail->record_id = $invite->record_id;
        $fail->status = $invite->status;
        $fail->create_time = $invite->create_time;
        $fail->starmaker_uid = $invite->starmaker_uid;
        $fail->answer = $invite->answer;
        $fail->answer_type = $invite->answer_type;
        $fail->answer_time = $invite->answer_time;
        $fail->zan_count = $invite->zan_count;
        $fail->media_duration = $invite->media_duration;
        $fail->media_url = $invite->media_url;
        $fail->media_pic = $invite->media_pic;
        $fail->gold = $invite->gold;
        $user = \app\user\model\UserModel::getinstance($invite->uid);
        $fail->user_pic = $user->get_userpic();
        $fail->user_nickname= $user->get_nickname();
        $fail->reason = $comment;
        
        $fail->comment_time = $invite->comment_time;
        $fail->check_time = time();
        
        $fail->save();
        
//         $db = Sys::get_container_db_eloquent();
//         $sql="select * from bb_record where id=?";
//         $record_row = DbSelect::fetchRow($db, $sql,[ $invite->record_id ]);
//         $row = \BBExtend\BBRecord::get_detail_by_row($record_row,10000);
        
        
        // 现在发消息给倒霉的星推官
        $client = new \BBExtend\service\pheanstalk\Client();
        $client->add(
            new \BBExtend\service\pheanstalk\Data($invite->starmaker_uid,
                \BBExtend\fix\MessageType::yaoqing_dianping_fail,    
                ['key' => $fail->id,'other_uid' => $invite->uid, ], time()  )
        );
        
        // 最后还原状态
        $invite->status= TableType::bb_record_invite_starmaker__type_yiyaoqing;
      //  $invite->starmaker_uid=0; 
        $invite->answer='';
        $invite->answer_type=   0;
        $invite->answer_time=0;
        
        $invite->comment_time=0;
        
        $invite->zan_count=0;
        $invite->lock_time=0;
        $invite->lock_uid=0;
        $invite->media_duration=0;
        $invite->media_url='';
        $invite->media_pic='';
        if ( $invite->push_type==1 ) { //指定导师
            $invite->new_status =6; 
        }
        if ( $invite->push_type==2 ) {//自己群发
            $invite->new_status =5;
            $invite->starmaker_uid=0;
        }
        if ( $invite->push_type==3 ) {  //官方群发
            $invite->new_status =5;
            $invite->starmaker_uid=0;
        }
        $invite->check_time=time();
        
        //注意：gold字段完全不变！！
        $invite->save();
        
        
        // 下面都是日志
        $id = intval( $invite->id);
        $sql="select * from bb_record_invite_starmaker
               where id = {$id}
             ";
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $result = \BBExtend\DbSelect::fetchRow($db, $sql);
        
        $result['answer'] = $fail->answer;
        $result['answer_type'] = $fail->answer_type;
        $result['answer_time'] = $fail->answer_time;
        $result['comment_time'] = $fail->comment_time;
        $result['zan_count'] = $fail->zan_count;
        $result['media_duration'] = $fail->media_duration;
        $result['media_url'] = $fail->media_url;
        $result['media_pic'] = $fail->media_pic;
        $result['starmaker_uid'] = $fail->starmaker_uid;
        
        
        $result['reason'] = $comment;
        $db::table('bb_record_invite_starmaker_log')->insert($result);
        
        
        return ["code"=>1 ];
        
    }
    
    /**
     * 系统消息的审核失败详情页
     * @param unknown $key
     */
    public function get_fail($key)
    {
        $fail = RecordInviteStarmakerFail::find($key);
        if (!$fail) {
            return ['code'=>0,'message'=>'key error'];
        }
        return ['code'=>1,'data'=>[
            'create_time' => $fail->create_time,
            'answer' => $fail->answer,
            'answer_type' =>$fail->answer_type,
            'zan_count'   =>$fail->zan_count,
            'media_duration' => $fail->media_duration,
            'media_url' => $fail->media_url,
            'media_pic' => $fail->media_pic,
            'pic'  => $fail->user_pic,
            'nickname' => $fail->user_nickname,
            'reason' =>$fail->reason,
        ]];
        
    }
    
    
    
    /**
     * 星推官web详情页
     * @param number $ds_id
     */
    public function detail($uid=0)
    {
        $db = Sys::get_container_db();
        $type = Config::get("http_head_mobile_type"  );
        $css = ($type=='android')? "/html5/css/style.css" : "/html5/css/style_ios.css";
        $ds_id = intval($uid);
        $sql="select * from bb_users_starmaker where uid={$ds_id}";
        
        $detail_arr = $db->fetchRow($sql);
        if (!$detail_arr) {
            return false;
        }
        
        
        $detail=strval($detail_arr['html_info']);
        $title = strval($detail_arr['title']);
        $s=<<<html
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1.0"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name='apple-mobile-web-app-status-bar-style' content='black'>
    <meta name='format-detection' content='telephone=no'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$title}-详情</title>
    <link rel="stylesheet" type="text/css" href="{$css}">
    <script type="text/javascript" src="/share/js/jquery.min.js"></script>
    <script type="text/javascript" src="/share/js/Adaptive.js"></script>
</head>

<body>
<div class="main" id="main">
{$detail}
</div>
</body>
</html>

html;
echo $s;
    }
    
      
}