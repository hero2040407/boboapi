<?php

namespace BBExtend\common;

/**
 * 通用xml函数
 * 
 * 
 * @author 谢烨
 */
class Unicode {
    //数组转xml
    public static function num2byte($num)
    {
        if (is_array($num)) {
            $bytes='';
            foreach ($num as $v) {
                $bytes .= self::utf8_bytes($v);
            }
            return $bytes;
        }else {
            return self::utf8_bytes($num);
        }
    }
    
    private static function utf8_bytes($cp){
        if ($cp > 0x10000){
            # 4 bytes
            return	chr(0xF0 | (($cp & 0x1C0000) >> 18)).
            chr(0x80 | (($cp & 0x3F000) >> 12)).
            chr(0x80 | (($cp & 0xFC0) >> 6)).
            chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x800){
            # 3 bytes
            return	chr(0xE0 | (($cp & 0xF000) >> 12)).
            chr(0x80 | (($cp & 0xFC0) >> 6)).
            chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x80){
            # 2 bytes
            return	chr(0xC0 | (($cp & 0x7C0) >> 6)).
            chr(0x80 | ($cp & 0x3F));
        }else{
            # 1 byte
            return chr($cp);
        }
    }
    
}

