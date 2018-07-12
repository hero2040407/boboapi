<?php
namespace app\game\controller;

use think\Db;
use think\Controller;
//use BBExtend\user\lottery\Play;
use BBExtend\user\lottery\PlaySign;
use BBExtend\user\lottery\PlayMerchant ;
use BBExtend\BBUser;
use app\user\controller\Signin;

//*
//任务抽奖改成7天签到抽奖*/
class Lottery extends Controller
{
    public function index()
    {
        $gameid = input('?param.gameid')?input('param.gameid'):'0';
        $uid = input('?param.uid')?input('param.uid'):'0';
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';

        $signin = new Signin();
        $can_lottery = $signin->query($uid,$userlogin_token)['data']['can_lottery'];

        //$my_task = $this->get_task($uid);

        $all_prize_count = $this->get_all_prize_count($uid);

        $my_bo = $this->get_my_bo($uid);

        $all_prize = $this->get_all_prize();

        $roulette_list = Db::table('lt_roulette')->where('type=1')->select();
        echo $this->fetch('',['uid'=>$uid,'all_prize'=>$all_prize,'roulette_list'=>$roulette_list,'userlogin_token'=>$userlogin_token,
            'my_bo'=>$my_bo,'can_lottery'=>$can_lottery,
            //'my_task'=>$my_task,
            'all_prize_count'=>$all_prize_count]);
        exit;
   }
   
   public function get_lottery(){
       $uid = input('?param.uid')?input('param.uid'):'0';
       $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
       if (!BBUser::validation_token($uid,$userlogin_token))
       {
           return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
       }

       $obj = PlaySign::getinstance($uid);
       $ltinfo = $obj->start();

       if($ltinfo['code'] == 1 ){
           $info = Db::table('lt_roulette')->where('type=1')->find($ltinfo['data']);
           if($ltinfo['data']>1){
               $info['angle'] = mt_rand(30,60)+45*($ltinfo['data']-2);
           }else{
               $info['angle'] = mt_rand(1,18)+(rand(0,1)*345);
           }
       }else{
           $info['angle'] = -1801 ;
           $info['lt_type'] = 0;
           $info['title'] = $ltinfo['message'];
       }
       echo json_encode($info);
       exit;
   }

//    public function get_valid_count($uid) {
//        $result = Play::getinstance($uid)->get_valid_count();
//        return $result;
//    }
//    public function get_task($uid) {
//        $result = Play::getinstance($uid)->get_task();
//
//        $arr = ['今日分享达到5次',
//            '今日直播时长累积满30分钟',
//            '今日上传小视频认证成功',
//            '今日在线时长累积满60分钟',
//            '今日观看直播满30分钟',
//            '今日被其他用户点赞10次',
//            '今日点赞其他用户20次',
//            '今日成功发布评论10条',
//            '今日关注20位不同用户',
//            '今日被10位不同用户关注',
//        ];
//        foreach ($result as &$v){
//            $v['title'] = $arr[$v['type']-2];
//            $v['state'] = $v['has_complete'] ==0?'未完成':'已完成';
//        }
//
//        return $result;
//    }

    public function get_all_prize_count($uid) {
        $result = Db::table('lt_draw_log')->where('lt_type in (5,1,2) and type =1')->count();
        return $result;
    }

    public function get_all_prize() {
        $result = Db::table('lt_draw_log a')->join('bb_users b','a.uid = b.uid')->where('lt_type in (5,1,2) and type =1 ')->order('id desc')->limit(1,20)->select();
//        $str='';
//        $a=0;
//        foreach ($result as $k=>$v){
//            if($k%2==0){
//                $nickname1 = substr($result[$k]['nickname'],0,9).'**';
//                $nickname2 = substr($result[$k+1]['nickname'],0,9).'**';
//                if($a%2==0){
//                    $str.="<li class='list_white'>
//                            <div class=\"prize_list\"><span class=\"prize_nickname\">{$nickname1}</span> 获得 {$result[$k]['bonus_name']}</div>
//                            <div class=\"prize_list\"><span class=\"prize_nickname\">{$nickname2}</span> 获得 {$result[$k+1]['bonus_name']}</div>
//                            </li>";
//                }else{
//                    $str.="<li class='list_red'>
//                            <div class=\"prize_list\"><span class=\"prize_nickname\">{$nickname1}</span> 获得 {$result[$k]['bonus_name']}</div>
//                            <div class=\"prize_list\"><span class=\"prize_nickname\">{$nickname2}</span> 获得 {$result[$k+1]['bonus_name']}</div>
//                            </li>";
//                }
//                $a++;
//            }
//        }

        return $result;
    }

    public function get_my_bo($uid) {
        $result = Db::table('bb_currency')->where(['uid'=>$uid])->find()['gold'];
        return $result;
    }
   
   
   
   
   //商户转盘

    public function store_index()
    {
        $uid = input('?param.uid')?input('param.uid'):'0';
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';

        $signin = new Signin();
        $can_lottery = $signin->query($uid,$userlogin_token)['data']['can_lottery'];

        $all_prize = $this->get_all_prize();

        $roulette_list = Db::table('lt_roulette')->where('type=2')->select();
        echo $this->fetch('',['uid'=>$uid,'all_prize'=>$all_prize,'roulette_list'=>$roulette_list,'userlogin_token'=>$userlogin_token,'can_lottery'=>$can_lottery,]);
        exit;
    }

    public function get_store_lottery(){
        $uid = input('?param.uid')?input('param.uid'):'0';
        $userlogin_token = input('?param.userlogin_token')?input('param.userlogin_token'):'';
        if (!BBUser::validation_token($uid,$userlogin_token))
        {
            file_put_contents('./ww2.txt','12212');
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        $obj = PlayMerchant::getinstance($uid);
        $ltinfo = $obj->start();
        //$ltinfo = ['code'=>1,'data'=>16];
        echo json_encode($ltinfo);
        exit;
    }

}

