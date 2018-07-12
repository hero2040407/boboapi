<?php
namespace app\shop\controller;
//use BBExtend\BBShop;
use think\Db;

/**
 * 
 * 物流控制器
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Wuliu 
{
    
//     /**
//      * 该接口接收快递鸟的订阅信息
//      */
//    public function index(){
//        $post = json_encode($_POST,  JSON_UNESCAPED_UNICODE);
//        $post = strval($post);
//        $time = date("Y-m-d H:i:s");
//        Db::table('bb_alitemp')->insert(['content'=> $post,'create_time'=>$time ]);
//        if (isset($_POST['Data'])) {
//           Db::table('bb_alitemp')->insert(['content'=> $_POST['Data'],'create_time'=>$time ]);
//        }
//        return [
//            "EBusinessID"=> '1262605', //这是我公司在快递鸟的id
//            "UpdateTime" => date("Y-m-d H:i:s"),
//            "Success"=>true,
//            "Reason"=>'',
           
//        ];
//    }
   
    
    public function query_at_once()
    {
        
    }
    
    
    /**
     * 本接口由快递鸟平台调用
     * 
     * @return string[]|boolean[]
     */
   public function push_trace()
   {
       $post = json_encode($_POST,  JSON_UNESCAPED_UNICODE);
       $post = strval($post);
       $time = date("Y-m-d H:i:s");
       Db::table('bb_alitemp')->insert(['content'=> $post,'create_time'=>$time ]);
       if (isset($_POST['Data'])) {
           Db::table('bb_alitemp')->insert(['content'=> $_POST['Data'],'create_time'=>$time ]);
       }
       return [
           "EBusinessID"=> '1262605', //这是我公司在快递鸟的id
           "UpdateTime" => date("Y-m-d H:i:s"),
           "Success"=>true,
           "Reason"=>'',
            
       ];
       
   }
   
   /**
    * 物流轨迹。暂时使用立即查询的接口
    * @param unknown $order
    */
   public function guiji($order='')
   {
       $order_obj = \app\shop\model\Order::get(['serial' => $order ]);
//        dump($order);
       if (!$order_obj) {
           return ['code'=>0,'message'=>'订单不存在'];
       }
      // dump(33);
       
       if (    (!$order_obj->getData('logistics')) || 
               (!$order_obj->getData('logistics_company'))
          ) {
           return ['code'=>0,'message'=>'此订单尚未发货，请耐心等待。'];
       }
      //echo 11;exit();
       $wuliu_help = new \BBExtend\pay\Kuaidi();
       $result= $wuliu_help->query_at_once($order_obj->getData('logistics_company'),
               $order_obj->getData('logistics') );
       return $result;
   }
   
   /**
    * 用户签收。非常简单的接口
    * @param unknown $order
    */
   public function qianshou($order)
   {
       $order = \app\shop\model\Order::get(['serial' => $order ]);
       if (!$order) {
           return ['code'=>0,'message'=>'订单不存在'];
       }
       if ($order->getData('logistics_is_complete')) {
           return ['code'=>0,'message'=>'订单已签收'];
       }
       $order->setAttr('logistics_is_complete', 1);
       $order->save();
       return ['code'=>1,'message'=>'ok'];
   }
   
   
   /**
    * 本接口由管理后台调用
    * 
    * 订阅接口，只提供商城订单号
    * 使用接口前提：订单已付款，订单已物流下单，已物流取件，填写了物流单号和物流公司代号，
    * 
    * @param unknown $order
    */
   public function dingyue($order)
   {
       $order = \app\shop\model\Order::get(['serial' => $order ]);
       if (!$order) {
           return ['code'=>0,'message'=>'订单不存在'];
       }
       if ($order->getData('is_success')==0 ||
          $order->getData('logistics_is_order')==0 ||
          $order->getData('logistics_is_pickup')==0 ||
          (!$order->getData('logistics')) ||
          (!$order->getData('logistics_company')) 
       ) {
          return ['code'=>0,'message'=>'自查前提不满足，使用接口前提：订单已付款，订单已物流下单，'.
             '已物流取件，已填写了物流单号和物流公司代号',];
       }
       $wuliu_help = new \BBExtend\pay\Kuaidi();
       $result= $wuliu_help->dingyue($order->getData('logistics_company'), 
               $order->getData('logistics') );
       return $result;
   }
   
   /**
    * 本接口由管理后台调用
    * 
    * 下单接口，提供商城订单号，和物流公司代号
    * $order  订单号
    * $company 物流公司代号
    * 
    */
   public function xiadan($order='',$company='')
   {
       $wuliu_help = new \BBExtend\pay\Kuaidi();
       
       $company_list = $wuliu_help->get_company();
       $company_list = array_keys($company_list);
       if (!in_array( $company, $company_list )) {
           return ['code'=>0, 'message' => '未选择物流公司' ];
       }
       
       $order_obj = \app\shop\model\Order::get(['serial' => $order ]);
       if (!$order_obj) {
           return ['code'=>0,'message'=>'订单不存在'];
       }
       $goods = \app\shop\model\ShopGoods::get($order_obj->goods_id );
       if (!$goods) {
           return ['code'=>0,'message'=>'订单中的商品不存在'];
       }
//        $user = \app\shop\model\Users::get($order->uid);
//        if (!$user) {
//            return ['code'=>0,'message'=>'用户不存在'];
//        }
       $address = \app\shop\model\Address::get($order_obj->address_id);
       if (!$address) {
           return ['code'=>0,'message'=>'地址不存在'];
       }
       
       
       
       $result = $wuliu_help->set_wuliuhao($company) //选择快递公司
           ->set_order($order)                       //我公司订单号
           ->set_goods_name($goods->getData('title'))
           ->set_receiver_name($address->getData('name'))
           ->set_receiver_phone($address->getData('phone'))
           ->set_receiver_province($address->getData('province'))
           ->set_receiver_city($address->getData('city'))
           ->set_receiver_area(strval($address->getData('area')))
           ->set_receiver_address($address->getData('street'))
           ->send_request();
       return $result;
       //dump($result);
       
   }
   
   
}