<?php

/**
 * Created by PhpStorm.
 * User: 谢烨
 * 
 * 这是微信支付的帮助类
 * //
 * 
 * Date: 2016/8/20
 * Time: 20:30
 */
namespace BBExtend\pay\wxpay;
use think\Db;
use BBExtend\pay\alipay\Logs;
 
use  app\pay\model\Buy;
use app\pay\model\Users;
use app\shop\model\ShopOrderPrepare;
use app\race\model\DsMoneyPrepare;
use app\race\model\DsMoneyLog;
use app\race\model\DsRegisterLog;
use BBExtend\Sys;
use BBExtend\DbSelect;

require_once realpath( EXTEND_PATH).'/WxpayAPI/lib/WxPay.Config.php';
 require_once ( realpath( realpath( EXTEND_PATH)."/WxpayAPI/lib/WxPay.Api.php"));
 require_once ( realpath( realpath( EXTEND_PATH)."/WxpayAPI/lib/WxPay.Notify.php"));
 
/**
 * 该类封装 阿里支付，根据其demo改编。
 * @author 谢烨
 *
 */
class Help
{
//     <xml><appid><![CDATA[wx05fb5b232abf83e7]]></appid>
//     <bank_type><![CDATA[CFT]]></bank_type>
//     <cash_fee><![CDATA[2]]></cash_fee>
//     <fee_type><![CDATA[CNY]]></fee_type>
//     <is_subscribe><![CDATA[N]]></is_subscribe>
//     <mch_id><![CDATA[1361225402]]></mch_id>
//     <nonce_str><![CDATA[ri3f59q0ac6cayy11ehwb98sm5mt1ba9]]></nonce_str>
//     <openid><![CDATA[oH5lMxGz0xK_onJr4MElmtnYXMFI]]></openid>
//     <out_trade_no><![CDATA[a891a59c792e7112608c5389f9f0de66]]></out_trade_no>
//     <result_code><![CDATA[SUCCESS]]></result_code>
//     <return_code><![CDATA[SUCCESS]]></return_code>
//     <sign><![CDATA[BF26BF61E4D093312BCEA3B8D34A7DDD]]></sign>
//     <time_end><![CDATA[20160906170447]]></time_end>
//     <total_fee>2</total_fee>
//     <trade_type><![CDATA[APP]]></trade_type>
//     <transaction_id><![CDATA[4009332001201609063265188316]]></transaction_id>
//     </xml>

    private $success = '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>';
    
    public function test(){
        echo "hello, 支付test";
    }
    
