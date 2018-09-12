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
    public function sign_buy($advise_id=0, $role_id=0, $uid=0,$token )
    {
        $goods_id = $ds_id =$advise_id=  intval($advise_id);
        $uid = intval($uid);
        
        $role_id = intval( $role_id );
        
        $user =  \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['message'=>'uid err','code'=>0];
        }
        if (!$user->check_token($token)) {
            return ['message'=>'token err','code'=>0];
        }
        
        if ( \BBExtend\model\UserCheck::is_sign_check($uid)===false ) {
            return ['code'=>0, 'message'=>'您是签约用户的话才可以享有免费特权'];
        }
        
        
        
        $db = Sys::get_container_db();
        
        $advise = \BBExtend\model\Advise::find($advise_id);
        if (!$advise) {
            return ['message'=>'id err','code'=>0];
        }
        
        if ( $advise->money_fen ==0 ) {
            return ['message'=>'无需支付金额','code'=>0];
        }
        
        if ( $advise->check_card_count() <3 ) {
            return ['message'=>'卡片数量不足，暂时不能购买','code'=>0];
        }
        if ( $advise->is_active==0  ) {
            return ['message'=>'通告未激活','code'=>0];
        }
        if ( $advise->end_time < time()  ) {
            return ['message'=>'通告已过期','code'=>0];
        }
        
        if ( !$advise->check_max_join_count() ) {
            return ['message'=>'该通告参加人数已满，谢谢您的参与','code'=>0];
            //             $message='该通告参加人数已满，谢谢您的参与';
        }
       
        // 谢烨，现在判断这个人付钱是否合适
        if ($advise->has_join( $uid)) {
            return ['message'=>'您已经参加此通告，不可重复报名','code'=>0];
            
        }
        
        
        
        //否则，应该把订单表中置为成功！
        $advise = \BBExtend\model\Advise::find($advise_id);
        
//         Sys::debugxieye("hui diao :15");
        // xieye，现在要绑定一张试镜卡。
        $db = Sys::get_container_db();
        
        //  用乐观锁死循环，确保用户得到一张卡片。
        while (true) {
            $sql="select * from bb_audition_card
where status=2 and uid=0
and type_id =?
     and online_type=1
";
            $card_row = $db->fetchRow($sql, $advise->audition_card_type );
            
            if(!$card_row){
                exit;
            }
            
            $version_old = $card_row['lock_version'];
            $version_new = $version_old+1;
            
            $where = "id = ". $card_row['id'] . "  and lock_version={$version_old}";
            
            $rows_affected = $db->update('bb_audition_card', [
                    'uid' =>$uid,
                    'lock_version' => $version_new,
                    'status' =>5,
                    'bind_time'=>time(),
                    
            ], $where);
        //    Sys::debugxieye("hui diao :xunhuan");
            if ($rows_affected) {
                break;
            }
        }
    //    Sys::debugxieye("hui diao :16");
        
        
        // 现在，插入到报名表当中。
//         $json = $prepare->json_parameter;
//         $json_arr = json_decode($json,1);
        
//         $role_id = $json_arr['role_id'];
         $card_id = $card_row['id'];
        
//         // 对试镜卡表做状态修改。
//         $sql ="update bb_audition_card set has_pay=1 where id=?";
//         $db->query($sql,[ $card_id ]);
        
        // 调用通用的接口。
         \BBExtend\model\Advise::public_advise_join($advise_id, $role_id, $uid, $card_id);
        Sys::debugxieye("hui diao :17");
        // 返回阿里和微信支付 各自的回复。
        return ['code'=>1 ];
        
        
    }
   
    /**
     * 购买或者兑换物品下订单
     * 
     * paytype ali或者wx
     * mobile 只有两个值，android或者ios
     */
    public function buy($advise_id=0, $role_id=0, $uid=0,  $paytype='', $mobile='',$token )
    {
        $goods_id = $ds_id =$advise_id=  intval($advise_id);
        $uid = intval($uid);
        
        $role_id = intval( $role_id );
        
        $user =  \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['message'=>'uid err','code'=>0];
        }
        if (!$user->check_token($token)) {
            return ['message'=>'token err','code'=>0];
        }
        
        if ( \BBExtend\model\UserCheck::is_phone_renzheng($uid)===false ) {
            return ['code'=>0, 'message'=>'您需要先绑定手机号才可以使用此功能。'];
        }
        
        
        
        $db = Sys::get_container_db();
        
        $advise = \BBExtend\model\Advise::find($advise_id);
        if (!$advise) {
            return ['message'=>'id err','code'=>0];
        }
        
        if ( $advise->money_fen ==0 ) {
            return ['message'=>'无需支付金额','code'=>0];
        }
        
        if ( $advise->check_card_count() <3 ) {
            return ['message'=>'卡片数量不足，暂时不能购买','code'=>0];
        }
        if ( $advise->is_active==0  ) {
            return ['message'=>'通告未激活','code'=>0];
        }
        if ( $advise->end_time < time()  ) {
            return ['message'=>'通告已过期','code'=>0];
        }
        
        // xieye ,现在查条件。
        if ( !$advise->can_join_by_auth( $uid ) ) {
            return ['message'=>$advise->get_msg() ,'code'=>0];
        }
        if ( !$advise->check_max_join_count() ) {
            return ['message'=>'该通告参加人数已满，谢谢您的参与','code'=>0];
            //             $message='该通告参加人数已满，谢谢您的参与';
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
       $uid = $user->uid;
       
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money_fen=1;
        }
        $price_fen = strval( $money_fen); //转成分。
        $title = $advise->title;
        $title_goods = strval( $title );
        
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
        
        
        $money = \BBExtend\common\Numeric::decimal($money);
        if ($money< 0.01) {
            $money=0.01;
        }
        
        $uid = $user->uid;
        
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money_fen=1;
            $money=0.01;
        }
        $price_fen = strval( $money_fen); //转成分。
        $title = $advise->title;
        $title_goods = strval( $title );
        
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
            "seller_id"=> config('wechat.ali_seller_id') , //商户帐号
            
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