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

class Test extends \think\Controller
{
    public function _initialize()
    {
        if (PHP_OS != 'WINNT') {
            return;
        }
    }
    
    /**
     * 重要勿删，阿里回调
     */
    public function alipay_notify()
    {
        //         require_once ( realpath( realpath( APP_PATH)."/../extend/alipay/alipay.config.php"));
        //         require_once ( realpath( realpath( APP_PATH)."/../extend/alipay/lib/alipay_core.function.php"));
        //         require_once ( realpath( realpath( APP_PATH)."/../extend/alipay/lib/alipay_rsa.function.php"));
        //         //除去待签名参数数组中的空值和签名参数
        //         $log =  \BBExtend\pay\alipay\Logs::get_instance();
        //         $para_filter = paraFilter($_POST);
        //         $log->log(11);
        //         //对待签名参数数组排序
        //         $para_sort = argSort($para_filter);
        //         $log->log(12);
        //         //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        //         $prestr = createLinkstring($para_sort);
    
        //         $isSgin = rsaVerify($prestr, trim($alipay_config['alipay_public_key']), $_POST['sign']);
    
        //         if ($isSgin) {
        //             $log->log("ok2");
        //         }else {
        //             $log->log("ok1");
        //         }
        //把对返回的结果验证记录到日志文件
        //         $L =  \BBExtend\pay\alipay\Log::get_instance();
    
        $help = new \BBExtend\pay\alipay\AlipayHelp();
        $result = $help->receive_ali_post();
    
        echo $result;
    }
    
    public function test_bom(){
        $s='{"aa":1}';
        echo md5($s);
        echo "<br>";
        $s =file_get_contents("http://localhost/shop/test/test_bom22");
        echo md5($s);
        
    }
    
    public function test_bom22(){
        return ['aa'=>1];
    }
    
    
    /**
     * 重要，测试下单
     */
    public function test_wuliu(){
        //我现在先生产一个订单，然后下物流单,货号1，地址1，
        //具体要求，有10046这个人
        //  有 goods_id = 1 这个商品。
        // 有address_id=1 这个地址。
        
       $sql = "delete from bb_shop_order where id=1";
       Db::execute($sql);
       $temp = md5(time());
        Db::table('bb_shop_order')
          ->insert([
              'id'=>1,
              'uid'=>10046,
              'address_id'=>1,
              'price'=>10,
              'type'=>2,
              'goods_id'=>1,
              'serial'=>$temp,
              'is_success'=>1,
              'count'=>1,
          ]);
        
         $wuliu_help = new \BBExtend\pay\Kuaidi();
         $result = $wuliu_help->set_wuliuhao("YTO") //选择快递公司
             ->set_order($temp)          //我公司订单号
             ->set_goods_name("鞋子")
             ->set_receiver_name("张三")
             ->set_receiver_phone("15062288888")
             ->set_receiver_province("江苏省")
             ->set_receiver_city("南京市")
             ->set_receiver_area("")
             ->set_receiver_address("玄武大道699号苏宁公司")
             ->send_request();
         dump($result);
    }
    
    
    /**
     * 测试 立即查询
     *
     */
    public function test_query()
    {
        $sql = "delete from bb_shop_order where id=1";
        Db::execute($sql);
        $temp = md5(time());
        //谢烨，直接把订单表设置成已下单，已取件，有物流单号和物流公司，未订阅。
        $logistics = '768469195586'; // 测试时由快递鸟公司提供
        $company = "ZTO"; // 测试时由快递鸟公司提供
        Db::table('bb_shop_order')
          ->insert([
            'id'=>1,
            'uid'=>10046,
            'address_id'=>1,
            'price'=>10,
            'type'=>2,
            'goods_id'=>1,
            'serial'=>$temp,
            'is_success'=>1,
            'count'=>1,
            'logistics' => $logistics,
            'logistics_company'=> $company,
            'logistics_is_subscribe'=> 0, //未订阅
            'logistics_is_pickup'=>1,     //已取件
            'logistics_is_order'=> 1,     //已下单
        ]);
    
        $wuliu_help = new \BBExtend\pay\Kuaidi();
        $result = $wuliu_help->query_at_once($company, $logistics);
        dump($result);
    
    }
    
    
    /**
     * 测试订阅
     * 
     */
    public function test_dingyue()
    {
        $sql = "delete from bb_shop_order where id=1";
        Db::execute($sql);
        $temp = md5(time());
        //谢烨，直接把订单表设置成已下单，已取件，有物流单号和物流公司，未订阅。
        $logistics = '3311443764514'; // 测试时由快递鸟公司提供
        $company = "STO"; // 测试时由快递鸟公司提供
        
        
        Db::table('bb_shop_order')
          ->insert([
            'id'=>1,
            'uid'=>10046,
            'address_id'=>1,
            'price'=>10,
            'type'=>2,
            'goods_id'=>1,
            'serial'=>$temp,
            'is_success'=>1,
            'count'=>1,
            'logistics' => $logistics,
            'logistics_company'=> $company,
            'logistics_is_subscribe'=> 0, //未订阅
            'logistics_is_pickup'=>1,     //已取件
            'logistics_is_order'=> 1,     //已下单
        ]);
        
        $wuliu_help = new \BBExtend\pay\Kuaidi();
        $result = $wuliu_help->dingyue($company, $logistics);
        dump($result);
        
    }
    
    
    private function create_img_folder(){
        $path = realpath( realpath( APP_PATH)."/../public");
        $path = $path ."/test" ; 
         Folder::create_dir($path);
        return $path;
    }
    public function fetchimg()
    {
        $img1 = file_get_contents("http://www.jspeople.com/zendtest/yi1.jpg");
        $img2 = file_get_contents("http://www.jspeople.com/zendtest/yi2.jpg");
        $img3 = file_get_contents("http://www.jspeople.com/zendtest/yi3.jpg");
        $path = $this->create_img_folder();
        file_put_contents( $path .'/yi1.jpg', $img1);
        file_put_contents($path . '/yi2.jpg', $img2);
        file_put_contents($path . '/yi3.jpg', $img3);
        echo "抓3张图完成";
    }
    
