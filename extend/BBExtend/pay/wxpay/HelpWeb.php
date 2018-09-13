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
 
use app\race\model\DsMoneyPrepare;
use app\race\model\DsMoneyLog;
use app\race\model\DsRegisterLog;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\Record;
use BBExtend\model\Push;
use BBExtend\model\Rewind;

use BBExtend\model\Present;
use BBExtend\model\DashangPrepare;
use BBExtend\model\DashangLog;
use BBExtend\Currency;
use BBExtend\BBRecord;


require_once realpath( EXTEND_PATH).'/WxpayAPI/lib/WxPay.Config.Web.php';
 require_once realpath( EXTEND_PATH)."/WxpayAPI/lib/WxPay.Api.php";
 require_once realpath( EXTEND_PATH)."/WxpayAPI/example/WxPay.JsApiPay.php";
 require_once realpath( EXTEND_PATH).'/WxpayAPI/example/log.php';
 require_once realpath( EXTEND_PATH).'/WxpayAPI/lib/WxPay.Data.php';
/**
 * 该类封装 阿里支付，根据其demo改编。
 * @author 谢烨
 *
 */
class HelpWeb
{


    private $success = '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>';
    
    
    public function test(){
        echo "hello, 支付test";
    }
    
    public function receive_post(){
        
//         $temp = new \app\pay\model\Alitemp();
//         $temp->data('url', 'weixin_post_to_me');
        
        $response =file_get_contents("php://input");
        
        //这里的异常处理就是：验签错误，说明不是微信服务器发送的请求，是假的！！
        try {
            $result = \WxPayResults::Init($response);
        }catch (\Exception $e) {
            return '<xml><return_code><![CDATA[FAIL]]></return_code></xml>';
        }
        
        
        $request = \think\Request::instance( );
        $third_party = new \BBExtend\model\ThirdPartyPayCallBack();
        $third_party->type='wx';
        $third_party->url = $request->url(true) ;
        $third_party->create_time = time();
        $third_party->post_body=$response;
        $third_party->save();
        
        
      //  Sys::debugxieye( json_encode($result ) );
        
//         {"appid":"wx190ef9ba551856b0",
//         "attach":"\u602a\u517dbobo",
//         "bank_type":"CFT",
//         "cash_fee":"1",
//         "fee_type":"CNY",
//         "is_subscribe":"Y",
//         "mch_id":"1434556402",
//         "nonce_str":"m0sqrtxai0i11d474q2dmjmr61ayjlre",
//         "openid":"oFERUwGQ6Zp99bxmwWEIDXRlbyQ0",
//         "out_trade_no":"DS20170321321873229342767",
//         "result_code":"SUCCESS",
//         "return_code":"SUCCESS",
//         "sign":"2C925F6D2827558FAD0C0E86555793CE",
//         "time_end":"20170321170927",
//         "total_fee":"1",
//         "trade_type":"JSAPI",
//         "transaction_id":"4009332001201703214154376378"}
        
        //现在验签通过，需要看微信告诉我 成功了 ，还是失败了。
        if (isset($result['result_code']) &&
            isset($result['return_code']) &&
            $result['result_code']== 'SUCCESS' &&
            $result['return_code'] == 'SUCCESS'
        ) {
            
            //ti代表打赏的订单的开头的字符串。
            if (preg_match('#^TI#', $result['out_trade_no'])) {
                return $this->pay_dashang($result['out_trade_no'], $result['transaction_id'],
                        $result['total_fee']
                        );
            }
            
            
            if (preg_match('#^DS#', $result['out_trade_no'])) {
            // 大赛。
              return $this->pay($result['out_trade_no'], $result['transaction_id'], $result['total_fee']);
            }
            
            if (preg_match('#^DL#', $result['out_trade_no'])) {
                // 大赛。
                return $this->pay_like($result['out_trade_no'], $result['transaction_id'], $result['total_fee']);
            }
            
        }
        
         return $this->success;
    }
    
    
    
    
    
    
    /**
     * 201808，给大赛报名者点赞。付钱点赞。
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function pay_like($out_trade_no, $trade_no, $money_fen)
    {
        
        $order = \BBExtend\model\DsMoneyPrepare::where( 'order_no' , $out_trade_no )->first();
        
//         $order = DashangPrepare::where('order_no' , $out_trade_no)->first()  ;
        if (!$order) {
            exit();
        }
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($order->has_success == 1) {
            return $this->success;
        }
        //否则，应该把订单表中置为成功！
        $order->has_success= 1 ;
        $order->third_name= 'wx';
        $order->third_serial= $trade_no ;
        $order->money= $money_fen /100;
        $order->save();
        
        // 下面怎办，调用一个类的方法，设置最终结局。
        $json = $order->json_info;
        $json = json_decode($json,1);
        
        // 谢烨，现在获取此人的个人信息。
        $info2 = new \BBExtend\model\UserRace();
        $result = $info2->like ($json['self_uid'] , $json['log_id'] , 4);
        
        
        return $this->success;
    }
    
    
    
    
    
    /**
     * 打赏付钱 的异步回调。
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function pay_dashang($out_trade_no, $trade_no, $money_fen)
    {
        $order = DashangPrepare::where('order_no' , $out_trade_no)->first()  ;
        if (!$order) {
            exit();
        }
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($order->has_success == 1) {
            return $this->success;
        }
        //否则，应该把订单表中置为成功！
        $order->has_success= 1 ;
        $order->third_name= 'wx';
        $order->third_serial= $trade_no ;
        $order->money_fen= $money_fen ;
        $order->save();
        
        $record = $this->get_record($order->room_id);
        
        if (!$record) {
            exit();
        }
        
       // $record = Record::where('room_id', $order->room_id )->first();
      //  $present = Present::find($order->present_id );
        $target_uid = $record->uid;
        // 现在开始给另一个用户加钱。但是加波豆。
        $bean = Currency::cny_to_bean_for_dashang($money_fen/100 ) ; // 把元改成分。
        Currency::add_bean($target_uid,  $bean, '被匿名打赏');
        
//         $this->log($uid,  $price, $room_id, $target_uid,$type);
//         // 最后发消息
        //记录此次打赏日志
        $log = new \app\shop\model\Dashang();
        $log->data('create_time', time());
        $log->data('uid', 0 ); // 这是匿名打赏
        $log->data('room_id',  $order->room_id );
        $log->data('target_uid', $target_uid );
        $log->data('gold', 0);
        $log->data('present_id',   0);
        $log->data('present_name', '' );
        $log->data('bean', $bean );
        $log->save();
        $time = time();
        // 
    
        
        // 大量善后处理。
        $db = Sys::get_container_db();
        $daystr = date("Ymd");
        
        
        //把视频的表的打赏总数也要加啊。
       // $table = BBRecord::get_table_name($room_id);
//         $table = 'bb_record';
//         $sql ="update {$table} set dashang_bean_all = dashang_bean_all + {$bean} where room_id=? ";
//         $db->query($sql,$room_id);
    
        return $this->success;
    }
    
    
    private function get_record($room_id){
        $table = BBRecord::get_table_name($room_id);
        $record=null;
        if ($table=='bb_record') {
            $record = Record::where('room_id',$room_id)->first();
        }elseif($table=='bb_push') {
            $record = Push::where('room_id',$room_id)->first();
        }elseif($table=='bb_rewind') {
            $record = Rewind::where('room_id',$room_id)->first();
        }
        return $record;
    }
    
    /**
     * 大赛报名付钱 的异步回调。
     * @param unknown $out_trade_no 我们自己的订单号，bb_buy
     */
    public function pay($out_trade_no,$trade_no, $money_fen)
    {
        $order = DsMoneyPrepare::get(['order_no' => $out_trade_no]);
        if (!$order) {
            Sys::debugxieye('回调，订单不存在');
            exit();
        }
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($order->getData('has_success') == 1) {
            return $this->success;
        }
        
        $log = new DsMoneyLog();
        $log->data('ds_id', $order->getData('ds_id') );
        $log->data('uid',   $order->getData('uid') );
        $log->data('money', $money_fen/100 );
        $log->data('create_time', time() );
        $log->save();
        
        
        //否则，应该把订单表中置为成功！
        $order->setAttr('has_success', 1) ;
        $order->setAttr('third_name', 'wx');
        $order->setAttr('third_serial', $trade_no );
        $order->setAttr('money', $money_fen/100 );
        $order->save();
        
        $db = Sys::get_container_db_eloquent();
        $sql="select * from ds_register_log where uid=? and zong_ds_id=?";
        $uid = $order->getData('uid');
        $ds_id = $order->getData('ds_id');
        $row = DbSelect::fetchRow($db, $sql,[ $uid, $ds_id ]);
    //    Sys::debugxieye("支付回调大赛报名：uid：{$uid},ds_id:{$ds_id}"  );
        
        if ($row) {
            
            $db::table('ds_register_log')->where('uid', $uid)->where('zong_ds_id', $ds_id)->update(
                    [
                            'has_pay' =>1,
                            'money'   => $money_fen/100,
                    ]);
            // 发送通知。
            $msg_help = new \BBExtend\video\RaceNew();
            $msg_help->insert_post($ds_id, $row['ds_id'], $uid);
            
        }else {
            Sys::debugxieye("不正常的情况，支付回调，没找到大赛的报名人。");
        }
        
        
        return $this->success;
    }
    

    
    
    
    /**
     *
     * 打赏。
     *
     * 统一下单，目的是获得prepay_id
     *
     * uid,phone,ds_id，openid
     * 谢烨，应该防止用户重复报名交钱，这个功能最后再做！
     *
     *
     */
    public function tongyi_xiadan_for_dashang($room_id,$money_fen,  $openid)
    {
//         Sys::debugxieye(123333);
//         Sys::debugxieye($openid);
        
        
        $trade = \BBExtend\pay\Order::get_order_serial_dashang();
        $openid = strval($openid);
        $record = $this->get_record($room_id);
        
        if (!$record) {
            return ['code'=> 0 , 'message' => '视频不存在' ];
        }
        
        $db = Sys::get_container_db_eloquent();
        //$price = $ds['money'];
        $price_fen = floatval( $money_fen );
        if ($price_fen<1) {
            return ['code'=> 0 , 'message' => '金额必须不能为零' ];
        }
        $bodou = $price_fen / 10;//1波豆 = 10分人民币
        $bodou = (int)$bodou;
        
        $price_fen = strval( $price_fen );
        $time = time();
    
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("怪兽bobo打赏");// 谢烨，这是显示在用户个人的微信支付流水里的title，很重要。
        $input->SetAttach("怪兽bobo");     //作用未知
        $input->SetOut_trade_no( $trade );
        $input->SetTotal_fee( $price_fen  ); // 付款金额，注意是分！！
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://bobo.yimwing.com/race/notify/index");//设置我们的服务器异步回调
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid); //必须设置，否则无法支付。
        $return_arr = \WxPayApi::unifiedOrder($input);
        
