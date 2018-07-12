<?php
namespace app\shop\model;

use think\Model;
use think\Db;
/**
 * 订单模型类
 * @author xieye
 *
 */
class Order extends Model
{
    protected $autoWriteTimestamp = true; //自动加时间字段。
    protected $table = 'bb_shop_order';
    
    /**
     * 下单成功
     * 
     * 参数：公司名，物流单号
     */
    public function xiadan($company, $no)
    {
        $this->setAttr('logistics_company', $company);
        $this->setAttr('logistics', $no);
        $this->save();
        
    }
    
    /**
     * 得到订单的状态名，只有待发货，待收货，已收货
     */
    public function get_logistics_title()
    {
        $state ='待发货';
        if ($this->getData('logistics_is_pickup') &&  (!$this->getData('logistics_is_complete') ) ){
            $state ='待收货';
        }
        if ($this->getData('logistics_is_pickup')) {
            $state ='已收货';
        }
        return $state;
    }
    
    //订阅成功
    public function dingyue()
    {
        $this->setAttr('logistics_is_subscribe', 1);
        $this->save();
    }
    
    /**
     * 接收推送
     */
    public function receive($company,$arr)
    {
        $no = $this->getData('logistics');
        $order = $this->getData("serial");
        $time = time();
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!$no) {
            return;
        }
        if ($arr && is_array($arr) && count($arr)>0 ) {
            Db::table('bb_shop_logistics_trace')->where('order_no',$order)->delete();
            foreach ($arr as $v ) {
                $data =[
                    'order_no'=>$order,
                    'logistics'=> $this->getData('logistics'),
                    'company'  => $company,
                    'craete_time' => $time,
                    'remote_addr' => $ip,
                    'accept_time' => $v['AcceptTime'],
                    'accept_station'=> $v['AcceptStation'] ,
                ];
               Db::table('bb_shop_logistics_trace')->insert($data); 
            }
        }
        
    }
    //
    
}
