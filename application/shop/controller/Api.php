<?php
namespace app\shop\controller;

use think\Db;
use think\Controller;
use BBExtend\Sys;
use BBExtend\common\Client;
use BBExtend\fix\TableType;
use BBExtend\model\User;

/**
 * 商城主要类，包括地址管理，支付宝回调接口，商品下单。
 * 
 * @author xieye
 */
class Api extends Controller
{
    const ali_partner   = "2088421400078132";    //阿里 开发者帐号
    const ali_seller_id = ALIPAY_SELLER_ID;     //商户帐号
    
    /**
     * 这段代码是加一个校验，保证交易安全 
     */
    public function _initialize()
    {
//         $request = request();
//         $chekc_action =['buy', 'get_default_address', 'get_address_list', 'add_address', 
//                 'editor_address','del_address', ];
//         if ( in_array( $request->action(), $chekc_action )) {
//             $help = new \BBExtend\pay\Sign();
//             $result = $help->check(input('param.v'), input('param.uid'), 
//                 input('param.time'), input('param.sign')      );
//             if (!$result) {
//                 echo json_encode(["code"=>0, "message"=>$help->get_info() ] , 
//                     JSON_UNESCAPED_UNICODE);
//                 exit();
//             }
//         }
    }
    
    
    /**
     * 微信服务器对我的回调，重要
     */
    public function wxpay_notify()
    {
        $help = new \BBExtend\pay\wxpay\Help();
        $result = $help->receive_post();
        echo $result;
    }
    
     
    /**
     * 支付宝服务器对我的回调
     * 
     * 包括充值，和购物两个处理流程。
     * 注意安卓充值也在这里。
     */
    public function alipay_notify()
    {
        $help = new \BBExtend\pay\alipay\AlipayHelp();
        $result = $help->receive_ali_post();
        echo $result;
    }
    
    
    
    public function buy_vip($uid, $token,$paytype)
    {
        $uid      = intval($uid);
//         if (!in_array($mobile, array('ios', 'android'))) {
//             return ['message'=>'mobile字段错误','code'=>0];
//         }
        if ( !in_array($paytype, array('ali','wx', ))  ) {
            return ['message'=>'支付类型错误','code'=>0];
        }
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'uid error'];
        }
        
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        // 如果以前有成功支付50元的经历，则不给通过。
        $db = Sys::get_container_db();
        $sql="select count(*) from bb_vip_application_log
               where status=1
                 and uid =?
