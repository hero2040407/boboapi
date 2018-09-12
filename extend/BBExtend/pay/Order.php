<?php
/**
 * 订单生成类库
 * 
 * User: 谢烨
 */
namespace BBExtend\pay;

class Order
{
    //安卓充值波币。
    //p表示pay充值，A表示安卓。
    public static function get_order_serial_PA()
    {
        return "PA"  . self::get_part_of_serial();
    }
    
    //通告 购买 试镜卡。
    public static function get_order_serial_TG()
    {
        return "TG"  . self::get_part_of_serial();
    }
    
    // vip 申请成为童星。
    public static function get_order_serial_vip()
    {
        return "XX1"  . self::get_part_of_serial();
    }
    
    // 报名，可能是从系统消息里加载的。
    public static function get_order_serial_baoming()
    {
        return "BM"  . self::get_part_of_serial();
    }
    
    // 商城购物
    public static function get_order_serial_shop()
    {
        if (mt_rand(1,10) > 5 ) {
            $pre = "BA";
        }else {
            $pre = "BI";
        }
        
        return $pre . self::get_part_of_serial();
    }
    
    // 大赛报名
    public static function get_order_serial_race()
    {
        return 'DS' . self::get_part_of_serial();
    }
    
    
    
    
    
    // 得到一个随机字符串。
    private static function get_part_of_serial(){
        return date("Ymd") . strtoupper(dechex(date('m'))) . date('d') . 
            substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    }
    
    
}