<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use think\Controller;
use BBExtend\pay\wxpay\HelpWeb;
use think\Session;
use think\Db;
 require_once realpath( EXTEND_PATH).'/WxpayAPI/lib/WxPay.Config.Web.php';
require_once realpath( EXTEND_PATH). "/WxpayAPI/example/WxPay.JsApiPay.php";

class Pay  extends Controller
{
   
   /**
    * 谢烨
    * 这是支付前的一个demo页。
    */
   public function html()
   {Sys::debugxieye("wx:html");
       echo $this->fetch('html',
               [
                       'aa' =>1223
               ]
               );
   }
   
   
  
   
   /**
    * 这是支付发起请求的demo
    * 谢烨。
    */
   public function jsapi()
   {
       //①、获取用户openid
    //   Sys::debugxieye("wx:000");
       $tools = new \JsApiPay();
    //   Sys::debugxieye("wx:001");
       $openId = $tools->GetOpenid();

       //②、统一下单
       $uid=1;
       $phone='1';
       $ds_id=202;
    //   Sys::debugxieye("wx:00");
       $help = new HelpWeb();
    //   Sys::debugxieye("wx:01");
       $order = $help->tongyi_xiadan_v5($ds_id, $uid, $phone, $openId);
    //   Sys::debugxieye("wx:02");
       if ($order['code']==1) {
      //     Sys::debugxieye("wx:03");
           $jsApiParameters = $tools->GetJsApiParameters($order['data']);
           $this->assign('jsApiParameters', $jsApiParameters);
           echo $this->fetch(); // 展示正常的页面
       }   else {
      //     Sys::debugxieye("wx:04");
           echo $order['message']; //显示错误。
       }
   }
   
   
   
}
