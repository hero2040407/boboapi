<?php
/* *
 * 配置文件
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['partner']		= '2088421400078132';

$alipay_config['seller_id']     = config('wechat.ali_seller_id');

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$alipay_config['private_key']	= '-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgQDCxr7grEyzNl75tjtwxN46LShclan+YF25dTlRzhukLquKWV2w
twskEyY52+WjCqoX/gUnGWKR8fzoEGYICjkIiIJjwYXyu4j4iIz3/3dSpAiH2GT7
LSEtuGw8P3qMe2JBouE90rQipCnNUqPTnxovfIE1R2ZF/Tg3uBs6OkVwdQIDAQAB
AoGAALRRSmS1HkzLCSIkkuLTXuh85eDQrY/RpvMKkwYoyW41xplOIm53Btle4QSv
juhh9xY/FIYd+iMi4//zLoJC9IhRqGKvQrm0AGO3UK71nL2c8iOd0nGtLLsHzYJB
iO0HOm5R5s3+tyrOroXO8tjxm6z3zMnVgEu+Wvmx0iCVqr0CQQD6f9/H8mYbPqi9
CUUI8NCP9kVhxm8ILAarrcdBP7cC4K8tcBBC+nEJx9vTO5MkVSPHOR3a3HSZ1K8j
ppU62Bv7AkEAxw2i7hOlglcsdFb7CaYoLlV/bPCTKsUveC9KzZM2E+JKk1Pg+y4t
QfjPe6hahRRPIvHw7u0NnQOzu4/XU+cKTwJAIyg7Uia1KfG7YPyiEcUqoGniBv0A
rFbxgLrdEk1M9DxwmaH2xk+7+bFxKs5bsme4o8diZ0s1mjl9czV4EFAwbQJAP9Gr
b1F1Ozjf090fV5SiRVi8Jh1r0cau1YW0If0U1YM0DdBSzbWcZQ5011y+yPQd+0I+
0RvHxZOuSHBxxUDKGQJAbmZgdft3AYBvXn6A/9VtYbLinfYPyNkyRq/dsxeVjNWt
GaMj3eiMyEPVrCQNwp8pM6pw3NZRlP9Upht1grdP2g==
-----END RSA PRIVATE KEY-----';

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';


//异步通知接口
$alipay_config['service']= 'https://bobo.yimwing.com/shop/api/alipay_notify';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//签名方式 不需修改
$alipay_config['sign_type']    = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'/cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
?>