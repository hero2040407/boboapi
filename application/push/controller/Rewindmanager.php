<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/9/3
 * Time: 18:25
 */

namespace app\push\controller;

use BBExtend\message\Message;
use app\user\controller\User;
use think\Db;
use BBExtend\BBUser;
use BBExtend\user\exp\Exp;
use BBExtend\fix\MessageType;
use BBExtend\common\Client;

use BBExtend\Sys;

class Rewindmanager
{
    //保存视频到我的直播中
    public function save_push()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $stream_name = input('?param.stream_name')?(string)input('param.stream_name'):"";
        $is_save =  input('?param.is_save')?(int)input('param.is_save'):0;
        if (\app\user\model\Exists::userhExists($uid)==1)
        {
            $RewindDB = Db::table('bb_rewind')->where(['stream_name'=>$stream_name])->find();
            if ($RewindDB)
            {
                $RewindDB['is_save'] = $is_save;
                $RewindDB['end_time'] = time();
                $RewindDB['event'] = 'rewind';
                $RewindDB['room_id'] = $stream_name.time();
                if ($is_save)
                {
                    //xieye count
                    $Count = Db::table('bb_rewind')->where(['uid'=>$uid,'event'=>'rewind',
                        'is_remove'=>0,'is_save'=>1])->count();
                    
//                     $is_vip = User::get_user_vip($uid)['vip'];
//                     if ($is_vip)
//                     {

                        $RewindDB['is_vip'] = 1;
                        Db::table('bb_rewind')->where(['stream_name'=>$stream_name])->update($RewindDB);
                        return ['message'=>'保存成功','code'=>1];
//                     }
//                     // xieye count
//                     $NotVIPCount = Db::table('bb_rewind')->where(['uid'=>$uid,
//                         'event'=>'rewind','is_remove'=>0,'is_save'=>1,'is_vip'=>0])->count();
                    
//                     if (!$is_vip && $NotVIPCount < 5)
//                     {
//                         Db::table('bb_rewind')->where(['stream_name'=>$stream_name])->update($RewindDB);
//                         return ['message'=>'保存成功','code'=>1];
//                     }
//                     else
//                     {
//                         return ['message'=>'请购买VIP或者删除多余的视频','code'=>0];
//                     }
                }
            }
            return ['message'=>'没有该直播记录','code'=>0];
        }
        return ['code'=>\app\user\model\Exists::userhExists($uid)];
    }

    
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
        $record = $db::table( 'bb_rewind' )->where('is_remove',0)
        ->where('id',$id)->first();
        if (!$record) {
            return ['code'=>0,'message'=>'id err'];
        }
        $record = get_object_vars($record);
        
        $temp = \BBExtend\BBPush::get_rewind_detail2018_by_row($record, $uid );
        return [
                'code'=>1,
                'data' =>$temp,
        ];
        
    }
    
    
    //得到直播记录 不是VIP每次只有5个
    public function get_push_rewind()
    {
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):0;
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
       // $is_vip = User::get_user_vip($uid)['vip'];
        $is_vip=1;
//         if ($is_vip)
//         {
            $User_rewind_movies = Db::table('bb_rewind')-> where(['uid'=>$uid,'event'=>'rewind','is_remove'=>0,'is_save'=>1])->limit($start_id,$length)->order('end_time','desc')->select();
