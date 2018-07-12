<?php
namespace BBExtend\pay;


/**
 * 商城签名校验方案。
 * 
 * @author 谢烨
 * 
 * 2016 10 13
 *
 */
class Sign 
{
    /**
     * 错误信息
     * @var string
     */
    public $message='';
        
    /**
     * 根据签名校验，10.0.0.200文档有《签名校验方案》
     * 
     * 只能一小时误差内
     * 
     * @param int $v
     * @param int $uid
     * @param int $time
     * @param string $sign
     * @return boolean
     */
    public  function check($v, $uid, $time, $sign )
    {
        $v =intval($v);
        $uid = intval($uid);
        $time = intval($time);
        $sign = strval($sign);
        
        if ($v==0) {
            return true;
        }
        
        if (!$sign) {
            $this->message = '缺少签名信息';
            return false;
        }
        
        if ($v!=2) {
            $this->message = '版本错误1';
            return false;
        }
        $server_time =time();
        
        if ( ($time < $server_time - 3600) || ($time > $server_time + 3600)  ) {
            $this->message = '时间错误';
            return false;
        }
        
        $user = \BBExtend\BBUser::get_user($uid);
        if (!$user) {
            $this->message = "用户不存在";
            return false;
        }
        
        $token = $user['userlogin_token'];
        if (!$token) {
            $this->message = "token不存在";
            return false;
        }
        
        $s = strval($uid) . strval($time) . substr($token, 0, 10) . 'C0W509' ;
        $s =  strtolower( substr(md5($s), 0, 12) );
        if ($s != $sign) {
            $this->message = 'sign校验错误';
            return false;
        }
        return true;
    }
    
    /**
     * 返回错误信息
     * @return string
     */
    public function get_info()
    {
        return $this->message;
    }
   
    
}