    public function setgold($gold=0)
    {
        if (!$gold) {
            $sql ="delete from bb_currency where uid=10046";
            Db::query($sql);
            return;
        }
        $currency = \app\pay\model\Currency::get(514);
        $currency->setAttr('gold', $gold);
        $currency->save();
        
        $temp = Db::table('bb_currency')->where("uid",10046)->find();
        dump($temp['gold']);
    }
   
    /**
     * 模拟支付宝的回调
     * 测试方法：
     * 1、现金下订单。用网址，
     * 2、
     */
    public function simulate()
    {
        $result = Db::table('bb_shop_order')->order('id','desc')->limit(2)->select();
        dump($result);
        echo "==============<br>现在开始支付====================<br>";
        $url ="http://www.test1.com/shop/api/buy/type/1/uid/10046/goods_id/1".
       "/address_id/1/count/2/standard/%E4%B8%AD%E5%B0%BA%E5%AF%B8/style/%E9%BB%91%E8%89%B2";
        
        $temp =  file_get_contents($url);
        $temp = trim_json_decode($temp);
        
        $help = new AlipayHelp();
        $help->buy($temp['data']['out_trade_no'] , 'ali', '123333');
        
        $result = Db::table('bb_shop_order')->order('id','desc')->limit(2)->select();
        dump($result);
        
        
    }
    
    
    public function info_money()
    {
        
    }
    