    public function receive_post(){
        
        $temp = new \app\pay\model\Alitemp();
        $temp->data('url', 'weixin_post_to_me');
        
        $response =file_get_contents("php://input");
        
        //这里的异常处理就是：验签错误，说明不是微信服务器发送的请求，是假的！！
        try {
            $result = \WxPayResults::Init($response);
        }catch (\Exception $e) {
            return '<xml><return_code><![CDATA[FAIL]]></return_code></xml>';
        }
        
        $temp->data('content', json_encode($result) );
        
        $temp->data('create_time',date("Y:m:d H-i-s"));
        $temp->save();
        
        //现在验签通过，需要看微信告诉我 成功了 ，还是失败了。
        if (isset($result['result_code']) &&
            isset($result['return_code']) &&
            $result['result_code']== 'SUCCESS' &&
            $result['return_code'] == 'SUCCESS'
        ) {
            
            //pa代表安卓充值。
            if (preg_match('#^XX1#', $result['out_trade_no'])) {
                return $this->pay_vip($result['out_trade_no'],'wx', $result['transaction_id'],
                        $result['total_fee']
                        );
            }
            
            
            //pa代表安卓充值。
            if (preg_match('#^PA#', $result['out_trade_no'])) {
                return $this->pay_android($result['out_trade_no'], $result['transaction_id'],
                        $result['total_fee']
                        );
            }
            
            // 201707 别改顺序
            if (preg_match('#^BM#', $result['out_trade_no'])) {
                return $this->pay_ds($result['out_trade_no'], 'wx',$result['transaction_id'] ,
                        $result['total_fee']
                        );
            }
            // xieye 特别注意，这个必须在上面的后面！！千万别改顺序，
            // 201707 别改顺序
            if (preg_match('#^B#', $result['out_trade_no'])) {
                return $this->buy($result['out_trade_no'], 'wx',$result['transaction_id'] );
            }
            if (preg_match('#^DS#', $result['out_trade_no'])) {
                return $this->ds_register($result['out_trade_no'], 'wx',$result['transaction_id'],
                        $result['total_fee']
                        );
            }
            
            if (preg_match('#^TG#', $result['out_trade_no']) ) {
                return \BBExtend\model\Advise::pay_process(
                    $result['out_trade_no'], 'wx',$result['transaction_id'],$result['total_fee'] );
//                 $this->ds_register($result['out_trade_no'], 'wx',$result['transaction_id'],
//                         $result['total_fee']
//                         );
            }
            
            //判断该笔订单是否在商户网站中已经做过处理
            //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            //如果有做过处理，不执行商户的业务程序
        }
        
        return $this->success;
    }
    
    
    /**
     * vip支付成功
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function pay_vip($out_trade_no,$third_name,$third_serial, $total_fee)
    {
        //注意，这里查的是临时表
        //         $prepare = DsMoneyPrepare::get(['order_no' => $out_trade_no ]);
        $db = Sys::get_container_db();
        $sql ="select * from bb_baoming_order_prepare where serial=?";
        $prepare = $db->fetchRow($sql, $out_trade_no);
        if (!$prepare) {
            exit();
        }
        $time=time();
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($prepare['is_success'] == 1) {
            return $this->success;
        }
        $db->update("bb_baoming_order_prepare", [
                "is_success"   => 1,
                "third_serial"=> $third_serial,
                "third_name"=> 'wx',
                
                "price"       =>  $total_fee/100,
                "update_time" => $time,
                
        ], "id=".$prepare["id"] );
        // 再修改bb_baoming表
        
        $order = new  \BBExtend\model\BaomingOrder();
        $order->uid = $prepare['uid'];
        $order->price = $total_fee/100;
        $order->serial = $prepare['serial'];
        $order->is_success = 1;
        $order->create_time = time();
        $order->third_name = 'wx';
        $order->third_serial = $third_serial;
        $order->newtype = 2;
        $order->save();
        
        
        // 还没完，还要一个日志
        $log = new \BBExtend\model\VipApplicationLog();
        $log->uid = $prepare['uid'];
        $log->money = $total_fee/100;
        $log->status = 1;
        $log->create_time = time();
        $log->save();      
        
        return 'success';
    }
    
    
    
    /**
     * 特别收费支付成功
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function pay_ds($out_trade_no,$third_name,$third_serial, $total_fee)
    {
        //注意，这里查的是临时表
//         $prepare = DsMoneyPrepare::get(['order_no' => $out_trade_no ]);
        $db = Sys::get_container_db();
        $sql ="select * from bb_baoming_order_prepare where serial=?";
        $prepare = $db->fetchRow($sql, $out_trade_no);
        if (!$prepare) {
            exit();
        }
        $time=time();
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($prepare['is_success'] == 1) {
            return $this->success;
        }
        $db->update("bb_baoming_order_prepare", [
            "is_success"   => 1,
            "third_serial"=> $third_serial,
            "price"       =>  $total_fee/100,
            "update_time" => $time,
            
        ], "id=".$prepare["id"] );
        // 再修改bb_baoming表
        $db->update("bb_baoming", [
            "is_success"   => 1,
            "pay_time"=> $time,
        ], "uid=".$prepare["uid"]." and ds_id =  ".$prepare["ds_id"] );
        
        $sql = "select msg_id from bb_baoming where uid=".$prepare["uid"].
            " and ds_id =  ".$prepare["ds_id"];
        $msg_id = $db->fetchOne($sql);
        
        $sql="update bb_msg set sort=0 where id =".intval($msg_id);
        $db->query($sql);
        
        // 最终，插入正式报名订单表
        $db->insert("bb_baoming_order", [
            "uid"  => $prepare["uid"],
            "price" => $total_fee/100,
            "ds_id" => $prepare["ds_id"],
            "serial" => $prepare["serial"],
            "third_serial" => $third_serial,
            "create_time" => $time,
            "is_success" =>1,
        ]);
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
            return $this->success;
        }
        //否则，应该把订单表中置为成功！
        $prepare->setAttr('has_success', 1) ;
        $prepare->setAttr('third_name', 'wx');
        $prepare->setAttr('third_serial', $third_serial );
        $prepare->setAttr('money', $total_fee/100 );
        $prepare->save();
        
        $log = new DsMoneyLog();
        $log->data('ds_id', $prepare->getData('ds_id') );
        $log->data('uid',   $prepare->getData('uid') );
        $log->data('money', $total_fee/100 );
        $log->data('create_time', time() );
        $log->save();
        
//         $register_log = DsRegisterLog::get(['zong_ds_id' => $prepare->getData('ds_id'),
//             'uid'=>$prepare->getData('uid')  ]);
//         if (!$register_log) {
//             exit();
//         }else {
//             $register_log->setAttr('money', $total_fee/100);
//             $register_log->setAttr('has_pay', 1);
//             $register_log->save();
//         }

        $db = Sys::get_container_db_eloquent();
        $sql="select * from ds_register_log where uid=? and zong_ds_id=?";
        $uid = $prepare->getData('uid');
        $ds_id = $prepare->getData('ds_id');
        $row = DbSelect::fetchRow($db, $sql,[ $uid, $ds_id ]);
        if ($row) {
            
            $db::table('ds_register_log')->where('uid', $uid)->where('zong_ds_id', $ds_id)->update(
                    [
                            'has_pay' =>1,
                            'money'   => $total_fee/100 ,
                    ]);
            // 发送通知。
            $msg_help = new \BBExtend\video\RaceNew();
            $msg_help->insert_post($ds_id, $row['ds_id'], $uid);
            
        }
        
        
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
            return $this->success;
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
     * 安卓 微信 充值处理
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
            return $this->success;
        }
        //否则，应该把订单表中置为成功！
        $order->setAttr('successful', 1) ;
        $order->setAttr('third_name', 'wx');
        $order->setAttr('third_serial', $trade_no );
        $order->save();
        
        \BBExtend\user\Tongji::getinstance($order->getData('uid'))
          ->money27($money/100);
        //把订单表buy的对应行的successful设置1.
        //bb_currency表，先查有没有uid对应，否则还得先添加。
        //总之确保获得。
        //现在给bb_currency表增加gold字段
        //再记录到日志表bb_currency_log
        //bb_msg表发送信息。
        $user = Users::get($order->getData('uid'));
        $user->pay_success($order->getData('count'));
        return $this->success;
    }
    
    /**
     * 查询第三方支付的服务器。
     * @param unknown $serial
     */
    public function query_remote($serial){
        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($serial);
        $return_arr = \WxPayApi::orderQuery($input);
     //   return $return_arr;
        // 以下字段在return_code为SUCCESS的时候有返回
        try{
            if ($return_arr['return_code'] =='SUCCESS') {
                if ($return_arr['result_code']  =='SUCCESS' ) {
                    //xieye,这里有好几个值
                    if (isset( $return_arr['trade_state'] )
                        && $return_arr['trade_state']=='SUCCESS'     
                       ){
                        return ['code'=>1, 'data'=>['success' =>1 ]];
                    }else {
                        return ['code'=>1, 'data'=>['success' =>0 ]];
                    }
                    //return ['code'=>1, 'data'=> $this->get_mobile_query($return_arr['prepay_id']) ];
                }else {
                    return ['code'=>0, 'message'=> $return_arr['err_code_des']];
                }
        
            }else {
                return ['code'=>0, 'message'=> $return_arr['return_msg']];
            }
        }catch(\Exception $s) {
            return ['code'=>0, 'message'=> '未知的错误异常。'];
        }
    }
    
