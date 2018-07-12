<?php
namespace app\shop\controller;

use think\Db;
use app\shop\model\ShopGoods;

use BBExtend\common\Folder;

 use app\pay\model\Currency;
use app\shop\model\Users;
use BBExtend\pay\alipay\AlipayHelp;

use BBExtend\Sys;

/**
 * 
 * 建立测试用数据，不可以在正式服务器执行。
 * 
 * trim( $result , "\xEF\xBB\xBF" )
 * 
 * 
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Dataorder 
{
    public function remove()
    {
        $db = Sys::get_container_db();
        $ids = get_test_userid_arr();
        foreach ($ids as $id) {
           $sql ="update ds_register_log set has_pay=0 where uid = {$id}";
           $db->query($sql);
           
           $sql = "delete from ds_money_log where uid = {$id}";
           $db->query($sql);
           $sql = "delete from ds_money_prepare where uid = {$id}";
           $db->query($sql);
            
           $sql = "delete from ds_record where uid = {$id}";
           $db->query($sql);
            
           
        }
        echo "删除测试帐号付款记录成功";
    }
    
   
    
    /**
     * 
     * 测试案例
     * uid 10046
     * 
     * 设置金币数量
     * www.test1.com/shop/test/setgold/gold/0
     * www.test1.com/shop/test/setgold/gold/1000
     * 
     * 查看金币数量
     * www.test1.com/shop/test/getgold
     * 
     * 消费
     * 商品id1，波币10元
     * 
     * 消费2个
     * www.test1.com/shop/api/buy/type/2/uid/10046/address_id/1/goods_id/1/count/2/standard/%E4%B8%AD%E5%B0%BA%E5%AF%B8/style/%E9%BB%91%E8%89%B2
     * 
     * 查看金币数量应该980
     * 查看订单表是否生成，
     * 查看金额扣减日志currency_log表是否有记录。
     * 
     * 
     */
    public function index($uid = 10072)
    {
//         echo PHP_OS;
        if (PHP_OS != 'WINNT') {
            return;
        }
        $sql='delete from bb_shop_order where id between 100 and 200';
        Db::execute($sql);
        $id=100;
        $id++;
       
        //$uid=10072;
        $logistics= "wl". mt_rand(100000, 999999);
        $logistics_company = "SF";
        $goods_id =110;
        $serial ="dd".mt_rand(100000, 999999);
        $create_time = time() + 24*3600* mt_rand(10, 20);
        $logistics_is_complete=1; //已完成
        $logistics_is_pickup =0; //待发货。
        $model =["大尺寸","中尺寸","小尺寸"];
        $style = ["红色","黑色",'蓝色',];
        
        $price_cash=15;
        $price_bobi = 100;
        
        //logistics_company
        
        for ($ii=0;$ii<30; $ii++ ) {
            $id++;
            
            if ($ii< 10) {
                $logistics_is_complete=0; 
                $logistics_is_pickup =0; //待发货。
                $count=1;//使用波币
                $price = $count * $price_bobi;
                $type=1;
                
            }
            if ($ii>= 10 && $ii < 20 ) {
                $logistics_is_complete=0;
                $logistics_is_pickup =1; //待收货。
                $count=2;
                $price = $count * $price_cash;
                $type=2;
            }
            if ($ii>= 20 ) {
                $logistics_is_complete=1;
                $logistics_is_pickup =1; //已收货。
                $count=3;
                $price = $count * $price_cash;
                $type=2;
            }
            $arr = [
                'id'=>$id,
                'uid'=>$uid,
                'logistics'=> "wl". mt_rand(100000, 999999),
                'price' => $price,
                "type" =>$type,
                "goods_id" =>$goods_id,
                "serial" => "dd".mt_rand(100000, 999999),
                'create_time' =>  time() - 24*3600* mt_rand(10, 20),
                "count" =>$count,
                "model" => $model[ array_rand($model) ],
                'style' => $style[ array_rand($style) ] ,
                'logistics_company' => $logistics_company,
                'logistics_is_complete'=>$logistics_is_complete,
                'logistics_is_pickup'=>$logistics_is_pickup,
                'address_id' => 1,
               
            ];
            Db::table('bb_shop_order')->insert($arr);
            dump($arr);
        }
        
              
        
        echo '<br>订单30条数据完成';
    }
       
  
   
}