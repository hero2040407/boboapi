<?php



define('SHOP_TYPE_HEAD',100);//热门
define('SHOP_TYPE_REC',101);//推荐
define('SHOP_TYPE_ZHE',102);//折扣
define('SHOP_TYPE_MY',103);//我能兑换

define('SHOP_TYPE_MONEY',1); //RMB购买
define('SHOP_TYPE_GOLD',2);//虚拟货币购买
/**
 * 支付用阿里网关回调
 */
function ali_gateway()
{
    return 'https://bobo.yimwing.com/shop/api/alipay_notify';
   // return 'http://test.yimwing.com/shop/api/alipay_notify';
    
}
/**
 * 支付用微信网关回调
 */
function wx_gateway()
{
  //  $url = \BBExtend\common\BBConfig::get_server_url_https();
    return  'https://bobo.yimwing.com/shop/api/wxpay_notify';
}
/**
 * 设置一批测试帐号，在正式服和测试服都起作用，
 * 买东西或充值，都只需1分钱。
 */
function get_test_userid_arr()
{
    
    // 这是一个保险的设置
    $time = strtotime("2018-07-01 00:00:00");
    $time+= 30  *24*3600;
    if (time() > $time ) {
        return [];
    }
  
    
    //10023 沈德志
    // 12138 小宋
    // 10007 沈德志
    return [7049564,10010 ];
    
}