    /**
     * app支付统一下单，目的是获得prepay_id
     */
    public function tongyi_xiadan($body, $trade, $price)
    {
        $input = new \WxPayUnifiedOrder();
        
//         $input->SetAppid( \WxPayConfig::APPID );//应用id
//         $input->SetMch_id(\WxPayConfig::MCHID  ); //商户号
//         $input->SetNonce_str(md5(time()) ); //随机字符串
        $input->SetBody($body);//商品描述
        $input->SetOut_trade_no($trade ); //商城订单号
        $input->SetTotal_fee($price); //2分钱
    //    $input->SetSpbill_create_ip( $_SERVER['REMOTE_ADDR'] ); //终端IP
        $input->SetTrade_type('APP');
        $input->SetNotify_url( wx_gateway() );
        //得到返回
        $return_arr = \WxPayApi::unifiedOrder($input);
    //    Sys::debugxieye($return_arr);
        //这里的要点是，return_code SUCCESS/FAIL
        // 以下字段在return_code为SUCCESS的时候有返回
        try{
        if ($return_arr['return_code'] =='SUCCESS') {
            if ($return_arr['result_code']  =='SUCCESS' ) {
                $temp = $this->get_mobile_query($return_arr['prepay_id']);
                $temp['out_trade_no'] = $trade;
                
                // 谢烨，201707 为了安卓不能解析package字段
                $temp['packagevalue'] = $temp['package'];
                
                
                return ['code'=>1, 'data'=> $temp ];
            }else {
                return ['code'=>0, 'message'=> $return_arr['err_code_des']];
            }
            
        }else {
            return ['code'=>0, 'message'=> $return_arr['return_msg']];
        }
        }catch(\Exception $s) {
            return ['code'=>0, 'message'=> '未知的错误异常。'];
        }
     //   $input->SetOpenid($openId);echo 3;
       // $order = WxPayApi::unifiedOrder($input);
       //return $return_arr;
    }
    
    /**
     * 该函数计算 手机发起支付的所有参数。
     * @param unknown $prepay_id
     */
    private function get_mobile_query($prepay_id)
    {
        $data_obj = new \WxPayResults();
        $data_obj->SetData("package", "Sign=WXPay");
        $data_obj->SetData("appid", \WxPayConfig::APPID);
        $data_obj->SetData("partnerid", \WxPayConfig::MCHID);
        $data_obj->SetData("noncestr", \WxPayApi::getNonceStr());
        $data_obj->SetData("timestamp", time());
        $data_obj->SetData("prepayid", $prepay_id);
        $data_obj->SetSign();
        $data = $data_obj->GetValues();
        return $data;
    }
    
   
    
}

