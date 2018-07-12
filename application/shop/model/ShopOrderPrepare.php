<?php
namespace app\shop\model;

use think\Model;

/**
 * 临时订单模型类
 * @author Administrator
 *
 */
class ShopOrderPrepare extends Model
{
    protected $autoWriteTimestamp = true; //自动加时间字段。
    protected $table = 'bb_shop_order_prepare';
}
