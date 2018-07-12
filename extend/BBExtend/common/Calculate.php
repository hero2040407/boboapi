<?php
/**
 * 通用函数
 * 
 * 
 * @author 谢烨
 */
class Public_Calculate
{
    
    /**
     * 提供两数相除的结果11111111，百分数显示
     */
    public static function div_scale($a, $b)
    {
        if ($b < 0.00001)
            return 0;
        $temp = self::decimal($a / $b, 4);
        $temp = $temp * 100;
        $temp .= '%';
        return $temp;
    }
    
    /**
     * 提供两数相除的结果11111111，百分数显示
     */
    public static function div_scale_int($a, $b)
    {
        if ($b < 0.00001)
            return 0;
        $temp = self::decimal($a / $b, 2);
        $temp = $temp * 100;
        $temp .= '%';
        return $temp;
    }
    
    /**
     * 提供两数相除的结果11111111
     */
    public static function div($a, $b, $decimals=2)
    {
        if ($b < 0.00001)
            return 0;
        return self::decimal($a / $b, $decimals);
    }
    
    /**
     * 将一个数字美化为两位小数
     */
    public static function sprintf($s)
    {
        return sprintf('%1.2f', $s);
    }
    
    /**
     * 格式化为2位小数，或者自定义
     */
    public static function decimal($s, $decimals=2)
    {
        return number_format($s, $decimals, '.','');
    }
    
    
    
  
}//end class

