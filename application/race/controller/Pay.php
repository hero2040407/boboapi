<?php
namespace app\race\controller;

use BBExtend\Sys;
use think\Controller;
use BBExtend\pay\wxpay\HelpWeb;
use think\Session;
use think\Db;
use think\Redis;

require_once realpath( EXTEND_PATH).'/WxpayAPI/lib/WxPay.Config.Web.php';
require_once realpath( EXTEND_PATH). "/WxpayAPI/example/WxPay.JsApiPay.php";

class Pay  extends Controller
{

   /**
    * 谢烨
    * 这是支付前的一个demo页。
    */
   public function html()
   {
       echo $this->fetch();
   }


   public function order_for_like($ds_id ,$self_uid, $target_uid,$openid,$type)
   {

       //Sys::display_all_error();
       //①、获取用户openid
       $tools = new \JsApiPay();
       //  $openId = $tools->GetOpenid();

       $help = new HelpWeb();
       $order = $help->tongyi_xiadan_for_race_like($ds_id,  $self_uid, $target_uid,   $openid);
       if ($order['code']==1) {
           $jsApiParameters = $tools->GetJsApiParameters($order['data']);

           $temp = json_decode($jsApiParameters  ,1  );

           return  ['code' =>1,'data' =>  $temp ] ;
       }else {
           return  ['code' =>0,'message' =>  $order['message'] ] ;
       }
   }

    /**
     *新的投票接口地址
     */
    public function vote_wxpay_like($ds_id,$self_uid,$target_uid,$openid,$type)
    {
        //读取json文件
        $data = file_get_contents(APP_PATH.'/json/spec.json');
        //json转换
        $data = json_decode($data,true);
        //类型
        $spec = $data['price_vnum'];
        //删除
        unset($data);
        //判断是否存在
        if(!isset($spec[$type])) return  ['code' =>0,'message' => '非法访问' ] ;
        //重新赋值
        $spec = $spec[$type];
        $spec['ds_id'] = $ds_id;
        $spec['self_uid'] = $self_uid;
        $spec['target_uid'] = $target_uid;
        $spec['openid'] = $openid;
        //生成签名
        $help = new HelpWeb();
        $order = $help->uniform_gener_wxrace_like($spec);
        //生成微信支付签名
        if ($order['code']==1) {
            //①、获取用户openid
            $tools = new \JsApiPay();
            $jsApiParameters = $tools->GetJsApiParameters($order['data']);
            $jsApiParameters = json_decode($jsApiParameters,true);
            return  ['code' =>1,'data' => $jsApiParameters ] ;
        }else {
            return  ['code' =>0,'message' =>  $order['message'] ] ;
        }
    }
   public function create_html_v5($uid, $ds_id,$openid)
   {

       //Sys::display_all_error();
       //①、获取用户openid
       $tools = new \JsApiPay();
       //  $openId = $tools->GetOpenid();

       $help = new HelpWeb();
       $order = $help->tongyi_xiadan_v5($ds_id, $uid, '', $openid);
       if ($order['code']==1) {
           $jsApiParameters = $tools->GetJsApiParameters($order['data']);

           $temp = json_decode($jsApiParameters  ,1  );

           return  ['code' =>1,'data' =>  $temp ] ;
       }else {
           return  ['code' =>0,'message' =>  $order['message'] ] ;
       }
   }



  public function create_html($uid, $ds_id,$openid,$v=1)
   {

       if ($v>=5) {
           return $this->create_html_v5($uid, $ds_id, $openid);
       }
       return  ['code' =>0,'message' =>  '方法不存在'] ;
      //Sys::display_all_error();
       //①、获取用户openid
       $tools = new \JsApiPay();
     //  $openId = $tools->GetOpenid();

       $help = new HelpWeb();
       $order = $help->tongyi_xiadan_demo($ds_id, $uid, '', $openid);
       if ($order['code']==1) {
           $jsApiParameters = $tools->GetJsApiParameters($order['data']);

           $temp = json_decode($jsApiParameters  ,1  );

           return  ['code' =>1,'data' =>  $temp ] ;
       }else {
           return  ['code' =>0,'message' =>  $order['message'] ] ;
       }
   }
    /**
     * 获取
     */
   public function spec()
   {
       //获取文件配置
       $data = file_get_contents(APP_PATH.'/json/spec.json');
       //json转换
       $data = json_decode($data,true);
       //存在redis里面
//       $redis = Sys::get_container_redis();
//       $redis->set('_boboapi_spec_price_vnum_',json_encode($data['price_vnum']));
       //返回
       return ['code' =>1,'data' =>  $data['spec']] ;
   }
   /**
    * 这是支付发起请求的demo
    * 谢烨。
    */
   public function jsapi()
   {
       //①、获取用户openid
       $tools = new \JsApiPay();
       $openId = $tools->GetOpenid();

       //②、统一下单
       $uid = Session::has('uid')?Session::get('uid'):'0';
       $ds_id = Session::has('ds_id')?Session::get('ds_id'):'0';
       $phone = Session::has('phone')?Session::get('phone'):'0';

       if($ds_id == '0'){
           return ['code'=>0,'message'=>'信息不全,请检查!'];
       }else{
           $money = Db::table('ds_race')->where(['id'=>$ds_id])->find()['money'];
       }

       $help = new HelpWeb();
       $order = $help->tongyi_xiadan($ds_id, $uid, $phone, $openId);
       if ($order['code']==1) {
           $jsApiParameters = $tools->GetJsApiParameters($order['data']);
           $this->assign('jsApiParameters', $jsApiParameters);
           echo $this->fetch('',['uid'=>$uid,'ds_id'=>$ds_id , 'money'=>$money]); // 展示正常的页面
       }   else {
           abort(404,'本页禁止刷新!');
       }
   }

//    分享页面打赏支付
    public function wxf()
    {
        //①、获取用户openid
        $tools = new \JsApiPay();
        $openId = $tools->GetOpenid();

        //②、统一下单
        $room_id = input('?param.room_id') ? input('param.room_id') : '';
        $money = input('?param.money') ? input('param.money') : '0';

        if ($room_id == '0' || $money == '0') {
            return ['code' => 0, 'message' => '信息不全,请检查!'];
        }

        $help = new HelpWeb();
        $order = $help->tongyi_xiadan_for_dashang($room_id, $money*100, $openId);
        if ($order['code'] == 1) {
            $jsApiParameters = $tools->GetJsApiParameters($order['data']);
            $this->assign('jsApiParameters', $jsApiParameters);
            echo $this->fetch('', ['room_id' => $room_id, 'money' => $money]); // 展示正常的页面
            exit;
        } else {
            abort(404, '本页禁止刷新!');
        }
    }



}
