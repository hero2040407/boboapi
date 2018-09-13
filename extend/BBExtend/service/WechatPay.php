<?php
namespace BBExtend\service;

/**
 * 自己做一个微信服务
 * 
 * 让微信各种功能好用一些。
 * 
 * 
 * 微信支付分 app支付，公众号支付，H5支付。
 * 
 * 
 * 
 * @author xieye
 *
 */
class WechatPay 
{
    private $response; // 这是微信服务器的信息，表示支付是否成功。
    private $my_success_answer; // 这是我对微信服务器的应答。
    
    public $pay_business_type; // 支付的类型，例如大赛等。
    
    /**
     * 查询是否是 微信服务器发来的请求。
     */
    public function pay_check_server(){
        
    }
    
    /**
     * 检查本次发来的请求，是否 成功付款。
     */
    public function pay_check_pay_success(){
        
    }
    
    
    /**
     * 远程验证微信支付订单，是否真的成功。
     */
    public function pay_check_remote_success(){
        
    }
    
    /**
     * 返回支付成功时的
     */
    public function pay_success_answer(){
        
    }
    
    
    
    /**
     * app统一下单
     */
    public function pay_tongyi_xiadan_app()
    {
        
        
    }
    
    /**
     * 公众号统一下单
     */
    public function pay_tongyi_xiadan_official_account()
    {
        
        
    }
    
    /**
     * H5统一下单,这个是没有的，放在这里看看用。
     */
    public function pay_tongyi_xiadan_h5()
    {
        
        
    }
    
    
    
}