";
        $result = $db->fetchOne($sql,[ $uid ]);
        if ($result) {
            return ['code'=>0,'message'=>'vip申请费用不可以重复缴纳'];
        }
        
        $serial = $this->get_vip_order_serial();     //订单号
        $title_goods = "VIP小童星申请费用";
        $money = 50;
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money = 0.01;
        }
        if ( $paytype=='ali' ) {
            $db->insert("bb_baoming_order_prepare", [
                    "uid" =>$uid,
                    "price" => $money,
                    "type"  =>1,
                    "ds_id" => 0,
                    "info"  => '',
                    
                    "is_success"=>0,
                    "create_time" => time(),
                    "third_name"=>'ali',
                    "serial"=> $serial,
                    "newtype" =>2,
            ]);
            $help = new \BBExtend\pay\alipay\AlipayHelp();
            
            $sign_urlencode = $help->sign( [
                    'out_trade_no' => $serial,
                    'subject'=> $title_goods,
                    "body" => $title_goods,
                    "total_fee"    => $money ,         //订单总价
            ] );
            //返回给客户端
            $data = [
                    "out_trade_no"   => $serial,         //服务器生成的订单号
                    "total_fee"      => $money,  //订单总价
                    "notify_url"     => ali_gateway(),   // 异步回调地址，是服务端的
                    "subject"        => $title_goods,    //商品的名称
                    "partner"        => self::ali_partner,   //开发者帐号
                    "seller_id"      => self::ali_seller_id, //商户帐号
                    "body"           => $title_goods,    //商品详细描述
                    "all_request"    => $sign_urlencode, // 具体的请求数据
            ];
            return ["data"=>$data, "code"=>1 ];
        }
        
        if ( $paytype=='wx' ) {
            $db->insert("bb_baoming_order_prepare", [
                    "uid" =>$uid,
                    "price" => $money,
                    "type"  =>1,
                    "ds_id" => 0,
                    "info"  => '',
                    
                    "is_success"=>0,
                    "create_time" => time(),
                    "third_name"=>'wx',
                    "serial"=> $serial,
                    "newtype" =>2,
            ]);
            //现在开始发送统一订单接口
            $price = $money * 100;
            $price = (int)$price;
            $help = new \BBExtend\pay\wxpay\Help();
            return $help->tongyi_xiadan($title_goods, $serial, $price);
        }
    }
    
    
    /**
     * 购买或者兑换物品下订单
     * 
     * 这是个大接口，几乎涉及所有的关于商品的交易
     * 
     * @param int $goods_id    商品id
     * @param int $uid         用户id
     * @param int $address_id  地址id
     * @param int $count       数量
     * @param string $standard 规格
     * @param string $style    样式
     * @param string $paytype  'ali','wx', 'bo', 'score'，'vip',vip是201803新增。
     * @param string $mobile   'ios', 'android'
     * 
     * @author xieye
     */
    public function buy($goods_id=0, $uid=0,  $address_id=0,
            $count=1, $standard='', $style='',$paytype='', $mobile='' )
    {
        $goods_id = intval($goods_id);
        $uid      = intval($uid);
        $address_id = intval($address_id);
        $count    = intval($count);
        $standard = strval($standard);
        $style    = strval($style);
        $user =  \app\shop\model\Users::get($uid);
        $address =  \app\shop\model\Address::get($address_id);
        if (!$address) {
            return ["message"=>'地址不存在','code'=>0 ];
        }
        $goods =  \app\shop\model\ShopGoods::get($goods_id);
        if (!$goods) {
            return ["message"=>'商品不存在','code'=>0 ];
        }
        if ($count <=0 || $count > 100) {
            return ["message"=>'商品数量错误','code'=>0 ];
        }
        if ( $goods->getData("inventory") <= 0 ) {
            return ["message"=>'库存数量不足','code'=>0 ];
        }
        if ( !in_array($paytype, array('ali','wx', 'bo', 'score'))  ) {
            return ['message'=>'支付类型错误','code'=>0];
        }
        if (!in_array($mobile, array('ios', 'android'))) {
            return ['message'=>'mobile字段错误','code'=>0];
        }
        if( !$user ) {
            return ['message'=>'没有这个用户！','code'=>-100];
        }
        switch ($paytype)
        {
            case 'ali':
                return $this->pay_money_ali($user,$address,$goods,  $goods_id, $uid,  
                    $address_id, $count, $standard, $style,$mobile );
            case 'wx':    
                return $this->pay_money_wx( $user,$address,$goods,  $goods_id, $uid,  
                    $address_id, $count, $standard, $style ,$mobile);
            case 'bo':
                return $this->pay_coin(  $user,$address,$goods,     $goods_id, $uid,  
                    $address_id, $count, $standard, $style,$mobile );
            case 'score':
                return $this->pay_score(  $user,$address,$goods,    $goods_id, $uid,  
                    $address_id, $count, $standard, $style,$mobile );
        }
    }
    
    
    /**
     * 微信支付，201707，大赛特别收费
     * @param number $ds_id
     * @param number $uid
     */
    public function pay_ds($ds_id=0, $uid=0 )
    {
        $ds_id=intval($ds_id);
        $uid=intval($uid);
        $user =  \app\shop\model\Users::get($uid);
        if(!$user)
        {
            return ['message'=>'没有这个用户！','code'=>-100];
        }
        $db = Sys::get_container_db();
        $sql="select * from bb_baoming where uid={$uid} and ds_id={$ds_id} and is_success=0 ";
        $result = $db->fetchAll($sql);
        if (count( $result )  !=1 ) {
            return ['message'=>'您已支付成功，无需重复支付。','code'=>0];
        }
        $result = $result[0];
        
        // 谢烨，现在我要生成预订单
        $money = $result["price"];
        if ($money<0) {
            return ["message"=>'该商品不能用现金购买','code'=>0 ];
        }
        if ( in_array($uid, get_test_userid_arr() )  ) { //对于内部测试人员
            $money = 0.01;
        }
        
        //既然条件都对，生成订单号，最后插入临时订单表。
        $title_goods = $result["info"] ? $result["info"] : '支付' ;
        $serial = $this->get_baoming_order_serial();//订单号
        $db->insert("bb_baoming_order_prepare", [
            "uid" =>$uid,
            "price" => $money,
            "ds_id" => $ds_id,
            "info"  => $title_goods,
            "create_time" => time(),
            "serial"=> $serial,
        ]);
        
        //修改 货币单位， 元改为分 
        $price = $money * 100;
        //现在开始发送统一订单接口
        $help = new \BBExtend\pay\wxpay\Help();
        return $help->tongyi_xiadan($title_goods, $serial, (int)$price);
    }
    
    
    /**
     * 微信下购物订单，现金购买
     *
     */
    private function pay_money_wx($user,$address,$goods, $goods_id=0, $uid=0,  
            $address_id=0, $count=1, $standard='', $style='' ,$mobile)
    {
        //现金购买无需判断商品等级和 波币数量。
        //查商品价格
        //查波币数量
        $money = $goods->right_money();
        if ($money<0) {
            return ["message"=>'该商品不能用现金购买','code'=>0 ];
        }
        $money = $money * $count;
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money = 0.01;
        }
        
        $title_goods = $goods->getData('title');
        if (!$title_goods) {
            $title_goods ='商品';
        }
        
        //既然条件都对，生成订单号，最后插入临时订单表。
        $serial = $this->get_order_serial($mobile);//订单号
        $order = new \app\shop\model\ShopOrderPrepare();
        $order->uid = $uid;
        $order->goods_id = $goods_id;
        $order->address_id = $address_id;
        $order->serial = $serial;
        $order->data('type', TableType::bb_shop_order__type_xianjin ); 
        $order->price = $money;
        $order->is_success=0;//等订单正式生成，要改这个字段。
        $order->data('count', $count);
        $order->data('model', $standard);
        $order->data('style', $style);
        $order->data('terminal', Client::user_agent() );
        $order->data("terminal_type", ($mobile=="ios"?1:2));
        $order->data("third_name",   'wx');
        $order->data("third_serial",  "");
        $order->save();
    
        //现在开始发送统一订单接口
        $price = $money * 100;
        $price = (int)$price;
        $help = new \BBExtend\pay\wxpay\Help();
        return $help->tongyi_xiadan("购物：". $title_goods, $serial, $price);
    }
    
    
    /**
     * 阿里下购物订单
     * 
     */
    private function pay_money_ali($user,$address,$goods, $goods_id=0, $uid=0,  
            $address_id=0, $count=1, $standard='', $style='' ,$mobile)
    {
        //现金购买无需判断商品等级和 波币数量。
        //查商品价格，查波币数量
        $money = $goods->right_money();
        if ($money<0) {
            return ["message"=>'该商品不能用现金购买','code'=>0 ];
        }
        $money = $money * $count;
        if ( in_array($uid, get_test_userid_arr() )  ) {
            $money = 0.01;
        }
        //既然条件都对，生成订单号，最后插入临时订单表。
        $serial = $this->get_order_serial($mobile);     //订单号
        $order = new \app\shop\model\ShopOrderPrepare();
        $order->uid = $uid;
        $order->goods_id = $goods_id;
        $order->address_id = $address_id;
        $order->serial = $serial;
        $order->data('type', TableType::bb_shop_order__type_xianjin ); 
        $order->price = $money;
        $order->is_success=0;//等订单正式生成，要改这个字段。
        $order->data('count', $count);
        $order->data('model', $standard);
        $order->data('style', $style);
        $order->data('terminal', Client::user_agent() );
        $order->data("terminal_type", ($mobile == "ios" ? 1 : 2) );
        $order->data("third_name",   'ali');
        $order->data("third_serial", "");
        $order->save();
        // 之所以这么做，数据库字段确保了只有两位小数，比较好，直接取
        $temp = Db::table('bb_shop_order_prepare')->where("id",$order->getData('id'))->find();
        
        $title_goods = $goods->getData('title');
        if (!$title_goods) {
            $title_goods ='商品';
        }
        
        $help = new \BBExtend\pay\alipay\AlipayHelp();
        $sign_urlencode = $help->sign( [
            'out_trade_no' => $serial,
            'subject'=> $title_goods,
            "body" => $title_goods,
            "total_fee"    => $temp['price'] ,         //订单总价
            ] ); 
        //返回给客户端
        $data = [
            "out_trade_no"   => $serial,         //服务器生成的订单号
            "total_fee"      => $temp['price'],  //订单总价
            "notify_url"     => ali_gateway(),   // 异步回调地址，是服务端的
            "subject"        => $title_goods,    //商品的名称
            "partner"        => self::ali_partner,   //开发者帐号
            "seller_id"      => self::ali_seller_id, //商户帐号
            "body"           => $title_goods,    //商品详细描述
            "all_request"    => $sign_urlencode, // 具体的请求数据
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
     */
    private function pay_coin($user,$address,$goods, $goods_id=0, $uid=0,  
            $address_id=0, $count=1,$standard='',$style='', $mobile) 
    {
        $userinfo = $user->get_buy_info();
        //查商品等级
        if ($userinfo['level'] < $goods->getData("exchange_level")  ){
            $message = "真遗憾，这件礼物需要宝贝达到Lv." . $goods->getData("exchange_level").
                "才能兑换哦～\n". "快快去秀场完成今天的任务赚取更多经验值吧～";
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
        $serial = $this->get_order_serial($mobile);
        $order = new \app\shop\model\Order();
        $order->data('uid', $uid);
        $order->data('address_id', $address_id);
        $order->data('price', $pay_bobi);
        $order->data('type', TableType::bb_shop_order__type_bobi );
        $order->data('goods_id', $goods_id);
        $order->data('serial', $serial);
        $order->data('is_success', 1);
        $order->data('terminal', Client::user_agent() );
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

    
    /**
     * 用积分兑换商品
     *
     * 20171018
     * 
     */
    private function pay_score($user,$address,$goods, $goods_id=0, $uid=0,  
            $address_id=0, $count=1,$standard='',$style='', $mobile)
    {
        $userinfo = $user->get_buy_info();
        //查商品等级
        if ($userinfo['level'] < $goods->getData("exchange_level")  ){
            $message = "真遗憾，这件礼物需要宝贝达到Lv." . $goods->getData("exchange_level").
            "才能兑换哦～\n". "快快去秀场完成今天的任务赚取更多经验值吧～";
            return ["message"=>$message,'code'=>0 ];
        }
        //查积分数量
        $pay_bobi = $goods->right_score();
        if ($pay_bobi < 0) {
            return ["message"=>"该商品不能用积分兑换",'code'=>0 ];
        }
        $pay_bobi = $pay_bobi * $count;
        $gold = $userinfo['score'];
        if ($gold < $pay_bobi) {
            $message = "真遗憾，宝贝您的积分还不足够兑换这件礼物哦～\n".
                    "快快去秀场完成今天的任务换取更多的积分吧～";
            return ["message"=>$message,'code'=>\BBExtend\fix\Err::code_jifen_buzu ];
        }
    
        //既然条件都对，生成订单号，最后插入订单表。
        $serial = $this->get_order_serial($mobile);
        $order = new \app\shop\model\Order();
        $order->data('uid', $uid);
        $order->data('address_id', $address_id);
        $order->data('price', $pay_bobi);
        $order->data('type', TableType::bb_shop_order__type_jifen );
        $order->data('goods_id', $goods_id);
        $order->data('serial', $serial);
        $order->data('is_success', 1);
        $order->data('terminal', Client::user_agent());
        $order->data('count', $count);
        $order->data('model', $standard);
        $order->data('style', $style);
        $order->data("terminal_type", ($mobile=="ios"?1:2) );
        $order->save();
    
        //扣减用户波币，并记录日志
        $user->buy_success_score($pay_bobi,$serial, $count);
        //返回给客户端
        $data = [
            "out_trade_no" => $serial, //服务器生成的订单号
            "total_fee"    => $order->price,   //订单总价
            "subject"      => $goods->title,   //商品的名称
        ];
        return ["data"=>$data, "code"=>1 ];
    }
     
    
    private function get_vip_order_serial()
    {
        $pre = "XX1";
        $orderSn = $pre .date("Ymd") . strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) .  substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }

    
    /**
     * 产生订单号
     * 
     * @param unknown $mobile
     */
    private  function get_order_serial($mobile)
    {
        $pre = $mobile=="ios" ? 'BI':'BA';
        $orderSn = $pre .date("Ymd") . strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) .  substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
    

    /**
     * 特别收费  产生订单号
     * 
     * 201707
     */
    private  function get_baoming_order_serial()
    {
        $pre = $mobile="BM";
        $orderSn = $pre .date("Ymd") . strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) .  substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
    
   
    /**
     * 兑换
     * xieye 20170628
     */
    public function exchange($goods_id=0, $uid=0,  $address_id=0,
             $standard='', $style='',$mobile='',$userlogin_token='')
    {
        $exchange_count=50;
        
        $count=1;
        $goods_id = intval($goods_id);
        $uid = intval($uid);
        //  $type = intval($type);
        $address_id = intval($address_id);
        $standard = strval($standard);
        $style = strval($style);
        $db = Sys::get_container_db();
        if (!in_array($mobile, array('ios', 'android'))) {
            return ['message'=>'mobile字段错误','code'=>0];
        }
        
        if (!\BBExtend\BBUser::validation_token($uid,$userlogin_token))
        {
            return ['message'=>'非法操作','code'=>0];
        }
        
        $user =  \app\shop\model\Users::get($uid);
        $address =  \app\shop\model\Address::get($address_id);
        if (!$address) {
            return ["message"=>'地址不存在','code'=>0 ];
        }
        $goods =  \app\shop\model\ShopGoods::get($goods_id);
        if (!$goods) {
            return ["message"=>'商品不存在','code'=>0 ];
        }
        if ($count <=0 || $count > 100) {
            return ["message"=>'商品数量错误','code'=>0 ];
        }
        if ( $goods->getData("inventory") <= 0 ) {
            return ["message"=>'库存数量不足','code'=>0 ];
        }
        $code = \app\user\model\Exists::userhExists($uid);
        if($code!=1)
        {
            return ['message'=>'没有这个用户！','code'=>$code];
        }
        
        //查碎片
//         $sql="select id 
//                 from lt_user_owner 
//                where lt_type=5 and uid = {$uid} 
//                  and is_use=0
//                  and bonus_id= {$goods_id}
//                order by id asc limit {$exchange_count}";
//         $suipian_ids = $db->fetchCol($sql);
//         $suipian_ids=(array)$suipian_ids;
//         if ( count($suipian_ids) < $exchange_count  ) {
//             $message = "真遗憾，您的兑换券数量还不足够兑换这件礼物哦～";
//             return ["message"=>$message,'code'=>0 ];
//         }
        $sql="select id
                from lt_user_owner
               where lt_type=5 and uid = {$uid}
                 and is_use=0
                 and bonus_id= {$goods_id}
               order by id asc 
               limit 1";
        $lt_id = $db->fetchOne($sql);
        if ( $lt_id < 1  ) {
            $message = "真遗憾，您的兑换券数量还不足够兑换这件礼物哦～";
            return ["message"=>$message,'code'=>0 ];
        }
        
        
        //既然条件都对，生成订单号，最后插入订单表。
        $serial = $this->get_order_serial($mobile);
        $user_agent = \think\Request::instance()->header('User-Agent');
        $order = new \app\shop\model\Order();
        $order->data('uid', $uid);
        $order->data('address_id', $address_id);
        $order->data('price', 0);
        $order->data('type', TableType::bb_shop_order__type_suipian );
        $order->data('goods_id', $goods_id);
        $order->data('serial', $serial);
        $order->data('is_success', 1);
        $order->data('terminal', strval($user_agent));
        $order->data('count', $count);
        $order->data('model', $standard);
        $order->data('style', $style);
        $order->data("terminal_type", ($mobile=="ios"?1:2) );
        $order->save();
        
        //扣减兑换卷
       // $sql = "update lt_user_owner set is_use=1 where id in (". implode(',', $suipian_ids) .")";
        
        $sql = "update lt_user_owner 
                   set is_use=1  
                 where id={$lt_id}
                ";
        
        $db->query($sql);
        $order = \app\shop\model\Order::get(['serial' => $serial ]);
        
        $count2 = $order->getData('count');
        $xiao = $this->get_xiaoliang($order->getData('goods_id'));
    
        $kucun = $goods->getData("inventory") - $count2;
        $kucun = ($kucun <0) ? 0: $kucun;
        $goods->setAttr('sell_num', $xiao);
        $goods->setAttr('unreal_sell_num', $goods->getAttr('unreal_sell_num') + $count );
        $goods->setAttr("inventory", $kucun);
        $goods->save();
        
        //返回给客户端
        $data = [
            "out_trade_no" => $serial, //服务器生成的订单号
            "subject"      => $goods->title,   //商品的名称
        ];
        return ["data"=>$data, "code"=>1 ];
    }
    
    
    /**
     * 获取销量
     * 
     * @param unknown $goods_id
     */
    private function get_xiaoliang($goods_id)
    {
        $goods_id = intval($goods_id);
        $sql ="select count(distinct uid) count2 from bb_shop_order where
        logistics_is_complete = 1 and
        goods_id = {$goods_id}";
        $arr = Db::query($sql);
        $count = $arr[0]['count2'];
        return $count;
    }
    

    /**
     * 增加用户地址
     */
    public function add_address()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $name = input('?param.name')?(string)input('param.name'):'';
        $phone = input('?param.phone')?(string)input('param.phone'):'';//手机号码
        $countries = input('?param.countries')?(string)input('param.countries'):'中国';//国家
        $province = input('?param.province')?(string)input('param.province'):'';//省
        $city = input('?param.city')?(string)input('param.city'):'';//市
        $area = input('?param.area')?(string)input('param.area'):'';//区
        $street = input('?param.street')?(string)input('param.street'):'';//街道地址
        $tel = input('?param.tel')?(string)input('param.tel'):'';//电话
        $is_default = input('?param.is_default')?(int)input('param.is_default'):0;//是否默认地址
        $zip_code = input('?param.zip_code')?(string)input('param.zip_code'):''; //邮编
        if (\app\user\model\Exists::userhExists($uid)!=1) {
            return ['message'=>'没有此用户','code'=>0];
        }
        if (!$name) {
            return ['message'=>'用户名称不能为空','code'=>0];
        }
        $AddressDB = array();
        if ($phone) {
            $AddressDB['phone'] = $phone;
        }
        if ($countries) {
            $AddressDB['countries'] = $countries;
        }
        if ($province) {
            $AddressDB['province'] = $province;
        }
        if ($city)   {
            $AddressDB['city'] = $city;
        }
        if ($area)  {
            $AddressDB['area'] = $area;
        }
        if ($street)  {
            $AddressDB['street'] = $street;
        }
        if ($tel)  {
            $AddressDB['tel'] = $tel;
        }
        if ($zip_code) {
            $AddressDB['zip_code'] = $zip_code;
        }
        $AddressDB['is_default'] =0;
        if ($is_default) {
            $AddressDB['is_default'] = $is_default;
            Db::table('bb_address')->where(['uid'=>$uid,'is_default'=>1])
                ->update(['is_default'=>0]);
        }
        $AddressDB['uid'] = $uid;
        $AddressDB['name'] = $name;
        $AddressDB['time'] = time();
        Db::table('bb_address')->insert($AddressDB);
        $AddressDB['id'] = Db::table('bb_address')->getLastInsID();
        return ['data'=>$this->conversion_address($AddressDB),'code'=>1];
    }
    

    /**
     * 删除用户地址
     */
    public function del_address()
    {
        $id = input('?param.id')?(int)input('param.id'):0;
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $AddressDB = Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->find();
        if ($AddressDB)
        {
            Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->update(["is_del"=>1 ]);
            return ['message'=>'删除成功','code'=>1];
        }
        return ['message'=>'没有当前的这个ID地址请检查','code'=>0];
    }

    
    /**
     * 修改用户地址
     */
    public function editor_address()
    {
        $id = input('?param.id')?(int)input('param.id'):0;
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $phone = input('?param.phone')?(string)input('param.phone'):'';//手机号码
        $countries = input('?param.countries')?(string)input('param.countries'):'';//国家
        $province = input('?param.province')?(string)input('param.province'):'';//省
        $city = input('?param.city')?(string)input('param.city'):'';//市
        $area = input('?param.area')?(string)input('param.area'):'';//区
        $street = input('?param.street')?(string)input('param.street'):'';//街道地址
        $tel = input('?param.tel')?(string)input('param.tel'):'';//电话
        $is_default = input('?param.is_default')?(int)input('param.is_default'):0;//是否默认地址
        $zip_code = input('?param.zip_code')?(string)input('param.zip_code'):''; //邮编
        
        $name = input('?param.name')?(string)input('param.name'):''; //xieye 201706
        
        $AddressDB = Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->find();
        if ($AddressDB) {
            if ($phone) {
                $AddressDB['phone'] = $phone;
            }
            if ($name)  {
                $AddressDB['name'] = $name;
            }
            if ($countries) {
                $AddressDB['countries'] = $countries;
            }
            if ($province) {
                $AddressDB['province'] = $province;
            }
            if ($city) {
                $AddressDB['city'] = $city;
            }
            if ($area) {
                $AddressDB['area'] = $area;
            }
            if ($street) {
                $AddressDB['street'] = $street;
            }
            if ($tel) {
                $AddressDB['tel'] = $tel;
            }
            if ($zip_code) {
                $AddressDB['zip_code'] = $zip_code;
            }
            $AddressDB['is_default'] =0;
            if ($is_default) {
                $AddressDB['is_default'] = $is_default;
                Db::table('bb_address')->where(['uid'=>$uid,'is_default'=>1])
                    ->update(['is_default'=>0]);
            }
            Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->update($AddressDB);
            return ['message'=>'修改成功','code'=>1];
        }
        return ['message'=>'没有此用户请检查UID以及地址id','code'=>0];
    }

    
    /**
     * 得到用户所有地址
     */
    public function get_address_list()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        if(\app\user\model\Exists::userhExists($uid)==1)
        {
            $AddressDB_list = Db::table('bb_address')->where(['uid'=>$uid, "is_del"=>0])->select();
            $DB_List = array();
            foreach ($AddressDB_list as $AddressDB)
            {
                array_push($DB_List,$this->conversion_address($AddressDB));
            }
            return ['data'=>$DB_List,'code'=>1];
        }
        return ['message'=>'没有此用户请检查UID','code'=>0];
    }
    
    
    /**
     * 得到默认地址
     */
    public function get_default_address()
    {
        $uid =  input('?param.uid')?(int)input('param.uid'):0;
        $AddressDB = Db::table('bb_address')->where(['uid'=>$uid,'is_default'=>1, "is_del"=>0])
            ->find();
        if (!$AddressDB)
        {
            $AddressDB = Db::table('bb_address')->where(['uid'=>$uid, "is_del"=>0])
                ->order('time','desc')->find();
        }
        if (!$AddressDB)
        {
            return ['message'=>'该用户没有设置任何地址','code'=>0];
        }
        return ['data'=>$this->conversion_address($AddressDB),'code'=>1];
    }

    
    /**
     * 强制转换类型
     * 
     * @param unknown $AddressDB
     */
    private function conversion_address($AddressDB)
    {
        $AddressDB['id'] = (int)$AddressDB['id'];
        $AddressDB['uid'] = (int)$AddressDB['uid'];
        return $AddressDB;
    }
    
    
}