<?php
namespace BBExtend\common;
/**
 * 通用html函数
 * 
 * 
 * @author 谢烨
 */
class Pwd {
    
    /**
     * 管理员密码强度测试
     * 
     * @param unknown $pwd
     * @return number[]|string[]
     */
    public static function check_amdin($pwd) {
        if ($pwd == null) {
            return ['code' => 0,  'message' => '密码不能为空'];
        }
        $pwd = trim($pwd);
        
        if (strlen($pwd) < 6) {//必须大于6个字符
            return ['code' => 0,  'message' => '密码必须大于6字符'];
        }
       
        if (preg_match("/^[a-zA-Z]+$/", $pwd)) {
            return ['code' => 0,  'message' => '密码不能全是字母，请包含数字，字母大小写或者特殊字符'];
        }
        if (preg_match("/^[0-9A-Z]+$/", $pwd)) {
            return ['code' => 0,  'message' => '密码请包含数字，字母大小写或者特殊字符'];
        }
        if (preg_match("/^[0-9a-z]+$/", $pwd)) {
            return ['code' => 0,  'message' => '密码请包含数字，字母大小写或者特殊字符'];
        }
        return ['code' => 1, ];
    }
    
    /**
     * 包含大写，小写，数字的6位密码。
     * 
     * @return string
     */
    public static function create_full_pass()
    {
        $str1="QWERTYUIOPASDFGHJKLZXCVBNM";
        $str2="qwertyuiopasdfghjklzxcvbnm";
        $str3="1234567890";
        
        $s1 = substr(str_shuffle($str1),0,2);
        $s2 = substr(str_shuffle($str2),0,2);
        $s3 = substr(str_shuffle($str3),0,2);
        
        $s = $s1.$s2.$s3;
        
        
        
        return str_shuffle($s);
        
        
    }
    
        
}//end class

