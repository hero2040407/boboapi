<?php
namespace app\pay\model;

use think\Model;
use app\pay\model\Currency;
use app\pay\model\CurrencyLog;
use BBExtend\BBMessage;

use app\shop\model\ShopOrderPrepare;
use app\shop\model\Order;
use think\Db;

use BBExtend\Level;
use BBExtend\message\Message;
use BBExtend\user\exp\Exp;
use BBExtend\fix\MessageType;

/**
 * 用户模型类
 * @author 谢烨
 *
 */
class Users extends Model
{
    
    protected $table = 'bb_users';
    
    
    public function is_vip()
    {
        $boo = false;
        $expire = intval( $this->getData('vip_time') );
        if ($this->getData('vip') && $expire > time()   ) {
            $boo = true;
        }
        return $boo;
    }
    
    /**
     * 处理用户购物成功的流程,注意这里只处理了支付宝现金支付的情况。
     * 波币购买的流程在 /spplication/shop/Api.php的pay_coin函数里处理。
     * 
     * 
     * 完成这么几件事。（注意，临时订单表设字段这里不管）
     * 
     //复制到正式订单表，同时发送物流消息。
     *
     * @param unknown $out_trade_no 订单号
     */
    public function buy_success_money($out_trade_no,$third_name,$third_serial)
    {
        $prepare = ShopOrderPrepare::get(['serial' => $out_trade_no ]);
        $order = new Order();
        $order->data('uid', $prepare->getData('uid') );
        $order->data('address_id', $prepare->getData('address_id') );
        $order->data('logistics', '' );
        $order->data('ems', 0 );
        $order->data('price', $prepare->getData('price') );
        $order->data('type', 1 );//现金为1
        $order->data('goods_id', $prepare->getData('goods_id') );
        $order->data('serial', $out_trade_no );
        $order->data('is_success', 1 ); //成功了，强制为1
        $order->data('terminal', $prepare->getData('terminal') ); // 
       // $order->data('create_time', $prepare->getData('uid') );
        $order->data('count', $prepare->getData('count') );
        $order->data('model', $prepare->getData('model') );
        $order->data('style', $prepare->getData('style') );
        
        $order->data('third_name', $third_name);
        $order->data('third_serial', $third_serial);
        $order->data('terminal_type', $prepare->getData('terminal_type') );
        $order->save();
        
        //购物成功，此时，需把订单中的购物数量取出，找到商品表，给商品表增加销量，减少库存
        // sell_num 销量
        // inventory 库存
        $count = $prepare->getData('count');
        $goods = \app\shop\model\ShopGoods::get($prepare->getData('goods_id'));
        if ($goods) {
            //$xiao = $goods->getData("sell_num") + $count;
            
            
            $xiao = $this->get_xiaoliang($prepare->getData('goods_id'));
            $kucun = $goods->getData("inventory") - $count;
            $kucun = ($kucun <0) ? 0: $kucun;
            $goods->setAttr('sell_num', $xiao);
            
            $goods->setAttr('unreal_sell_num', $goods->getAttr('unreal_sell_num') + 
                    $prepare->getData('count') );
            
            $goods->setAttr("inventory", $kucun);
            $goods->save();
            
        }
     //   Level::add_user_exp($this->uid, LEVEL_SHOP);
        Exp::getinstance($this->uid)->set_typeint(Exp::LEVEL_SHOP)->add_exp();
    }
     
    public function get_xiaoliang($goods_id){
        $goods_id = intval($goods_id);
        $sql ="select count(distinct uid) count2 from bb_shop_order where 
        logistics_is_complete = 1 and
        goods_id = {$goods_id}";
        $arr = Db::query($sql);
        $count = $arr[0]['count2'];
        return $count;
    }
    
