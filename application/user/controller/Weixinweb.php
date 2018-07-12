<?php
/**
 * 本类是一些微信相关的公共类。
 * User: 谢烨
 */

namespace app\user\controller;
use BBExtend\Sys;

use BBExtend\common\Json;
class Weixinweb
{
    const appid = 'wx190ef9ba551856b0';
    const secret = '55a4e4aa42e36a3691ee242c967ffd5f';
    
    public function login($code)
    {
        if (!$code) {
            return ['code'=>0,'message'=>'code err'];
        }
        
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='. self::appid . 
         '&secret=' . self::secret . '&code='. $code.'&grant_type=authorization_code' ;
        $result = file_get_contents ( $url );
      //  $redis->set ( $key, $result ); // 保存在redis里的是一个json字符串，包括token和失效时间。
        $json = json_decode ( $result, true );
     //   $redis->setTimeout ( $key, $json ['expires_in'] );
        
     //   $json = json_decode ( $result, true );
        if ($json && isset( $json['access_token'] ) && isset( $json['unionid'] )   ){
          return ['code'=>1,'data' =>$json ] ;
          
          
          
        }
        return ['code'=>0,'message'=>'解析错误'];
    }
    
    
   
    
    
}
