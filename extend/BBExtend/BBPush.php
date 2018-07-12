<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/13
 * Time: 14:07
 */

namespace BBExtend;
use app\user\controller\User;
use think\Db;
use BBExtend\message\Message;
use BBExtend\BBUser;
use BBExtend\Sys;
use BBExtend\BBRedis;
use BBExtend\user\exp\Exp;

class BBPush extends Currency
{
    //创建唯一字符串
    public static function Create_Online_ID($uid)
    {
        $time = uniqid("", true);
        $MD5String = $uid.$time.time();
        for ($Index = md5( $MD5String, true ), $String = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
             $Data = '',
             $Begin = 0;
             $Begin < 8;
             $Go = ord( $Index[ $Begin ] ),
             $Data .= $String[ ( $Go ^ ord( $Index[ $Begin + 8 ] ) ) - $Go & 0x1F ], $Begin++);
        return $Data;
    }
    //点赞
    public static function _like($uid,$room_id)
    {
        $MoviesDB = self::get_push_DB_by_room_id($room_id);
        if ($MoviesDB)
        {
            $LikeDB = Db::table('bb_push_like')->where(['uid'=>$uid,'room_id'=>$room_id])->find();
            if (!$LikeDB)
            {
                \BBExtend\user\Tongji::getinstance($uid)->zan($MoviesDB['uid']);
                $Data = array();
                $Data['uid'] = $uid;
                $Data['room_id'] = $room_id;
                $Data['time'] = time();
                Db::table('bb_rewind_like')->insert($Data);
                $MoviesDB['like']++;
                Db::table('bb_push')->where(['room_id'=>$room_id,'uid'=>$uid])->update(['like'=>$MoviesDB['like']]);
                BBRedis::getInstance('push')->hMset($room_id,$MoviesDB);
                
                $user = BBUser::get_user($uid);
//                 $db = Sys::get_container_db();
//                 $sql='select ';
                Exp::getinstance($uid)->set_typeint(Exp::LEVEL_ACTIVITY_LIKE )->add_exp();
                //成就
                $ach = new \BBExtend\user\achievement\Dianzan($uid);
                $ach->update(1);
                $ach = new \BBExtend\user\achievement\Zhubo($MoviesDB['uid']);
                $ach->update(1);
                
                $user22 = \app\user\model\UserModel::getinstance($uid);
                $pic = $user22->get_user_pic_no_http();
                
                Message::get_instance()
                ->set_title('系统消息')
                ->set_img($pic)
                ->add_content(Message::simple()->content($user['nickname'])->color(0xf4a560)  
                        ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                        )
                ->add_content(Message::simple()->content('赞了你的视频'))
                ->add_content(Message::simple()->content($MoviesDB['title'])->color(0xf4a560)  )
                ->set_type(119)
                ->set_uid($MoviesDB['uid'])
                ->send();
                
                return ['message'=>'点赞成功','code'=>1];
            }
            return ['message'=>'你已经点过赞了','code'=>0];
        }
        return  ['message'=>'没有这个视频信息请检查房间号是否正确','code'=>0];
    }
    //取消点赞
    public static function _un_like($uid,$room_id)
    {
        $MoviesDB = self::get_push_DB_by_room_id($room_id);
        if ($MoviesDB)
        {
            $LikeDB = Db::table('bb_push_like')->where(['uid'=>$uid,'room_id'=>$room_id])->find();
            if ($LikeDB)
            {
                Db::table('bb_push_like')->where(['uid'=>$uid,'room_id'=>$room_id])->delete();
                $MoviesDB['like']--;
                if ($MoviesDB['like']<0)
                {
                    $MoviesDB['like'] = 0;
                }
                Db::table('bb_push')->where(['room_id'=>$room_id,'uid'=>$uid])->update(['like'=>$MoviesDB['like']]);
                return ['message'=>'取消成功','code'=>1];
            }
        }
        return  ['message'=>'你好像没有点过赞哦','code'=>0];
    }
    public static function get_rewind_count($uid)
    {
        $UserDB = \BBExtend\BBUser::get_user($uid);
        $is_vip = $UserDB['vip'];
        if ($is_vip)
        {
            $Data =  Db::table('bb_rewind')-> where(['uid'=>$uid,'event'=>'rewind','is_remove'=>0,'is_save'=>1])->order('end_time','desc')->select();
            return  count($Data);
        }
        $Data =  Db::table('bb_rewind')-> where(['uid'=>$uid,'event'=>'rewind','is_remove'=>0,'is_save'=>1,'is_vip'=>0])->order('end_time','desc')->limit(0,5)->select();
        if ($Data)
        {
            return  count($Data);
        }
        return 0;
    }
    public static function get_push_DB_by_room_id($room_id)
    {
       // $PushDB = BBRedis::getInstance('push')->hGetAll($room_id);
        $PushDB =null;
        if (!$PushDB)
        {
            $uid = str_replace('push', '', (string)$room_id);
            $PushDB = Db::table('bb_push')->where('uid',$uid)->find();
            if ($PushDB)
            {
                BBRedis::getInstance('push')->hMset($room_id,$PushDB);
            }
        }
        return $PushDB;
    }
    public static function  get_push_DB($uid)
    {

        $vid = $uid.'push';
        //$PushDB = BBRedis::getInstance('push')->hGetAll($vid);
        
        $PushDB = null;
        if (!$PushDB)
        {
            $PushDB = Db::table('bb_push')->where('uid',$uid)->find();
        }
        if (!$PushDB)
        {
            $PushDB = array();
            $PushDB['uid'] = $uid;
            $PushDB['event'] = '';
            $PushDB['space_name'] = '';
            $PushDB['ip'] = '';
            $PushDB['like'] = 0;
            $PushDB['people'] = 0;
            $PushDB['room_id'] = $uid.'push';
            $PushDB['heat'] = 0;
            BBRedis::getInstance('push')->hMset($vid,$PushDB);
            //插入直播记录
            Db::table('bb_push')->insert($PushDB);
            $PushDB = Db::table('bb_push')->where('uid',$uid)->find();
        }
        return $PushDB;
    }
    
