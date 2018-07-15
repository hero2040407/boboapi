<?php
namespace app\shop\controller;
// use BBExtend\BBShop;
// use think\Db;
// use think\Controller;
use BBExtend\Sys;
// use app\race\model\DsMoneyPrepare;
// use app\race\model\DsMoneyLog;
// use app\race\model\DsRegisterLog;


/**
 * 
 * 通告报名
 * 
 * User: xieye
 */
class Advise 
{
    
    
   
    /**
     * 购买或者兑换物品下订单
     * 
     * paytype ali或者wx
     * mobile 只有两个值，android或者ios
     */
    public function buy($advise_id=0, $role_id=0, $uid=0,  $paytype='', $mobile='' )
    {
        $goods_id = $ds_id =$advise_id=  intval($advise_id);
        $uid = intval($uid);
        
        $role_id = intval( $role_id );
        
        $user =  \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['message'=>'uid err','code'=>0];
        }
        
        $db = Sys::get_container_db();
        
        $advise = \BBExtend\model\Advise::find($advise_id);
        if (!$advise) {
            return ['message'=>'id err','code'=>0];
        }
        
        if ( $advise->money_fen ==0 ) {
            return ['message'=>'无需支付金额','code'=>0];
        }
        
        
        
        if ( !in_array($paytype, array('ali','wx'))  ) {
            return ['message'=>'支付类型错误','code'=>0];
        }
        if (!in_array($mobile, array('ios', 'android'))) {
            return ['message'=>'mobile字段错误','code'=>0];
        }
        
       
        // 谢烨，现在判断这个人付钱是否合适
        if ($advise->has_join( $uid)) {
            return ['message'=>'您已经参加此通告，不可重复报名','code'=>0];
            
        }
        
        switch ($paytype)
        {
            case 'ali':
                return $this->pay_money_ali($user, $advise,   $mobile ,$role_id);
            case 'wx':    
                return $this->pay_money_wx($user, $advise,   $mobile ,$role_id);
          
        }
    }
    
    /**
     * 微信下报名订单
     *
     * @param number $goods_id
     * @param number $uid
     */
    private function pay_money_wx($user, $advise,   $mobile,$role_id )
    {
        $money_fen = $advise->money_fen;
       $uid = $user->id;
       
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money_fen=1;
        }
        $price_fen = strval( $money_fen); //转成分。
        $title = $advise->title;
        $title_goods = strval( title );
        
        //既然条件都对，生成订单号，最后插入临时订单表。
        $user_agent = \think\Request::instance()->header('User-Agent');
        $user_agent = strval($user_agent);
        
        $serial = $this->get_order_serial($mobile);//订单号
        
        
        $prepare = new \BBExtend\model\BaomingOrderPrepare();
        $prepare->uid=$uid  ;
        $prepare->ds_id=$advise->id  ;
        $prepare->serial = $serial ;
        $prepare->price_fen = $money_fen ;
        $prepare->newtype = 3;
        $prepare->third_name = 'wx';
        $prepare->create_time = time();
        $prepare->is_success = 0;

        $prepare->json_parameter = \BBExtend\common\Json::encode(['role_id' => $role_id  ]  );
        
        $prepare->save();
        
    //现在开始发送统一订单接口
     
        $help = new \BBExtend\pay\wxpay\Help();
        return $help->tongyi_xiadan("报名：".$title_goods, $serial, (int)$price_fen);
    }
    
   
    
    /**
     * 阿里下购物订单
     * 
     * @param number $goods_id
     * @param number $uid
     * @param number $type
     * @param number $address_id
     */
    private function pay_money_ali($user, $advise,   $mobile,$role_id)
    {
        $money_fen = $advise->money_fen;
        $money = $money_fen/100;
        $uid = $user->id;
        
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money_fen=1;
        }
        $price_fen = strval( $money_fen); //转成分。
        $title = $advise->title;
        $title_goods = strval( title );
        
        //既然条件都对，生成订单号，最后插入临时订单表。
        $user_agent = \think\Request::instance()->header('User-Agent');
        $user_agent = strval($user_agent);
        
        $serial = $this->get_order_serial($mobile);//订单号
        
        
        $prepare = new \BBExtend\model\BaomingOrderPrepare();
        $prepare->uid=$uid  ;
        $prepare->ds_id=$advise->id  ;
        $prepare->serial = $serial ;
        $prepare->price_fen = $money_fen ;
        $prepare->newtype = 3;
        $prepare->third_name = 'ali';
        $prepare->create_time = time();
        $prepare->is_success = 0;
        
        $prepare->json_parameter = \BBExtend\common\Json::encode(['role_id' => $role_id  ]  );
        
        $prepare->save();
        
        
        $help = new \BBExtend\pay\alipay\AlipayHelp();
        $sign_urlencode = $help->sign( [
            'out_trade_no' => $serial,
            'subject'=> $title_goods,
            "body" => $title_goods,
            "total_fee"    => $money ,         //订单总价
            ] ); 
        
        //返回给客户端
        $data =[
            "out_trade_no"=>$serial, //服务器生成的订单号
            "total_fee"=>$money,         //订单总价
            "notify_url"=> ali_gateway(), // 异步回调地址，是服务端的
            "subject"=>$title_goods,   //商品的名称
            "partner"=>"2088421400078132",             //开发者帐号
            "seller_id"=>ALIPAY_SELLER_ID, //商户帐号
            "body"=>$title_goods,      //商品详细描述
            "all_request" => $sign_urlencode,
        ];
         return ["data"=>$data, "code"=>1 ];
        
    }
    
   
    //产生订单号
    // 
    private  function get_order_serial($mobile)
    {
        $pre = $mobile=="ios" ? 'TGI':'TGA';
        
        $orderSn = $pre .date("Ymd") . strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) .  substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
   
    
    
    
    
    
}