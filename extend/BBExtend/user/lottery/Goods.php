<?php
namespace BBExtend\user\lottery;

/**
 * 关注类
 * 
 * 数量用最简单的set单变量！
 * 关注人列表用集合
 * 
 * 谢烨
 */

use BBExtend\Sys;
use think\Db;


class Goods
{
    
    public $uid;
    public $datestr;// 类似20170801
    public  $goods_id;
    /**
     * 
     * @param number $uid
     */
    public function  __construct($uid=0,$goods_id=0) {
        $uid = intval($uid);
        $this->uid = $uid;
        $this->goods_id=intval($goods_id);
        $datestr = $this->datestr = date("Ymd");
    }
    
    public static function getInstance($uid,$goods_id)
    {
        return new self($uid,$goods_id);
    }
    
    /**
    *  兑换商品
     */
    public function exchange()
    {
        $uid = $this->uid;    
        $db = Sys::get_container_db(); 
        $datestr = date("Ymd");
        $goods_id = $this->goods_id;
        
        $user =  \app\shop\model\Users::get($uid);
        $address =  \app\shop\model\Address::get($address_id);
        if (!$address) {
            return ["message"=>'地址不存在','code'=>0 ];
        }
        $goods =  \app\shop\model\ShopGoods::get($goods_id);
       
    }
    

}