<?php
namespace app\api\controller;
use app\push\controller\Pushmanager;
use app\push\controller\Rewindmanager;
use app\record\controller\Recordmanager;
use BBExtend\BBRedis;
use think\Db;
use BBExtend\BBRecord;
use BBExtend\message\Message;
use BBExtend\BBUser;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\BBPush;

class Boboapi
{
    public function index_window($uid=0, $token=0)
    {
        $db = Sys::get_container_dbreadonly();
        $uid = intval($uid);
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token( $token ) ) {
            return ['code'=>0,'message'=>'token error'];
        }
        
        // 键index_window_open 1打开 0关闭
        // 键index_window_link   html链接
        // 键index_window_style  1全屏，2弹框带边框
        // 键index_window_type   1公告
        
        $sql="select * from bb_config_str where type=12";
        $result = $db->fetchAll($sql);
        if ($result && count($result) >1 ) {
            $open=0;
            $link='';
            $style=1;
            $type=1;
            foreach ($result as $v) {
                if ($v['config'] =='index_window_open' ) {
                    $open = intval($v['val']);
                }
                if ($v['config'] =='index_window_link' ) {
                    $link = $v['val'];
                }
                if ($v['config'] =='index_window_style' ) {
                    $style = intval($v['val']);
                }
                if ($v['config'] =='index_window_type' ) {
                    $type = intval($v['val']);
                }
                
            }
            return ['code'=>1,'data'=>['open'=>$open,'link'=>$link,'style'=>$style,'type'=>$type ] ];
            
        }
        return ['code'=>1,'data'=>['open'=>0,'link'=>'','style'=>1,'type'=>1 ] ];
        
    }
    
    
    //谢烨 2016 10 14
    public function ip_like($ip='',$room_id='', $type='')
    {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return \BBExtend\video\Like::ip_like($ip,$type, $room_id);
    }
    
    public function ip_unlike($ip='',$room_id='', $type='')
    {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return \BBExtend\video\Like::ip_unlike($ip,$type, $room_id);
    }
    
    public function like()
    {
        $uid = input('?param.uid') ?(int) input('param.uid') : 0;
        $room_id = input('?param.room_id') ?(string) input('param.room_id') :'';
        $type = input('?param.type') ?(string) input('param.type') : 'push';
     //   \BBExtend\user\Tongji::getinstance($uid)->zan();
    //    $db = Sys::get_container_db_eloquent();
//         $sql="select count(*) from bb_tongji_log where uid =? and type =18 and datestr=? ";
//         $result = DbSelect::fetchOne($db, $sql,[ $uid, date("Ymd") ]);
//         if ($result) {
//             return ['code'=> -210, 'message'=>'您已用完了今日的点赞次数'];
//         }
        
        
        switch ($type)
        {
            case 'push':
                return Pushmanager::_like($uid,$room_id);
            case 'record':
                return BBRecord::record_like($uid,$room_id);
            case 'rewind':
                return Rewindmanager::like($uid,$room_id);
        }
        return ['message'=>'传入类型错误','code'=>0];
    }
    
    public function unlike()
    {
        $uid = input('?param.uid') ?(int) input('param.uid') : 0;
        $room_id = input('?param.room_id') ?(string) input('param.room_id') :'';
        $type = input('?param.type') ?(string) input('param.type') : 'push';
        // 谢烨 20171020，禁止取消点赞。
        return ['code'=>0,'message' =>'点过赞就不能取消啦！' ];
        
        switch ($type)
        {
            case 'push':
                return Pushmanager::_un_like($uid,$room_id);
            case 'record':
                
                return Recordmanager::record_un_like($uid,$room_id);
            case 'rewind':
                return Rewindmanager::un_like($uid,$room_id);
        }
        \BBExtend\user\Tongji::getinstance($uid)->un_zan();
        return ['message'=>'传入类型错误','code'=>0];
    }
    //获取服务器配置
    public function get_config()
    {
        // 2017 05 统计第一次使用
        $is_init = input('?param.is_init')?input('param.is_init'):0;
        $is_init=intval($is_init);
        if ($is_init==1) {
            \BBExtend\user\Tongji::getinstance(0)->first_use();
        }
        
        
        $configinfo = json_decode(BBRedis::getInstance('config')->hGet('config','bb_config'),true);
        $configinfo =null;// xieye 我每次都这样。201709
        if(!$configinfo){
            $configinfo = Db::table('bb_config')->where('id',1)->find();
            unset($configinfo['id']);
            unset($configinfo['ucloudkey']);
            //读取配置到缓存
            BBRedis::getInstance('config')->hSet('config','bb_config',json_encode($configinfo));
        }
        if (!$configinfo)
        {
            return ['code'=>-101,'message'=>'获取配置信息失败!'];
        }
        $configinfo['monsterani_url']=$configinfo['picserver'].$configinfo['monsterani_url'];
        // xieye invite_share字段邀请分享。
        $data = ['configinfo' =>$configinfo,'user_state'=>'0','invite_share'=>1,
                'aliyun_dir'=> [
                        'header_pic_upload_dir'=> 'uploads/headpic_date/'.date("Ymd"),
                        'apply_pic_upload_dir'=> 'uploads/apply_date/'.date("Ymd"),
                        'race_backstage_pic_upload_dir'=> 'uploads/race_backstage_date/'.date("Ymd"),
                        'user_updates_pic_upload_dir'=> 'uploads/user_updates_date/'.date("Ymd"),
                        'make_card_pic_upload_dir'=> 'uploads/card_updates_date/'.date("Ymd"),
                        
                ],
                
        ];
        return ['data'=>$data,'code'=>1];
    }

    //获取全部角色
    public function get_bb_role() {
        $role = json_decode(BBRedis::getInstance('config')->hGet('config','bb_role'),true);
        if(!$role){
            //读取角色到缓存
            $role = Db::table('bb_role') -> select();
            if ($role)
            {
                BBRedis::getInstance('config')->hSet('config','bb_role',json_encode($role));
            }
        }
        if (!$role)
        {
            return ['code'=>-102,'message'=>'获取全部角色失败!'];
        }
        return ['data'=>$role,'code'=>1];
    }
    
    //获取分类信息
    public function get_bb_usersorts()
    {
        $usersortlist = json_decode(BBRedis::getInstance('config')->hGet('config','bb_usersort'),true);
        if(!$usersortlist){
            //读取分类到缓存
            $usersortlist = Db::table('bb_usersort') -> select();
            if ($usersortlist)
            {
                BBRedis::getInstance('config')->hSet('config','bb_usersort',json_encode($usersortlist));
            }
        }
        if (!$usersortlist)
        {
            return ['code'=>-103,'message'=>'获取分类信息失败!'];
        }
        return ['data'=>$usersortlist,'code'=>1];
    }

    //分类获取首页轮播图片
    // 谢烨 2016 10 09 加两个字段
    // xieye linktype=4表示大赛，复用字段activity_id表示大赛id
    
    // 参数中sort_id=2 表示是首页的。
    public function get_toppiclist()
    {
        $shouye = \BBExtend\fix\TableType::bb_toppic__sort_id_shouye;
        $sort_id = input('?param.sort_id')?(int)input('param.sort_id'): $shouye ;
        // client_type这个参数，1正常，2是大屏幕pad苹果专用。
        $client_type_id = input('?param.client_type')?(int)input('param.client_type'):1;
        $self_uid = input('?param.self_uid')?(int)input('param.self_uid'):0;
        
        $list = Db::table('bb_toppic')->where('sort_id',$sort_id)->order("id asc")->select();
        //正常的逻辑，只需sort_id,11，12，13特别，需要之后跟14
        if ( $sort_id==11 ||$sort_id==12 ||$sort_id==13  ) {
            $db = Sys::get_container_dbreadonly();
            $sql="select * from bb_toppic where sort_id in ({$sort_id}, 14) order by sort_id asc";
            $list = $db->fetchAll($sql);
//             $list = Db::table('bb_toppic')->where('sort_id',$sort_id)->order("id asc")->select();
        }
        
        
        //|sort_id|  轮播图分组id，默认值是2，11 导师栏目，12vip栏目，13机构栏目  |
        
        
        if ( $sort_id==11 ||$sort_id==12 ||$sort_id==13  ) {
            $user= \BBExtend\model\User::find( $self_uid );
            if ($user->role>1 ) {
                $new = [];
                foreach ( $list as $v ) {
                    if ( !in_array( $v['linktype'],[ 11,12,13 ])  ){
                        $new []= $v;
                    }
                }
                $list = $new;
            }
        }
        
        
        foreach ($list as $k =>  $TopDB) {
            if ($client_type_id==1) {
                $list[$k]['picpath'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $TopDB['picpath']);
            }elseif ($client_type_id==2) {
                if ($TopDB['picpath_pad']){
                    $temp = $TopDB['picpath_pad'];
                }else {
                    $temp = $TopDB['picpath'];
                }
                $list[$k]['picpath'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https($temp);
            }
            
            $list[$k]['live_video']=null;
            if ($self_uid && $sort_id==2) {
                
                // 谢烨 2017 1010，现在改成布尔变量，如果这个大赛有直播。
                
                //$list[$k]['live_video']=$this->has_live_video($TopDB['broadcast_uid'], $self_uid);
                $list[$k]['live_video']=$this->has_live_video( $TopDB['activity_id'] );
            }
            
            unset($list[$k]["sort_id"]);
            unset($list[$k]["id"]);
            unset($list[$k]["picpath_pad"]);
            unset($list[$k]["broadcast_uid"]);
            
        }
        
        
        if(!$list){
            return ['code'=> 0 , 'message'=>'分组'.$sort_id.'轮播图片信息获取失败!'];
        }else{
            return ['data'=>$list,'code'=>1];
        }
    }
    
    /**
     * 查一个大赛是否有直播
     */
    private function has_live_video($ds_id)
    {
        $ds = \BBExtend\model\Race::find($ds_id);
        if (!$ds) {
            return false;
        }
        return $ds->has_live_video();
        
    }
    
    private function get_live_video($uid,$self_uid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select bb_push.* from bb_push
               where bb_push.event='publish'
                 and uid = ? ";
        $row = DbSelect::fetchRow($db, $sql,[$uid]);
        if (!$row) {
            return null;
        }
        $result = BBPush::get_detail_by_row($row, $self_uid);
        return $result;
    }
    
    
    public function bottom_bar_for_android(){
        $rule_id = \BBExtend\model\BottomBar::get_rule_id();
        $result = \BBExtend\model\BottomBar::get_pics_lists($rule_id);
        
        return ['code'=>1,'data'=>[ 'object' =>$result['android'] ]];
        
    }
    
    
    public function bottom_bar_for_ios($version=0){
        
        $version = intval( $version );
        
        $rule_id = \BBExtend\model\BottomBar::get_rule_id();
        $result = \BBExtend\model\BottomBar::get_pics_for_ios($rule_id);
        if ($result['create_time'] != $version ) {
        
        
            return ['code'=>1,'data'=>[ 'zip' =>$result['zip_path'], 'list'=> $result['all_pic'] , 
                     'version'=>$result['create_time']  ]];
        }else {
            return ['code'=>1,'data'=>[ 'zip' =>'', 'version'=>$version,'list' =>null  ]];
        }
        
    }
   
    
    
    //获取才艺标签列表
    public function get_label2018(){
        
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_label order by id asc";
        $result = $db->fetchAll($sql);
        
                //         }
                $data = self::conversion($result);
                return ['data'=>['list' =>$data ],'code'=>1];
        }
    
    
    //获取才艺标签列表
    public function get_label(){
        $data = json_decode(BBRedis::getInstance('config')->hGet('config','bb_label'),true);
        if(!$data){
            //读取分类到缓存
            $data = Db::table('bb_label') -> select();
            if ($data)
            {
                BBRedis::getInstance('config')->hSet('config','bb_label',json_encode($data));
            }
            foreach ($data as $db)
            {
                BBRedis::getInstance('config')->hMset($db['id'].'label',$db);
            }
        }
        if (!$data)
        {
            return ['code'=>-106,'message'=>'标签列表获取失败!'];
        }
        $data = self::conversion($data);
        return ['data'=>$data,'code'=>1];
    }
    //获取学啥标签列表
    public function get_label_learn(){
        $data = json_decode(BBRedis::getInstance('config')->hGet('config','bb_label_learn'),true);
        if(!$data){
            //读取分类到缓存
            $data = Db::table('bb_label_learn') -> select();
            if ($data)
            {
                BBRedis::getInstance('config')->hSet('config','bb_label_learn',json_encode($data));
            }
        }
        if (!$data)
        {
            return ['code'=>-106,'message'=>'标签列表获取失败!'];
        }
        $data = self::conversion($data);
        return ['data'=>$data,'code'=>1];
    }
    //转换接口
    private function conversion($data)
    {
        $Data = array();
        foreach ($data as $DB)
        {
            $DB['id'] = (int)$DB['id'];
            $DB['name'] = (string)$DB['name'];
            array_push($Data,$DB);
        }
        return $Data;
    }
    //获取玩啥标签列表
    public function get_label_activity(){
        $data = json_decode(BBRedis::getInstance('config')->hGet('config','bb_label_activity'),true);
        if(!$data){
            //读取分类到缓存
            $data = Db::table('bb_label_activity') -> select();
            if ($data)
            {
                BBRedis::getInstance('config')->hSet('config','bb_label_activity',json_encode($data));
            }
            foreach ($data as $db)
            {
                BBRedis::getInstance('config')->hMset($db['id'].'bb_label_activity',$db);
            }
        }
        if (!$data)
        {
            return ['code'=>-106,'message'=>'标签列表获取失败!'];
        }
        $data = self::conversion($data);
        return ['data'=>$data,'code'=>1];
    }
    //上传android的log日志
    public function upload_android_log(){
        $uid = input('?post.uid') ?(int) input('post.uid') : '';
        $type=array("log");//文件上传类型
        $file =  request()->file('log');
        $prefix = date('Y-m-d');
        $httppath = '/uploads/androidlog/'.$uid.'/'.$prefix;
        $bigpicpath = '.'.$httppath;
        if (!is_dir($bigpicpath)){
            mkdir($bigpicpath,0775,true);
        }
        if ($file and in_array(pathinfo($file->getInfo()['name'],PATHINFO_EXTENSION), $type)) {
            $file->move($bigpicpath,'');
        }
    }
    //得到配置版本号
    public function get_config_version()
    {
        $ConfigDB = Db::table('bb_version')->find();
        $Config = array();
        $Config['id'] = (int)$ConfigDB['id'];
        $Config['config'] = (float)$ConfigDB['config'];
        $Config['label_baby'] = (float)$ConfigDB['label_baby'];
        $Config['label_activity'] = (float)$ConfigDB['label_activity'];
        $Config['label_learn'] = (float)$ConfigDB['label_learn'];
        $Config['label_speciality'] = (float)$ConfigDB['label_speciality'];
        
        $Config['jubao'] = (float)$ConfigDB['jubao'];
        return ['data'=>$Config,'code'=>1];
    }
    
    /**
     * 特别策划表
     * @return number[]|number[]
     */
    public function promote()
    {
        $db = Sys::get_container_db();
        $time=time();
        $sql="select id,type,is_html,url from bb_game where is_active=1
              and (start_time=0 or start_time < {$time} )
              and (end_time=0 or end_time > {$time} )
              order by id desc
              limit 1  
                ";
        $row = $db->fetchRow($sql);
        if ($row)
            return ["code" =>1, "data"=>$row ];
        else {
            return ["code"=>0];
        }
    }
    
    
}
