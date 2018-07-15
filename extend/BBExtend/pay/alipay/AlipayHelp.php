<?php

/**
 * Created by PhpStorm.
 * User: 谢烨
 * 
 * 这是阿里支付的回调类。
 * 阿里支付全部进入此类处理，含 商城购物buy() 和 安卓充值pay_android()。
 * 
 * Date: 2016/8/20
 * Time: 20:30
 */
namespace BBExtend\pay\alipay;
use think\Db;
use BBExtend\pay\alipay\Logs;
 
use BBExtend\Sys;
use BBExtend\DbSelect;

use  app\pay\model\Buy;
use app\pay\model\Users;
use app\shop\model\ShopOrderPrepare;

use app\race\model\DsMoneyPrepare;
use app\race\model\DsMoneyLog;
use app\race\model\DsRegisterLog;

 require_once ( realpath( realpath( EXTEND_PATH)."/alipay/lib/alipay_notify.class.php"));
 //require_once ( realpath( realpath( APP_PATH)."/../extend/alipay/lib2/AopSdk.php"));
 require_once ( realpath( realpath( EXTEND_PATH)."/alipay/lib2/aop/AopClient.php"));
 require_once ( realpath( realpath( EXTEND_PATH)."/alipay/lib2/aop/SignData.php"));
 require_once ( realpath( realpath( EXTEND_PATH)."/alipay/lib2/aop/request/AlipayTradeQueryRequest.php"));
 
/**
 * 该类封装 阿里支付，根据其demo改编。
 * @author 谢烨
 *
 */
class AlipayHelp
{
    
    
    public function test(){
        echo "hello, 支付test";
    }
    
    /**
     * 对数组签名，并返回数组，
     * @param unknown $data2
     */
    public function sign($data2)
    {
        //global $alipay_config;
        require ( realpath( realpath( EXTEND_PATH)."/alipay/alipay.config.php"));
        //将post接收到的数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串。
        
        //无论是否有，这里添加系统参数
        $data2['partner']=$alipay_config['partner'];
        $data2['seller_id']=$alipay_config['seller_id'];
        $data2['notify_url']=$alipay_config['service'];
        $data2['service']='mobile.securitypay.pay';
        $data2['payment_type']='1';
        $data2['_input_charset']='utf-8';
        $data2['it_b_pay']='30m';
        
        $data= $this->createLinkstring($data2);
        //将待签名字符串使用私钥签名,且做urlencode. 注意：请求到支付宝只需要做一次urlencode.
        $rsa_sign=urlencode(rsaSign($data, $alipay_config['private_key']));
        
        //把签名得到的sign和签名类型sign_type拼接在待签名字符串后面。
        $data = $data.'&sign='.'"'.$rsa_sign.'"'.'&sign_type='.'"'.$alipay_config['sign_type'].'"';
        $data = urlencode($data);
        return $data;
        
    }
    
    function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=\"".$val."\"&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
    
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
    
        return $arg;
    }
    
//     public function test_ali_post()
//     {
//         $out_trade_no = 'PA823559381043750';
//         $result = $this->pay($out_trade_no);
//         echo $result;
        
