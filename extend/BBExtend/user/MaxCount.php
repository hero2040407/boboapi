<?php
namespace BBExtend\user;

use BBExtend\Sys;
use BBExtend\common\Json;
use BBExtend\fix\TableType;
use BBExtend\BBUser;
use BBExtend\Currency;
use BBExtend\DbSelect;

/**
 * 微信公众号相关类
 *
 */
class MaxCount
{
    public static function get_max($type, $id)
    {
        $mod = $id % 9;
        $mod = $mod+10;
        $mod = $mod / 10; // 这是一个小数。1.0到1.9之间。
        
        if ($type == 'news') {
            $base = 200000;
           
        }
        
        if ($type == 'updates') {
            $base = 100000;
        }
        
        if ($type == 'star_updates') {
            $base = 200000;
        }
        
        return $base * $mod;
        
    }
    
    
    
}