<?php
/**
 * Created by PhpStorm.
 * User: tRee
 * Date: 2016/7/7
 * Time: 19:37
 */
namespace app\boboshare\controller;
use app\task\controller\Info;
use app\task\controller\Taskactivityapi;
use think\Controller;
use think\Cookie;
use think\Db;
use think\Cache;
use app\user\controller\User;
use app\record\controller\Recordmanager;
use app\push\controller\Pushmanager;
use BBExtend\BBRedis;

define('SHARE_PUSH_TYPE',1);//直播
define('SHARE_PUSH_REW_TYPE',2);//直播回播
define('SHARE_RECORD_TYPE',3); //录播

class Index extends Controller
{

    public function index()
    {
        $id = input('?param.id')?input('param.id'):0;
        if(input('?param.amp;id'))$id = input('param.amp;id');
        $type = input('?param.type')?input('param.type'):0;

        $ip = $_SERVER['REMOTE_ADDR'];
        Cookie::init(['prefix'=>'BOBO_','expire'=>0,'path'=>'/']);
        if(!Cookie::has('share_page_uid','BOBO_')){
            $user_cookie = md5($ip.time().rand(1000,9999));
            Cookie('share_page_uid',$user_cookie,0);
        }else{
            $user_cookie = Cookie('share_page_uid');
        }

        $data = array();
        $RecordDB = array();
        $data['id'] =0;
        $data['type'] ='mp4';
        $data['livetype'] = 'vod';
        $data['urltype'] = 'videourl';
        $data['bigpic'] ='../share/images/default.jpg';
        $data['pic'] ='../share/images/default_icon.png';
        $data['nickname'] = '怪兽BoBo';
        $data['people'] = 0;
        $data['sex'] = 0;
        $data['age'] = 0;
        $data['address'] = '来自星星';
        $data['audit'] = 1;
        $data['like'] = 0;
        $data['like_state'] = 0;
        $data['title'] = '欢迎来到怪兽BoBo';
        $data['labelname'] = '#热门#';
        $data['url'] ='http://upload.guaishoubobo.com/1006449/video_1006449_20170920133358.mp4';
        $data['event'] ='';
        $uid = 0;
        $activity_id='';
        $data['comments_count']=0;
        $record_list='';
        $gold['gold_sum']=0;
        $gold['gold_people_num']=0;
        $share_server = \BBExtend\common\BBConfig::get_share_server_url();
        switch ($type)
        {
            case SHARE_PUSH_TYPE:
                $pushdata = Pushmanager::get_push_DB($id);
                if (isset($pushdata['bigpic'])) {
                    if ($pushdata['bigpic'] == '' || empty($pushdata['bigpic'])) {
                        $data['bigpic'] = User::get_userpic($id);
                    } else {
                        if ((strpos($pushdata['bigpic'], 'http') !== false)) {
                            $data['bigpic'] = $pushdata['bigpic'];
                        }else{
                            $data['bigpic'] = $share_server . $pushdata['bigpic'];
                        }
                    }
                }
                $uid = $id;
               if (isset($pushdata['pull_url'])){
                  //$request = \think\Request::instance();
                   if (  \BBExtend\common\Request::isMobile() )
                   {
                       $data['livetype'] = 'live';
                       $data['urltype'] = 'streamurl';
                       $data['type'] = 'm3u8';
                       if(strpos($pushdata['pull_url'], 'http') === false){
						   $data['url'] = str_replace("rtmp","http",$pushdata['pull_url']).'.m3u8';
                       }else{
                           $data['url'] = str_replace("flv","m3u8",$pushdata['pull_url']);
                       }
                   }else{
                       $data['type'] = 'flv';
                       $data['url'] = $pushdata['pull_url'];
                       if(strpos($pushdata['pull_url'], 'http') === false){
                           $data['url'] = str_replace("rtmp","http",$pushdata['pull_url']).'.flv';
                       }
                   }
                   $data['like'] = $pushdata['like'];
                   $data['address'] = $pushdata['address'];
                   $data['event'] = $pushdata['event'];
                   $data['people'] = $pushdata['people']++*5;
                   $data['labelname'] =  '#'.Db::table('bb_label')->where('id',$pushdata['label'])->find()['name'].'#';
                   $data['title'] = str_replace($data['labelname'],'',$pushdata['title']);
                   $data['labelname'] = str_replace('##','',$data['labelname']);
                   if(Db::table('bb_push_like')->where(['room_id'=>$id.'push','ip'=>$ip])->find()){
                       $data['like_state'] =  1;
                   }
                   Db::table('bb_push')->where('uid',$id)->setInc('people');
                   BBRedis::getInstance('push')->hSet($uid.'push','people',$pushdata['people']);
                   $gold =  Db::table('bb_dashang_log')->where(['uid'=>0,'room_id'=>$id.'push'])->field('sum(bean) as gold_sum,count(*) as gold_people_num')->find();
               }else{
                   $data['event'] ='publish_done';
               }
                break;
            case SHARE_PUSH_REW_TYPE:
                $rewdata = Db::table('bb_rewind')->where('room_id',$id)->field('id,uid,rewind_url,bigpic,people,title,like,label,address')->find();
                if ($rewdata) {
                    $uid = $rewdata['uid'];
                    if (isset($rewdata['bigpic'])) {
                        if ($rewdata['bigpic'] == '' || empty($rewdata['bigpic'])) {
                            $data['bigpic'] = User::get_userpic($uid);
                        } else {
                            if ((strpos($rewdata['bigpic'], 'http') !== false)) {
                                $data['bigpic'] = $rewdata['bigpic'];
                            } else {
                                $data['bigpic'] = $share_server . $rewdata['bigpic'];
                            }
                        }
                    }
                    $data['type'] = 'm3u8';
                    $activity_id = $rewdata['id'];
                    $data['id'] = $rewdata['id'];
                    $data['address'] = $rewdata['address'];
                    $data['title'] = $rewdata['title'];
                    $data['like'] = $rewdata['like'];
                    $data['people'] = $rewdata['people']++ * 5;
                    $data['url'] = $rewdata['rewind_url'];
                    $data['labelname'] = '#' . Db::table('bb_label')->where('id', $rewdata['label'])->find()['name'] . '#';
                    $data['title'] = str_replace($data['labelname'], '', $rewdata['title']);
                    $data['labelname'] = str_replace('##', '', $data['labelname']);
                    if (Db::table('bb_rewind_like')->where(['room_id' => $id, 'ip' => $ip])->find()) {
                        $data['like_state'] = 1;
                    }
                    Db::table('bb_rewind')->where('room_id', $id)->setInc('people');
                    $gold = Db::table('bb_dashang_log')->where(['uid' => 0, 'room_id' => $id])->field('sum(bean) as gold_sum,count(*) as gold_people_num')->find();
                }
                break;
            case SHARE_RECORD_TYPE:
                $RecordDB = Recordmanager::get_activity_movies_by_room_id($id);
                if ($RecordDB){
                    $uid = $RecordDB['uid'];
                    if (isset($RecordDB['big_pic'])) {
                        if ($RecordDB['big_pic'] == '' || empty($RecordDB['big_pic'])) {
                            $data['bigpic'] = User::get_userpic($uid);
                        } else {
                            if ((strpos($RecordDB['big_pic'], 'http') !== false)) {
                                $data['bigpic'] = $RecordDB['big_pic'];
                            }else{
                                $data['bigpic'] = $share_server . $RecordDB['big_pic'];
                            }
                        }
                    }
                    if ($RecordDB)
                    {
                        $uid = $RecordDB['uid'];
                    }

                    $data['audit'] = $RecordDB['audit'];
                    $data['type'] = 'mp4';
                    $activity_id = $RecordDB['id'];
                    $data['id']= $RecordDB['id'];
                    $data['address'] = $RecordDB['address'];
                    $data['like'] = $RecordDB['like'];
                    $data['people'] = $RecordDB['look']++;
                    $data['url'] = $RecordDB['video_path'];
                    $data['labelname'] =  '#'.Db::table('bb_label')->where('id',$RecordDB['label'])->find()['name'].'#';
                    $data['title'] = str_replace($data['labelname'],'',$RecordDB['title']);
                    $data['labelname'] = str_replace('##','',$data['labelname']);
                    if(Db::table('bb_record_like')->where(['room_id'=>$id,'ip'=>$ip,'count'=>20])->find() || BBRedis::getInstance('record')->hGet($id.'record','record_zan_cookie_'.$user_cookie.date('Ymd').'_'.$id) !== false){
                        $data['like_state'] =  1;
                    }
                    Db::table('bb_record')->where('room_id',$id)->setInc('look');
                    Db::table('bb_record')->where('room_id',$id)->setInc('real_people');
                    BBRedis::getInstance('record')->hSet($id.'record','look',$RecordDB['look']);
                    $gold =  Db::table('bb_dashang_log')->where(['uid'=>0,'room_id'=>$id])->field('sum(bean) as gold_sum,count(*) as gold_people_num')->find();
                }
                break;
        }
        $data['record_count']=Db::table('bb_record')->where(['uid'=>$uid,'audit'=>1,'is_remove'=>0])->where('type','neq',3)->count();
        if($uid != 0){
            $data['pic'] =  User::get_userpic($uid);
            $data['sex'] = User::get_usersex($uid);
            $data['age'] = User::get_userage($uid);
            $data['nickname'] = User::get_nickname($uid);
            $record_list= Db::table('bb_record')->where(['uid'=>$uid,'audit'=>1,'is_remove'=>0])->where('type','neq',3)->order('look desc')->limit(0,20)->select();
            $a=0;
            foreach ($record_list as $v)
            {
                $record_list[$a]['sharelink'] = $share_server.'/boboshare?type=3&id='.$v['room_id'];
                $a++;
            }
        }
        if( $data['bigpic'] === \BBExtend\common\BBConfig::get_server_url()){
            $data['bigpic'] = '';
        }

        if($type == 3){
            $comment= Db::table('bb_record_comments')->where(['activity_id'=>$activity_id,'audit'=>1,'is_remove'=>0])->order('time desc')->limit(0,20)->select();
            $data['comments_count']= Db::table('bb_record_comments')->where(['activity_id'=>$activity_id,'audit'=>1,'is_remove'=>0])->count();
            $a=0;
            foreach ($comment as $v)
            {
                $comment[$a]['pic'] =  User::get_userpic($v['uid']);
                $comment[$a]['sex'] = User::get_usersex($v['uid']);
                $comment[$a]['age'] = User::get_userage($v['uid']);
                $comment[$a]['nickname'] = User::get_nickname($v['uid']);
                $comment[$a]['like'] = Db::table('bb_record_comments_like')->where(['comments_id'=>$comment[$a]['id'],'type'=>1])->count();
                $comment[$a]['reply_count'] = Db::table('bb_record_comments_reply')->where(['comments_id'=>$comment[$a]['id'],'is_remove'=>0])->count();
                if(Db::table('bb_record_comments_like')->where(['comments_id'=>$comment[$a]['id'],'ip'=>$ip,'type'=>1])->find()){
                    $comment[$a]['like_state'] =  1;
                }else{
                    $comment[$a]['like_state'] =  0;
                }
                $a++;
            }
        }else{
            $comment= Db::table('bb_rewind_comments')->where(['activity_id'=>$activity_id,'audit'=>1,'is_remove'=>0])->order('time desc')->limit(0,20)->select();
            $data['comments_count']= Db::table('bb_rewind_comments')->where(['activity_id'=>$activity_id,'audit'=>1,'is_remove'=>0])->count();
            $a=0;
            foreach ($comment as $v)
            {
                $comment[$a]['pic'] =  User::get_userpic($v['uid']);
                $comment[$a]['sex'] = User::get_usersex($v['uid']);
                $comment[$a]['age'] = User::get_userage($v['uid']);
                $comment[$a]['nickname'] = User::get_nickname($v['uid']);
                $comment[$a]['like'] = Db::table('bb_rewind_comments_like')->where(['comments_id'=>$comment[$a]['id'],'type'=>1])->count();
                $comment[$a]['reply_count'] = Db::table('bb_rewind_comments_reply')->where(['comments_id'=>$comment[$a]['id'],'is_remove'=>0])->count();
                if(Db::table('bb_rewind_comments_like')->where(['comments_id'=>$comment[$a]['id'],'ip'=>$ip,'type'=>1])->find()){
                    $comment[$a]['like_state'] =  1;
                }else{
                    $comment[$a]['like_state'] =  0;
                }
                $a++;
            }
        }

        $all_record_list= Db::table('bb_record')->where(['audit'=>1,'is_remove'=>0])->order('look desc')->limit(0,20)->select();
        $a=0;
        foreach ($all_record_list as $v)
        {
            $all_record_list[$a]['sharelink'] = $share_server.'/boboshare?type=3&id='.$v['room_id'];
            $a++;
        }
        $data['nickname']=str_replace("\n","",$data['nickname']);
        $data['nickname']=str_replace("\r","",$data['nickname']);
        $data['nickname']=str_replace("\r\n","",$data['nickname']);
        $data['nickname']=htmlspecialchars($data['nickname']);

        $data['title']=str_replace("\n","",$data['title']);
        $data['title']=str_replace("\r","",$data['title']);
        $data['title']=str_replace("\r\n","",$data['title']);
        $data['title']=htmlspecialchars($data['title']);

        $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $wx_info = $this->wx_sha1($url);
        $this->assign('type',$type);
        $this->assign('share_url',$url);
        $this->assign('signature',$wx_info['wxSha1']);
        $this->assign('timestamp',$wx_info['timestamp']);
        $this->assign('id',$id);
        $this->assign('uid',$uid);
        $this->assign('data',$data);
        $this->assign('gold',$gold);
        $this->assign('comment',$comment);
        $this->assign('my_record_list',$record_list);
        $this->assign('all_record_list',$all_record_list);
        if($type==1){
            //只显示视频
            $only_video = input('?param.only_video')?input('param.only_video'):0;
            if($only_video == 1)return $this->fetch('index_push_only_video');

            return $this->fetch('index_push');
        }else{
            //$this->assign('dotime',date('YmdHis',time()));
            return $this->fetch('index');
        }

    }

