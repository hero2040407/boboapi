<?php
/**
 * Created by PhpStorm.
 * User: tRee
 * Date: 2016/7/7
 * Time: 19:37
 */
namespace app\boboshare\controller;
use think\Controller;
use think\Db;
use think\Cache;
use BBExtend\service\Sms;
use app\user\controller\Invite as shanghu;
use app\user\controller\Invite as user_invite;


class Invite extends Controller
{
//    邀请分享
    public function index()
    {
        $uid = input('?param.uid') ? input('param.uid') : 0;
        if (input('?param.amp;uid')) $uid = input('param.amp;uid');

        $share_server = \BBExtend\common\BBConfig::get_share_server_url();
        $user = Db::table('bb_users')->where(['uid'=>$uid])->find();
        $list = Db::table('bb_config_str')->where(['type'=>2])->select();
        $arr =array();
        foreach ($list as $vo){
            $arr[$vo['config']] = $vo['val'];
        }
        $this->assign('share_server',$share_server);
        $this->assign('user',$user);
        $this->assign('uid',$uid);
        $this->assign('info',$arr);
        echo $this->fetch('index');
        exit;
    }
//    分享后页面
    public function share()
    {
        $uid = input('?param.uid') ? input('param.uid') : 0;
        if (input('?param.amp;uid')) $uid = input('param.amp;uid');

        $share_server = \BBExtend\common\BBConfig::get_share_server_url();
        $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $wx_info = $this->wx_sha1($url);
        $this->assign('share_url',$url);
        $this->assign('signature',$wx_info['wxSha1']);
        $this->assign('timestamp',$wx_info['timestamp']);
        $this->assign('uid',$uid);
        return $this->fetch();
    }
//发送短信
    public function invite_send_code(){
        $phone = input('?param.phone')?(string)input('param.phone'):'0';
        if(Db::table('bb_users_invite_register')->where(['phone'=>$phone])->find())return ['code'=>0,'message'=>'该手机号已经被邀请过了!' ];
        $sms = new Sms($phone);
        $result = $sms->send_verification_code();
        return $result;
    }
    //检测短信
    public function invite_check_code(){
        $uid = input('?param.uid')?input('param.uid'):'0';//此为商户ID
        $code = input('?param.code')?(string)input('param.code'):'0';
        $phone = input('?param.phone')?(string)input('param.phone'):'0';

        $sms = new Sms($phone);
        $sms->set_must_success_phone('15160005310');
        $sms->set_must_success_phone('18658866486');

        $result = $sms->check($code);

        if($result['code'] == 1){
            $user_invite = new user_invite();
            $res = $user_invite->register($uid,$phone);
            return $res;
        }else{
            return $result;
        }
    }

//    商户邀请页面
    public function store(){
        $store_id = input('?param.store_id') ? input('param.store_id') : 0;
        if (input('?param.amp;id')) $uid = input('param.amp;id');

        $share_server = \BBExtend\common\BBConfig::get_share_server_url();
        $store = Db::table('bb_shanghu')->where(['id'=>$store_id])->find();
//        $shop = Db::table('lt_roulette')->alias('a')->join('bb_shop_goods b','b.id = a.bonus_id')->where('a.lt_type = 5 and a.type = 2')->select();
//
//        $piclist=array();
//        $i=0;
//        foreach ($shop as $vo){
//            if($vo['pic_list'] !=''){
//                $piclist[$i]['id'] = $vo['id'];
//                $piclist[$i]['picpath'] = json_decode($vo['pic_list'],true)[0]['picpath'];
//                $piclist[$i]['title'] = $vo['title'];
//                $i++;
//            }
//        }
        $this->assign('share_server',$share_server);
        //$this->assign('piclist',$piclist);
        $this->assign('store',$store);
        $this->assign('store_id',$store_id);
        echo $this->fetch('store');
        exit;
    }

    //商户页面发送短信
    public function sendcode(){
        $phone = input('?param.phone')?(string)input('param.phone'):'0';
        if(Db::table('bb_users_shanghu_invite_register')->where(['phone'=>$phone])->find())return ['code'=>0,'message'=>'该手机号已经被邀请过了!' ];
        $sms = new Sms($phone);
        $result = $sms->send_verification_code();
        return $result;
    }
    //商户页面检测短信
    public function checkcode(){
        $store_id = input('?param.store_id')?input('param.store_id'):'0';//此为商户ID
        $code = input('?param.code')?(string)input('param.code'):'0';
        $phone = input('?param.phone')?(string)input('param.phone'):'0';

        $sms = new Sms($phone);
        $sms->set_must_success_phone('15160005310');
        $sms->set_must_success_phone('18658866486');

        $result = $sms->check($code);

        if($result['code'] == 1){
            $sh = new shanghu();
            $res = $sh->register_shanghu($store_id,$phone);
            return $res;
        }else{
            return $result;
        }
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
            $ticket = $res['ticket'];
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
