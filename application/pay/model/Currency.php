<?php
namespace app\pay\model;

use think\Model;
use think\Db;
/**
 * 用户模型类
 * @author xieye
 *
 */
class Currency extends Model
{
    
    protected $table = 'bb_currency';
    
    /**
     * 工厂方法，根据用户id返回本对象实例，如没有会先自动插入表中。
     * 
     * 注意本方法不判断用户id是否存在，在外面判断
     * @param unknown $uid
     * @return \app\pay\model\Currency
     */
    public static function factory($uid){
        $uid =intval($uid);
        if (!$uid) {
            exit();
        }
        //
        //先查是否存在记录
        $result = Db::table('bb_currency')->where('uid',$uid)->find();
        if ($result) {
            return self::get($result['id']);
        }else {
            $currency = new self();
            $currency->data('uid', $uid);
            $currency->data('gold', 0);
            $currency->data('gold_income', 0);
            $currency->data('flower', 0);
            $currency->data('discount', 0);
            $currency->data('monster', 0);
            $currency->data('score', 0);
            $currency->save();
            return $currency;
        }
    }
    
    
}