//         }
//         else
//         {
//             $User_rewind_movies = Db::table('bb_rewind')-> where(['uid'=>$uid,'event'=>'rewind','is_remove'=>0,'is_save'=>1,'is_vip'=>0])->limit(0,5)->order('end_time','desc')->select();
//         }
        $Data = array();
        foreach ($User_rewind_movies as &$RewindDB)
        {
            $RewindDB['id'] = (int)$RewindDB['id'];
            $RewindDB['content_type'] = 20;
            
            $RewindDB['uid'] = (int)$RewindDB['uid'];
            $RewindDB['like'] = (int)$RewindDB['like'];
            $RewindDB['people'] = (int)$RewindDB['people'];
             
            
            if ($RewindDB['bigpic'])
            {
                $RewindDB['bigpic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                           $RewindDB['bigpic'] );
            }
            else
            {
                $RewindDB['bigpic'] = User::get_userpic($uid);
            }
            
            // 2017 04 加两个字段
            $user = \app\user\model\UserModel::getinstance($RewindDB['uid']);
            $RewindDB['sex']= $user->get_usersex();
            $RewindDB['nickname']=$user->get_nickname();
            $RewindDB['level']=$user->get_user_level();
            
            // xieye,加上时间差。
            $RewindDB['end_time_int'] = intval($RewindDB['end_time']);
            $RewindDB['time_cha'] =$RewindDB['end_time_int'] + (15 *60) - time();  
            
            if ($query_uid)
            {
                $RewindDB['is_like'] = self::get_is_like($query_uid,$RewindDB['room_id']);
                $RewindDB['is_focus'] = \BBExtend\Focus::get_focus_state($query_uid, $uid );
                
                
            }
            array_push($Data,$RewindDB);
        }
        if (!$is_vip)
        {
            return ['data'=>$Data,'is_bottom'=>1,'code'=>1];
        }
        if (count($Data)==$length)
        {
            return ['data'=>$Data,'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$Data,'is_bottom'=>1,'code'=>1];
    }
    
    
    
    
    
    //得到直播记录 不是VIP每次只有5个
    public function get_push_rewind_v2()
    {
        $query_uid = input('?param.query_uid')?(int)input('param.query_uid'):0;
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        // $is_vip = User::get_user_vip($uid)['vip'];
        $is_vip=1;
        //         if ($is_vip)
            //         {
        $User_rewind_movies = Db::table('bb_rewind')-> where(['uid'=>$uid,'event'=>'rewind','is_remove'=>0,'is_save'=>1])->limit($start_id,$length)->order('end_time','desc')->select();
        //         }
        //         else
            //         {
        //             $User_rewind_movies = Db::table('bb_rewind')-> where(['uid'=>$uid,'event'=>'rewind','is_remove'=>0,'is_save'=>1,'is_vip'=>0])->limit(0,5)->order('end_time','desc')->select();
        //         }
        $Data = array();
        foreach ($User_rewind_movies as &$RewindDB)
        {
            
            $temp = \BBExtend\model\Rewind::find( $RewindDB['id'] );
            $temp->self_uid = $query_uid;
            $Data[]= $temp->get_all();
            
        }
        $is_bottom = (count($Data)==$length)? 0:1;
        return ['data'=>['list' =>$Data, 'is_bottom'=>$is_bottom, ],  'code'=>1];
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
        
        $Data =  Db::table('bb_rewind')-> where(['uid'=>$uid,'id'=>$id,'is_remove'=>0])->find();
        if ($Data)
        {
            Db::table('bb_rewind')-> where(['id'=>$id])->update(['is_remove'=>1]);
        }else
        {
            return ['message'=>'删除失败','code'=>0];
        }
        return ['message'=>'删除成功','code'=>1];
    }
    
    
    //进入房间回调
    public static function notify_enterroom($uid,$room_id)
    {
        $MoviesDB =  Db::table('bb_rewind')->where(['room_id'=>$room_id])->find();
        if ($MoviesDB)
        {
            $cound = $MoviesDB['people'];
            $cound++;
            Db::table('bb_rewind')->where('room_id',$room_id)->update(['people'=>$cound]);
        }
        return $MoviesDB['like'];
    }
    
    
    public static function like($uid,$room_id)
    {
        $movieDB = Db::table('bb_rewind')->where(['room_id'=>$room_id])->find();
        if ($movieDB)
        {
            $LikeDB = Db::table('bb_rewind_like')->where(['uid'=>$uid,'room_id'=>$room_id])->find();
            if (!$LikeDB)
            {
                $Data = array();
                $Data['uid'] = $uid;
                $Data['room_id'] = $room_id;
                $Data['time'] = time();
                Db::table('bb_rewind_like')->insert($Data);
                $like = $movieDB['like'] + 1;
                Db::table('bb_rewind')->where(['room_id'=>$room_id])->update(['like'=>$like]);
                
                $user = BBUser::get_user($uid);
             //   $movieDB22 = self::get_movies_by_room_id($room_id);
                Exp::getinstance($uid)->set_typeint(Exp::LEVEL_ACTIVITY_LIKE )->add_exp();
                $user22 = \app\user\model\UserModel::getinstance($uid);
                $pic = $user22->get_user_pic_no_http();
                Message::get_instance()
                ->set_title('系统消息')
                ->set_img($pic)
                ->add_content(Message::simple()->content($user['nickname'])->color(0xf4a560)
                        ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                        )
                        ->add_content(Message::simple()->content('赞了你的视频'))
                        ->add_content(Message::simple()->content($movieDB['title'])->color(0xf4a560)  )
                        ->set_type( MessageType::shipin_beizan )
                        ->set_uid($movieDB['uid'])
                        ->send();
                
                return ['message'=>'点赞成功','code'=>1];
            }
            return ['message'=>'你已经点过赞了','code'=>0];
        }
        return  ['message'=>'没有这个视频信息请检查房间号是否正确','code'=>0];
    }
    
    
    public static function un_like($uid,$room_id)
    {
        $movieDB = Db::table('bb_rewind')->where(['room_id'=>$room_id])->find();
        if ($movieDB)
        {
            $LikeDB = Db::table('bb_rewind_like')->where(['uid'=>$uid,'room_id'=>$room_id])->find();
            if ($LikeDB)
            {
                Db::table('bb_rewind_like')->where(['uid'=>$uid,'room_id'=>$room_id])->delete();
                $like = $movieDB['like'] - 1;
                if ($like<0)
                {
                    $like = 0;
                }
                Db::table('bb_rewind')->where(['room_id'=>$room_id])->update(['like'=>$like]);
                return ['message'=>'取消成功','code'=>1];
            }
        }
        return  ['message'=>'你好像没有点过赞哦','code'=>0];
    }
    
    
    public static function get_is_like($uid,$room_id)
    {
        $LikeDB = Db::table('bb_rewind_like')->where(['uid'=>$uid,'room_id'=>$room_id])->find();
        if ($LikeDB)
        {
            return true;
        }
        return false;
    }
    
    
    //通知回播地址
    //spaceName    空间名称
    //streamName    流名称
    //startTime    录制开始时间
    //endTime    录制结束时间
    //url    回播地址
    public function notify_rewind()
    {
        $spaceName = input('?param.spaceName')?(string)input('param.spaceName'):"";
        $streamName = input('?param.streamName')?(string)input('param.streamName'):"";
        $startTime = input('?param.startTime')?(string)input('param.startTime'):"";
        $endTime = input('?param.endTime')?(string)input('param.endTime'):"";
        $url =  input('?param.url')?(string)input('param.url'):"";
        $RewindDB = Db::table('bb_rewind')->where('stream_name',$streamName)->find();
        if ($RewindDB)
        {
            $RewindDB['space_name'] = $spaceName;
            $RewindDB['start_time'] = $startTime;
            $RewindDB['end_time'] = $endTime;
            $RewindDB['room_id'] = $streamName.$endTime;
            $RewindDB['rewind_url'] = $url;//设置回播地址
            $RewindDB['event'] = "rewind"; //设置为回播事件
            Db::table('bb_rewind')->where('stream_name',$streamName)->update($RewindDB);
        }else
        {
            $RewindDB = array();
            $RewindDB['space_name'] = $spaceName;
            $RewindDB['start_time'] = $startTime;
            $RewindDB['end_time'] = $endTime;
            $RewindDB['room_id'] = $streamName.$endTime;
            $RewindDB['rewind_url'] = $url;//设置回播地址
            $RewindDB['event'] = "rewind"; //设置为回播事件
            Db::table('bb_rewind')->insert($RewindDB);
             
//             if (\BBExtend\common\BBConfig::get_server_url() == 'http://bobo.yimwing.com' ) {
//                // if (!$uid) { //推定是测试服的数据，因为现在测试服的uid从10万开始了。
//                     $param=[
//                         'startTime'=>$startTime,
//                         'endTime'=>$endTime,
//                         'spaceName'=>$spaceName,
//                         'streamName'=>$streamName,
//                         'url'=>$url,
//                     ];
//                     $url ='http://test.yimwing.com/push/rewindmanager/notify_rewind?'.
//                             http_build_query($param);
//                     file_get_contents($url);
                            
//                // }
//             }
            
            
        }
    }
    
}