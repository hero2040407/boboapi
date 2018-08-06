<?php
namespace app\record\controller;

use think\Db;

use BBExtend\Sys;
use BBExtend\BBRecord;
use BBExtend\BBPush;
use BBExtend\Level;
use BBExtend\BBUser;

use BBExtend\user\exp\Exp;
use BBExtend\user\RecordCheck;
use BBExtend\message\Message;

use app\user\controller\User;
use BBExtend\fix\MessageType;
use BBExtend\fix\TableType;

use BBExtend\common\Client;
use BBExtend\model\RecordInviteStarmaker;
use BBExtend\Currency;

/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/12
 * Time: 9:40
 */
class Recordmanager extends BBRecord
{
 
    /**
     * 获取视频详情，201801
     * @param unknown $id
     * @param unknown $uid
     * @return number[]|string[]|number[]|number[][]|string[][]|boolean[][]|NULL[][]|unknown[][]|mixed[][]|unknown[][][]|string[][][]
     */
    public function get($id,$uid){
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0, 'message' =>'uid error' ];
        }
        
        $db = Sys::get_container_db_eloquent();
        $record = $db::table( 'bb_record' )->where('is_remove',0)
           ->where('id',$id)
           ->where('type' ,'<>',3)->first();
        
        if (!$record) {
            return ['code'=>0,'message'=>'视频不存在'];
        }
        if ( $record->audit != 1   ) {
            if ( $record->uid != $uid ) {
                return ['code'=>0,'message'=>'视频未审核通过'];
            }
            
        }
        
        
        $record = get_object_vars($record);
           
        $temp = \BBExtend\BBRecord::get_detail_by_row($record, $uid );
        return [
                'code'=>1,
                'data' =>$temp,
        ];
        
    }
    
    
    /**
     * 得到用户的视频按页
     */
    public function get_other_user_movies()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):$uid;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        // 该视频类型 1：秀场 2：邀约 3：个人验证
        // 审核 0：未审核 1：通过审核 2：未通过
        
        $db = Sys::get_container_db();
        $sql="
           select id,type,time from bb_record
where bb_record.uid={$uid} and is_remove=0
  and type !=3
  and audit=1
union all
select id,-100, start_time as time from bb_rewind 
where uid={$uid} 
  and event='rewind'
  and is_remove=0
  and is_save=1