    public function get_buy_info()
    {
        $uid  = $this->getData("uid");
        $arr = Db::table('bb_users_exp')->where('uid', $uid)->find();
        if (!$arr) {
            $arr = [];
            $arr['level']=0;
        }
        $currency = Currency::factory($uid);
        return [
            'uid'  => $uid,
            'level' => (int)$arr['level'],
            'gold' => $currency->getData('gold')  ,
            'score' =>$currency->getData('score')  ,
        ];
    }
    
    
    
    /**
     *
     * 注：扣减不在本函数判断是否负数，在外面判断！！
     *
     * 用户用波币消费成功
     * 完成这么几件事。
     * //bb_currency表，先查有没有uid对应，否则还得先添加。
     //总之确保获得。
     //现在给bb_currency表扣减gold字段
     //再记录到日志表bb_currency_log
     //bb_msg表发送信息。
      * 
      * 再次考虑经验值。
     *
     * 使用了两个常量，1，5，谢烨20160824
     *
     * @param unknown $gold_count
     */
    public function buy_success_coin($gold_count,$serial, $count)
    {
        $gold_count = abs(intval($gold_count ));
        if (!$gold_count) {
            return;
        }
        $currency = Currency::get(['uid' => $this->uid ]  );
        if (!$currency) {
            $currency = new Currency();
            $currency->uid = $this->uid;
            $currency->gold = 0;
            $currency->gold_income = 0;
            $currency->flower = 0;
            $currency->discount = 0;
            $currency->monster = 0;
            $currency->save();
        }
        $currency->gold -=  $gold_count;
        $currency->save();
        $c_log = new CurrencyLog();
        $c_log->uid = $this->uid;
        $c_log->data("type",1); //固定值
        $c_log->count = 0 - $gold_count ;
        $c_log->time = time();
        $c_log->way = "商城消费";
        $c_log->save();
        
        $order = \app\shop\model\Order::get(['serial' => $serial ]);
        
        $goods = \app\shop\model\ShopGoods::get($order->getData('goods_id'));
        if ($goods) {
            $count = $order->getData('count');
            //$xiao = $goods->getData("sell_num") + $count;
            $xiao = $this->get_xiaoliang($order->getData('goods_id'));
            
            $kucun = $goods->getData("inventory") - $count;
            $kucun = ($kucun <0) ? 0: $kucun;
            $goods->setAttr('sell_num', $xiao);
            
            $goods->setAttr('unreal_sell_num', $goods->getAttr('unreal_sell_num') + $count );
            
            $goods->setAttr("inventory", $kucun);
            $goods->save();
        
        }
       // Level::add_user_exp($this->uid, LEVEL_SHOP);
        Exp::getinstance($this->uid)->set_typeint(Exp::LEVEL_SHOP)->add_exp();
        
        //对目标用户修改排名
      //  \BBExtend\user\Ranking::getinstance($this->uid)->set_caifu_ranking();
    
        //         $ContentDB = array();
        //         $ContentDB = BBMessage::AddMsg($ContentDB,'恭喜您充值成功账户充入');
        //         $ContentDB = BBMessage::AddMsg($ContentDB, $gold_count .'Bo币', ORANGE_MESSAGE_COL);
        //         $ContentDB = BBMessage::AddMsg($ContentDB,'请查收');
        //         BBMessage::SendMsg(5,'充值成功',$ContentDB,$this->uid);
    
    }
    
    
    /**
     *
     * 注：扣减不在本函数判断是否负数，在外面判断！！
     *
     * 用户用积分消费成功
     * 完成这么几件事。
     * //bb_currency表，先查有没有uid对应，否则还得先添加。
     //总之确保获得。
     //现在给bb_currency表扣减gold字段
     //再记录到日志表bb_currency_log
     //bb_msg表发送信息。
     *
     * 再次考虑经验值。
     *
     * 使用了两个常量，1，5，谢烨20160824
     *
     * @param unknown $gold_count
     */
    public function buy_success_score($gold_count,$serial, $count)
    {
        $gold_count = abs(intval($gold_count ));
        if (!$gold_count) {
            return;
        }
        $currency = Currency::get(['uid' => $this->uid ]  );
        $currency->score -=  $gold_count;
        $currency->save();
        // 日志的type：1波币,2怪兽蛋,10波豆,11积分
        $c_log = new CurrencyLog();
        $c_log->uid = $this->uid;
        $c_log->data("type",11); //固定值
        $c_log->count = 0 - $gold_count ;
        $c_log->time = time();
        $c_log->way = "积分兑换礼品";
        $c_log->save();
    
        $order = \app\shop\model\Order::get(['serial' => $serial ]);
    
        $goods = \app\shop\model\ShopGoods::get($order->getData('goods_id'));
        if ($goods) {
            $count = $order->getData('count');
            //$xiao = $goods->getData("sell_num") + $count;
            $xiao = $this->get_xiaoliang($order->getData('goods_id'));
    
            $kucun = $goods->getData("inventory") - $count;
            $kucun = ($kucun <0) ? 0: $kucun;
            $goods->setAttr('sell_num', $xiao);
    
            $goods->setAttr('unreal_sell_num', $goods->getAttr('unreal_sell_num') + $count );
    
            $goods->setAttr("inventory", $kucun);
            $goods->save();
    
        }
        
  //      Exp::getinstance($this->uid)->set_typeint(Exp::LEVEL_SHOP)->add_exp();
    }
    
