<?php
/**
 * 本类是一些微信相关的公共类。
 * User: 谢烨
 */

namespace app\user\controller;
use BBExtend\Sys;

use BBExtend\common\Json;
class Weixin
{
    /**
     * 服务端 强制刷新微信token，请勿随便使用，仅用于发现token错误的情况下，才使用本方法。
     * 注意，正常情况下，应该使用Sys::get_wx_gongzhong_token(),而不是本方法
     */
    public function flushtoken() {
        $redis = \BBExtend\Sys::getredis11 ();
    
        $key = 'get_wx_gongzhong_token';
    
        $appid = 'wx190ef9ba551856b0';
        $secret = '55a4e4aa42e36a3691ee242c967ffd5f';
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
        $result = file_get_contents ( $url );
        $redis->set ( $key, $result ); // 保存在redis里的是一个json字符串，包括token和失效时间。
        $json = json_decode ( $result, true );
        $redis->setTimeout ( $key, $json ['expires_in'] );
    
        $json = json_decode ( $result, true );
        return $json ['access_token'];
    }
    
    /**
     * 本方法可以任意调用，用浏览器查看当前token
     */
    public function showtoken()
    {
        echo Sys::get_wx_gongzhong_token();
        exit;
    }
    
    
    /**
     * 创建菜单，利用此接口给我们的公众号创建菜单。
     * 
     * 1、自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。
       2、一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
       3、创建自定义菜单后，菜单的刷新策略是，在用户进入公众号会话页或公众号profile页时，
                如果发现上一次拉取菜单的请求在5分钟以前，就会拉取一下菜单，如果菜单有更新，就会刷新客户端的菜单。
                测试时可以尝试取消关注公众账号后再次关注，则可以看到创建后的效果。
     */
    public function createmenu()
    {
//         $data =[
//             'button'=>[
//                 [
//                     "type" =>'view',   
//                     "name" =>'下载APP',   
//                     "url"  =>'http://www.guaishoubobo.com/downloads/index.html',   
//                 ],
//                 [
//                 "type" =>'view',
//                 "name" =>'参加大赛',
//                 "url"  =>'http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MzIyNjc0NzkyNA==&from=1#wechat_webview_type=1&wechat_redirect',
//                 ],
//             ],
//         ];
        
        $data =[
                'button'=>[
                        [
                                "type" =>'view',
                                "name" =>'下载APP',
                                "url"  =>'http://www.guaishoubobo.com/downloads/index.html',
                        ],
                        [
                                "type" =>'view',
                                "name" =>'参加大赛',
                                "url"  =>'https://bobo.yimwing.com/vue/dasai/#/contest/dasai',
                        ],
                ],
        ];
        
        $token = Sys::get_wx_gongzhong_token();
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
        $headers = array('Content-Type' => 'application/json');
        $response = \Requests::post($url, $headers, Json::encode($data)  );
        var_dump($response->body);
    }
    
    
   
    
    /**
     * 用户关注公众号，微信公司给怪兽的回调接口。
     * 
     * 注意：这也是除了微信支付外，其余所有 我们接收 微信公众号向我们推送的接口。
     * 
     * @author xieye
     *
     */
   public function notify()
   {
   //    Sys::debugxieye("weixin:notify");
       
       $db = Sys::get_container_db();
       
       $xml =file_get_contents("php://input");
       $arr = $this->getwx_push($xml);
       // 记日志，很多代码
       $type ='';
       if (isset($arr['Event'])){
           $type = $arr['Event'];
       }
       $openid='';
       if (isset($arr['FromUserName'])){
           $openid = $arr['FromUserName'];
       }
       
       $db->insert("bb_user_weixin_push_log", [
           'create_time' => date("Y-m-d H:i:s"),
           'info' => $xml,
           'type'=>$type,
           'openid'=>$openid,
       ]);
       //真正执行。
       \BBExtend\user\Weixin::getInstance()->event($arr);
    //   Sys::debugxieye( var_export($_GET,1) );
       if (isset($_GET['echostr'])){
         echo $_GET['echostr'];
       }
       exit;
   }
   
   public function aa()
   {
       $openid = 'oFERUwGQ6Zp99bxmwWEIDXRlbyQ0';
       $token = Sys::get_wx_gongzhong_token();
      $url= "https://api.weixin.qq.com/cgi-bin/user/info?".
          "access_token={$token}&openid={$openid}&lang=zh_CN";
      $response = \Requests::get($url);
      
      $json = Json::decode($response->body);
      return $json['unionid'];
   }
   
    
   /**
    * 获取post数据
    */
   private function getwx_push($xml)
   {
     //  $xml =file_get_contents("php://input");
       libxml_disable_entity_loader(true);
       $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
       return $values;  
       
   }
   
   /**
    * 本函数一般不执行,只用于命令行
    */
   public function get_all(){
       Sys::display_all_error();
       $token = Sys::get_wx_gongzhong_token();
       $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$token;
       $response = \Requests::get($url);
       $db = Sys::get_container_db();
       $json = Json::decode($response->body);
       $arr = $json['data']['openid'];
       $help = \BBExtend\user\Weixin::getInstance();
       
       foreach ($arr as $v) {
           echo $v."\n";
           $sql="select count(*) from bb_user_weixin_id where gz_openid=?";
           $result = $db->fetchOne($sql,  $v);
           if (!$result) {
               // 先查unionid
               $unionid = $help->get_unionid($v);
               $db->insert("bb_user_weixin_id", [
                   "gz_openid" => $v,
                   "create_time" => time(),
                   "is_active" =>1,
                   "unionid" => $unionid,
               ]);
           }
           
           
       }
   }
   
      
}