    /**
     * 便利性函数，
     * 方便返回给客户端，输入是一个表里查到行，并确保存在。
     * 输入uid，是当前用户。
     * @param unknown $DB
     */
    public static function get_detail_by_row($DB,$uid=0)
    {
        $DataDB =[];
        $user = \app\user\model\UserModel::getinstance($DB['uid']);
        
        
        $user_detail = \BBExtend\model\User::find( $DB['uid'] ); 
        
        $DataDB['role'] = $user_detail->role;
        $DataDB['frame'] = $user_detail->get_frame();
        $DataDB['badge'] = $user_detail->get_badge();
        
        
        $DataDB['id'] = (int)$DB['id'] ;
        $DataDB['uid'] = (int)$DB['uid'] ;
        //201708
        $DataDB['ach_count']        = $user->get_ach_count() ;
        
        $DataDB['vip'] = $user->get_user_vip();
        $DataDB['event'] = $DB['event'];
        $DataDB['pull_url'] = $DB['pull_url'];
        $DataDB['title'] = $DB['title'];
        $DataDB['label'] = (int)$DB['label'];
        $DataDB['login_address'] = $DB['address'];
        $DataDB['sex'] = $user->get_usersex();
        $DataDB['specialty'] = $user->get_specialty();
        $DataDB['time'] = $DB['time'];
        $DataDB['current_time'] = (string)time();
        $DataDB['is_focus'] = Focus::get_focus_state($uid,$DB['uid']);
        //显示在线观看人数以及点赞人数
        $RedisDB = BBRedis::getInstance('push')->hGetAll($DataDB['uid'].'push');
        $DataDB['is_like'] = false;
        if ($RedisDB)
        {
                $DataDB['like'] = (int)$RedisDB['like'];
                $DataDB['people'] = (int)$RedisDB['people'] + 1 ;
        }else {
            $DataDB['like'] =0;
            $DataDB['people'] =1;
        }
        $DataDB['nickname'] = $user->get_nickname();
      //  $Pic = $DB['bigpic'];
        $DataDB['bigpic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                 $DB['bigpic'], $user->get_userpic());
        //如果没有http://
//         if ($Pic)
//         {
//             if (!(strpos($Pic, 'http://') !== false))
//             {
//                 $DataDB['bigpic'] = $ServerURL.$Pic;
//             }
//             else
//             {
//                 $DataDB['bigpic'] = $Pic;
//             }
//         }else
//         {
//             $DataDB['bigpic'] = $user->get_userpic();
//         }
        $DataDB['pic'] =  $user->get_userpic();
        $DataDB['room_id'] = $DB['room_id'];
        $DataDB['age'] = $user->get_userage();
        // 2017 04 加等级
        $DataDB['level']        = $user->get_user_level();
        $DataDB['type'] = 'push';
        $DataDB['push'] = true;
        
        
        if (\BBExtend\BBRecord::display_comment_count_by_version()) {
            $DataDB['comment_count']=0;
        }
        
        //xieye 2016 10 购买课程
        $buy_help = new \BBExtend\user\Relation();
//         $DataDB['price'] = (int)$DB['price'] ;
//         $DataDB['price_type'] = (int)$DB['price_type'] ;
//         $DataDB['has_buy'] = $buy_help->has_buy_video($uid, $DataDB['room_id']);// 整形
        $DataDB['is_lahei'] = $buy_help->has_lahei( $DataDB['uid'] , $uid );// 整形
//        $DataDB['is_lahei'] =0;
        
        $DataDB['content_type'] = 10;
        $DataDB['start_time'] = intval( $DB['time'] );
        $DataDB['end_time'] = intval( $DB['end_time'] );// 启用。
        $DataDB['create_time'] = (int)$DB['create_time'] ;
        
        if($DB['price_type'] ==2 ) {
            $time = time();
            $DataDB['content_type'] = 11;// 伪直播
          //  $DataDB['end_time'] =  $time +  $DataDB['end_time'] - intval( $DataDB['time']) ;// 伪直播
          //  $DataDB['start_time'] =  $DataDB['time'] = $DataDB['craete_time']  =$time; // 伪直播
          //  $DataDB['time']=strval($DataDB['time']);
        }
        
        
        return $DataDB;        
    }
    
    
    
    /**
     * 便利性函数，
     * 方便返回给客户端，输入是一个表里查到行，并确保存在。
     * 输入uid，是当前用户。
     * @param unknown $DB
     */
    public static function get_rewind_detail2018_by_row($DB,$uid=0)
    {
        $DataDB =[];
        $user = \app\user\model\UserModel::getinstance($DB['uid']);
        
        $user_detail = \BBExtend\model\User::find( $DB['uid'] );
        
        $DataDB['role'] = $user_detail->role;
        $DataDB['frame'] = $user_detail->get_frame();
        $DataDB['badge'] = $user_detail->get_badge();
        
        
        $DataDB['id'] = (int)$DB['id'] ;
        $DataDB['uid'] = (int)$DB['uid'] ;
        
        $DataDB['content_type'] = 20;
        $DataDB['like'] = (int)$DB['like'];
        $DataDB['publish_time'] = $DB['start_time'];
        $DataDB['people'] = (int)$DB['people'];
        $DataDB['bigpic'] = \BBExtend\common\Image::geturl($DB['bigpic']);
        
        
        //201708
        $DataDB['ach_count']        = $user->get_ach_count() ;
        
        $DataDB['sex']= $user->get_usersex();
        $DataDB['nickname']=$user->get_nickname();
        $DataDB['level']=$user->get_user_level();
        $DataDB['pic']        = $user->get_userpic();
        $DataDB['age']        = $user->get_userage();
        $DataDB['level']        = $user->get_user_level();
        $DataDB['pull_url']   = $DB['rewind_url'];
        $DataDB['title']      = strval($DB['title']);
        $DataDB['room_id']      = strval($DB['room_id']);
        $DataDB['ds']      =null;
        $DataDB['type']      ='rewind';
        
        
        $DataDB['is_like'] = self::get_rewind_is_like($uid,$DB['room_id']);
        $DataDB['is_focus'] = \BBExtend\Focus::get_focus_state($uid, $DB['uid'] );
        
        
        
        
        if (\BBExtend\BBRecord::display_comment_count_by_version()) {
            // 评论数
            $DataDB['comment_count']=\BBExtend\BBComments::Get_comments_count(
                    "bb_rewind_comments", $DB['id'] );
        }
        return $DataDB;
    }
    
    
    
    
    
    /**
     * 便利性函数，
     * 方便返回给客户端，输入是一个表里查到行，并确保存在。
     * 输入uid，是当前用户。
     * @param unknown $DB
     */
    public static function get_rewind_detail_by_row($DB,$uid=0)
    {
        $DataDB =[];
        $user = \app\user\model\UserModel::getinstance($DB['uid']);
        $DataDB['id'] = (int)$DB['id'] ;
        $DataDB['uid'] = (int)$DB['uid'] ;
        
        $DataDB['content_type'] = 20;
        $DataDB['like'] = (int)$DB['like'];
        $DataDB['publish_time'] = $DB['start_time'];
        $DataDB['people'] = (int)$DB['people'];
        $DataDB['bigpic'] = \BBExtend\common\Image::geturl($DB['bigpic']);
        
        // 2017 04 加两个字段
     //   $user = \app\user\model\UserModel::getinstance($DB['uid']);
        
        //201708
        $DataDB['ach_count']        = $user->get_ach_count() ;
        
        $DataDB['sex']= $user->get_usersex();
        $DataDB['nickname']=$user->get_nickname();
        $DataDB['level']=$user->get_user_level();
        $DataDB['pic']        = $user->get_userpic();
        $DataDB['age']        = $user->get_userage();
        $DataDB['level']        = $user->get_user_level();
        $DataDB['pull_url']   = $DB['rewind_url'];
        $DataDB['title']      = strval($DB['title']);
        $DataDB['room_id']      = strval($DB['room_id']);
        $DataDB['ds']      =null;
        $DataDB['type']      ='rewind';
        
        
        $DataDB['is_like'] = self::get_rewind_is_like($uid,$DB['room_id']);
        $DataDB['is_focus'] = \BBExtend\Focus::get_focus_state($uid, $DB['uid'] );
        
        if (\BBExtend\BBRecord::display_comment_count_by_version()) {
            // 评论数
             $DataDB['comment_count']=\BBExtend\BBComments::Get_comments_count(
                                   "bb_rewind_comments", $DB['id'] );
        }
        return $DataDB;
    }
    
    
    public static function get_rewind_is_like($uid,$room_id)
    {
        $LikeDB = Db::table('bb_rewind_like')->where(['uid'=>$uid,'room_id'=>$room_id])->find();
        if ($LikeDB)
        {
            return true;
        }
        return false;
    }
    
}