//     }
    
    /**
     * 查询支付宝服务器，看订单是否付钱了
     * @param unknown $order
     */
    public function query_remote($order)
    {
       // require ( realpath( realpath( APP_PATH)."/../extend/alipay/alipay.config.php"));
        
        $pub1 = realpath( realpath( APP_PATH)."/../extend/rsa/alipay_public_key.pem");
        $pub2 = realpath( realpath( APP_PATH)."/../extend/rsa/alipay_public_key2.pem");
        $pub3 = realpath( realpath( APP_PATH)."/../extend/rsa/alipay_public_key3.pem");
        $pri = realpath( realpath( APP_PATH)."/../extend/rsa/app_private_key.pem");
        
//         echo file_get_contents($pub1);
//         echo "<br>";
//         echo file_get_contents($pub2);
//         return;
        
//         echo  $pub2;
//         return;
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2016070801594503';
        $aop->rsaPrivateKeyFilePath = $pri;
        $aop->alipayPublicKey=$pub3;
        $aop->apiVersion = '1.0';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayTradeQueryRequest ();
        $request->setBizContent("{" .
                "    \"out_trade_no\":\"{$order}\"" .
                "  }");
        $result = $aop->execute ( $request);
       $result= $result->alipay_trade_query_response ;
       $result = (array)$result;
       
       if (isset( $result['code'] ) &&
               isset( $result['trade_status'] ) &&
               $result['code'] == '10000' &&
               $result['trade_status'] == "TRADE_SUCCESS"
       ) {
                   return ["code"=>1, "data"=>["success" =>1 ]];
       }
       
       return ["code"=>1, "data"=>["success" =>0 ]];
       // $result = json_decode($result,1);
//         return $result['alipay_trade_query_response'];
    }
    
    
    public function receive_ali_post() {
        
        //注意，千万不能require_once,谢烨注
         require ( realpath( realpath( APP_PATH)."/../extend/alipay/alipay.config.php"));
        $alipayNotify = new \AlipayNotify($alipay_config);
     //   $log = Logs::get_instance();
        //先去掉get
        if ( strtolower( $_SERVER['REQUEST_METHOD']) == "get" ) {
            return "not get";
        }
        
        if($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
        {
            if($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])) {//使用支付宝公钥验签
                
                $temp = new \app\pay\model\Alitemp();
                $temp->data('url', 'alipay_post_to_me');
                $temp->data('content', json_encode($_POST) );
                $temp->data('create_time',date("Y:m:d H-i-s"));
                $temp->save();
                
                
                //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
                //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
                //商户订单号
                $out_trade_no = $_POST['out_trade_no'];
                //支付宝交易号
                $trade_no = $_POST['trade_no'];
                //交易状态
                $trade_status = $_POST['trade_status'];
                if($_POST['trade_status'] == 'TRADE_FINISHED' || 
                        $_POST['trade_status'] == 'TRADE_SUCCESS'
                        ) {
                    //XX1 = vip申请。
                    if (preg_match('#^XX1#', $out_trade_no)) {
                        return $this->pay_vip($out_trade_no,'ali', $trade_no,$_POST['total_fee']);
                    }
                    
                    if (preg_match('#^TG#', $out_trade_no)) {
//                         return $this->ds_register($out_trade_no, 'ali', $trade_no,$_POST['total_fee']
//                                 );
                        
                        return \BBExtend\model\Advise::pay_process(
                                $out_trade_no, 'ali',$trade_no,$_POST['total_fee'] * 100);
                        
                    }
                            
                    //pa代表安卓充值。        
                    if (preg_match('#^PA#', $out_trade_no)) {
                        return $this->pay_android($out_trade_no, $trade_no,$_POST['total_fee']);
                    }
                    if (preg_match('#^B#', $out_trade_no)) {
                        return $this->buy($out_trade_no, 'ali', $trade_no );
                    }
                    if (preg_match('#^DS#', $out_trade_no)) {
                        return $this->ds_register($out_trade_no, 'ali', $trade_no,$_POST['total_fee']
                                );
                    }
                    //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //如果有做过处理，不执行商户的业务程序
                    //注意：
                    //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                    //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                }
                
                return "success";        //请不要修改或删除
            }
            else //验证签名失败
            {
                return "sign fail";
            }
        }
        else //验证是否来自支付宝的通知失败
        {
            return "response fail";
        }
    }
    
    
    
    
    /**
     * vip 申请 支付成功
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function pay_vip($out_trade_no,$third_name,$third_serial, $total_fee)
    {
        //注意，这里查的是临时表
     //   $prepare = DsMoneyPrepare::get(['order_no' => $out_trade_no ]);
        $db = \BBExtend\Sys::get_container_db();
        $prepare = \BBExtend\model\BaomingOrderPrepare::where("serial",$out_trade_no )->first();
        if (!$prepare) {
            exit();
        }
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($prepare->is_success == 1) {
            return "success";
        }
        
        $prepare->is_success =1;
        $prepare->third_name ='ali';
        $prepare->third_serial =$third_serial;
        $prepare->price =$total_fee;
        $prepare->update_time = time();
        $prepare->save();
        
        $order = new  \BBExtend\model\BaomingOrder();
        $order->uid = $prepare->uid;
        $order->price = $prepare->price;
        $order->serial = $prepare->serial;
        $order->is_success = 1;
        $order->create_time = time();
        $order->third_name = $prepare->third_name;
        $order->third_serial = $prepare->third_serial;
        $order->newtype = $prepare->newtype;
        $order->save();
        
        // 还没完，还要一个日志
        $log = new \BBExtend\model\VipApplicationLog();
        $log->uid = $prepare->uid;
        $log->money = $prepare->price;
        $log->status = 1;
        $log->create_time = time();
        $log->save();        
        
        return 'success';
    }
    
    
    
    
    
    
    
    /**
     * 大赛报名支付成功
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function ds_register($out_trade_no,$third_name,$third_serial, $total_fee)
    {
        //注意，这里查的是临时表
        $prepare = DsMoneyPrepare::get(['order_no' => $out_trade_no ]);
    
        if (!$prepare) {
            exit();
        }
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($prepare->getData( 'has_success') == 1) {
            return "success";
        }
        //否则，应该把订单表中置为成功！
        $prepare->setAttr('has_success', 1) ;
        $prepare->setAttr('third_name', 'ali');
        $prepare->setAttr('third_serial', $third_serial );
        $prepare->setAttr('money', $total_fee );
        $prepare->save();
    
        $log = new DsMoneyLog();
        $log->data('ds_id', $prepare->getData('ds_id') );
        $log->data('uid',   $prepare->getData('uid') );
        $log->data('money', $total_fee );
        $log->data('create_time', time() );
        $log->save();
    
        // 这里，是大赛的报名成功的善后工作。
        $db = Sys::get_container_db_eloquent();
        $sql="select * from ds_register_log where uid=? and zong_ds_id=?";
        $uid = $prepare->getData('uid');
        $ds_id = $prepare->getData('ds_id');
        $row = DbSelect::fetchRow($db, $sql,[ $uid, $ds_id ]);
        if ($row) {
           
            $db::table('ds_register_log')->where('uid', $uid)->where('zong_ds_id', $ds_id)->update(
                    [
                            'has_pay' =>1,
                            'money'   => $total_fee,
                    ]);
            // 发送通知。
            $msg_help = new \BBExtend\video\RaceNew();
            $msg_help->insert_post($ds_id, $row['ds_id'], $uid);
            
        }
        
        
//         $register_log = DsRegisterLog::get(['zong_ds_id' => $prepare->getData('ds_id'),
//             'uid'=>$prepare->getData('uid')  ]);
//         if (!$register_log) {
//             exit();
//         }else {
//             $register_log->setAttr('money', $total_fee);
//             $register_log->setAttr('has_pay', 1);
//             $register_log->save();
//         }
    
        //生成正式订单表，发送物流信息，
        //         $user = Users::get($prepare->getData('uid'));
        //         $user->buy_success_money($out_trade_no,$third_name,$third_serial);
        return 'success';
    }
    
    /**
     * 商城支付成功处理
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function buy($out_trade_no,$third_name,$third_serial)
    {
        //注意，这里查的是临时表
        $prepare = ShopOrderPrepare::get(['serial' => $out_trade_no ]);
    
        if (!$prepare) {
            exit();
        }
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($prepare->getData( 'is_success') == 1) {
            return "success";
        }
        //否则，应该把订单表中置为成功！
        $prepare->setAttr('is_success', 1) ;
        $prepare->save();
        //生成正式订单表，发送物流信息，
        $user = Users::get($prepare->getData('uid'));
        $user->buy_success_money($out_trade_no,$third_name,$third_serial);
        return 'success';
    }
    
    
    /**
     * 安卓支付宝充值处理
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function pay_android($out_trade_no,$trade_no,$money)
    {
        $order = Buy::get(['order' => $out_trade_no]);
        if (!$order) {
            exit();
        }
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($order->successful == 1) {
            return "success";
        }
        //否则，应该把订单表中置为成功！
        $order->setAttr('successful', 1) ;
        $order->setAttr('third_name', 'ali');
        $order->setAttr('third_serial', $trade_no );
        $order->save();
        
        \BBExtend\user\Tongji::getinstance($order->getData('uid'))
          ->money27($money);
        
        //把订单表buy的对应行的successful设置1.
        //bb_currency表，先查有没有uid对应，否则还得先添加。
        //总之确保获得。
        //现在给bb_currency表增加gold字段
        //再记录到日志表bb_currency_log
        //bb_msg表发送信息。
        $user = Users::get($order->getData('uid'));
        $user->pay_success($order->getData('count'));
        return 'success';
    }
    
}

