<?php
namespace app\pay\model;

use think\Model;
use think\Db;
/**
 * 充值模型类
 * @author Administrator
 *
 */
class Buy extends Model
{
     
    protected $table = 'bb_buy';
    
    /**
     * 该方法是支付宝成功返回后的调用
     * 1、设置successful字段的值。
     * 2、调用user类的pay_success方法。
     */
    public function pay_success()
    {
        // 现在，
    }
    
}
