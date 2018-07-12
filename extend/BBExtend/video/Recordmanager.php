<?php
namespace BBExtend\video;

use app\task\controller\Taskapi;
use BBExtend\BBRecord;
use BBExtend\BBRedis;
use think\Db;
use app\user\controller\User;
use app\task\controller\Taskactivityapi;
use BBExtend\Level;

use BBExtend\user\RecordCheck;

/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/12
 * Time: 9:40
 */
class Recordmanager extends BBRecord
{
 //得到用户的视频按页
    public function get_user_movies()
    {
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):0;
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $startid = input('?param.startid')?input('param.startid'):0;
        $length = input('?param.length')?input('param.length'):20;
        if ($uid!= $query_uid) {
        
          $MoviesDB = Db::table('bb_record')->where(['uid'=>$uid,'is_remove'=>0])
          
          ->where("type != 3")
          ->order('time','desc')->limit($startid,$length)->select();
        } else {
            $MoviesDB = Db::table('bb_record')->where(['uid'=>$uid,'is_remove'=>0])
           
            ->order('time','desc')->limit($startid,$length)->select();
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
                $MoviesDB[$i]['content_type'] = (int)$MoviesDB[$i]['usersort'] ;
                if ($MoviesDB[$i]['content_type'] == 0) {
                    $MoviesDB[$i]['content_type'] =  2;
                }
                
                
                // xieye 2016 10 25
                $MoviesDB[$i]['has_buy'] = $buy_help->has_buy_video($query_uid, $MoviesDB[$i]['room_id'] );
                
                
                $Pic = $MoviesDB[$i]['big_pic'];
                unset($MoviesDB[$i]['big_pic']);
                $serverUrl = \BBExtend\common\BBConfig::get_server_url();
                
                $MoviesDB[$i]['bigpic'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                        $Pic,$MoviesDB[$i]['thumbnailpath']  );
                
                $Look = BBRedis::getInstance('record')->hGet($MoviesDB[$i]['room_id'].'record','look');
                if ($Look)
                {
                    $DataDB['people'] = (int)$Look;
                }else
                {
                    $DataDB['people'] = (int) $MoviesDB[$i]['look'];
                }
                if ($query_uid)
                {
                    $MoviesDB[$i]['is_like'] = self::get_is_like($query_uid,$MoviesDB[$i]['room_id']);
                }
                $MoviesDB[$i]['age'] = (int) User::get_userage($MoviesDB[$i]['uid']);
            }
            if (count($MoviesDB) == $length)
            {
                return ['data'=>$MoviesDB,'is_bottom'=>0,'code'=>1];
            }
        }
        return ['data'=>$MoviesDB,'is_bottom'=>1,'code'=>1];
    }
    //删除界面
    public function remove_rewind()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $id = input('?param.id')?(int)input('param.id'):0;
        $Data =  Db::table('bb_record')->where(['uid'=>$uid,'id'=>$id])->find();
        if ($Data)
        {
            Db::table('bb_record')->where(['uid'=>$uid,'id'=>$id])->update(['is_remove'=>1]);
        }else
        {
            return ['message'=>'删除失败','code'=>1];
        }
        return ['message'=>'删除成功','code'=>1];
    }
    public function upload_record()
    {
        //xieye log
            $log = new \app\pay\model\Alitemp();
            $log->data('url', 'upload_record');
            $log->data('content', json_encode(['uid'=>(int)input('post.uid'), 
                'type'=>(int)input('post.type'),
                'activity_id'=> (int)input('post.activity_id'),
            ]) );
            $log->data('create_time',date("Y:m:d H-i-s"));
            $log->save();
        
        
        $uid = input('?post.uid')?(int)input('post.uid'):0;
        $type = input('?post.type')?(int)input('post.type'):0;//秀场 1   邀约 2  个人验证 3
        $video_path = input('?post.video_path')?(string)input('post.video_path'):'';
        $thumbnailpath = input('?post.thumbnailpath')?(string)input('post.thumbnailpath'):'';
        $activity = input('?post.activity_id')?(int)input('post.activity_id'):0;//活动id
        $sort = input('?post.sort')?(int)input('post.sort'):0;//活动id
        $address = input('?post.address')?(string)input('post.address'):'未设定';
        $title = input('?post.title')?(string)input('post.title'):'';
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
        
        // xieye 2016 10
        if ($type != 3) {
          \BBExtend\user\Tongji::getinstance($uid)->upload_movie();
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
        $recordDB['type'] = $type;//视频类型 //秀场 1   邀约 2  个人验证 3
        if ($type == 3)//个人认证视频
        {
            \BBExtend\BBUser::set_attestation($uid,1);
            \BBExtend\user\Tongji::getinstance($uid)->renzheng_yonghu();
        }
        $recordDB['video_path'] = $video_path;//视频路径地址
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
        //房间ID-》使用用户id + 数据库索引号组成
        self::update_record($recordDB);
        User::set_address($uid,$address);
        //增加经验
        Level::add_user_exp($uid,LEVEL_RECORD);
        return ['data'=>$recordDB,'code'=>1];
    }

    //后台API
    //测试接口
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
        return self::set_cer_movies($id,$audit);
    }
    protected static function set_cer_movies($id,$audit)
    {
        $DB = Db::table('bb_record')->where('id',$id)->find();
        if ($DB)
        {
            // 谢烨，最新改动。
            if (in_array($DB['type'], [2,3])) {
                $record_check = new  RecordCheck($id, $audit);
                $result = $record_check->check();
                if ($result) {
                    return ["code"=>1];
                }else {
                    return ['code'=>0,'message'=>$record_check->message];
                }
            }
            
            
            $DB['audit'] = $audit;
            if ($audit == 1)//如果是通过审核
            {
                switch ($DB['type'])
                {
                    case 1://秀场
                        switch ($DB['usersort'])
                        {
                            case 0: //默认为宝贝秀
                                $DB['usersort'] = 2;
                                break;
                            case 1://学啥

                                break;
                            case 2://宝贝秀

                                break;
                            case 3://玩啥

                                break;
                        }
                        break;
//                     case 2://邀约
                        
//                         // xieye ，必须有前置条件。
                        
//                         $activity_id = $DB['activity_id'];
//                         $activityDB = Taskactivityapi::get_activity($activity_id);
//                         if ($activityDB)
//                         {
//                             Taskactivityapi::join_activity($DB['uid'],$activity_id);
//                             //邀约奖励获取
//                             Taskapi::CompleteTask($DB['uid'],$activityDB['task_id']);
//                         }else
//                         {
//                             return 'is not activity';
//                         }
//                         break;
//                     case 3://个人验证
//                         $activity_id = $DB['activity_id'];
//                         $activityDB = Taskactivityapi::get_activity($activity_id);
//                         if ($activityDB)
//                         {
//                             Taskactivityapi::join_activity($DB['uid'],$activity_id);
//                             Taskapi::CompleteTask($DB['uid'],$activityDB['task_id']);
//                             User::set_attestation($DB['uid'],2);
//                             Level::add_user_exp($DB['uid'],LEVEL_COMPLETE_ATTESTAION);
//                         }else
//                         {
//                             return 'is not activity';
//                         }
//                         break;
                    case 4:

                        break;
                }
            }
            else if($audit == 2)//认证未通过
            {
                switch ($DB['type'])
                {
                    case 1://秀场
                        switch ($DB['usersort'])
                        {
                            case 0: //默认为宝贝秀
                                $DB['usersort'] = 2;
                                break;
                            case 1://学啥

                                break;
                            case 2://宝贝秀

                                break;
                            case 3://玩啥

                                break;
                        }
                        break;
//                     case 2://邀约
//                         $activity_id = $DB['activity_id'];
//                         $activityDB = Taskactivityapi::get_activity($activity_id);
//                         if ($activityDB)
//                         {
//                             Taskactivityapi::del_join($DB['uid'],$activity_id);
//                         }
//                         break;
//                     case 3://个人验证
//                         $activity_id = $DB['activity_id'];
//                         $activityDB = Taskactivityapi::get_activity($activity_id);
//                         if ($activityDB)
//                         {
//                             Taskactivityapi::del_join($DB['uid'],$activity_id);
//                             User::set_attestation($DB['uid'],3);
//                         }
//                         break;
                    case 4:

                        break;
                }
            }
            self::update_record($DB);
            return ['code'=>1,'message'=>''];
        }
        return ['code'=>0,'message'=>'有错'];
    }

}