    //活动分享
    public function activity(){
        $id = input('?param.activityid')?input('param.activityid'):0;
        if($id==0)$id = input('?param.id')?input('param.id'):0;
        if($id==0)$id = input('?param.amp;activityid')?input('param.amp;activityid'):0;
        if(input('?param.amp;id'))$id = input('param.amp;id');
        $sort = input('?param.sort')?input('param.sort'):3;
        $share_server = \BBExtend\common\BBConfig::get_share_server_url();
        if($sort == 2){
            $activity = Db::table('bb_task_activity')->where('id',$id)->find();
            if($activity['room_id']){
                $videodata = Db::table('bb_record b')->join('bb_users a','b.uid = a.uid')->where('b.room_id',$activity['room_id'])->find();
                if($videodata){
                    $videodata['age'] = date('Y') - substr($videodata['birthday'],0,4);
                    $videodata['labelname'] = Db::table('bb_label')->where('id',$videodata['label'])->find()['name'];
                    $this->assign('videodata',$videodata);
                }else{
                    $activity['room_id'] = 0;
                }
            }

            $bigpic_list= json_decode($activity['bigpic_list'],true);

            $activity['detail_link'] =  '/task/taskactivityapi/detail/id/'.$activity['id'];
            if($activity['type'] == 3){
                $type = 0;
                if(empty($activity['pk_statistics_json'])){
                    $red_temp = Db::table('bb_record')->where(['type'=>2,'activity_id'=>$id,'is_remove'=>0,'audit'=>1,'usersort'=>11])->field('count(*) as red_count,sum(`like`) as red_like')->find();
                    $blue_temp = Db::table('bb_record')->where(['type'=>2,'activity_id'=>$id,'is_remove'=>0,'audit'=>1,'usersort'=>12])->field('count(*) as blue_count,sum(`like`) as blue_like')->find();
                    $activity['red_count'] = $red_temp['red_count'];
                    $activity['red_like'] = $red_temp['red_like'];
                    $activity['red_score'] =$red_temp['red_count']* 100 + $red_temp['red_like'] * 5;

                    $activity['blue_count'] = $blue_temp['blue_count'];
                    $activity['blue_like'] = $blue_temp['blue_like'];
                    $activity['blue_score'] = $blue_temp['blue_count']* 100 + $blue_temp['blue_like'] * 5;
                }else{
                    $temp = json_decode($activity['pk_statistics_json'] , 1);
                    $activity['red_count'] = $temp['red_count'];
                    $activity['red_like'] = $temp['red_like'];
                    $activity['red_score'] =$temp['red_count']* 100 + $temp['red_like'] * 5;

                    $activity['blue_count'] = $temp['blue_count'];
                    $activity['blue_like'] = $temp['blue_like'];
                    $activity['blue_score'] = $temp['blue_count']* 100 + $temp['blue_like'] * 5;
                }

            }else{
                $type = $activity['type'];
            }

            $this->assign('activity',$activity);
            $this->assign('share_server',$share_server);
            $this->assign('bigpic_list',$bigpic_list);
            $this->assign('type',$type);
            return $this->fetch('task_activity');
        }
        if($sort == 3){
            $activity = Db::table('bb_activity')->where('id',$id)->find();
            $activity['count'] = Db::table('bb_activity_comments')->where(['activity_id'=>$id])->count();
            $push = Db::table('bb_push')->where(['event'=>'publish','sort'=>$sort,'activity_id'=>$id])->order(['like'=>'desc'])->limit(10)->select();
            $a=0;
            foreach ($push as $v)
            {
                $user[$a] = Db::table('bb_users')->where('uid',$v['uid'])->find();
                $push[$a]['label'] = $share_server.Db::table('bb_label_activity')->where('id',$v['label'])->find()['image'];
                $push[$a]['age'] = date('Y') - substr($user[$a]['birthday'],0,4);
                $push[$a]['nickname'] = $user[$a]['nickname'];
                $push[$a]['address'] = $user[$a]['address'];
                $push[$a]['sex'] = $user[$a]['sex'];
                $push[$a]['bigpic'] = $share_server.$push[$a]['bigpic'];
                $push[$a]['sharelink'] = '/boboshare?type=1&id='.$v['uid'];
                $a++;
            }
            $b=0;
            $bigpic_list= json_decode($activity['bigpic_list'],true);
            foreach ($bigpic_list as $vo)
            {
                $bigpic_list[$b]['picpath'] = $share_server.$vo['picpath'];
                $b++;
            }
            $this->assign('activity',$activity);
            $this->assign('bigpic_list',$bigpic_list);
            $this->assign('type',$activity['type']);
            $this->assign('push',$push);
            return $this->fetch('activity');
        }
    }