    /**
     * 购买视频
     * @param unknown $gold_count
     */
    public function buy_video_success_coin($gold_count)
    {
        $gold_count = abs(intval($gold_count ));
        if (!$gold_count) {
            return;
        }
        
        \BBExtend\Currency::add_currency($this->uid, CURRENCY_GOLD,0-$gold_count,'购买课程');
        
//         $currency = Currency::get(['uid' => $this->uid ]  );
//         if (!$currency) {
//             $currency = new Currency();
//             $currency->uid = $this->uid;
//             $currency->gold = 0;
//             $currency->gold_income = 0;
//             $currency->flower = 0;
//             $currency->discount = 0;
//             $currency->monster = 0;
//             $currency->save();
//         }
//         $currency->gold -=  $gold_count;
//         $currency->save();
//         $c_log = new CurrencyLog();
//         $c_log->uid = $this->uid;
//         $c_log->data("type",1); //固定值
//         $c_log->count = 0 - $gold_count ;
//         $c_log->time = time();
//         $c_log->way = "购买视频";
//         $c_log->save();
    }
    
    
    /**
     * 用户充值成功
     * 完成这么几件事。（注意，buy订单表这里不管）
     * //bb_currency表，先查有没有uid对应，否则还得先添加。
        //总之确保获得。
        //现在给bb_currency表增加gold字段
        //再记录到日志表bb_currency_log
        //bb_msg表发送信息。
         * 
         * 使用了两个常量，1，5，谢烨20160824
         * 
         * @param unknown $gold_count
     */
    public function pay_success($gold_count)
    {
        $gold_count = abs(intval($gold_count ));
        if (!$gold_count) {
            return;
        }
        \BBExtend\Currency::add_currency($this->uid,CURRENCY_GOLD,$gold_count,'充值');
        
//         $currency = Currency::get(['uid' => $this->uid ]  );
       
        
//         $currency->setAttr("gold", $gold_count + $currency->getData("gold") );
        
        
//         $currency->save();
//         $c_log = new CurrencyLog();
//         $c_log->uid = $this->uid;
//         $c_log->data("type",1); //固定值
//         $c_log->count = $gold_count ;
//         $c_log->time = time();
//         $c_log->way = "充值";
//         $c_log->save();
        
        Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content('恭喜您充值成功账户充入'))
            ->add_content(Message::simple()->content("{$gold_count}BO币" )->color(0xf4a560)  )
            ->add_content(Message::simple()->content('，请查收。'))
            ->set_type(MessageType::chongzhi)
            ->set_uid($this->uid)
            ->send();
        
        
    }
    
}