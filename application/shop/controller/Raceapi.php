<?php
namespace app\shop\controller;
// use BBExtend\BBShop;
use think\Db;
use think\Controller;
use BBExtend\Sys;
use app\race\model\DsMoneyPrepare;
use app\race\model\DsMoneyLog;
use app\race\model\DsRegisterLog;

/**
 * 
 * 商城主要类，包括地址管理，支付宝回调接口，商品下单。
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/8/3
 * Time: 11:42
 */
class Raceapi extends Controller
{
    
    
    /**
     * 微信回调
     */
    public function wxpay_notify()
    {
        $help = new \BBExtend\pay\wxpay\Help();
        $result = $help->receive_post();
        echo $result;
    }
    
    /**
     * 这个接口非常特别，是支付宝的返回接口
     * 包括充值，和购物两个处理流程。
     * 注意安卓充值也在这里。
     */
    public function alipay_notify()
    {
        $help = new \BBExtend\pay\alipay\AlipayHelp();
        $result = $help->receive_ali_post();
        echo $result;
    }
    
    /**
     * 购买或者兑换物品下订单
     * 
     * 测试网址　 现金购买：支付宝
     * /shop/api/buy/mobile/android/paytype/ali/uid/10046/goods_id/1/address_id/1/count/1
     *   /standard/%E4%B8%AD%E5%B0%BA%E5%AF%B8/style/%E9%BB%91%E8%89%B2

     * 测试网址　 现金购买：微信
     * /shop/api/buy/mobile/android/paytype/wx/uid/10046/goods_id/1/address_id/1/count/1
     *   /standard/%E4%B8%AD%E5%B0%BA%E5%AF%B8/style/%E9%BB%91%E8%89%B2
     * 
     * paytype ali或者wx
     * mobile 只有两个值，android或者ios
     */
    public function buy($goods_id=0, $uid=0,  $paytype='', $mobile='' )
    {
        $goods_id = $ds_id =  intval($goods_id);
        $uid = intval($uid);
      //  $type = intval($type);
        
        
        $user =  \app\shop\model\Users::get($uid);
        $db = Sys::get_container_db();
        $sql ="select * from ds_race where level=1  and is_active=1  and id = {$ds_id}";
        $ds = $db->fetchRow($sql);
        if (!$ds) {
            return ['code'=>0, 'message'=>'大赛不存在'];
        }
        
        if ($ds['money'] <0.01) { //不能写==0
            return ['code'=>0, 'message'=>'大赛无需支付报名费'];
        }
        
        if ( !in_array($paytype, array('ali','wx'))  ) {
            return ['message'=>'支付类型错误','code'=>0];
        }
        if (!in_array($mobile, array('ios', 'android'))) {
            return ['message'=>'mobile字段错误','code'=>0];
        }
        
       
        // 谢烨，现在判断这个人付钱是否合适
//         $status_arr = \BBExtend\video\Race::get_user_race_status($uid, $ds_id);
        
        
        $status_arr =  \BBExtend\video\RaceStatus::get_status_v5($uid, $ds_id);
        
        if ($status_arr['code']==0) {
            return ['code'=>0, 'message'=>$status_arr['message']];
        }
        if ($status_arr['data']['status'] != 3 ) {
            return ['code'=>0, 'message'=>'请先完成报名流程'];
        }
        
        switch ($paytype)
        {
            case 'ali':
                return $this->pay_money_ali($user, $ds, $goods_id, $uid,  $mobile );
            case 'wx':    
                return $this->pay_money_wx( $user,$ds, $goods_id, $uid,  $mobile);
          
        }
    }
    
    /**
     * 微信下大赛报名订单
     *
     * @param number $goods_id
     * @param number $uid
     */
    private function pay_money_wx($user, $ds,  $goods_id=0, $uid=0, $mobile)
    {
        //现金购买无需判断商品等级和 波币数量。
        //查商品价格
        //查波币数量
        $money = $ds['money'];
       
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money = 0.01;
        }
        $price_fen = strval( intval( $money * 100 )); //转成分。
        
        $title_goods = $ds['title'];
        if (!$title_goods) {
            $title_goods ='报名费用';
        }
        
        //既然条件都对，生成订单号，最后插入临时订单表。
        $user_agent = \think\Request::instance()->header('User-Agent');
        $user_agent = strval($user_agent);
        
        $serial = \BBExtend\pay\Order::get_order_serial_race();
        
        
        $prepare = new DsMoneyPrepare();
        $prepare->data('uid',$uid  );
        $prepare->data('phone',''  );
        $prepare->data('order_no',$serial  );
        $prepare->data('ds_id',$goods_id  );
        $prepare->data('create_time',time()  );
        $prepare->data('has_success',0  );
        
