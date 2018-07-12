<?php
namespace app\push\controller;

use app\record\controller\Recordmanager;
use BBExtend\BBPush;
use BBExtend\BBRedis;
use think\Config;
use think\Controller;
use think\Db;
use app\user\controller\User;

use BBExtend\BBUser;
use BBExtend\user\exp\Exp;
use BBExtend\Sys;
use BBExtend\message\Message;
use BBExtend\fix\MessageType;


/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/13
 * Time: 18:27
 */

class Pushmanager extends BBPush
{
    //api
    private function get_Stream_Name($url)
    {
        $arr = parse_url($url);
        $str = str_replace("/mlandclub/","",$arr["path"]);
        return $str;
    }
    
    
    //得到所有正在推的房间信息
    public function get_publish_all()
    {
        $time = input('?param.time')?(int)input('param.time'):0;
        if ($time)
        {
            $PushDB_list = Db::table('bb_push')->where(['event'=>'publish'])->where('time','gt',$time)->select();
        }else
        {
            $PushDB_list = Db::table('bb_push')->where(['event'=>'publish'])->select();
        }
        $Data = array();
        foreach ($PushDB_list as $PushDB)
        {
            $DB = array();
            $DB['room_id'] = $PushDB['room_id'];
            $DB['uid'] = $PushDB['uid'];
            array_push($Data,$DB);
        }
        return ['code'=>1,'data'=>$Data,'time'=>time()];
    }
    
    
    //得到所有推流信息
    public function get_push_all()
    {
        $PushDB_Array_Keys = BBRedis::getInstance('push')->hGetAllKey();
        $data = array();

        foreach ($PushDB_Array_Keys as $Key)
        {
            $PushDB = BBRedis::getInstance('push')->hGetAll($Key);
            if ($PushDB)
            {
                array_push($data,$PushDB);
            }
        }
        return ['data'=>$data,'code'=>1];
    }
    
    
    //startlive对应原先接口
    //开始直播提交当前封面
    public function upload_pic()
    {
        $uid = input('?post.uid') ?(int) input('post.uid') : 0;
        $tempuser = \BBExtend\model\User::find($uid);
        
        if (!$tempuser){
            return ["code"=>0,'message'=>'uid err' ];
        }
        $db = Sys::get_container_db();
        
        $sql="select uid from bb_users where role=4";
        
        $allow_users_arr= $db->fetchCol($sql);
        
        
        if ( \BBExtend\model\User::is_test($uid) || in_array($uid, $allow_users_arr)   
                || $uid ==8064543 || ( !Sys::is_product_server() )
                ) {
            
            
        }else {
        
            $time =date("H:i:s");
            $date = date("Ymd");
            if (  $date >= "20180410" ){
                return ["code"=>0,'message'=>'哎呀直播功能被外星人绑架了，我们正在解救中' ];
            }
            
            if ($time<"07:00:00"  || $time > "22:00:00"   ) {
                return ["code"=>0,'message'=>'夜间保护时间，禁止直播' ];
            }
        }
        
     //   $uid = input('?post.uid') ?(int) input('post.uid') : '';
        $exists = \app\user\model\UserModel::getinstance($uid);
        if (!$exists->has_user()) {
            return ['code'=> 0, 'message'=>'用户不存在'];
        }
        if (!$exists->can_zhibo()) {
            return ['code'=> -302, 'message'=>'您已被禁止直播'];
        }
        if (!$exists->has_zhibo_renzheng() ) {
            return ['code'=> -303, 'message'=>'您需要在个人中心里进行直播认证'];
        }
        // 谢烨 2017 03
        $ds_id = input('?post.ds_id') ?(int) input('post.ds_id') : 0;
        
        $address = input('?post.address') ? input('post.address') : '未设定';
        
        if (preg_match('#null#', $address)) {
            $address='未设定';
        }
        
        $title = input('?post.title') ? input('post.title') : '';
        $label = input('?post.label') ?(int) input('post.label') : 0;
        $sort = input('?post.sort') ? input('post.sort') : 2; //1：学啥 2宝贝秀 3玩啥
        $activity_id = input('?post.activity_id') ? (int)input('post.activity_id') : 0;
        $longitude = input('?post.longitude') ? input('post.longitude') : 0.0;//经度
        $latitude = input('?post.latitude') ? input('post.latitude') : 0.0;//纬度
        $userlogin_token = input('?post.userlogin_token') ? input('post.userlogin_token') : '';
        $stealth = input('?post.stealth') ? input('post.stealth') : 0;//是否隐身
        if (!User::validation_token($uid,$userlogin_token))
        {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
 
        //检查结束
        //按时间文件夹存放头像
        $type=array("jpg","gif","jpeg","png");//文件上传类型
        $file =  request()->file('image');
        $prefix = date('Y-m-d');
        $httppath = '/uploads/bigpic/'.$uid.'/';
        $bigpicpath = '.'.$httppath;
        if (!is_dir($bigpicpath)){
            mkdir($bigpicpath,0775,true);
        }
        
        if ($file and in_array(pathinfo($file->getInfo()['name'],PATHINFO_EXTENSION), $type)){
            $info = $file->rule('uniqid')->move($bigpicpath);
            $bigpic = $httppath.$info->getFilename();
            $bigpic = \BBExtend\common\Image::geturl($bigpic);
            
            $db->update("bb_users", [
                'live_cover' => $bigpic,
            ], "uid = {$uid}  ");
            
        }else{
            $bigpic = User::get_user_cover($uid);
        }
        $PushDB = Db::table('bb_push')->where('uid',$uid)->find();
        if (!$PushDB)
        {
            $PushDB =  array();
            $PushDB['uid'] = $uid;
            $PushDB['sort'] = $sort;
            $PushDB['bigpic'] = $bigpic;
            $PushDB['title'] = $title;
            $PushDB['label'] = (int)$label;
            $PushDB['address'] = $address;
            $PushDB['room_id'] = $uid.'push';
            User::set_address($uid,$address);
            $PushDB['activity_id'] = $activity_id;
            $PushDB['longitude'] = $longitude;
            $PushDB['latitude'] = $latitude;
            $PushDB['event'] = 'publish_done';
            $PushDB['stealth'] = $stealth;
            $PushDB['flowers'] = 0;
          //  Db::table('bb_push')->where('uid',$uid)->insert($PushDB);
            Db::table('bb_push')->insert($PushDB);
            $video_id = Db::table('bb_push')->getLastInsID();
           
            BBRedis::getInstance('push')->hMset($uid.'push',$PushDB);
        }else
        {
            $PushDB['uid'] = $uid;
            $PushDB['sort'] = $sort;
            $PushDB['bigpic'] = $bigpic;
            $PushDB['title'] = $title;
            $PushDB['label'] = (int)$label;
            $PushDB['address'] = $address;
            $PushDB['room_id'] = $uid.'push';
            $PushDB['like'] = 0;
            $PushDB['people'] = 0;
            $PushDB['activity_id'] = $activity_id;
            $PushDB['longitude'] = $longitude;
            $PushDB['latitude'] = $latitude;
            $PushDB['event'] = 'publish_done';
            $PushDB['stealth'] = $stealth;
            User::set_address($uid,$address);
            
            Db::table('bb_push')->where('uid',$uid)->update($PushDB);
            $video_id = Db::table('bb_push')->where('uid',$uid)->value('id');
            BBRedis::getInstance('push')->hMset($uid.'push',$PushDB);
        }
        $Data = array();
        $Data['room_id'] = $uid.'push';
        $Data['chat_token'] = md5($uid.'push');
        $Data['live_cover'] = $bigpic;
        $userStr = array();
        $userStr['sign']=time();
        $userStr['userType'] = 2;
        $userStr['uid'] = $uid;
        $userStr['roomnum'] = $uid.'push';
        $userStr['nickname'] = User::get_nickname($uid);
        $userStr['pic'] = User::get_userpic($uid);
        User::set_address($uid,$address);
        
        // 谢烨，设置大赛直播，
        //特别注意，这里并未检查参数正确性。
        if ($ds_id && $ds_id>0) {
            $this->race_push($uid, $ds_id, $video_id);
        }
        return ['data'=>$Data,'code'=>1];
    }
    
    
    // 2017 03 设置大赛直播
    //特别注意，这里并未检查参数正确性。！！！
    private function race_push($uid, $ds_id=0, $video_id=0)
    {
        //首先，把表里的记录删除掉
        $db = Sys::get_container_db();
        $uid =intval($uid);
        $ds_id=intval($ds_id);
        $video_id=intval($video_id);
        
        $sql ="
                delete from ds_show_video
where ds_id={$ds_id} and uid ={$uid} and type=1
                ";
        $db->query($sql);
        $db->insert("ds_show_video", [
            'ds_id' =>$ds_id,
            'uid'  =>$uid,
            'create_time' => time(),
            'type' => 1,
            'video_id' => intval($video_id),
            'room_id'  => $uid.'push',
        ]);
    }
    
    
    //查询直播状态
    public function query_push_state()
    {
        $room_id =  input('?param.room_id')?(string)input('param.room_id'):"";
        $roominfo = Db::table('bb_push')->where('room_id',$room_id)->find();
        if ($roominfo)
        {
            $Data = array();
            if ($roominfo['event']=='publish')
            {
                $Data['push'] = true;
                return ['data'=>$Data,'code'=>1];
            }else
            {
                $Data['push'] = false;
                return ['data'=>$Data,'code'=>1];
            }
        }
        return ['message'=>'','code'=>0];
    }
    
    
    //创建推流
    public function create_push($uid)
    {
        $push_url = 'www.yimwing.com';
        
        
        $exists = \app\user\model\UserModel::getinstance($uid);
        if (!$exists->has_user()) {
            return ['code'=> 0, 'message'=>'用户不存在'];
        }
        if (!$exists->can_zhibo()) {
            return ['code'=> -302, 'message'=>'您已被禁止直播'];
        }
        if (!$exists->has_zhibo_renzheng() ) {
            return ['code'=> -303, 'message'=>'您需要在个人中心里进行直播认证'];
        }
       
        $redis = Sys::getredis11();
        $key = ":pushid:";
        $id = $redis->get($key);
        $id = intval($id);
        $id++;
        if ($id > 19) {
            $id =1;
        }
        $redis->set($key, $id);
        $push_url = "push{$id}.yimwing.com";
        
        $PushDB = self::get_push_DB($uid);
        $Time =  time() - $PushDB['end_time'];
        if ($Time > 25)
        {
            $PushDB['stream_name'] = self::Create_Online_ID($uid).'-'.$uid.'push';
            $PushDB['end_time'] = time();
        }
        $PushDB['push_url'] = Config::get('ALY_SERVER').Config::get('ALY_APP_NAME').$PushDB['stream_name'].'?vhost='. $push_url ;
        $PushDB['pull_url'] = 'rtmp://'. $push_url .Config::get('ALY_APP_NAME').$PushDB['stream_name'];
        $PushDB['domain'] = $push_url;
        
        $vid = $uid.'push';
        Db::table('bb_push')->where('uid',$uid)->update($PushDB);
        BBRedis::getInstance('push')->hMset($vid,$PushDB);
        $Data = array();
        $Data['push_url'] = $PushDB['push_url'];
        $Data['pull_url'] = $PushDB['pull_url'];
        
        return ['code'=>1,'data'=>$Data];
    }
    
    
    //开始推流,
    /**
     * 谢烨 2016 11 29 
     * 梁晨：安卓主播的create_time字段需要加几秒，由传参delay决定。默认为0可不传。
     * 
     * @return string[]|number[]|string[]|number[]|NULL[]
     */
    public function start_push()
    {
        
//         $date = date("Ymd");
//         if (  $date >= "20180410" ){
//             return ["code"=>0,'message'=>'哎呀直播功能被外星人绑架了，我们正在解救中' ];
//         }
        
        
        
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $push_url = input('?param.push_url')?(string)input('param.push_url'):"";
        $pull_url = input('?param.pull_url')?(string)input('param.pull_url'):"";
        
        $db = Sys::get_container_db();
        $sql ="select * from bb_otherpush where uid = ?";
        $count = $db->fetchRow($sql,$uid);
        if ($count) {
            $sql="select push_url,pull_url from bb_push where uid= ?";
            $url2 = $db->fetchRow($sql,$uid);
            if ($url2) {
                if ($url2['push_url']) {
                    $push_url =$url2['push_url'];
                }
                if ($url2['pull_url']) {
                    $pull_url =$url2['pull_url'];
                }
            }
            
        }
        
        
//         if  ($uid== 12405) {
//             $push_url = 'rtmp://video-center.alivecdn.com/bobo/DP8KU6I6-12405push?vhost=www.yimwing.com';
//             $pull_url = 'rtmp://218.92.2.109/live/201701193000000jg13?stream_id=201701193000000jg13';
//         }
        
        $type = input('?param.type')?(int)input('param.type'):0;
        $delay = input('?param.delay')?(int)input('param.delay'):0;
      //  \BBExtend\Sys::debugxieye($delay);
        if (!$push_url&&!$pull_url)
        {
            return ['message'=>'推流地址以及拉流地址为空','code'=>0];
        }
        $vid = $uid.'push';
        $PushDB = self::get_push_DB($uid);
        $PushDB['space_name'] = '';
        $PushDB['ip'] = '';
        $PushDB['like'] = 0;
        $PushDB['people'] = 0;
        $PushDB['room_id'] = $uid.'push';
        $PushDB['heat'] = 0;
        $PushDB['time'] = time();
       
        $mobile_type = Config::get('http_head_mobile_type'); 
     //   \BBExtend\Sys::debugxieye($mobile_type);
        if ($mobile_type=='ios') {
            
            $PushDB['create_time'] = time();
        }else {
            $PushDB['create_time'] = time() + $delay ;
        }
        
        
        $PushDB['pull_url'] = $pull_url;
        $PushDB['push_url'] = $push_url;
        switch ($type)
        {
            case 0:
                $PushDB['stream_name'] = $this->get_Stream_Name($push_url);
                break;
            case 1:
                $PushDB['event'] = 'publish';
                //$PushDB['stream_name'] = $this->get_Stream_Name($push_url);
                break;
        }
        BBRedis::getInstance('push')->hMset($vid,$PushDB);
        Db::table('bb_push')->where('uid',$uid)->update($PushDB);
        
        $RewindDB = Db::table('bb_rewind')->where(['stream_name'=>$PushDB['stream_name']])->find();
        if (!$RewindDB)
        {
            $RewindDB = array();
            $RewindDB['stream_name'] = $PushDB['stream_name'];
            $RewindDB['uid'] = $PushDB['uid'];
            $RewindDB['activity_id'] = (int)$PushDB['activity_id'];
            $RewindDB['title'] = $PushDB['title'];
            $RewindDB['label'] = $PushDB['label'];
            $RewindDB['bigpic'] = $PushDB['bigpic'];
            //谢烨20160928
            $RewindDB['address'] = $PushDB['address'];
            
            $RewindDB['start_time'] = time();
            switch ($type)
            {
                case 0://趣拍
                    $RewindDB['rewind_url'] = 'http://vod.lss.qupai.me/mlandclub/'.$PushDB['stream_name'].'.m3u8';
                    break;
                case 1://阿里云
                    $RewindDB['rewind_url'] = 'http://pushall.oss-cn-shanghai.aliyuncs.com/record/bobo/'.$PushDB['stream_name'].'.m3u8';
                    break;
            }

            Db::table('bb_rewind')->insert($RewindDB);
        }
        self::push_fensi($uid);
        return ['data'=>$PushDB,'message'=>'','code'=>1];
    }
    
    
    public static function push_fensi($uid) 
    {
        $db=Sys::get_container_db();
        $uid =intval($uid);
        $sql ="select uid,is_online from bb_users where permissions < 5 
                 
                and exists (select 1 from bb_focus
                  where bb_users.uid = bb_focus.uid
                    and bb_focus.focus_uid ={$uid} 
                )
                order by is_online desc, permissions desc, login_time desc 
                limit 500
                ";
        $ids = $db->fetchAll($sql);
        
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_userpic();
        $time=time();
        
        
//         $sql="select * from bb_push where uid = {$uid}";
//         $push_row = $db->fetchRow($sql);
        
        
        //你的好友#玩家昵称#，开启了直播，点击进入直播间
        \Resque::setBackend('127.0.0.1:6380');
        
        foreach ($ids as $v) {
            $args = array(
                'target_uid' => $v['uid'],
                'uid'  => $uid,
                'time' => $time,
                
                'pic'      => $pic,
                'nickname' => $nickname,
                'type' => '124',
                
            );
       //     \Resque::enqueue('jobs2', '\app\command\controller\Job2', $args);
            \Resque::enqueue('jobs22', '\app\command\controller\Job22', $args);
        }
    }
    
    
    //获取直播信息
    public function push_roominfo()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):"";
    }
    
    
    //获得观看信息
    public function watch_roominfo()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):"";
    }
    
    
    //admin api
    //admin  start
    public function Clear()
    {
        BBRedis::getInstance('push')->flushDB();
    }

    
    //QuPai blackcall
    //查询当前直播
    //RequestId    String    该请求任务的ID
    //AppName    String    应用名称(和spaceName一致)
    //StreamName    String    流名称
    //PublishTime    String    开始推流时刻 UTC时间
    //PublishUrl    String    推流完整URL地址
    //DomainName    String    流所属加速域名
    public function query_current()
    {
        $url = "http://open.paas.qupaicloud.com/push/online/list";
        $data = array();
        $node_service = Sys::get_container_node();
        $result = $node_service->http_Request($url,$data,'GET');
        return json_decode( $result ) ;
    }
    
    
    //查询历史历史直播
    //RequestId    String    该请求任务的ID
    //AppName    String    应用名称(和spaceName一致)
    //StreamName    String    流名称
    //PublishTime    String    开始推流时刻 UTC时间
    //StopTime    String    停止推流是时刻 UTC时间
    //PublishUrl    String    推流完整URL地址
    //DomainName    String    流所属加速域名
    public function query_history($startTime,$endTime)
    {
        $url = "http://open.paas.qupaicloud.com/push/history/list";
        $data = array();
        $data['startTime'] = $startTime;
        $data['endTime'] = $endTime;
        $data['spaceName'] = "mlandclub";
        $data['appKey'] = "20995aca1e3add2";
        $data['auth_key'] = $this->auth_key();
        $node_service = Sys::get_container_node();
        $result = $node_service->http_Request($url,$data,'GET',
                array("Content-type: text/html; charset=utf-8"));
        return json_decode($result) ;
    }
    
    
    //查询推流在线人数
    //RequestId    String    该请求任务的ID
    //TotalUserNumber    int    在线观看人数
    public function query_online_num($streamName)
    {
        $url = "http://open.paas.qupaicloud.com/live/online/num";
        $data = array();
        if ($streamName)
        {
            $data['streamName'] = $streamName;//如果没有推流名称 则返回该应用下所有在线人数

        }
        $data['spaceName'] = "mlandclub";
        $data['appKey'] = "20995aca1e3add2";
        $data['auth_key'] = $this->auth_key();
        
        $node_service = Sys::get_container_node();
        $result = $node_service->http_Request($url,$data,'GET',
                array("Content-type: text/html; charset=utf-8"));
        return json_decode($result) ;
    }
    
    
    private static function auth_key()
    {
        $appSecret = "e5bc2344f98d4ebd8dab39d1f1aea6d7";
        $time = time() + 30;
        $hashValue = md5($time."-".$appSecret);
        $auth_key = $time.'-'.$hashValue;
        return $auth_key;
    }
    
    