    public function like()
    {
        $id = input('?param.id')?input('param.id'):0;
        if(input('?param.amp;id'))$id = input('param.amp;id');
        $type = input('?param.type')?input('param.type'):0;
        $ip = $_SERVER['REMOTE_ADDR'];

        Cookie::init(['prefix'=>'BOBO_','expire'=>0,'path'=>'/']);
        if(!Cookie::has('share_page_uid','BOBO_')){
            $user_cookie = md5($ip.time().rand(1000,9999));
            Cookie('share_page_uid',$user_cookie,0);
        }else{
            $user_cookie = Cookie('share_page_uid');
        }
        $datestr = date('Ymd');
        switch ($type)
        {
            case SHARE_PUSH_TYPE:
                $movieDB = Db::table('bb_push')->where(['room_id'=>$id.'push'])->find();
                if ($movieDB)
                {
                    $likeDB = Db::table('bb_push_like')->where(['ip'=>$ip, 'room_id'=>$id.'push'])->find();
                    if (!$likeDB)
                    {
                        $Data = array();
                        $Data['uid'] = 16666;
                        $Data['ip'] = $ip;
                        $Data['room_id'] = $id.'push';
                        $Data['time'] = time();
                        Db::table('bb_push_like')->insert($Data);
                        $num = $movieDB['like'] + 1;
                        Db::table('bb_push')->where(['room_id'=>$id.'push'])->update(['like'=>$num]);
                        return ['message'=>'点赞成功','code'=>1,'num'=>$num];
                    }
                }
                return ['code'=>0];
                break;
            case SHARE_PUSH_REW_TYPE:
                $movieDB = Db::table('bb_rewind')->where(['room_id'=>$id])->find();
                if ($movieDB)
                {
                    $likeDB = Db::table('bb_rewind_like')->where(['ip'=>$ip, 'room_id'=>$id])->find();
                    if (!$likeDB)
                    {
                        $Data = array();
                        $Data['uid'] = 16666;
                        $Data['ip'] = $ip;
                        $Data['room_id'] = $id;
                        $Data['time'] = time();

                        Db::table('bb_rewind_like')->insert($Data);
                        $num = $movieDB['like'] + 1;
                        Db::table('bb_rewind')->where(['room_id'=>$id])->update(['like'=>$num]);
                        return ['message'=>'点赞成功','code'=>1,'num'=>$num];
                    }
                }
                return ['code'=>0];
                break;
            case SHARE_RECORD_TYPE:
                $movieDB = Db::table('bb_record')->where(['room_id'=>$id])->find();
                if ($movieDB)
                {
                    $likeDB = Db::table('bb_record_like')->where(['ip'=>$ip, 'room_id'=>$id, 'datestr'=>$datestr])->find();

                    if (!$likeDB)
                    {
                        $Data = array();
                        $Data['uid'] = 16666;
                        $Data['ip'] = $ip;
                        $Data['room_id'] = $id;
                        $Data['time'] = time();
                        $Data['datestr'] = $datestr;
                        $Data['count'] = 1;
                        Db::table('bb_record_like')->insert($Data);
                        $like_num = $movieDB['like'] + 1;
                        $real_like_num = $movieDB['real_like'] + 1;
                        Db::table('bb_record')->where(['room_id'=>$id])->update(['like'=>$like_num,'real_like'=>$real_like_num]);
                        BBRedis::getInstance('record')->hSet($id.'record','record_zan_cookie_'.$user_cookie.$datestr.'_'.$id,'1');
                        return ['message'=>'点赞成功','code'=>1,'num'=>$like_num];

                    }elseif($likeDB['count'] < 5 && BBRedis::getInstance('record')->hGet($id.'record','record_zan_cookie_'.$user_cookie.$datestr.'_'.$id) === false) {
                        $Data = array();
                        $Data['count'] = $likeDB['count'] + 1;
                        Db::table('bb_record_like')->where(['ip'=>$ip, 'room_id'=>$id, 'datestr'=>$datestr])->update($Data);
                        $like_num = $movieDB['like'] + 1;
                        $real_like_num = $movieDB['real_like'] + 1;
                        Db::table('bb_record')->where(['room_id'=>$id])->update(['like'=>$like_num,'real_like'=>$real_like_num]);
                        BBRedis::getInstance('record')->hSet($id.'record','record_zan_cookie_'.$user_cookie.$datestr.'_'.$id,'1');
                        return ['message'=>'点赞成功','code'=>1,'num'=>$like_num];
                    }

                }

                return ['code'=>0];
                break;
        }

    }

