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


use BBExtend\model\UserStarmaker;
use BBExtend\BBRecord;

class StarmakerV2
{
    // 导师身份
    /**
     * 导师抢单
     * 
     * @param unknown $uid
     * @param unknown $token
     * @param unknown $record_id
     * @return number[]|string[]|unknown[]|number[]|string[]|number[]
     */
    public function grab($uid, $token, $record_id)
    {
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
        
        $record = Record::find( $record_id );
        if (!$record) {
            return ['code'=>0,'message'=>'record  not exists'];
        }
        // 非常重要的一句话
        if ($record->audit != 1) {
            return ['code'=>0,'message'=>'短视频认证后才可以点评'];
        }
        
        
        // 查是否存在记录。
        $answer = RecordInviteStarmaker::where( "record_id",$record_id )
          ->first();
        if (!$answer) {
            return ['code'=>0,'message'=>'该视频未被邀请过'];
        }
        
        // 定义进程通信的键，放在redis中。
        $channel = 'dianping:'. md5( uniqid() . mt_rand(100000,999999));
        
        $client = new \BBExtend\service\pheanstalk\Client();
        $client->add_dianping(
                new \BBExtend\service\pheanstalk\Datadp($uid, $record_id, time(),$channel  )
        );
        $redis = Sys::get_container_redis();
        
        $result = $redis->blPop($channel , 30);// 30秒是等待时间，是阻塞的，超过这个时间，则返回错误。
        $redis->delete($channel );
        
        if (isset($result[1])) {
            $result = unserialize($result[1]);
            return $result;
            
        }
        
        //dump($result[1]);
        return ['code'=>0, 'message'=>'请求超时失败'];
    }
    
    
   /**
    * 导师身份
    * 导师邀请主页
    */
    public function detail($uid,$token)
    {
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
        
        $rank_obj = \BBExtend\user\StarmakerRanking::getinstance();
        $rank = $rank_obj->get_rank($uid);
        $db = Sys::get_container_db_eloquent();
        
        $sql="select * from bb_record_invite_starmaker where status=3
               order by id desc limit 5
        ";// 3已审核
        $rows = DbSelect::fetchAll($db, $sql);
        $gundong=[];
        
        foreach ($rows as $row) {
            $user_starmaker = User::find($row['starmaker_uid']);
            $user_normal = User::find($row['uid']);
            $str_arr = [
                 'pre'=>   $user_starmaker->get_nickname()."导师点评了",
                 'val'=>   $user_normal->get_nickname(),
                 'post'  => "获得".$row['gold']."BO豆",
            ];
            $gundong[]= $str_arr;
        }
        
        return ['code'=>1,'data'=>[
                'info'=>$gundong,
                'rank' =>$rank,
        ]];
    }
    
