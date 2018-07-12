<?php
namespace app\shop\controller;
use think\Db;
use app\shop\model\ShopGoods;
use app\pay\model\Currency;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Goods 
{
    
    public function help($goods_id) {
        $goods_id = intval($goods_id);
        $goods = ShopGoods::get($goods_id);
        if (!$goods) {
            return ['code'=>0, 'message'=>'商品不存在'];
        }
         
        //     title:'商品标题',
        //     discount: 1(含)－10(含)之间的整数，10不打折，9是9折等。。客户端：当原始波币价格不等于波币价格时，显示此字段
        //     currency_original: 原始波币价格，-1 表示 不能波币买。
        //     money_original:原始现金价格，可能有小数点， -1表示不能人民币购买。
        //     currency: 波币价格，-1 表示 不能波币买。
        //     money:现金价格，可能会有小数点，-1表示不能人民币购买。
        //     on_sale_start_time:促销起始时间，是时间戳。
        //     on_sale_end_time: 促销结束时间， 是时间戳。
        //     exchange_level：商品等级。整型。
        //     sell_num:销量
        //     inventory:库存
        //     info:详情
        $data = [
            'id' => $goods->getData("id"),
            'title'=> $goods->getData('title'),
            'discount'=> $goods->getData('discount'),
            'currency_original'=> $goods->getData('currency'),
            'money_original'=> floatval( $goods->format_money()),
            'currency'=> $goods->right_currency(),
            'money'=>   floatval( $goods->right_money() ),//floatval非常重要，杨桦好做
            'on_sale_start_time'=> $goods->getData('on_sale_start_time'),
            'on_sale_end_time'=> $goods->getData('on_sale_end_time'),
            'exchange_level'=> (int)$goods->getData('exchange_level'),
            'sell_num'=> (int)$goods->getData('unreal_sell_num'), // xieye 2017 03 ，假销量返回
            'inventory'=> (int)$goods->getData('inventory'),
            'info'=> strval($goods->getData('info')),
            'model' => strval($goods->getData('model_list')),
            'style'=>  strval($goods->getData('style_list')),
            'pic' => $goods->right_pic(),
            'pic_list' => $goods->right_pic_list_arr(),
            'show_pic_list' => $goods->right_show_pic_list_arr(),
            'score'   => $goods->right_score(),
        ];
        return $data;
    }
    
    public function details($goods_id=0)
    {
        $goods_id = intval($goods_id);
        $goods = ShopGoods::get($goods_id);
        if (!$goods) {
            return ['code'=>0, 'message'=>'商品不存在'];
        }
     
//     title:'商品标题',
//     discount: 1(含)－10(含)之间的整数，10不打折，9是9折等。。客户端：当原始波币价格不等于波币价格时，显示此字段
//     currency_original: 原始波币价格，-1 表示 不能波币买。
//     money_original:原始现金价格，可能有小数点， -1表示不能人民币购买。
//     currency: 波币价格，-1 表示 不能波币买。
//     money:现金价格，可能会有小数点，-1表示不能人民币购买。
//     on_sale_start_time:促销起始时间，是时间戳。
//     on_sale_end_time: 促销结束时间， 是时间戳。
//     exchange_level：商品等级。整型。
//     sell_num:销量
//     inventory:库存
//     info:详情
        $data = [
            'title'=> $goods->getData('title'),
            'discount'=> $goods->getData('discount'),
            'currency_original'=> $goods->getData('currency'),
            'money_original'=>  floatval(  $goods->format_money()),
            'currency'=> $goods->right_currency(),
            'score'   => $goods->right_score(),
            'money'=>   floatval(  $goods->right_money()),
            'on_sale_start_time'=> $goods->getData('on_sale_start_time'),
            'on_sale_end_time'=> $goods->getData('on_sale_end_time'),
            'exchange_level'=> (int)$goods->getData('exchange_level'),
            'sell_num'=> (int)$goods->getData('unreal_sell_num'), // xieye 2017 03 ,假销量
            'inventory'=> (int)$goods->getData('inventory'),
            'info'=> strval($goods->getData('info')),
            'model' => strval($goods->getData('model_list')),
            'style'=>  strval($goods->getData('style_list')),
            'pic' => $goods->right_pic(),
            'pic_list' => $goods->right_pic_list_arr(),
            'show_pic_list' => $goods->right_show_pic_list_arr(),
            
        ];
        return ['code'=>1,'data'=>$data ];
    }
    
    
    /**
     * 得到商品列表
     * 
     * type 是这4种
     * define('SHOP_TYPE_HEAD',100);//热门，即销量降序
define('SHOP_TYPE_REC',101);//推荐， 推荐字段降序
define('SHOP_TYPE_ZHE',102);//折扣，不排序，所有打折商品
define('SHOP_TYPE_MY',103);//我能兑换，商品可以用波币购买，且价格小于等于用户的波币。
     * 
     * @param number $uid 用户id
     * @param number $type 见上
     * @param number $start 起始序号，从0开始
     * @param number $length 长度
     */
    public function lists($uid=0, $type=101, $start=0, $length=10 ) {
        
        $goodsDB_list = array();
        $code = \app\user\model\Exists::userhExists($uid);
        if ($code!=1) {
            return ['message'=>'没有这个用户！~','code'=>$code];
        }
        
        switch ($type)
        {
            case SHOP_TYPE_HEAD: //100 热门
                $goodsDB_list = Db::table('bb_shop_goods')
                    ->where('is_remove',0)
                    ->where('money','>', 0)
                    ->order('sell_num','desc')
                    ->limit($start,$length)->select();
                break;
            case SHOP_TYPE_REC:  //101 推荐
                $goodsDB_list = Db::table('bb_shop_goods')
                    ->where('is_remove',0)->where('money','>', 0)
                    ->order('is_rmd','desc')
                    ->limit($start,$length)->select();
                break;
            case SHOP_TYPE_ZHE: //102 有折扣列表，判断促销时间
                $time = time();
                $goodsDB_list = Db::table('bb_shop_goods')
                    ->where('discount<10')->where('money','>', 0)
                    ->where('is_remove',0)
                    ->where(" {$time} between  on_sale_start_time and on_sale_end_time ")
                    ->order("create_time",'desc')
                    ->limit($start,$length)->select();
                break;
            case SHOP_TYPE_MY:  //103 我能兑换的商品
                $currency = Currency::factory($uid);
                
                $user =  \app\shop\model\Users::get($uid);
                $userinfo = $user->get_buy_info();
                //查商品等级
                //if ($userinfo['level'] < $goods->getData("exchange_level")
                
                $goodsDB_list = Db::table('bb_shop_goods')
                    ->where('currency >=0')->where('money','>', 0)
                    ->where('exchange_level <= ' . $userinfo['level'])
                    ->where('is_remove',0)
                    ->where('currency < ' . $currency->getData('gold') )
                    ->order("currency",'desc')
                    ->order("money",'desc')
                    ->limit($start,$length)->select();
                break;
        }
        $goods_list = array();
        foreach ($goodsDB_list as $goodsDB){
           array_push($goods_list, $this->help($goodsDB['id']  ) );
        }
        
        // xieye 20171101 防止苹果bug
        foreach ($goods_list as $k=> $v){
            $goods_list[$k]['score']=-1;        
        }
        
        if (count($goods_list)==$length)  {
            return ['data'=>$goods_list,'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$goods_list,'is_bottom'=>1,'code'=>1];
    }

    /**
     * 得到全部积分兑换商品
     *
     *
     */
    public function lists_score($uid ) 
    {
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message' => 'uid error' ];
        }
        $curr_score = $user->currency->score;
        $exchanged_score = $user->exchanged_score();
    
        $goodsDB_list = array();
        $db = Sys::get_container_db_eloquent();
        //按推荐排序
        $sql ="select * from bb_shop_goods 
                where is_remove=0 
                  and exchange_score > 0 
                order by is_rmd desc ";
            
        $goodsDB_list = DbSelect::fetchAll($db, $sql);
        $goods_list = [];
        
        //     currency_original: 原始波币价格，-1 表示 不能波币买。
        //     money_original:原始现金价格，可能有小数点， -1表示不能人民币购买。
        //     currency: 波币价格，-1 表示 不能波币买。
        //     money:现金价格，可能会有小数点，-1表示不能人民币购买。
        
        foreach ($goodsDB_list as $goodsDB){
            $temp = $this->help($goodsDB['id']);
            $temp['currency_original'] = -1;
            $temp['money_original'] = -1;
            $temp['currency'] = -1;
            $temp['money'] = -1;
            
            $goods_list[] = $temp;
            
        }
        return ['data'=>['list' => $goods_list,
            'curr_score'=>$curr_score,
            'exchanged_score' => $exchanged_score,
            
        ],'code'=>1];
    }
    
  
}

