<?php
/**
 * 通用函数
 * 
 * 注意：使用了常量
 * LOGIN_ENCRYPT_KEY
 * LOGIN_COOKIE_KEY
 * CURRENT_TIME
 * 
 * 使用了全局变量 $g_var;
 * 本类也不定义，假定一定存在。
 * 
 * 
 * @author 谢烨
 */
class Public_Cookie
{
    /**
     * 设置cookie，cookie的名字是常量LOGIN_COOKIE_KEY，
     * 目前设置为全局有效。域名为 / 
     * 
     * @param string $value 待设置的值
     * @param number $expires 存活时间
     * @param boolean $secure
     * @param boolean $httponly
     */
    public static function setcookie($value , $expires = 0, $secure = false, $httponly = false)
    {
        //值加密
        if ($value) {
            $value = Public_Encrypt::encrypt($value,LOGIN_ENCRYPT_KEY);
        }
        //延时
        $expires = $expires > 0 ? CURRENT_TIME + $expires : 0;
        $_SERVER["HTTP_HOST"]='';
        global $g_var;
        //php自带函数，
        // 1 名 2 值
        // 3 延时，4 路径
        // 5 域名 6 仅https通过，7，仅https通过。
        setcookie(LOGIN_COOKIE_KEY, $value, $expires, '/', '.' .  $g_var['child_url'] ,
            $secure, $httponly);
    }
    
  
}//end class