    public function comments_like()
    {
        $id = input('?param.id')?input('param.id'):0;
        if(input('?param.amp;id'))$id = input('param.amp;id');
        $type = input('?param.type')?input('param.type'):0;
        $c_type = input('?param.c_type')?input('param.c_type'):0;//评论还是回复
        $ip = $_SERVER['REMOTE_ADDR'];

        switch ($type)
        {
            case SHARE_PUSH_REW_TYPE:
                $LikeDB = Db::table('bb_rewind_comments_like')->where(['ip'=>$ip, 'comments_id'=>$id])->find();
                if (!$LikeDB)
                {
                    $Data = array();
                    $Data['uid'] = 16666;
                    $Data['ip'] = $ip;
                    $Data['comments_id'] = $id;
                    $Data['type'] = $c_type;
                    Db::table('bb_rewind_comments_like')->insert($Data);
                    return ['message'=>'点赞成功','code'=>1];
                }
                return ['code'=>0];
                break;
            case SHARE_RECORD_TYPE:
                $LikeDB = Db::table('bb_record_comments_like')->where(['ip'=>$ip, 'comments_id'=>$id])->find();
                if (!$LikeDB)
                {
                    $Data = array();
                    $Data['uid'] = 16666;
                    $Data['ip'] = $ip;
                    $Data['comments_id'] = $id;
                    $Data['type'] = $c_type;
                    Db::table('bb_record_comments_like')->insert($Data);
                    return ['message'=>'点赞成功','code'=>1];
                }
                return ['code'=>0];
                break;
        }

    }