        $prepare->data('openid',''  );
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
    private function pay_money_ali($user,$ds, $goods_id, $uid=0,   $mobile)
    {
         $money = $ds['money'];
       
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money = 0.01;
        }
      //  $price_fen = strval( intval( $price * 100 )); //转成分。
        
        $title_goods = $ds['title'];
        if (!$title_goods) {
            $title_goods ='报名费用';
        }
        
        //既然条件都对，生成订单号，最后插入临时订单表。
        $user_agent = \think\Request::instance()->header('User-Agent');
        $user_agent = strval($user_agent);
        
        $serial = \BBExtend\pay\Order::get_order_serial_race();
        
        
        $prepare = new DsMoneyPrepare();
        $prepare->data('uid',$uid  );
        $prepare->data('phone',''  );
        $prepare->data('order_no',$serial  );
        $prepare->data('ds_id',$goods_id  );
        $prepare->data('create_time',time()  );
        $prepare->data('has_success',0  );
        
        $prepare->data('openid',''  );
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
            "seller_id"=> config('wechat.ali_seller_id') ,  //商户帐号
            "body"=>$title_goods,      //商品详细描述
            "all_request" => $sign_urlencode,
        ];
         return ["data"=>$data, "code"=>1 ];
        
    }
    
    /**
     * 用bo币购买商品
     * 
     * 注意，这里只是波币的逻辑，现金的逻辑在app\pay\model\Users.php里面。
     *
     * 条件，波币数量必须对，用户等级必须大于等于商品等级。
     * 如果成功，则扣减波币，然后记录到波币扣减日志，然后，发送消息。
     * 然后，生成成功订单到order表中。
     * 就完成了！
     *
     * /shop/api/buy/type/2/uid/10046/goods_id/1/address_id/1/count/2
     *   /standard/%E4%B8%AD%E5%B0%BA%E5%AF%B8/style/%E9%BB%91%E8%89%B2
     *
     * @param \app\shop\model\Users $user
     * @param number $goods_id
     * @param number $uid
     * @param number $address_id
     * @param number $count 商品数量
     * @param number $stardand 规格
     * @param number $style    样式
     * 
     */
    private function pay_coin($user,$address,$goods,
            $goods_id=0, $uid=0,  $address_id=0, $count=1,$standard='',$style='',
            $mobile) 
    {
        $userinfo = $user->get_buy_info();
        //查商品等级
        if ($userinfo['level'] < $goods->getData("exchange_level")  ){
            $message = "真遗憾，这件礼物需要宝贝达到Lv." . $goods->getData("exchange_level").
                "才能兑换哦～\n".
                "快快去秀场完成今天的任务赚取更多经验值吧～";
            return ["message"=>$message,'code'=>0 ];
        }
        //查波币数量
        $pay_bobi = $goods->right_currency();
        
        if ($pay_bobi < 0) {
            return ["message"=>"该商品不能用BO币购买",'code'=>0 ]; 
        }
        $pay_bobi = $pay_bobi * $count;    
        $gold = $userinfo['gold'];
        if ($gold < $pay_bobi) {
            $message = "真遗憾，宝贝您的BO币还不足够兑换这件礼物哦～\n".
            "快快去秀场完成今天的任务换取更多的BO币或者直接充值兑换吧～";
            return ["message"=>$message,'code'=>\BBExtend\fix\Err::code_yuebuzu ];
        }
        
        
        //既然条件都对，生成订单号，最后插入订单表。
        $serial = \BBExtend\pay\Order::get_order_serial_race();
        $user_agent = \think\Request::instance()->header('User-Agent');
        $order = new \app\shop\model\Order();
        $order->data('uid', $uid);
        $order->data('address_id', $address_id);
        $order->data('price', $pay_bobi);
        $order->data('type', 2);//2 波币购买
        $order->data('goods_id', $goods_id);
        $order->data('serial', $serial);
        $order->data('is_success', 1);
        $order->data('terminal', strval($user_agent));
//         $order->data('create_time', time());
//         $order->data('update_time', 0);
        $order->data('count', $count);
        $order->data('model', $standard);
        $order->data('style', $style);
        $order->data("terminal_type", ($mobile=="ios"?1:2) );
        $order->save();
        
        //扣减用户波币，并记录日志
        $user->buy_success_coin($pay_bobi,$serial, $count);
        //返回给客户端
        $data = [
            "out_trade_no" => $serial, //服务器生成的订单号
            "total_fee"    => $order->price,         //订单总价
            "subject"      => $goods->title,   //商品的名称
        ];
        return ["data"=>$data, "code"=>1 ];
    
    }
    
    
 
    
    
}