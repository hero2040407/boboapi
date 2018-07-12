<?php
namespace app\shop\controller;

use think\Db;
use app\shop\model\ShopGoods;
use app\shop\model\Order;
use app\shop\model\Users;
use app\shop\model\Address;
use BBExtend\pay\wxpay\Help;
use BBExtend\pay\alipay\AlipayHelp;

/**
 * 该类的作用：查询订单是否付款成功，支付宝和微信
 * 
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Orderquery
{
    
    public function remove($order='') {
        $order_obj = Order::get(["serial" => $order ]);
        if (!$order_obj) {
            return ['code'=>0,'message'=>"订单不存在"];
        }
        
        if ($order_obj->getData('logistics_is_complete') ==0 ) {
            return ['code'=>0,'message'=>"订单尚未收货，不可用户删除"];
        }
        if ($order_obj->getData('is_user_delete') ==1 ) {
            return ['code'=>0,'message'=>"订单已被用户删除，不可重复删除"];
        }
        $order_obj->setAttr('is_user_delete', 1);
        $order_obj->save();
        return ['code'=>1,'data'=>["success" =>1 ]];
    }
    
    
    public function wx_query($order='')
    {
        $help = new Help();
        return $help->query_remote($order);
        
    }
    
    public function index($order='')
    {
//         
        
        //如果b开头，查临时订单表
        if (preg_match('#^B#', $order)) {
            $order_obj = ShopOrderPrepare::get(["serial" => $order ]);
            if (!$order_obj) {
                return ['code'=>0,'message'=>"订单不存在"];
            }
            $paytype = $order_obj->getData("third_name");
            if (!in_array($paytype, array( 'ali', 'wx' ))) {
                return ["code"=>0, "message"=>"paytype错误"];
            }
            
            if ($paytype=='wx') {
                $help = new Help();
                return $help->query_remote($order);
            }
            if ($paytype=='ali') {
                $help = new AlipayHelp();
                return $help->query_remote($order);
            }
        }
        if (preg_match('#^PA#', $order)) {
            $order_obj = \app\pay\model\Buy::get(["order" => $order ]);
            if (!$order_obj) {
                return ['code'=>0,'message'=>"订单不存在"];
            }
            $paytype = $order_obj->getData("third_name");
            if (!in_array($paytype, array( 'ali', 'wx' ))) {
                return ["code"=>0, "message"=>"paytype错误"];
            }
         
            if ($paytype=='wx') {
                $help = new Help();
                return $help->query_remote($order);
            }
            if ($paytype=='ali') {
               // echo  1135;
                $help = new AlipayHelp();
               // echo 7;
                return $help->query_remote($order);
            }
        }
        //
        
    }
    
}














