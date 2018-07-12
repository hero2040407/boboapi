<?php
namespace app\race\controller;

use BBExtend\Sys;
use think\Controller;
use BBExtend\pay\wxpay\HelpWeb;
use think\Session;
use think\Db;

require_once realpath( EXTEND_PATH). "/WxpayAPIWeb/example/WxPay.JsApiPay.php";

class Demo  extends Controller
{
   
   /**
    * 谢烨
    * 这是支付前的一个demo页。
    */
   public function html()
   {
       echo $this->fetch();
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
       $uid=1;
       $phone='1';
       $ds_id=1;
       $help = new HelpWeb();
       $order = $help->tongyi_xiadan_demo($ds_id, $uid, $phone, $openId);
       if ($order['code']==1) {
           $jsApiParameters = $tools->GetJsApiParameters($order['data']);
           $this->assign('jsApiParameters', $jsApiParameters);
           echo $this->fetch(); // 展示正常的页面
       }   else {
           echo $order['message']; //显示错误。
       }
   }
   
   
   
}
