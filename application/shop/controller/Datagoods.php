<?php
namespace app\shop\controller;

use think\Db;
use app\shop\model\ShopGoods;

use BBExtend\common\Folder;

 use app\pay\model\Currency;
use app\shop\model\Users;
use BBExtend\pay\alipay\AlipayHelp;
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

class Datagoods 
{
    
    
   
    
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
    public function index()
    {
//         echo PHP_OS;
        if (PHP_OS != 'WINNT') {
            return;
        }
        $sql='delete from bb_shop_goods where id between 100 and 200';
        Db::execute($sql);
        $id=100;
        $id++;
        $exchange_level = 10;
        $currency =10;
        $money = 10;
        $inventory = 1000;
        $pic_list ='[{"picpath":"/test/yi1.jpg","title":"11","linkurl":""},
                       {"picpath":"/test/yi2.jpg","title":"22","linkurl":""}
                    ]';
        $pic = '/test/yi3.jpg';
        $model_list ='大尺寸,中尺寸,小尺寸';
        $style_list ='红色,黑色';
        for ($ii=0;$ii<30; $ii++ ) {
            $id++;
            
            if (mt_rand(0,10) < 3 ) {
                $currency = -1;
            }else {
                $currency =10;
            }
            if (mt_rand(0,10) < 3 ) {
                $money = -1;
            }else {
                $money =10;
            }
            Db::table('bb_shop_goods')->insert([
                'id'=>$id,
                'exchange_level'=>$exchange_level,
                'currency'=> $currency,
                'money' => $money,
                'discount' => 10,
                'title' => '鞋子'.$id,
                'info' => '鞋子info'.$id,
                'inventory' => mt_rand(0, 3),
                'sell_num' => 2,
                'pic_list' => $pic_list,
                'pic' => $pic,
                'model_list' => $model_list,
                'style_list' => $style_list,
                'heat' => mt_rand(100, 999),
                'is_rmd' => mt_rand(0,1),
                'on_sale_start_time'=> time(),
                'on_sale_end_time'=> time() + 24*3600* mt_rand(10, 20) ,
                'create_time' => time() + mt_rand(-10000,10000),
            ]);
            
        }
        
              
        
        echo '数据完成';
    }
       
  
   
}