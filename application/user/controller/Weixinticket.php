<?php
/**
 * 本类是一些微信相关的公共类。
 * User: 谢烨
 */

namespace app\user\controller;
use BBExtend\Sys;

use BBExtend\common\Json;
class Weixinticket
{
    
    public function index($url="" )
    {
        
        $jsapi_ticket = Sys::get_wx_gongzhong_ticket(  );
        $noncestr = $this->getNonceStr();
        $timestamp = time();
        
        $string1 = "jsapi_ticket={$jsapi_ticket}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string1);

        return [
                'code'=>1,
                'data'=>[
                        'appId'=>'wx190ef9ba551856b0',
                        'timestamp'=>$timestamp,
                        'nonceStr'=>$noncestr,
                        'signature'=>$signature,

                ],
                
        ];
        
    }

         
    private function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    
}