//获得直播列表 传输 sort 为类型 1学啥 2宝贝秀 3玩啥 $limit_StartPos开始行数 $length 长度 $activity活动ID $heat是否推荐
//delete
    public function get_show_list($uid,$sort,$limit_StartPos=0,$length = 20,$activity = 0)
    {
        $DBList = Db::table('bb_push')->where(['sort'=>$sort,'event'=>'publish','activity_id'=>$activity])->order(['like'=>'desc','heat'=>'desc'])->limit($limit_StartPos,$length)->select();
        $Data = array();
        foreach ($DBList as $DB)
        {
            $DataDB['uid'] = (int)$DB['uid'] ;
            $DataDB['event'] = $DB['event'];
            $DataDB['pull_url'] = $DB['pull_url'];
            $DataDB['title'] = $DB['title'];
            $DataDB['label'] = (int)$DB['label'];
            $DataDB['specialty'] = User::get_specialty($uid);
            $DataDB['login_address'] = $DB['address'];
            //显示在线观看人数以及点赞人数
            $RedisDB = BBRedis::getInstance('push')->hGetAll($DataDB['uid'].'push');
            if ($RedisDB)
            {
                $DataDB['is_like'] = false;
                $DataDB['like'] = (int)$RedisDB['like'];
                $DataDB['people'] = (int)$RedisDB['people'];
            }
            $DataDB['nickname'] = User::get_nickname($DB['uid']);
            $Pic = $DB['bigpic'];
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            //如果没有http://
            if ($Pic)
            {
               
               $DataDB['bigpic'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                        $Pic );
            }else
            {
                $DataDB['bigpic'] = User::get_userpic($DB['uid']);
            }
            $DataDB['pic'] = User::get_userpic($DB['uid']);
            $DataDB['room_id'] = $DB['room_id'];
            $DataDB['age'] = User::get_userage($DB['uid']);
            $DataDB['push'] = true;
            array_push($Data,$DataDB);
        }
        return $Data;
    }
    
    
    public function notify_state_aly($action,$app,$appname,$id,$node,$ip,$time)
    {
        $uid = Db::table('bb_push')->where('stream_name',$id)->field('uid')->find();
        // xieye 2016 10 方便测试服测试
        
        // xieye 201712 下面的注释勿删
        
//         if (\BBExtend\common\BBConfig::get_server_url() == 'http://bobo.yimwing.com' ) {
//             if (!$uid) { //推定是测试服的数据，因为现在测试服的uid从10万开始了。
//                 $param=[
//                     'action'=>$action,
//                     'appname'=>$appname,
//                     'id'=>$id,
//                     'ip'=>$ip,
//                     'time'=>$time,
//                     'app'=>$app,
//                     'node'=>$node,
//                 ];
//                 $url ='http://test.yimwing.com/push/pushmanager/notify_state_aly?'.
//                     http_build_query($param);
//                 file_get_contents($url);
//                 exit();
//             }
//         }
        return self::notify_state($action,$appname,$id,$ip,$time,1);
    }
    
    // 直播日志保存
    private function save_log_push($uid)
    {
        $push = \BBExtend\model\Push::where("uid",$uid)->first();
        if ($push) {
           $attr = $push->getAttributes();
           $push_log = new \BBExtend\model\PushLog();
           foreach ( $attr as $k=> $v ) {
             if ($k!='id') {
                 $push_log->$k = $v;
             }
             $push_log->save();
           }
        }
    }
    
    
    //通知用户推流跟拉流状态
    //event    publish表示推流，publish_done表示断流
    //spaceName    空间名称
    //streamName    流名称
    //ip    推流的客户端ip
    //time    推流/断流时间
    public function notify_state($event,$spaceName,$streamName,$ip,$time,$type=0)
    {
        //\BBExtend\Sys::debugxieye('$event='.$event);
        $uid = Db::table('bb_push')->where('stream_name',$streamName)->field('uid')->find();
        // xieye 2016 10 方便测试服测试
        
        // xieye 201712 下面的注释勿删
        
//         if (\BBExtend\common\BBConfig::get_server_url() == 'http://bobo.yimwing.com' ) {
//             if (!$uid) { //推定是测试服的数据，因为现在测试服的uid从10万开始了。
              
                
//                 $param=[
//                     'event'=>$event,
//                     'spaceName'=>$spaceName,
//                     'streamName'=>$streamName,
//                     'ip'=>$ip,
//                     'time'=>$time,
//                 ];
//                 $url ='http://test.yimwing.com/push/pushmanager/notify_state?'.
//                   http_build_query($param);
//                 file_get_contents($url);
//                 exit();
//             }
//         }
        
        if (!$uid)
        {
            $uid = Db::table('bb_push')->where('ip',$ip)->field('uid')->find();
        }
        $uid = $uid['uid'];
        if($event == 'publish')
        {
            $PushDB = BBRedis::getInstance('push')->hGetAll($uid.'push');
            
//             $temp = new \app\shop\model\Alitemp();
//             $temp->data("url",'notify_state___publish_1');
//             $temp->data("create_time",date("Y-m-d H:i:s"));
//             $temp->data("content", json_encode($PushDB) );
//             $temp->data('test1',10000);
//             $temp->data("uid", $uid);
//             $temp->data('realurl', Config::get('http_head_url'));
//             $temp->save();
            
            if (!$PushDB)
            {
                $PushDB = Db::table('bb_push')->where('stream_name',$streamName)->find();
            }
            if ($PushDB)
            {
                $PushDB['event'] = $event;
                $PushDB['ip'] = $ip;
                $PushDB['time'] = $time;
                $PushDB['like'] = 0;
                BBRedis::getInstance('push')->hMset($uid.'push',$PushDB);
                
                
//                 $temp = new \app\shop\model\Alitemp();
//                 $temp->data("url",'notify_state___publish_2');
//                 $temp->data("create_time",date("Y-m-d H:i:s"));
//                 $temp->data("content", json_encode($PushDB) );
//                 $temp->data('test1',10000);
//                 $temp->data("uid", $uid);
//                 $temp->data('realurl', Config::get('http_head_url'));
//                 $temp->save();
                
            }
            Db::table('bb_push')->where('stream_name',$streamName)->update($PushDB);
            // xieye 2016 10 
            \BBExtend\user\Tongji::getinstance($uid)->zhibo_start($streamName);
            
            return 1;
        }else if($event == 'publish_done')
        {
            $PushDB = BBRedis::getInstance('push')->hGetAll($uid.'push');
            $PushDB['event'] = $event;
            
            $PushDB['time'] = $time;
            $PushDB['end_time'] = time();
            BBRedis::getInstance('push')->hMset($uid.'push',$PushDB);
            
            $RewindDB = array();
            $RewindDB['space_name'] = $spaceName;
            $RewindDB['stream_name'] = $streamName;
            $RewindDB['event'] = "publish_done"; //设置为回播事件
            if ($type)
            {
                $RewindDB['event'] = "rewind"; //设置为回播事件
                $RewindDB['end_time'] = time();
            }
            $RewindDB['room_id'] = $streamName.time();
            $RewindDB['bigpic'] = $PushDB['bigpic'];
            $RewindDB['uid'] = $PushDB['uid'];
            $RewindDB['like'] = (int)$PushDB['like'];
            $RewindDB['people'] = (int)$PushDB['people'];
            $RewindDB['title'] = $PushDB['title'];
            $RewindDB['label'] = (int)$PushDB['label'];
            $RewindDB['sort'] = (int)$PushDB['sort'];
            $RewindDB['activity_id'] = (int)$PushDB['activity_id'];
            $RewindDB['longitude'] = (float)$PushDB['longitude'];
            $RewindDB['latitude'] = (float)$PushDB['latitude'];
            $RewindDB['flowers'] =  (int)$PushDB['flowers'];
            $FindRewindDB = Db::table('bb_rewind')->where('stream_name',$streamName)->find();
            if ($FindRewindDB)
            {
                Db::table('bb_rewind')->where('stream_name',$streamName)->update($RewindDB);
            }else
            {
                Db::table('bb_rewind')->insert($RewindDB);
            }
            
            $PushDB['people']=0;
            // xieye 20180104
            $PushDB['heat']=0;
            
            Db::table('bb_push')->where('stream_name',$streamName)->update($PushDB);
//             if (!( \BBExtend\BBUser::get_user($uid)) ) {
//                 $temp = new \app\shop\model\Alitemp();
//                 $temp->data("url",'rewind_save');
//                 $temp->data("create_time",date("Y-m-d H:i:s"));
//                 $temp->data("content", json_encode($RewindDB));
//                 $temp->save();
//             }
            
            // xieye 2016 10
            $shi_cha= \BBExtend\user\Tongji::getinstance($uid)->zhibo_end($streamName);
            //增加经验
            Exp::getinstance($uid)
              ->set_typeint(Exp::LEVEL_PUSH)
              ->set_shi_cha($shi_cha)
              ->add_exp();
            // 201708 改成就啊。
            $ach = new \BBExtend\user\achievement\Zhibo($uid);
            $ach->update($shi_cha);
              
            \BBExtend\user\Tongji::getinstance($uid)->view_count($RewindDB['people']);
            $this->save_log_push($uid);
        //    Sys::debugxieye("notify_state uid:".$uid. "  PushDB['uid']:{$PushDB['uid']}");
            // 谢烨，2017 04 主播退出房间，要广播
            Message::get_instance()
                ->set_title('系统消息')
                ->set_type( MessageType::zhubo_xiaxian )
                ->set_uid($PushDB['uid'])
                ->send();
            
        }
        return 1;
    }

    
    public function notify_exitroom()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $room_id =  input('?param.room_id')?(string)input('param.room_id'):"";
        $type = input('?param.type')?(int)input('param.type'):0;
       // $MoviesDB = BBRedis::getInstance('push')->hGetAll($room_id);
        $MoviesDB = Db::table('bb_push')->where('room_id',$room_id)->find();
        
        if ($MoviesDB)
        {
            $cound = $MoviesDB['people'];
            if ($cound>0)
            {
                $cound--;
            }else
            {
                $cound = 0;
            }
            BBRedis::getInstance('push')->hSet($room_id,'people',$cound);
            
            $sql="update bb_push set people=people-1 where room_id=? and people >0";
            $db = Sys::get_container_db();
            $db->query($sql,$room_id);
            
            // 谢烨，这里插入统计代码
            \BBExtend\user\Tongji::getinstance($uid)->exit_room($room_id);
            
        }
        return ['code'=>1];
    }
    
    
    public function notify_enterroom()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $room_id =  input('?param.room_id')?(string)input('param.room_id'):"";