   //     Sys::debugxieye($return_arr);
        
        //        appid    appid
        //        mch_id  商户号
        //        nonce_str  随机字符串
        //        prepay_id 预生成订单号
        //        result_code SUCCESS
        //        return_code SUCCESS
        //        return_msg  OK
        //        sign  1...................
        //        trade_type JSAPI
        try{
            if ($return_arr['return_code'] =='SUCCESS') {
                if ($return_arr['result_code']  =='SUCCESS' ) {
                     
                    $prepare = new DashangPrepare();
                    $prepare->room_id=$room_id  ;
                    $prepare->present_id=0  ;
                    $prepare->order_no=$trade  ;
                    $prepare->create_time=time()  ;
                    $prepare->has_success=0  ;// 注意这里是0
                    $prepare->openid=$openid  ;
                    $prepare->target_uid = $record->uid;
                    //$prepare->money_fen = (int)$price_fen; // 这句话应该在真的成功后加
                    $prepare->save();
                    return ['code'=>1, 'data'=> $return_arr ];
                }else {
                    return ['code'=>-6, 'message'=> $return_arr['err_code_des']];
                }
    
            }else {
                return ['code'=>-5, 'message'=> $return_arr['return_msg']];
            }
        }catch(\Exception $s) {
            return ['code'=>-4, 'message'=> '未知的错误异常。'];
        }
    
    }
    
    
    
    
    
    
    /**
     *
     * 大赛报名-统一下单，
     *  注意：另外还有一个打赏。
     *
     * app支付统一下单，目的是获得prepay_id
     *
     * uid,phone,ds_id，openid
     * 谢烨，应该防止用户重复报名交钱，这个功能最后再做！
     *
     *
     */
    public function tongyi_xiadan_for_race_like($ds_id=0,  $self_uid=0, $target_uid=0,  $openid='')
    {
        //  Sys::debugxieye("wx:1");
        $trade = \BBExtend\pay\Order::get_order_serial_race_like();
        $uid =  $self_uid= intval($self_uid);
        $phone = '';
        $openid = strval($openid);
        
        $ds_id = intval($ds_id);
        $db = Sys::get_container_db();
        $sql ="select * from ds_race where is_active=1 and id = {$ds_id}";
        $ds  = $db->fetchRow($sql);
        if (!$ds) {
            return ['code'=> -1 , 'message' => '大赛不存在或未激活' ];
        }
        
        $sql ="select id from ds_register_log where zong_ds_id=? and uid=?";
        $log_id  = $db->fetchOne($sql,[ $ds_id, $target_uid ] );
        if (!$log_id) {
            return ['code'=> -1 , 'message' => '大赛未报名，不可打赏' ];
        }
        
        
        
        $price = 1;
        $price_fen = strval( intval( $price * 100 )); //转成分。
        if ( in_array($uid, get_test_userid_arr() ) ){
            $price = 0.01;
            $price_fen = strval( intval( $price * 100 )); //转成分。
        }
        
        $time = time();
        //         if ($time < $ds['register_start_time'] || $time > $ds['register_end_time'] ) {
        //             return ['code'=> -3 , 'message' => '报名时间错误，当前不可报名' ];
        
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("怪兽bobo大赛打赏");// 谢烨，这是显示在用户个人的微信支付流水里的title，很重要。
        $input->SetAttach("怪兽bobo");     //作用未知
        $input->SetOut_trade_no( $trade );
        $input->SetTotal_fee( $price_fen  ); // 付款金额，注意是分！！
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("https://bobo.yimwing.com/race/notify/index");//设置我们的服务器异步回调
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid); //必须设置，否则无法支付。
        $return_arr = \WxPayApi::unifiedOrder($input);
        //  Sys::debugxieye("wx:2，统一下单openid：{$openid}");
    
        try{
            if ($return_arr['return_code'] =='SUCCESS') {
                if ($return_arr['result_code']  =='SUCCESS' ) {
                    //    Sys::debugxieye("wx:success");
                    $prepare = new DsMoneyPrepare();
                    $prepare->data('uid',$uid  );
                    $prepare->data('phone',$phone  );
                    $prepare->data('order_no',$trade  );
                    $prepare->data('ds_id',$ds_id  );
                    $prepare->data('zong_ds_id',$ds_id  );
                    $prepare->data('create_time',time()  );
                    $prepare->data('has_success',0  );
                    $prepare->data('type',2  );
                    
                    $prepare->data('openid',$openid  );
                    
                    $temp = [
                        'open_id'=>$openid,
                            'ds_id' =>$ds_id,
                            'self_uid' =>$self_uid,
                            'target_uid' =>$target_uid,
                            'log_id' =>$log_id,
                    ];
                    $prepare->data( 'json_info', json_encode($temp)   );
                    
                    // $prepare->data('third_serial',$return_arr['prepay_id']  );
                    $prepare->save();
                    
                    
                    return ['code'=>1, 'data'=> $return_arr ];
                }else {
                    //      Sys::debugxieye("wx:4");
                    return ['code'=>-6, 'message'=> $return_arr['err_code_des']];
                }
                
            }else {
                //    Sys::debugxieye("wx:5");
                return ['code'=>-5, 'message'=> $return_arr['return_msg']];
            }
        }catch(\Exception $s) {
            //  Sys::debugxieye("wx:6");
            return ['code'=>-4, 'message'=> '未知的错误异常。'];
        }
        
    }
    
    
    
    /**
     *
     * 大赛报名-统一下单，
     *  注意：另外还有一个打赏。
     *
     * app支付统一下单，目的是获得prepay_id
     *
     * uid,phone,ds_id，openid
     * 谢烨，应该防止用户重复报名交钱，这个功能最后再做！
     *
     *
     */
    public function tongyi_xiadan_v5($ds_id=0,  $uid=0, $phone='', $openid='')
    {
        //  Sys::debugxieye("wx:1");
        $trade = \BBExtend\pay\Order::get_order_serial_race();
        $uid = intval($uid);
        $phone = strval($phone);
        $openid = strval($openid);
        
        $ds_id = intval($ds_id);
        $db = Sys::get_container_db();
        $sql ="select * from ds_race where  id = {$ds_id}";
        $ds  = $db->fetchRow($sql);
        if (!$ds) {
                 return ['code'=> -1 , 'message' => '大赛不存在或未激活' ];
        }
        
        $price = $ds['money'];
        $price_fen = strval( intval( $price * 100 )); //转成分。
        if ( in_array($uid, get_test_userid_arr() ) ){
            $price = 0.01;
            $price_fen = strval( intval( $price * 100 )); //转成分。
        }
        
        
        $time = time();
        //         if ($time < $ds['register_start_time'] || $time > $ds['register_end_time'] ) {
        //             return ['code'=> -3 , 'message' => '报名时间错误，当前不可报名' ];
        //         }
        // 这里暂未做防止重复报名的代码。
        // 。。。。。。。。。。 请勿删除此行
        // 。。。。。。。。。。 请勿删除此行
        // 。。。。。。。。。。 请勿删除此行
        
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("怪兽bobo大赛报名");// 谢烨，这是显示在用户个人的微信支付流水里的title，很重要。
        $input->SetAttach("怪兽bobo");     //作用未知
        $input->SetOut_trade_no( $trade );
        $input->SetTotal_fee( $price_fen  ); // 付款金额，注意是分！！
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("https://bobo.yimwing.com/race/notify/index");//设置我们的服务器异步回调
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid); //必须设置，否则无法支付。
        $return_arr = \WxPayApi::unifiedOrder($input);
      //  Sys::debugxieye("wx:2，统一下单openid：{$openid}");
        //        appid    appid
        //        mch_id  商户号
        //        nonce_str  随机字符串
        //        prepay_id 预生成订单号
        //        result_code SUCCESS
        //        return_code SUCCESS
        //        return_msg  OK
        //        sign  1...................
        //        trade_type JSAPI
        try{
            if ($return_arr['return_code'] =='SUCCESS') {
                if ($return_arr['result_code']  =='SUCCESS' ) {
                //    Sys::debugxieye("wx:success");
                    $prepare = new DsMoneyPrepare();
                    $prepare->data('uid',$uid  );
                    $prepare->data('phone',$phone  );
                    $prepare->data('order_no',$trade  );
                    $prepare->data('ds_id',$ds_id  );
                    $prepare->data('zong_ds_id',$ds_id  );
                    $prepare->data('type',1  );
                    
                    $prepare->data('create_time',time()  );
                    $prepare->data('has_success',0  );
                    
                    $prepare->data('openid',$openid  );
                    // $prepare->data('third_serial',$return_arr['prepay_id']  );
                    $prepare->save();
                    
                    
                    return ['code'=>1, 'data'=> $return_arr ];
                }else {
              //      Sys::debugxieye("wx:4");
                    return ['code'=>-6, 'message'=> $return_arr['err_code_des']];
                }
                
            }else {
            //    Sys::debugxieye("wx:5");
                return ['code'=>-5, 'message'=> $return_arr['return_msg']];
            }
        }catch(\Exception $s) {
          //  Sys::debugxieye("wx:6");
            return ['code'=>-4, 'message'=> '未知的错误异常。'];
        }
        
    }
    
    
    
    
    
    
    
    
    
    
    /**
     *
     * 大赛报名-统一下单，
     *  注意：另外还有一个打赏。
     *
     * app支付统一下单，目的是获得prepay_id
     *
     * uid,phone,ds_id，openid
     * 谢烨，应该防止用户重复报名交钱，这个功能最后再做！
     *
     *
     */
    public function tongyi_xiadan_demo($ds_id=0,  $uid=0, $phone='', $openid='')
    {
      //  Sys::debugxieye("wx:1");
        $trade = \BBExtend\pay\Order::get_order_serial_race();
        $uid = intval($uid);
        $phone = strval($phone);
        $openid = strval($openid);
        
        $ds_id = intval($ds_id);
        $db = Sys::get_container_db();
        $sql ="select * from ds_race where is_active=1 and id = {$ds_id}";
        $ds  = $db->fetchRow($sql);
        if (!$ds) {
       //     return ['code'=> -1 , 'message' => '大赛不存在或未激活' ];
        }
        
        $price = 0.01;
        $price_fen = strval( intval( $price * 100 )); //转成分。
        
        
        $time = time();
        //         if ($time < $ds['register_start_time'] || $time > $ds['register_end_time'] ) {
        //             return ['code'=> -3 , 'message' => '报名时间错误，当前不可报名' ];
        //         }
        // 这里暂未做防止重复报名的代码。
        // 。。。。。。。。。。 请勿删除此行
        // 。。。。。。。。。。 请勿删除此行
        // 。。。。。。。。。。 请勿删除此行
        
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("怪兽bobo大赛报名");// 谢烨，这是显示在用户个人的微信支付流水里的title，很重要。
        $input->SetAttach("怪兽bobo");     //作用未知
        $input->SetOut_trade_no( $trade );
        $input->SetTotal_fee( $price_fen  ); // 付款金额，注意是分！！
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://bobo.yimwing.com/race/notify/index");//设置我们的服务器异步回调
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid); //必须设置，否则无法支付。
        $return_arr = \WxPayApi::unifiedOrder($input);
        Sys::debugxieye("wx:2");
        //        appid    appid
        //        mch_id  商户号
        //        nonce_str  随机字符串
        //        prepay_id 预生成订单号
        //        result_code SUCCESS
        //        return_code SUCCESS
        //        return_msg  OK
        //        sign  1...................
        //        trade_type JSAPI
        try{
            if ($return_arr['return_code'] =='SUCCESS') {
                if ($return_arr['result_code']  =='SUCCESS' ) {
                    Sys::debugxieye("wx:success");
                    $prepare = new DsMoneyPrepare();
                    $prepare->data('uid',$uid  );
                    $prepare->data('phone',$phone  );
                    $prepare->data('order_no',$trade  );
                    $prepare->data('ds_id',$ds_id  );
                    $prepare->data('create_time',time()  );
                    $prepare->data('has_success',0  );
                    
                    $prepare->data('openid',$openid  );
                    // $prepare->data('third_serial',$return_arr['prepay_id']  );
                    $prepare->save();
                    
                    
                    return ['code'=>1, 'data'=> $return_arr ];
                }else {
                    Sys::debugxieye("wx:4");
                    return ['code'=>-6, 'message'=> $return_arr['err_code_des']];
                }
                
            }else {
                Sys::debugxieye("wx:5");
                return ['code'=>-5, 'message'=> $return_arr['return_msg']];
            }
        }catch(\Exception $s) {
            Sys::debugxieye("wx:6");
            return ['code'=>-4, 'message'=> '未知的错误异常。'];
        }
        
    }
    
    
    /**
     * 
     * 大赛报名-统一下单，
     *  注意：另外还有一个打赏。
     * 
     * app支付统一下单，目的是获得prepay_id
     * 
     * uid,phone,ds_id，openid
     * 谢烨，应该防止用户重复报名交钱，这个功能最后再做！
     * 
     * 
     */
    public function tongyi_xiadan($ds_id,  $uid, $phone, $openid)
    {
        $trade = \BBExtend\pay\Order::get_order_serial_race();
        $uid = intval($uid);
        $phone = strval($phone);
        $openid = strval($openid);
        
        $ds_id = intval($ds_id);
        $db = Sys::get_container_db();
        $sql ="select * from ds_race where is_active=1 and id = {$ds_id}";
        $ds  = $db->fetchRow($sql);
        if (!$ds) {
            return ['code'=> -1 , 'message' => '大赛不存在或未激活' ];
        }
        $price = $ds['money'];
        $price_fen = strval( intval( $price * 100 )); //转成分。
        if ($ds['money']==0) {
            return ['code'=> -2 , 'message' => '报名无需交钱' ];
        }
        
        // 核查用户是否已经交过钱
        $sql= "select * from ds_money_log where uid={$uid} and ds_id={$ds_id}";
        $row = $db->fetchRow($sql);
        if ($row) {
            return ['code'=> -3 , 'message' => '无需重复交钱。' ];
        }
        
        
        $time = time();
//         if ($time < $ds['register_start_time'] || $time > $ds['register_end_time'] ) {
//             return ['code'=> -3 , 'message' => '报名时间错误，当前不可报名' ];
//         }
        // 这里暂未做防止重复报名的代码。
        // 。。。。。。。。。。 请勿删除此行
        // 。。。。。。。。。。 请勿删除此行
        // 。。。。。。。。。。 请勿删除此行
        
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("怪兽bobo大赛报名");// 谢烨，这是显示在用户个人的微信支付流水里的title，很重要。
        $input->SetAttach("怪兽bobo");     //作用未知
        $input->SetOut_trade_no( $trade );
        $input->SetTotal_fee( $price_fen  ); // 付款金额，注意是分！！
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://bobo.yimwing.com/race/notify/index");//设置我们的服务器异步回调
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid); //必须设置，否则无法支付。
        $return_arr = \WxPayApi::unifiedOrder($input);
        //        appid    appid
        //        mch_id  商户号
        //        nonce_str  随机字符串
        //        prepay_id 预生成订单号
        //        result_code SUCCESS
        //        return_code SUCCESS
        //        return_msg  OK
        //        sign  1...................
        //        trade_type JSAPI
        try{
            if ($return_arr['return_code'] =='SUCCESS') {
                if ($return_arr['result_code']  =='SUCCESS' ) {
                   
                    $prepare = new DsMoneyPrepare();
                    $prepare->data('uid',$uid  );
                    $prepare->data('phone',$phone  );
                    $prepare->data('order_no',$trade  );
                    $prepare->data('ds_id',$ds_id  );
                    $prepare->data('create_time',time()  );
                    $prepare->data('has_success',0  );
                  
                    $prepare->data('openid',$openid  );
                   // $prepare->data('third_serial',$return_arr['prepay_id']  );
                    $prepare->save();
                    
        
                    return ['code'=>1, 'data'=> $return_arr ];
                }else {
                    return ['code'=>-6, 'message'=> $return_arr['err_code_des']];
                }
        
            }else {
                return ['code'=>-5, 'message'=> $return_arr['return_msg']];
            }
        }catch(\Exception $s) {
            return ['code'=>-4, 'message'=> '未知的错误异常。'];
        }
        
    }
    
   
    
    
   
    
}

