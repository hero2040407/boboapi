<?php

namespace BBExtend\common;

/**
 * 通用
 *
 *
 * @author 谢烨
 */
class PicPrefixUrl 
{
    /**
     * 二维数组加前缀
     * @param unknown $arr
     * @param unknown $key
     */
    public static function  add_pic_prefix_for_arr($arr, $key, $force_has_pic=0)
    {
       $arr =(array)$arr; 
       if (!$arr) {
           return $arr;
       }
       foreach ($arr as $k => $v) {
           if (array_key_exists($key, $v)) {
               $arr[$k][$key] = self::add_pic_prefix($v[$key], $force_has_pic);
           }
       }
       return $arr;
    }
    
    
    public static function add_pic_prefix($pic, $force_has_pic=0)
    {
        if (!$pic) {
            if ($force_has_pic) {
                return  \BBExtend\common\BBConfig::get_server_url(). '/public/toppic/topdefault.png';
            }
            
            return ''; 
        }else {
            if (preg_match('#^http#', $pic)) {
                return $pic;
            }else {
                return \BBExtend\common\BBConfig::get_server_url().$pic;
            }
        }
    }
    
    /**
     * 以下2方法为过度措施。https临时修改。
     * @param unknown $pic
     * @param number $force_has_pic
     */
    public static function add_pic_prefix_https($pic, $force_has_pic=0)
    {
        if (!$pic) {
            if ($force_has_pic) {
                return  \BBExtend\common\BBConfig::get_server_url_https() . '/public/toppic/topdefault.png';
            }
    
            return '';
        }else {
            if (preg_match('#^http#', $pic)) {
                return $pic;
            }else {
                return \BBExtend\common\BBConfig::get_server_url_https() .$pic;
            }
        }
    }
    
    /**
     * 直播用，
     * 201709
     * \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default()
     */
    public static function add_pic_prefix_https_use_default($pic, $default_pic='')
    {
        if (!$pic) {
//             if ($force_has_pic) {
//                 return  \BBExtend\common\BBConfig::get_server_url_https() . '/public/toppic/topdefault.png';
//             }
    
            return $default_pic;
        }else {
            if (preg_match('#^http#', $pic)) {
                return $pic;
            }else {
                // 谢烨，20171018临时修改。
                if (\BBExtend\Sys::get_machine_name()=='245') {
                    $temp = \BBExtend\common\BBConfig::get_server_url_https() .$pic;
                    return preg_replace('/test.yim/', 'bobo.yim', $temp);
                }
                
                return \BBExtend\common\BBConfig::get_server_url_https() .$pic;
            }
        }
    }
    
    
    public static function  add_pic_prefix_for_arr_https($arr, $key, $force_has_pic=0)
    {
        $arr =(array)$arr;
        if (!$arr) {
            return $arr;
        }
        foreach ($arr as $k => $v) {
            if (array_key_exists($key, $v)) {
                $arr[$k][$key] = self::add_pic_prefix_https($v[$key], $force_has_pic);
            }
        }
        return $arr;
    }
    
    
    
}//end class