//         $MoviesDB = BBRedis::getInstance('push')->hGetAll($room_id);
//         if (!$MoviesDB)
//         {
            $MoviesDB = Db::table('bb_push')->where('room_id',$room_id)->find();
            if ($MoviesDB)
            {
                BBRedis::getInstance('push')->hMset($room_id,$MoviesDB);
                $MoviesDB['is_like'] = false;
            }

//         }
        if ($MoviesDB)
        {
            $cound = $MoviesDB['people'];
            $cound++;
            BBRedis::getInstance('push')->hSet($room_id,'people',$cound);
            
            $sql="update bb_push set people=people+1 where room_id=?";
            $db = Sys::get_container_db();
            $db->query($sql,$room_id);
            
            // 谢烨，这里插入统计代码
            \BBExtend\user\Tongji::getinstance($uid)->enter_room($room_id);
            
        }
        if (!$MoviesDB)
        {
            $like = Recordmanager::notify_enterroom($uid,$room_id);
            $Data = array();
            if($like == -1)
            {
                $like = Rewindmanager::notify_enterroom($uid,$room_id);
                $Data = array();
                $Data['is_like'] = Rewindmanager::get_is_like($uid,$room_id);
                $Data['like'] = $like;
                return ['data'=>$Data,'code'=>1];
            }else
            {
                $Data['is_like'] = Recordmanager::get_is_like($uid,$room_id);
                $Data['like'] = $like;
                return ['data'=>$Data,'code'=>1];
            }
        }
        return ['flowers'=>$MoviesDB['flowers'],'code'=>1];
    }

    
    /**
     * 机器人用，调整围观人数的接口
     * @param unknown $room_id 直播视频的room_id
     * @param number $type 为1表示加，为2表示减
     */
    public function people($room_id, $type=1,$count=1)
    {
        $count=intval($count);
        if (!$count) {
            return ['code'=>0,'message'=>'count err'];
        }
        
        $db = Sys::get_container_db();
        if ($type==1) {
            $sql="update bb_push set people = people+{$count} where room_id=?";
            $db->query($sql,$room_id);
            BBRedis::getInstance('push')->hIncrBy($room_id,'people');
            
        }
        if ($type==2) {
            $sql="update bb_push set people = people-{$count} where room_id=?";
            $db->query($sql,$room_id);
            BBRedis::getInstance('push')->hIncrBy($room_id,'people',-1);
        }
        return ['code'=>1];
    }
    
    
}