order by time desc
limit {$startid},{$length}     
                ";
        $result  = $db->fetchAll($sql);
       // dump($result);
        $new=[];
        foreach ($result as $v) {
            if ($v['type'] == -100) { // 回播
                $sql = "select * from bb_rewind where id = {$v['id']}";
                $row = $db->fetchRow($sql);
                $temp = BBPush::get_rewind_detail_by_row($row,$query_uid);
                $temp['end_time'] = strval( $temp['publish_time'] );
                $new[]= $temp;
            }else {                    // 短视频
                $sql = "select * from bb_record where id = {$v['id']}";
                $row = $db->fetchRow($sql);
                $temp = BBRecord::get_detail_by_row($row, $query_uid );
                $temp['end_time'] = strval( $temp['publish_time'] );
                $new[]= $temp;
//                 $new[]= BBRecord::get_detail_by_row($row, $query_uid );
            }
            
        }
        return ['data'=>$new,'is_bottom'=>( count($new) == $length )?0:1,'code'=>1];
    }

    
    public function get_user_movies()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):$uid;
       
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        // 该视频类型 1：秀场 2：邀约 3：个人验证
        // 审核 0：未审核 1：通过审核 2：未通过
        if ($uid!= $query_uid) { // 看别人的短视频
        
          $MoviesDB = Db::table('bb_record')->where(['uid'=>$uid,'is_remove'=>0])
          ->where("type != 3")
          ->where("audit = 1")
          ->order('time','desc')->limit($startid,$length)->select();
        } else {                 // 看自己的短视频。
            $MoviesDB = Db::table('bb_record')->where(['uid'=>$uid,'is_remove'=>0])
           
            ->order('time','desc')->limit($startid,$length)->select();
            $new =[];
            foreach ($MoviesDB as $v) { //type =3 个人认证。
                if ( !( $v['type']==3 && $v['audit']==0) ) {
                    $new[]= $v;
                }
            }
            $MoviesDB = $new;
        }
        
        $buy_help = new \BBExtend\user\Relation();
        if ($MoviesDB)
        {
            for ($i = 0;$i<count($MoviesDB);$i++)
            {
                $MoviesDB[$i]['id'] = (int)$MoviesDB[$i]['id'] ;
                $MoviesDB[$i]['like'] = (int)$MoviesDB[$i]['like'] ;
                $MoviesDB[$i]['look'] = (int)$MoviesDB[$i]['look'] ;
                $MoviesDB[$i]['uid'] = (int)$MoviesDB[$i]['uid'] ;
                $MoviesDB[$i]['pic'] = User::get_userpic($MoviesDB[$i]['uid']);
                $MoviesDB[$i]['content_type'] = BBRecord::get_content_type(
                    $MoviesDB[$i]['room_id'], $MoviesDB[$i]['usersort'], $MoviesDB[$i]['type']);
              
                
                // xieye 2016 10 25
                $MoviesDB[$i]['has_buy'] = $buy_help->has_buy_video($query_uid, $MoviesDB[$i]['room_id'] );
                
                
                $Pic = $MoviesDB[$i]['big_pic'];
                unset($MoviesDB[$i]['big_pic']);
                $serverUrl = \BBExtend\common\BBConfig::get_server_url();
                if ($Pic) {
                    $MoviesDB[$i]['bigpic'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                            $Pic );
                    
                   
                } else {
                    $MoviesDB[$i]['bigpic'] = $MoviesDB[$i]['thumbnailpath'];
                }
                
                if ($query_uid)  {
                    $MoviesDB[$i]['is_like'] = self::get_is_like($query_uid,$MoviesDB[$i]['room_id']);
                }
                $MoviesDB[$i]['age'] = (int) User::get_userage($MoviesDB[$i]['uid']);
                
                // 2017 04 加两个字段。
                $user = \app\user\model\UserModel::getinstance($MoviesDB[$i]['uid']);
                $MoviesDB[$i]['sex']= $user->get_usersex();
                $MoviesDB[$i]['nickname']=$user->get_nickname();
                $MoviesDB[$i]['level']=$user->get_user_level();
            }
            if (count($MoviesDB) == $length) {
                return ['data'=>$MoviesDB,'is_bottom'=>0,'code'=>1];
            }
        }
        return ['data'=>$MoviesDB,'is_bottom'=>1,'code'=>1];
    }
    
    
    
    public function get_user_movies_v2()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):$uid;
        
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        
        $db = Sys::get_container_dbreadonly();
        // 该视频类型 1：秀场 2：邀约 3：个人验证
        // 审核 0：未审核 1：通过审核 2：未通过
        if ($uid!= $query_uid) { // 看别人的短视频
            
            $MoviesDB = Db::table('bb_record')->where(['uid'=>$uid,'is_remove'=>0])
            ->where("type != 3")
            ->where("audit = 1")
            ->order('time','desc')->limit($startid,$length)->select();
            
            $sql="select count(*) from bb_record where uid=? and is_remove=0
                   and type != 3 
                   and audit=1
  ";
            
            
        } else {                 // 看自己的短视频。
            $MoviesDB = Db::table('bb_record')->where(['uid'=>$uid,'is_remove'=>0])
            
            ->order('time','desc')->limit($startid,$length)->select();
            $new =[];
            foreach ($MoviesDB as $v) { //type =3 个人认证。
                if ( !( $v['type']==3 && $v['audit']==0) ) {
                    $new[]= $v;
                }
            }
            $MoviesDB = $new;
            
            $sql="select count(*) from bb_record where uid=? and is_remove=0
  ";
            
        }
        $all_count = $db->fetchOne($sql,[ $uid ]);
        
        //$buy_help = new \BBExtend\user\Relation();
        $new=[];
        foreach ( $MoviesDB as $v ) {
            $temp = \BBExtend\model\RecordDetail::find( $v['id'] );
            $temp->self_uid = $query_uid;
            $new[]= $temp->get_all();
        }
        $is_bottom = (count($new)==$length)? 0:1;
        return ['data'=>['list' =>$new, 'is_bottom'=>$is_bottom,'count'=> $all_count ],  'code'=>1];
        //return ['data'=>$MoviesDB,'is_bottom'=>1,'code'=>1];
    }
    
    
    
    //删除界面
    public function remove_rewind()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $id = input('?param.id')?(int)input('param.id'):0;
        
        if ((Client::is_android() && Client::big_than_version('3.2.3')) ||   
            (Client::is_ios()     && Client::big_than_version('3.2.0')) ) {
            $token = input('?param.token')? strval( input('param.token')):'';
            
            $user = \BBExtend\model\User::find($uid);
            if (!$user) {
                return ['code'=>0,'message'=>'uid error'];
            }
            if ( !$user->check_token($token ) ) {
                return ['code'=>0,'message'=>'uid error'];
            }
        }
        
        $Data =  Db::table('bb_record')->where(['uid'=>$uid,'id'=>$id])->find();
        if ($Data)
        {
            //$db = Sys::get_container_db();
            if ($Data['audit']==1  && isset($Data['activity_id']) &&  $Data['activity_id']>0 ) {
                return ['message'=>'该视频已参加活动且审核通过，不可删除','code'=>0];
            }
            
            
            
            
            // 谢烨 201807 这里一段逻辑，不要删除。就是下面的注释不删除。是导师的。
            
//             $obj = RecordInviteStarmaker::where("record_id", $id)->where( 'status',1 )->first();
//             if ($obj) {
//                 //退还波币，且删除邀请
//                 //if ($obj) 
//                 Currency::add_bobi($uid, $obj->gold, '邀请撤销返还' );
//                 if ($Data['audit']==1) {
//                     // 未点评，且审核通过，需要发送消息给星推官。
//                     $client = new \BBExtend\service\pheanstalk\Client();
//                     $client->add(
//                             new \BBExtend\service\pheanstalk\Data($obj->starmaker_uid,
//                                     MessageType::yaoqing_dianping_chexiao, ['record_id' => $id ,], time()  )
//                             );
                    
//                 }
                
                
//                 $obj->delete();
//             }
            

            // xieye 201807 删除短视频，检查是否同时删除动态。
            $db2 = Sys::get_container_db();
            if ( $Data['type']==6  ) {
                
                $sql="select bb_users_updates_id from  bb_users_updates_media where bb_record_id=?";
                $update_id = $db2->fetchOne($sql, $id);
                if ($update_id) {
                    $db2->update('bb_users_updates',['is_remove'=>1], 'id='.$update_id );
                }
            }
    

            Db::table('bb_record')->where(['uid'=>$uid,'id'=>$id])->update(['is_remove'=>1]);
            
            
            
            
            return ['message'=>'删除成功','code'=>1];
        } 
        return ['message'=>'删除失败','code'=>0];
    }
    
    
    public function upload_record()
    {
        
        $time_length_second = input('?post.time_length_second')?(int)input('post.time_length_second'):0;
        $baidu_citycode = input('?post.baidu_citycode')?input('post.baidu_citycode'):'';
        
        $uid = input('?post.uid')?(int)input('post.uid'):0;
        $type = input('?post.type')?(int)input('post.type'):0;//秀场 1   邀约 2  个人验证 3，       4大赛。5广告，6通告上传,7动态
        $video_path = input('?post.video_path')?(string)input('post.video_path'):'';
        $thumbnailpath = input('?post.thumbnailpath')?(string)input('post.thumbnailpath'):'';
        $activity = input('?post.activity_id')?(int)input('post.activity_id'):0;//活动id
        $theme_title = input('?post.theme_title')?(string)input('post.theme_title'):'';//话题
        
        //强转一下。
        if ($type==1) {
            $type= \BBExtend\fix\TableType::bb_record__type_updates ;
        }
        
        if (!in_array($type, [1,2,3,4,5,6,7,])){
            return ['code'=>0,'message'=>'type error2'];
        }
        
        if (in_array($type, [ TableType::bb_record__type_yaoyue, TableType::bb_record__type_dasai, 
            ])) {
            if ($activity==0) {
               // Sys::debugxieye("{$uid}用户上传短视频，未填写activity_id参数");
                return ['code'=>0,'message'=>'参数错误'];
            }
        }
        
        $sort = input('?post.sort')?(int)input('post.sort'):0;//活动id
        $address = input('?post.address')?(string)input('post.address'):'未设定';
        $title = input('?post.title')?(string)input('post.title'):'';
        $title = trim($title);
        //$title = str_replace(array("\r\n", "\r", "\n"), "", $title);
        
        $token = input('?post.token')?(string)input('post.token'):'';
        $label = input('?post.label')?(int)input('post.label'):0;
        $longitude = input('?post.longitude') ? input('post.longitude') : 0.0;//经度
        $latitude = input('?post.latitude') ? input('post.latitude') : 0.0;//纬度
        $userlogin_token = input('?post.userlogin_token') ? input('post.userlogin_token') : '';
        
        if (preg_match('#null#', $address)) {
            $address='未设定';
        }
        
        if (\app\user\model\Exists::userhExists($uid) != 1)
        {
            return ['message'=>'没有这个用户','code'=>0];
        }
        
        //xieye 这里加 一个 连续上传递时间限制
        $time_limit = new \BBExtend\video\RecordLimit($uid);
        if (!$time_limit->can_upload()) {
            return ['message'=>'您的上传速度过快了～','code'=>0];
        }
        
        
        // xieye 2016 10
        if ($type !=  TableType::bb_record__type_yanzheng ) {
          \BBExtend\user\Tongji::getinstance($uid)->upload_movie();
          Exp::getinstance($uid)->set_typeint(Exp::LEVEL_RECORD)->add_exp();
        }
        if ($type == TableType::bb_record__type_yaoyue ) {
            \BBExtend\user\Tongji::getinstance($uid)->activity();
        }
        //按UID文件夹存放封面
        $image_type=array("jpg","gif","jpeg","png");//文件上传类型
        $file =  request()->file('image');
        $http_path = '/uploads/record/'.$uid.'/';
        $big_pic_path = '.'.$http_path;
        if (!is_dir($big_pic_path)){
            mkdir($big_pic_path,0775,true);
        }
        $big_pic = '';
        if ($file and in_array(pathinfo($file->getInfo()['name'],PATHINFO_EXTENSION), $image_type)){
            $info = $file->rule('uniqid')->move($big_pic_path);
            $big_pic = $http_path.$info->getFilename();
        }else{
            $big_pic =$thumbnailpath;
        }
        $recordDB = array();
        $recordDB['uid'] = $uid;
        $recordDB['type'] = $type;//视频类型 //秀场 1   邀约 2  个人验证 3，      4是大赛
        if ($type == TableType::bb_record__type_yanzheng )//个人认证视频
        {
            \BBExtend\BBUser::set_attestation($uid,1);
            \BBExtend\user\Tongji::getinstance($uid)->renzheng_yonghu();
        }
        $recordDB['video_path'] = $video_path;//视频路径地址
        $recordDB['baidu_citycode'] = $baidu_citycode;//百度城市地址 201807
        
        $recordDB['big_pic'] = $big_pic;//视频封面 默认为头像
        $recordDB['thumbnailpath'] = $thumbnailpath; //视频缩影图片地址
        $recordDB['usersort'] = $sort;//秀场类型 在数据库中bb_usersort中的id号对应
        $recordDB['activity_id'] = $activity;//活动id
        $recordDB['address'] = $address;//地址
        $recordDB['title'] = $title;//主题名称
        $recordDB['token'] = $token;//视频验证码
        $recordDB['audit'] = 0;//未审核
        $recordDB['label'] = (int)$label; //标签
        $recordDB['audit'] = 0;//未审核
        $recordDB['longitude'] = (float)$longitude; //经度
        $recordDB['latitude'] = (float)$latitude; //纬度
        
        $recordDB['time_length_second'] =$time_length_second;// xieye 201807
        
        $recordDB['theme_title'] = $theme_title;
        $theme_id =  \BBExtend\model\Theme::get_and_create_id($theme_title);
        $recordDB['theme_id'] = $theme_id;
        
        
        if (preg_match('#(mov|qt|quicktime)$#i', $video_path)) {
            $recordDB['transcoding_complete'] = 0;
        }else {
            $recordDB['transcoding_complete'] = 1;
        }
        
        //房间ID-》使用用户id + 数据库索引号组成
        $record_arr= self::update_record($recordDB);
        $recordDB['id'] = $record_arr['id']; // 谢烨20171021，加id，星推官用。
        
        // xieye 201807 短视频视为动态。
        
        
        
//         $recordDB['star_maker_v2'] = [
//             'message' =>
//             "名师在线 专业鉴定\n".
//             "获得1星鉴定 赞+10\n".
//             "获得2星鉴定 赞+20\n".
//             "获得3星鉴定 赞+30\n".
//             "获得4星鉴定 赞+40\n".
//             "获得5星鉴定 赞+50\n",
//             'pay' => 50,
//         ];
        if ($type == TableType::bb_record__type_yanzheng ) {//个人认证视频
         //   $recordDB['star_maker'] = null;
        }
        $starmaker = new \BBExtend\model\UserStarmaker();
        
        // 系统消息 ， 20161110，
        if (in_array($type, [TableType::bb_record__type_yaoyue, TableType::bb_record__type_yanzheng ])) {
            $user = BBUser::get_user($uid);
            $db = Sys::get_container_db();
            
            
            // 谢烨，为了对付自动转码较早的情况，额外查一下mov和qt
            if ( preg_match('#(mov|qt|quicktime)$#i', $video_path) ) {
                $sql="select * from bb_aliyun_record where video_path=?";
                $row = $db->fetchRow($sql,  [$video_path ]);
                if ($row) {
                    $sql="update bb_record set video_path=?,transcoding_complete=1 where id=?";
                    $db->query( $sql,[ $row['target_path'],  $record_arr['id'] ] );
                }
            }
            $sql="select title from bb_task_activity where id =". intval($activity);
            $act_name = $db->fetchOne($sql);
            $message = Message::get_instance();
            $message->set_title('系统消息')
                ->add_content(Message::simple()->content("亲爱的"))
                ->add_content(Message::simple()->content($user['nickname'])->color(0x32c9c9)  )
                ->add_content(Message::simple()->content('您已成功参加'));
            if ($type==2) {
                $message->add_content(Message::simple()->content($act_name)->color(0xf4a560)
                      ->url(json_encode(['type'=>4, 'activity_id'=>$activity ]) )             );
            }else {
                $message->add_content(Message::simple()->content($act_name)->color(0xf4a560)  );
            }
            $message->add_content(Message::simple()->content('，您的视频内容已提交审核。'))
                ->set_type(MessageType::canjia_huodong)
                ->set_uid($uid)
                ->send();
        }
        
        // 谢烨，2017 04 2018 05 ，大赛特别处理。
        if ($type==TableType::bb_record__type_dasai ) {
            
            $help = new \BBExtend\video\RaceUpload();
            $help->upload_record($record_arr['id'], $activity, $uid);
        }
        
        // 动态特殊处理
        if ($type==TableType::bb_record__type_updates ) {
            
            \BBExtend\model\UserUpdates::insert_record_no_check($record_arr);
            
//             $help = new \BBExtend\video\RaceUpload();
//             $help->upload_record($record_arr['id'], $activity, $uid);
        }
        
        // 这句话重要，勿删
        $time_limit->set_limit();
        
        // 应 安卓特别要求。
        if ( $type == TableType::bb_record__type_yanzheng  
                 && \BBExtend\common\Client::is_android() ) {//个人认证视频
            $recordDB=null;
        }
        
        return ['data'=>$recordDB,'code'=>1];
    }

    
    
    
    //后台API，测试接口
    public function get_movies_test()
    {
        $DB = Db::table('bb_record')->where('audit',0)->select();
        return $DB;
    }
    
    
    //审核视频
    public function cer_movies()
    {
        $id = input('?param.id')?(int)input('param.id'):0;
        $audit = input('?param.audit')?(int)input('param.audit'):0;
        $fail_reason = input('?param.fail_reason')? strval( input('param.fail_reason')) :'';
        return self::set_cer_movies($id,$audit,$fail_reason);
    }
    
    
    protected static function set_cer_movies($id,$audit,$fail_reason='')
    {
        $DB = Db::table('bb_record')->where('id',$id)->find();
        if ($DB) {
            $record_check = new  RecordCheck($id, $audit,$fail_reason);
            $result = $record_check->check();
            if ($result) {
                return ["code"=>1];
            }else {
                return ['code'=>0,'message'=>$record_check->message];
            }
        }
        return ['code'=>0,'message'=>'有错'];
    }

    
    public function list_view_log($uid=0,$startid=0,$length=10)
    {
        $startid=intval($startid);
        $length=intval($length);
        $uid = intval($uid);
        
        $db = Sys::get_container_db();
        $sql="select * from bb_moive_view_unique_log
               where uid = {$uid}
               and exists(
                 select 1 from bb_record where bb_record.id = bb_moive_view_unique_log.movie_id
                 and bb_record.is_remove=0 and 
                 bb_record.audit=1
               )
               order by create_time desc
               limit {$startid},{$length}
                ";
        $result = $db->fetchAll($sql);
        $temp = [];
        foreach ($result as $record) {
            $sql ="select * from bb_record where id= {$record['movie_id']}";
            $temp2 = $db->fetchRow($sql);
            $t = BBRecord::get_subject_detail_by_row($temp2, $uid);
            $temp []= $t;
        }
        return ["code"=>1,'is_bottom'=> (count($result) ==$length )?0:1, "data" =>$temp  ];
    }
    
    
    public function list_view_log_v2($uid=0,$startid=0,$length=10)
    {
        $startid=intval($startid);
        $length=intval($length);
        $uid = intval($uid);
        
        $db = Sys::get_container_db();
        $sql="select * from bb_moive_view_unique_log
               where uid = {$uid}
               and exists(
                 select 1 from bb_record where bb_record.id = bb_moive_view_unique_log.movie_id
                 and bb_record.is_remove=0 and
                 bb_record.audit=1
               )
               order by create_time desc
               limit {$startid},{$length}
                ";
        $result = $db->fetchAll($sql);
        $new = [];
        foreach ($result as $record) {
//             $sql ="select * from bb_record where id= {$record['movie_id']}";
//             $temp2 = $db->fetchRow($sql);
//             $t = BBRecord::get_subject_detail_by_row($temp2, $uid);
            
            $temp = \BBExtend\model\RecordDetail::find( $record['movie_id'] );
            $temp->self_uid = $uid;
            $new []= $temp->get_all();
            
            
            
        }
        
        $is_bottom = (count($result) ==$length )?0:1;
        
        return ["code"=>1,    "data" =>['list' =>$new,'is_bottom' =>$is_bottom  ],  ];
    }
    
    
    
    /**
     * 广告投放
     * @param number $uid
     * @param number $record_id
     */
    public function ad($uid=0, $record_id=0)
    {
        $uid = intval($uid);
        $record_id = intval($record_id);
        $db = Sys::get_container_db();
        $sql="select * from bb_record
        where type=5
        and is_remove=0
        and bb_record.audit=1
        limit 50
        ";
        $arr=[];
        $result = $db->fetchAll($sql);
        if (!$result) {
            $arr['have_ad'] = 0;
            $arr['url'] = '';
            $arr['ad_url'] = '';
            return ["code"=>1, "data" =>$arr  ];
        }
        
        shuffle($result);
        $one_record = array_pop($result);
        $user = \app\user\model\UserModel::getinstance($uid);
        $is_vip = $user->get_user_vip();
    
        
        if ($is_vip) {
          $arr['have_ad'] = 0;
          $arr['url'] = '';
          $arr['ad_url'] = '';
          
        }else {
            $arr['have_ad'] = 1;
            $arr['url'] = $one_record['video_path'];
            $arr['ad_url'] = 'http://www.baidu.com/';
        }
        return ["code"=>1, "data" =>$arr  ];
    }
    
    
    /**
     * 用户观看短视频日志
     * 
     * 同时记录用户喜好。
     * 
     * @param number $uid
     * @param number $movie_id
     * @param number $target_uid
     */
    public function view_log($uid=0, $movie_id=0, $target_uid=0)
    {
        $db = Sys::get_container_db();
        $movie_id = intval($movie_id);
        $db->insert("bb_moive_view_log", [
            'uid' => intval($uid),
            'target_uid' => intval($target_uid),
            'movie_id'   => intval($movie_id),
            'create_time' => time(),
        ]);
        
        $sql ="update bb_record set real_people = real_people+1,look=look+1 
                where id = {$movie_id}";
        $db->query($sql);
        
        $record_model = new \BBExtend\model\Record();
        $record_model->add_views(intval( $movie_id));
        
        
        try{
            $db->insert("bb_moive_view_unique_log", [
                'uid' => intval($uid),
                'target_uid' => intval($target_uid),
                'movie_id'   => intval($movie_id),
                'create_time' => time(),
            ]);
            
        }catch (\Exception $e) {}
        
        //现在开始处理喜好的记录。
        $sql ="select * from bb_record where id = {$movie_id}";
        $result = $db->fetchRow($sql);
        $type = intval($result['type']);
        $usersort = intval( $result['usersort'] );
        $label = intval($result['label']);
        
        $sql ="select id from bb_moive_view_stats
                where uid = {$uid}
                  and type={$type}
                  and usersort = {$usersort}
                  and label ={$label}
                ";
        $id = $db->fetchOne($sql);
        if ($id) {
            $sql ="update bb_moive_view_stats 
                     set view_count = view_count+1
                    where id = {$id}
                    ";
            $db->query($sql);
        }else {
            $db->insert("bb_moive_view_stats", [
                'uid' => $uid,
                'type' => $type,
                'usersort' => $usersort,
                'label' => $label,
            ]);
        }
        
        return ['code'=>1,];
    }

}