    private function check($uid, $token)
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
        return ['code'=>1,'data'=>$user ];
        
    }
    
    /**
     * 这是导师身份
     * 新的系统消息失败接口。
     * 
     * @param unknown $uid
     * @param unknown $token
     * @param unknown $id
     * @return number[]|string[]|unknown[]|number[]|string[]|number[]|string[][]|number[][]|unknown[][]|NULL[][]
     */
    public function get_fail($uid, $token, $id)
    {
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
        $db = Sys::get_container_db_eloquent();
    //    $weitongguo_type=0;

        $fail = RecordInviteStarmakerFail::find($id);
        if (!$fail) {
            return ['code'=>0,'message'=>'key error'];
        }
       
        //$success=0;
        
        $new=[];
        $new['weitongguo_type']=0;
        $user = \BBExtend\model\User::find($fail->starmaker_uid );
        
        $new['role'] = $user->role;
        $new['frame'] = $user->get_frame();
        $new['badge'] = $user->get_badge();
        
        
        $new['pic'] = $user->get_userpic();
        $new['nickname'] =$user->get_nickname();
        $new['zan_count'] = $fail->zan_count;
        
        $new['answer_time'] =$fali->comment_time;
        $new['answer_type'] =$fail->answer_type;
        $new['answer'] =$fail->answer;
        $new['media_duration'] =$fail->media_duration;
        $new['media_url'] =$fail->media_url;
        $new['media_pic'] =$fail->media_pic;
        
        $new['record_id'] =$fail->record_id;
        
        $new['reason'] =$fail->reason;
       
        // 怎么得知该短视频，是可以继续点评，还是他人已经
        //
        //weitongguo_type 	int 	0不要显示下面的大按钮， 1可以重新点评，2已被其他导师点评。
        // 查最新状态
        $sql="select * from bb_record_invite_starmaker
                   where record_id = ?
                     limit 1
";
        $new_row = DbSelect::fetchRow($db, $sql,[ $fail->record_id ]);
        if ($new_row) {
            
            if ($new_row['starmaker_uid'] >0 && $new_row['starmaker_uid']!=  $uid ) {
                $new['weitongguo_type'] =2;
            }
            if ($new_row['starmaker_uid'] ==0 || $new_row['starmaker_uid']==  $uid ) {
                
                $sql="select * from bb_record where id=".$fail->record_id;
                $temp = DbSelect::fetchRow($db, $sql);
                if ($temp['is_remove']==0 && $temp['audit']==1 ) {
                    $new['weitongguo_type'] =1;
                }
            }
        }
        
        return ['code'=>1,'data'=>$new];
    }
    
    
    /**
     * 导师身份
     * 视频邀请点评，审核详情
     * 
     * @param unknown $uid
     * @param unknown $token
     * @param unknown $id
     * @param unknown $word
     * @return number[]|string[]|unknown[]|number[]|string[]
     */
    public function invite_info($uid, $token, $id)
    {
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
        $db = Sys::get_container_db_eloquent();
        
     //   $weitongguo_type=0;
        
        //$success=0;
       
            $sql="select * from bb_record_invite_starmaker_log
                   where 
                     starmaker_uid = ?
                     and logid = ?

";
            $row  = DbSelect::fetchRow($db, $sql,[ $uid, $id ]);
            if (!$row) {
                return ['code'=>0,'message'=>'info not exists'];
            }
            
//             $success=1;
       // }
       
//        if ($success) {
            $new=[];
            $new['weitongguo_type']=0;
            
            $user = \BBExtend\model\User::find($row['starmaker_uid'] );
            $new['pic'] = $user->get_userpic();
            $new['nickname'] =$user->get_nickname();
            $new['zan_count'] =$row['zan_count'];
            $new['answer_time'] =$row['comment_time'];
            $new['answer_type'] =$row['answer_type'];
            $new['answer'] =$row['answer'];
            $new['media_duration'] =$row['media_duration'];
            $new['media_url'] =$row['media_url'];
            $new['media_pic'] =$row['media_pic'];
            
            $new['record_id'] =$row['record_id'];
            
            $new['reason'] =$row['reason'];
            if ( $row['new_status']==4 ) {
                $new['reason'] ='审核已通过，获得'. $row['gold'] .'BO豆，请至财富中心查看';
            }
            // 怎么得知该短视频，是可以继续点评，还是他人已经
            // 
            //weitongguo_type 	int 	0不要显示下面的大按钮， 1可以重新点评，2已被其他导师点评。
         // 查最新状态
            $sql="select * from bb_record_invite_starmaker
                   where record_id = ?
                     limit 1
";
            $new_row = DbSelect::fetchRow($db, $sql,[ $row['record_id'] ]); 
            if ($new_row) {
            
                if ($new_row['starmaker_uid'] >0 && $new_row['starmaker_uid']!=  $uid ) {
                    $new['weitongguo_type'] =2;
                }
                if ( ($new_row['starmaker_uid'] ==0 || $new_row['starmaker_uid']==  $uid) && 
                        $new_row['status'] ==1
                        ) {
                    
                    $sql="select * from bb_record where id=".$row['record_id'];
                    $temp = DbSelect::fetchRow($db, $sql);
                    if ($temp['is_remove']==0 && $temp['audit']==1 ) {
                         $new['weitongguo_type'] =1;
                    }
                }
            }
            
            
            return ['code'=>1,'data'=>$new];
  //      }
      //  return ['code'=>0,'message' =>'id err' ];
        
    }
    
    
    /**
     * type==1 表示点评广场，
     * type==2 表示专属邀请
     * type==3，表示官方推送
     * 
     *  push_type 1指定某个导师邀请，2抢单模式邀请，3官方推送
     * 导师身份
     * 
     * 邀请列表
     * 
     * @param unknown $uid
     * @param unknown $token
     * @param unknown $type
     */
    public function invite_list($uid, $token, $type,$startid=0,$length=10)
    {
        $uid=intval( $uid );
        $startid=intval($startid);
        $length=intval($length);
        
        $starmaker = \BBExtend\model\UserStarmaker::where('uid', $uid )->first();
        $sql_part='';
        if ($type==1 || $type==3 ) {
            $labels = $starmaker->preference;
            if ($labels) {
            
                $sql_part = "  and find_in_set(bb_record.label,'{$labels}' ) ";
            }
        }
        
        
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
        
        $db = Sys::get_container_db_eloquent();
        $sql="
           select * from bb_record_invite_starmaker
             where status=1

              and exists(
                   select 1 from bb_record
                    where bb_record.id = bb_record_invite_starmaker.record_id
                      and bb_record.is_remove=0
                      and bb_record.audit=1
                      {$sql_part}
            )

";
        if ($type==1) {
          //  $pay=50;
            $sql.="
              and starmaker_uid=0
              and push_type=2
";
        }
        if ($type==2) {
            
            $sql.="
              and starmaker_uid={$uid}
              
";
        }
        if ($type==3) {
           // $pay=50;
            $sql.="
              and starmaker_uid=0
              and push_type=3
";
        }
        $sql.=" 

              order by id desc
              limit {$startid},{$length}
 ";
      //  Sys::debugxieye($sql);
        
        $result2 = DbSelect::fetchAll($db, $sql);
        $new=[];
        foreach ($result2 as $v) {
            $temp=[];
            $temp['pay'] = $v['gold'];
            $sql = "select * from bb_record where id= ". intval( $v['record_id']);
            $row = DbSelect::fetchRow($db, $sql);
            $temp['video'] = BBRecord::get_detail_by_row($row, $uid);
            $new[] = $temp;
            
        }
        return [
            'code'=>1,
            'data'=>['list' => $new,
                    'is_bottom' =>( count( $new )==$length )?0:1,
            ],
        ];
    }
    
    /**
     * 已完成列表
     * 导师身份
     * 
     * @param unknown $uid
     * @param unknown $token
     * @param number $startid
     * @param number $length
     * @return number[]|string[]|unknown[]|number[]|number[][]|NULL[][]|string[][][][]|number[][][][]|unknown[][][][]
     */
    public function invite_complete_list($uid, $token,$startid=0,$length=10)
    {
        $uid=intval( $uid );
        $startid=intval($startid);
        $length=intval($length);
        
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
        
        $starmaker = \BBExtend\model\UserStarmaker::where('uid',  $user->uid)->first();
        
        
        $db = Sys::get_container_db_eloquent();
        
        $sql = "
         select a.* 
from bb_record_invite_starmaker_log a
 where a.starmaker_uid = {$uid}
and a.new_status in (4,5,6)
and a.logid= (
 select max(logid) from bb_record_invite_starmaker_log where record_id = a.record_id
  and starmaker_uid = {$uid}
  and new_status in (4,5,6)
)
and exists (select 1 from bb_record where bb_record.id = a.record_id ". 
// and bb_record.is_remove=0
  " and bb_record.audit=1
)

order by a.logid desc
limit {$startid},{$length}

";
        $result2 = DbSelect::fetchAll($db, $sql);
        
        $new=[];
        foreach ($result2 as $v) {
            $temp=[];
            if ($v['new_status']==4) {
                //$sql="select * from bb_record_invite_starmaker where id=".$v['id'];
                $temp['status_word'] ='已通过';
                $temp['status_word_color'] =0x333333;
                $temp['gold'] =$v['gold'];
            }else {
                $temp['status_word'] ='未通过';
                $temp['status_word_color'] =0xff3b3b;
                $temp['gold'] =0;
            }
            
            $tempuser = \BBExtend\model\User::find($v['uid']);
            
            $temp['nickname'] =$tempuser->get_nickname();
            $temp['comment_time'] =$v['comment_time'];
            $temp['id'] = $v['logid'];
            $new[]= $temp;
        }
        
        return [
                'code'=>1,
                'data'=>['gold'=> $starmaker->income,
                        'is_bottom' => ( count( $new )==$length )?0:1,
                        'list' => $new,
                        ],
        ];
    }
    
    /**
     * 排行榜
     * 导师身份
     * 
     * type=1按点评收入
     * type=2 按粉丝排行
     * 
     * @param unknown $startid
     * @param unknown $length
     * @param number $type
     */
    public function ranking($startid, $length,$type=1)
    {
        $type=intval($type);
        $startid=intval($startid);
        $length=intval($length);
        
        $db = Sys::get_container_db_eloquent();
        
        if ($type==1) {
            $sql="select * from  bb_users_starmaker
  where is_show=1
   order by income desc
   limit ?,?
";
            $result = DbSelect::fetchAll($db, $sql,[ $startid,$length ]);
            $new=[];
            foreach ($result as $v) {
                $temp=[];
                $user = \BBExtend\model\User::find($v['uid']);
                $temp['uid'] = $v['uid'];
                $temp['pic'] = $user->get_userpic() ;
                $temp['nickname'] = $user->get_nickname() ;
                $temp['sex'] = $user->get_usersex() ;
                $temp['level'] = $v['level'] ;
                
                $temp['fans_count'] = 0 ;
                $temp['income'] =  $v['income'] ;
                
                $new[] = $temp;
            }
            
        }
        if ($type==2) {
            $help = new \BBExtend\user\StarmakerRankingFans();
            $result = $help->get_list($startid, $length);
            $new=[];
            foreach ($result as $v) {
                $temp=[];
                $user = \BBExtend\model\User::find($v['uid']);
                $temp['uid'] = $v['uid'];
                $temp['pic'] = $user->get_userpic() ;
                $temp['nickname'] = $user->get_nickname() ;
                $temp['level'] = $v['level'] ;
                $temp['sex'] = $user->get_usersex() ;
                
                
                $temp['fans_count'] = $v['fans_count'] ;
                $temp['income'] =0;
                
                $new[] = $temp;
            }
        }
        
        if ($type==3) {
            $sql="select * from  bb_users_starmaker
  where is_show=1
   order by id desc
   limit ?,?
";
            $result = DbSelect::fetchAll($db, $sql,[ $startid,$length ]);
            $new=[];
            foreach ($result as $v) {
                $temp=[];
                $user = \BBExtend\model\User::find($v['uid']);
                $temp['uid'] = $v['uid'];
                $temp['pic'] = $user->get_userpic() ;
                $temp['nickname'] = $user->get_nickname() ;
                $temp['sex'] = $user->get_usersex() ;
                $temp['level'] = $v['level'] ;
                
                $temp['fans_count'] = 0 ;
                $temp['income'] =  $v['income'] ;
                
                $new[] = $temp;
            }
            
        }
        
        return [
                'code'=>1,
                'data'=>[
                        'list'=> $new,
                        'is_bottom' =>  (  count( $new )== $length ) ? 0:1,
                ]
        ];
    }
    
    
    /**
     * 邀请星推官点评，
     * 
     * 用户身份
     */
    public function invite($record_id, $uid, $token,$starmaker_uid=0)
    {
        $starmaker_uid = intval( $starmaker_uid );
        
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        
        if ($starmaker_uid) {
          //  $starmaker = UserStarmaker::where('uid', $starmaker_uid)->first();
            
            $starmaker = UserStarmaker::check_and_get($starmaker_uid);
            if (!$starmaker) {
                return ['code'=>0,'message'=>'starmaker_uid error'];
            }
            $pay = $starmaker->get_price();
            
        }else {
            $pay=50;
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
        
        
       // $pay = 50;
        if ($user->currency->gold < $pay ) {
            return ['code'=>\BBExtend\fix\Err::code_yuebuzu ,'message'=>'您的BO币不足'];
        }
        
        $result=  Currency::add_bobi($uid, 0-$pay, '邀请点评',0);
        
        $invite = new RecordInviteStarmaker();
        $invite->uid = $uid;
        $invite->record_id = $record_id;
        $invite->status = TableType::bb_record_invite_starmaker__type_yiyaoqing;
        $invite->create_time = time();
        
        $invite->starmaker_uid = $starmaker_uid;
        $invite->gold = $pay;
        if ( $starmaker_uid ) {
            $invite->push_type =1;
            $invite->new_status=2;
           // $invite->gold = $starmaker->get_price();
        }else {
            $invite->push_type =2; // 2群发。
            $invite->new_status=1;
            //$invite->gold = $pay;
        }
        
        $invite->save();
        
        // 已审核短视频邀请时，立刻发消息。
        if ( $record->is_checked() && $starmaker_uid  ) {
            $client = new \BBExtend\service\pheanstalk\Client();
            $client->add(
                    new \BBExtend\service\pheanstalk\Data($starmaker_uid,
                            MessageType::yaoqing_dianping, ['record_id' => $record_id ,], time()  )
                    );
        }
        
        // 日志处理
        $id = intval( $invite->id);
        $sql="select * from bb_record_invite_starmaker
               where id = {$id}
             ";
        $db = Sys::get_container_db_eloquent();
        $result = DbSelect::fetchRow($db, $sql);
        $db::table('bb_record_invite_starmaker_log')->insert($result);
        
        
        return ['code'=>1 ];
    }
    
    
    
    
    
    /**
     * 用户身份
     * 
     * 视频邀请点评V2我的短视频
     * 
     * @param number $startid
     * @param number $length
     * @param unknown $uid
     * @return number[]|number[][]|unknown[][][]
     */
    public function myrecord($startid=0,$length=10,$uid )
    {
        $db = Sys::get_container_db_eloquent();
        $sql="
 select * from bb_record
where uid = ?
and type != 3
and audit in (0, 1)
and is_remove=0
and not exists(
  select 1 from bb_record_invite_starmaker
    where bb_record_invite_starmaker.record_id = bb_record.id
)
  order by id desc
  limit ? ,? 
";
        $result = DbSelect::fetchAll($db, $sql,[ $uid,$startid, $length ]);
        $new=[];
        foreach ($result as $k => $v) {
            $temp =  \BBExtend\BBRecord::get_detail_by_row($v, $uid);
            $temp['audit'] = $v['audit'];
            
            $new[]=$temp;
        }
        return [
                'code'=>1,
                'data' =>[
                        'is_bottom' =>( count($new) == $length )?0:1,
                        'list' =>$new,
                ]
        ];
        
    }
    
    
    public function set_preference($uid, $token,$ids='')
    {
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
        $db = Sys::get_container_db_eloquent();
        $sql="select id,name from bb_label where is_show=1";
        $result = DbSelect::fetchAll($db, $sql);
        $new_key = [];
        foreach ($result as $v) {
            $new_key[]= $v['id'];
        }
        
        $new = [];
        foreach ($result as $v) {
            $new[ $v['id'] ]= $v['name'];
        }
        $id_arr = explode(',', $ids);
        $result = false;
        $temp=[];
        foreach ($id_arr as $id) {
            if (in_array( $id, $new_key )) {
                $result=true;
                $temp[]= $id;
            }
        }
        
        if (!$result) {
            return ['code'=>0,'message'=>'至少得选择一项'];
        }
        $starmaker = \BBExtend\model\UserStarmaker::where('uid', $uid )->first();
        $starmaker->preference = implode(',', $temp);
        $starmaker->save();
        return ['code'=>1];
        
    }
    
    
    public function get_preference($uid, $token)
    {
        $result = $this->check($uid, $token);
        if ($result['code']==0) {
            return $result;
        }
        $user = $result['data'];
      //  return 33;
        $starmaker = \BBExtend\model\UserStarmaker::where('uid', $uid )->first();
        
        $db = Sys::get_container_db_eloquent();
        $sql="select id,name from bb_label where is_show=1";
        $result = DbSelect::fetchAll($db, $sql);
        $new_key = [];
        foreach ($result as $v) {
            $new_key[]= $v['id'];
        }
        $new = [];
        foreach ($result as $v) {
            $new[ $v['id'] ]= $v['name'];
        }
        $preference=[];
        if ($starmaker->preference =='' ) {
            $preference =   $new_key;
        }else {
            $preference = explode(',', $starmaker->preference);
        }
        $result=[];
        // 返回给客户端
        foreach ( $new as $id => $name ) {
            $temp = [
                    'id' => intval($id),
                    'name' => $name,
                    'is_checked' =>0,
            ];
            
            if (in_array( $id, $preference )) {
                $temp['is_checked']= 1;
            }
            $result[] = $temp;
        }
        return ['code'=>1,'data'=>['list'=> $result  ]];
    }
    
    
}