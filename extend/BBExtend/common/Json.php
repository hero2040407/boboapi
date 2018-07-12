<?php

namespace BBExtend\common;

/**
 * 通用 json帮助类 转义，反转义类
 * 
 * 
 * @author 谢烨
 */
class Json {
    /**
     *  将php数组 编码成 json          
     * @return {string} 转义后的字符串
     */
    public static function encode($arr) {
        $result = json_encode($arr, JSON_UNESCAPED_UNICODE );
        return $result;
        
    }
    
    /**
     * 将json字符串 还原成php数组
     * @param unknown $s
     * @return mixed
     */
    public static function decode($s) {
        $result = json_decode($s, true);
        return $result;
        
    }
    
    /**
     * 去除bom头后的 解析json格式
     *
     * xieye
     */
    function trim_json_decode($s)
    {
        $s =strval($s);
        $s = trim( $s, "\xEF\xBB\xBF" );
        return json_decode($s, true );
    }
    
    
}

