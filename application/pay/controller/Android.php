<?php
namespace app\pay\controller;

use BBExtend\Currency;
use  app\pay\model\Buy;
use app\pay\model\Users;
use think\Controller;

/**
 * 安卓调用接口，下单，购买波币
 * 
 * Created by PhpStorm.
 * User: xieye
 */
class Android extends Controller
{
    
    public function _initialize()
    {
        $request = request();
        $chekc_action =['set_order',  ];
        if ( in_array( $request->action(), $chekc_action )) {
            $help = new \BBExtend\pay\Sign();
            $result = $help->check(input('param.v'), input('param.uid'), 
                input('param.time'), input('param.sign')      );
            if (!$result) {
                echo json_encode(["code"=>0, "message"=>$help->get_info() ] ,
                        JSON_UNESCAPED_UNICODE);
                exit();
            }
        }
    }
    
    
    public function test_user_pay_success()
    {
        // uid = 10046
        // 订单号 PA824061594995362，跟订单号无关。
        $s = '{"aa":1}';
        $user = Users::get(10046);
        $user->pay_success(60);
    }
    
    /**
     * 安卓调用接口，下单，购买波币
     * http://www.test1.com/pay/android/set_order/paytype/ali/uid/10046/count/60
     * 
     * 6元  60个（目前测试时，0.01元）
30元  350个（目前测试时，0.02元）
60元   1200个（目前测试时，0.03元）
     * 
     * @param number $uid
     * @param number $count
     */
    public function set_order($uid=0, $count=0,$paytype='ali')
    {
        $uid = intval($uid);
        $count = intval($count);
        if (!in_array($count, array(60, 350, 1200))) {
            return ['message'=>'非法的数量','code'=>0];
        }
        if (\app\user\model\Exists::userhExists($uid) <= 0){
            return ['message'=>'非法的uid','code'=>0];
        }
        if (!in_array($paytype, array( 'ali', 'wx' ))) {
            return ['message'=>'支付方式只能是支付宝或微信','code'=>0];
        }
        
        $arr = get_test_userid_arr();
       // $arr[]= '11925';
        
        if ( in_array($uid, $arr )  ) {
            
            $price_arr = array(
                60 => 0.01, //应该6
                350 => 0.01, //应该30
                1200 => 0.01, //应该60
            );
        }else {
            $price_arr = array(
                60 => 6, //应该6
                350 => 30, //应该30
                1200 => 60, //应该60
            );
        }
        
       
        //既然条件都对，生成订单号，最后插入充值订单表。
        $serial = $this->get_order_serial();
        $order = new \app\pay\model\Buy();
        $order->uid = $uid;
        $order->order = $serial;
        $order->count = $count;
        $order->product_id =1;
        $order->time = time();
        $order->receipt='';
        $order->error='';
        $order->terminal_type=2;//2固定是安卓
        $order->successful = 0; //刚下单。
        $order->third_name=$paytype;
        $order->save();
        
        if ($paytype=='ali') {
            $help = new \BBExtend\pay\alipay\AlipayHelp();
            $sign_urlencode = $help->sign( [
                'out_trade_no' => $serial,
                'subject'=> "{$count}个bo币",
                "body" => "{$count}个bo币",
                "total_fee"    => $price_arr[$count] ,         //订单总价
            ] );
            
//             return ['code'=>1, 'data'=> $help->sign( [
//                 'out_trade_no' => $serial,
//                 'subject'=> "{$count}个bo币",
//                 "body" => "{$count}个bo币",
//                 "total_fee"    => $price_arr[$count] ,         //订单总价
//             ] ) ];
            
        
            //返回给客户端
            $data =[
                "out_trade_no" =>$serial, //服务器生成的订单号
                "total_fee"    => $price_arr[$count] ,         //订单总价
                "notify_url"   => ali_gateway() , // 服务端异步回调地址
                "subject"      =>"{$count}个bo币",   //商品的名称
                "partner"      =>"2088421400078132",             //开发者帐号
                "seller_id"    =>"201457175@qq.com", //商户帐号
                "body"         =>"{$count}个bo币",      //商品详细描述
                "all_request" => $sign_urlencode,
            ];
            return ["data"=>$data, "code"=>1 ];
        } // 阿里支付end
        
        if ($paytype=='wx') {
            $help = new \BBExtend\pay\wxpay\Help();
            return $help->tongyi_xiadan("充值", $serial, (int)($price_arr[$count] * 100));
        } //微信支付end
        
        return ["data"=>$data, "code"=>1 ];
        
    }
   
    //产生订单号
    //p表示pay充值，A表示安卓。
    private static function get_order_serial()
    {
        $orderSn = "PA"  .date("Ymd") . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
    
//     public function get_request(){
//         //谢烨，总结，需要订单号，商品名，详情，金额，
        
        
//         // 签约合作者身份ID
//         String orderInfo = "partner=" + "\"" + PARTNER + "\"";
        
//         // 签约卖家支付宝账号
//         orderInfo += "&seller_id=" + "\"" + SELLER + "\"";
        
//         // 商户网站唯一订单号
//         orderInfo += "&out_trade_no=" + "\"" + getOutTradeNo() + "\"";
        
//         // 商品名称
//         orderInfo += "&subject=" + "\"" + subject + "\"";
        
//         // 商品详情
//         orderInfo += "&body=" + "\"" + body + "\"";
        
//         // 商品金额
//         orderInfo += "&total_fee=" + "\"" + price + "\"";
        
//         // 服务器异步通知页面路径
//         orderInfo += "&notify_url=" + "\"" + "http://notify.msp.hk/notify.htm" + "\"";
        
//         // 服务接口名称， 固定值
//         orderInfo += "&service=\"mobile.securitypay.pay\"";
        
//         // 支付类型， 固定值
//         orderInfo += "&payment_type=\"1\"";
        
//         // 参数编码， 固定值
//         orderInfo += "&_input_charset=\"utf-8\"";
        
//         // 设置未付款交易的超时时间
//         // 默认30分钟，一旦超时，该笔交易就会自动被关闭。
//         // 取值范围：1m～15d。
//         // m-分钟，h-小时，d-天，1c-当天（无论交易何时创建，都在0点关闭）。
//         // 该参数数值不接受小数点，如1.5h，可转换为90m。
//         orderInfo += "&it_b_pay=\"30m\"";
        
//         // extern_token为经过快登授权获取到的alipay_open_id,带上此参数用户将使用授权的账户进行支付
//         // orderInfo += "&extern_token=" + "\"" + extern_token + "\"";
        
//         // 支付宝处理完请求后，当前页面跳转到商户指定页面的路径，可空
//         orderInfo += "&return_url=\"m.alipay.com\"";
        
//     }
    

}