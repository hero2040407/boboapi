<?php
namespace app\shop\controller;
use think\Db;
use app\shop\model\ShopGoods;
use app\shop\model\Order;
use app\shop\model\Users;
use app\shop\model\Address;
use think\Controller;
/**
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Myorder  extends Controller
{
    
    public function _initialize()
    {
        $request = request();
        $chekc_action =['index', 'before_shipment', 'before_receive', 'after_receive',
            'detail', ];
        if ( in_array( $request->action(), $chekc_action )) {
            $help = new \BBExtend\pay\Sign();
            $result = $help->check(input('param.v'), input('param.uid'), 
                input('param.time'), input('param.sign')      );
            if (!$result) {
                echo json_encode(["code"=>0, "message"=>$help->get_info() ] ,
                        JSON_UNESCAPED_UNICODE);
                exit();
            }
        }
    }
    
    /**
     * 单个订单详情。
     * @param string $order
     */
    public function detail($order='')
    {
        if (!$order) {
            return ['code'=>0,'message'=>"订单不存在"];
        }
        $order = Order::get(["serial" => $order ]);
        if (!$order) {
            return ['code'=>0,'message'=>"订单不存在"];
        }
        
        $goods = ShopGoods::get($order->getData('goods_id'));
        if (!$goods) {
            return ['code'=>0,'message' => "商品不存在"];
        }
        
        $address = Address::get($order->getData("address_id"));
        if (!$address) {
            return ['code'=>0,'message'=>'地址不存在'];
        }
        
        $state =$order->get_logistics_title();
        $logistics ='';
        $logistics_company ='';
        $help = new \BBExtend\pay\Kuaidi();
        $company_list = $help->get_company();
        
        
        if ($state != "待发货") {
            $logistics = $order->getData("logistics");
            $daihao = $order->getData("logistics_company");
            
            if ( isset( $company_list[$daihao] ) ) {
                 $logistics_company =  $company_list[$daihao];
            }
        }
        
        
        //商品名称，规格，样式。
        //收货人姓名，收货人地址。收货人电话
        //商城订单号，商城订单下单时间。
        //物流单号，物流公司名
        //注意，如果物流状态为待发货，客户端请勿显示物流单号，和物流公司名
        //物流状态为已发货，
        $temp1 = strval($address->getData('province')) . 
            strval($address->getData('city')) .
            strval($address->getData('area')) .
            strval($address->getData('street')) ;
         
        $data =[
            'goods_title' => $goods->getData("title"),
            "model" => $order->getData("model"),
            "style" => $order->getData("style"),
            "receiver_name" => $address->getData('name'),
            "receiver_phone" => $address->getData('phone')? 
                $address->getData('phone'):$address->getData('tel') ,
            "receiver_address" =>  $temp1 ,
            "serial" => $order->getData("serial"),
            "create_time" => $order->getData("create_time"),
            "logistics" => $logistics,
            "logistics_company"=> $logistics_company,
            "pic"  =>  $goods->right_style_pic( $order->getData("style")),
            
        ];
        
        return ["code"=>1,"data"=>$data];
    }
    
    private function list2($uid=0,$start=0,$length=1,$where='')
    {
        $uid = intval($uid);
        $start = intval($start);
        $length = intval($length);
        
        $user = Users::get($uid);
        if (!$user) {
            return ['code'=>0, 'message'=>'用户不存在'];
        }
        if ($start <0) {
            $start=0;
        }
        if ($length<1) {
            $length=1;
        }
        
        if ($where) {
            $where = " and {$where} ";
        }
        
        $sql ="
        select
logistics,logistics_company, price , type, serial,
bb_shop_order.create_time, count,model,style,logistics_is_complete,
logistics_is_pickup, goods_id,address_id,
bb_shop_goods.title
from bb_shop_order
left join bb_shop_goods
on bb_shop_goods.id = bb_shop_order.goods_id
where uid = {$uid}
     and bb_shop_order.is_user_delete = 0
{$where}
order by bb_shop_order.create_time desc
limit {$start},{$length}
                ";
        $arr = Db::query($sql);
        $arr = (array)$arr;
        if (count($arr) == $length ) {
            $has_next =1;
        }else {
            $has_next=0;
        
        }
        //20160912 ，杨桦要求加地址。
        foreach ($arr as $k => $v) {
            $address = Address::get($v['address_id'] );
            if (!$address) {
                
                $arr[$k]['receiver_name'] = '地址不存在';
                $arr[$k]['receiver_phone'] ='地址不存在';
                $arr[$k]['receiver_address'] = '地址不存在';
                
                //return ['code'=>0,'message'=>'地址不存在'];
            }else {
            
                $temp1 = strval($address->getData('province')) .
                    strval($address->getData('city')) .
                    strval($address->getData('area')) .
                    strval($address->getData('street')) ;
                 $arr[$k]['receiver_name'] = $address->getData('name');
                 $arr[$k]['receiver_phone'] = $address->getData('phone')?
                    $address->getData('phone'):$address->getData('tel');
                 $arr[$k]['receiver_address'] = $temp1;
            }
        }
        
      
        
        foreach ( $arr as $k=>$v ) {
            $temp = ShopGoods::get($v['goods_id']);
            if (!$temp) {
                return ['code'=>0, 'message'=>"商品不存在"];
            }
            $arr[$k]['pic'] = $temp->right_style_pic($v['style']);
        }
        $help = new \BBExtend\pay\Kuaidi();
        
        $hanzi_arr = $help->get_company();
        //汉字名称
        foreach ( $arr as $k=>$v ) {
            $temp = $v['logistics_company'];
            if ( array_key_exists($temp, $hanzi_arr) ) {
                $arr[$k]['company_hanzi'] = $hanzi_arr[$temp];
            }else {
                $arr[$k]['company_hanzi'] = '';
            }
        }
        
        
        return [ 'code'=>1, 'start'=>$start, 'length'=>$length,
            'is_bottom'=>1-$has_next,
            'data' => $arr,
        ];
    }
    /**
     * 全部的接口
     * @param number $uid
     * @param number $start
     * @param number $length
     */
    public function index($uid=0,$start=0,$length=1)
    {
        return $this->list2($uid,$start,$length);
    }
  
    /**
     * 待发货
     */
    public function before_shipment($uid=0,$start=0,$length=1){
        return $this->list2($uid,$start,$length, 'logistics_is_pickup=0  and  logistics_is_complete=0 ');
    }
    
    /**
     * 待收货
     */
    public function before_receive($uid=0,$start=0,$length=1){
        return $this->list2($uid,$start,$length, 'logistics_is_pickup=1 '.
                 'and logistics_is_complete=0');
    }
    
    /**
     * 已收货
     */
    public function after_receive($uid=0,$start=0,$length=1){
        return $this->list2($uid,$start,$length, 'logistics_is_complete=1');
    }
    
    
}