    public function info_bo()
    {
        //人的等级和波币数量查出。
        echo "用户信息<br>";
        $uid =10046;
        $user = Users::get($uid);
        $info = $user->get_buy_info();
        dump($info);
        
        echo "商品信息<br>";
        $goods = ShopGoods::get(1);
        $arr = [
            'price' => $goods->getData('currency'),
            'start_time' => date("Y-m-d H:i:s", $goods->getData('on_sale_start_time')),
            'end_time' => date("Y-m-d H:i:s", $goods->getData('on_sale_end_time')),
            'level'=> $goods->getData('exchange_level'),
            'discount' => $goods->getData('discount'),
            
        ];
        dump($arr);
        //
        echo "最后3个订单";
        $arr = Db::query("select * from bb_shop_order order by id desc limit 3");
        dump($arr);
        
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
    public function index()
    {
//         echo PHP_OS;
        if (PHP_OS != 'WINNT') {
            return;
        }
        $sql='delete from bb_users where uid=10046';
        Db::execute($sql);
        
        Db::table('bb_users')->insert([
            'uid'=>10046,
            'platform_id' =>'a163b327d492e9117f63864a8151afe8',    
            'nickname' => '梅子',
            'pic'=>  '/uploads/headpic/10046/578d80eb51045.jpg',
            'phone' => '15062288888',
            'address' =>'杭州市 上城区',
            'login_type' =>3,
            'login_time' => 1468890785,
            'userlogin_token' =>'a443617c861ec2cb0a6328a443ce1d07',
            'birthday'=>'2009-02-13',
            'permissions'=>1,
            'monster_count'=>1,
            'min_record_time'=>'8',
            'max_record_time'=>120,
            'specialty'=>'[6,8,13]',
        ]);
        
        $sql ='delete from bb_shop_goods where id<4';
        Db::query($sql);
        $arr = array(
            'id' =>1,
            'exchange_level'=>  2,
            'currency'=>  10,
            'money'=>  0.01,
            'discount'=>  9, //9折
            'title'=>  'xie测试商品',
            'info'=>  'xie测试商品的简介',
            'inventory'=>  100,
            'sell_num'=>  200,
            'pic_list'=> '[{"picpath":"/test/yi1.jpg","title":"11","linkurl":""},
                       {"picpath":"/test/yi2.jpg","title":"22","linkurl":""}
                    ]',
            'pic'=>  '/test/yi3.jpg',
            'model_list'=>  '大尺寸,中尺寸,小尺寸,超小',
            'style_list'=>  '红色,黑色',
            'on_sale_start_time'=>  time(),
            'on_sale_end_time'=>  time() + 30 *24 *3600,
            'is_rmd' =>0,
        );
        Db::table('bb_shop_goods')->insert($arr);
        
        
        $arr = array(
            'id' =>2,
            'exchange_level'=>  2,
            'currency'=>  20,
            'money'=>  0.01,
            'discount'=>  9, //9折
            'title'=>  'xie测试商品2号',
            'info'=>  'xie测试商品的简介',
            'inventory'=>  100,
            'sell_num'=>  200,
            'pic_list'=> '',
            'pic'=>  '/test/yi3.jpg',
            'model_list'=>  '大尺寸,中尺寸,小尺寸,超小',
            'style_list'=>  '红色,黑色',
            'on_sale_start_time'=>  time(),
            'on_sale_end_time'=>  time() + 30 *24 *3600,
            'is_rmd' =>1,
        );
        Db::table('bb_shop_goods')->insert($arr);
        
        $arr = array(
            'id' =>3,
            'exchange_level'=>  2,
            'currency'=>  30,
            'money'=>  0.01,
            'discount'=>  9, //9折
            'title'=>  'xie测试商品3号',
            'info'=>  'xie测试商品的简介',
            'inventory'=>  100,
            'sell_num'=>  200,
            'pic_list'=> '',
            'pic'=>  '/test/yi3.jpg',
            'model_list'=>  '大尺寸,中尺寸,小尺寸,超小',
            'style_list'=>  '红色,黑色',
            'on_sale_start_time'=>  time(),
            'on_sale_end_time'=>  time() + 30 *24 *3600,
            'is_rmd' =>1,
        );
        Db::table('bb_shop_goods')->insert($arr);
        
        $sql ="delete from bb_address where id=1";
        Db::execute($sql);
        Db::table('bb_address')->insert([
            'id'=>1,
            'uid'=> 10046,
            'name' =>'张三',
            'phone' => '15062287777',
            'province'=>'江苏省',
            'city'=>'南京市',
            'area'=>'',
            'street' => '玄武大道699号',
        ]);
        
        
        
        
        echo '数据完成';
    }
       
  
    /**
     * 123 
     */
    public function svnup()
    {
        $content = file_get_contents("http://127.0.0.1:2005/");
        echo $content;
    }
}