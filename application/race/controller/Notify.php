<?php
namespace app\race\controller;
// use BBExtend\BBShop;
use think\Controller;
use BBExtend\Sys;
/**
 *
 * 大赛
 *
 * 2017 03
 * User: xieye
 */
class Notify extends Controller
{
    /**
     *
     * 大赛专用。打赏专用。
     * 这是微信支付 的 回调，由 微信服务器向此接口发起请求。
     *
     * @return number[]|string[]|number[]|string[][]|boolean[][]|number[][]|mixed[][]
     */
    public function index( )
    {
        $help = new \BBExtend\pay\wxpay\HelpWeb();
        $result = $help->receive_post();
        echo $result;
    }

    /**
     *
     * 微信支付投票回调地址
     */
    public function wxindex( )
    {
        $help = new \BBExtend\pay\wxpay\HelpWeb();
        $result = $help->wx_receive();
        echo $result;
    }


}