    public function comments(){
        $record_id = input('?post.record_id')?input('post.record_id'):0;
        if(input('?post.amp;record_id'))$record_id = input('post.amp;record_id');
        $type = input('?post.type')?input('post.type'):0;
        $content = input('?post.content')?input('post.content'):0;

        if($content){
            switch ($type)
            {
                case SHARE_PUSH_REW_TYPE:

                    $Data['content'] = $content;
                    $Data['activity_id'] = $record_id;
                    $Data['time'] = time();
                    $Data['uid'] = 16666;
                    $Data['reply_count'] = 0;
                    $Data['audit'] = 0;
                    $Data['is_remove'] = 0;
                    $Data['score'] = 0;

                    $res = Db::table('bb_rewind_comments')->insert($Data);
                    if($res){
                        return ['message'=>'评论成功,请等待管理员审核!','code'=>1];
                    }
                    break;
                case SHARE_RECORD_TYPE:
                    $Data['content'] = $content;
                    $Data['activity_id'] = $record_id;
                    $Data['time'] = time();
                    $Data['uid'] = 16666;
                    $Data['reply_count'] = 0;
                    $Data['audit'] = 0;
                    $Data['is_remove'] = 0;
                    $Data['score'] = 0;

                    $res = Db::table('bb_record_comments')->insert($Data);
                    if($res){
                        return ['message'=>'评论成功,请等待管理员审核!','code'=>1];
                    }
                    break;
            }
        }

    }

    public function reward(){
        $uid = input('?param.uid')?input('param.uid'):1;
        $room_id = input('?param.room_id')?input('param.room_id'):1;
        $type = input('?param.type')?input('param.type'):3;
        $info = Db::table('bb_users')->where(['uid'=>$uid])->find();
        $info['age'] = User::get_userage($uid);
        $label = Db::table('bb_speciality')->select();
        $speciality = array();
        foreach (explode(',',$info['specialty']) as $vo){
            $speciality[] = $label[$vo - 1]['image'];
        }

        $list = Db::table('bb_present')->where('money_fen > 0')->select();
        $this->assign('info',$info);
        $this->assign('speciality',$speciality);
        $this->assign('list',$list);
        $this->assign('uid',$uid);
        $this->assign('room_id',$room_id);
        $this->assign('type',$type);
        $this->assign('server_url',\BBExtend\common\BBConfig::get_server_url());
        return $this->fetch();
    }

   
    
    
    public function webmovies(){
        $group_id = input('?param.day')?input('param.day'):1;
        if(input('?param.amp;day'))$group_id = input('param.amp;day');
        $list = Db::table('ds_webmovies a')->join('bb_record b','a.room_id = b.room_id')->where(['group_id'=>$group_id])->order('count asc')->select();
        $this->assign('list',$list);
        $this->assign('server_url',\BBExtend\common\BBConfig::get_server_url());
        return $this->fetch();
    }


    //微信自定义二次分享部分
    public function wx_get_jsapi_ticket(){
        $ticket = "";
        do{
            $ticket =  Cache::get('wx_ticket');
            if (!empty($ticket)) {
                break;
            }
            $token =  \BBExtend\Sys::get_wx_gongzhong_token();
            if (empty($token)) {
                break;
            }
            $url2 = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi",
                $token);
            $res = file_get_contents($url2);
            $res = json_decode($res, true);
            if(isset($res['ticket'])){
                $ticket = $res['ticket'];
            }else{
                $ticket = '';
            }
            // 注意：这里需要将获取到的ticket缓存起来（或写到数据库中）
            // ticket和token一样，不能频繁的访问接口来获取，在每次获取后，我们把它保存起来。
            Cache::set('wx_ticket',$ticket,3600);
        }while(0);
        return $ticket;
    }

    public function wx_sha1($url = ''){
        $timestamp = time();
        $wxnonceStr = "1234567890123";
        $wxticket = $this->wx_get_jsapi_ticket();
        $wxOri = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wxticket, $wxnonceStr, $timestamp,$url);
        $wxSha1 = sha1($wxOri);
        return ['wxSha1'=>$wxSha1,'timestamp'=>$timestamp,'wxticket'=>$wxticket];
    }
    public function _empty()
    {
        return null;
    }
}
