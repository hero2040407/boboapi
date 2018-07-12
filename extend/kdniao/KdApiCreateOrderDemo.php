<?php
//电商ID
defined('EBusinessID') or define('EBusinessID', 1262605);
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', '08f2e046-fd28-484e-a4fd-7e44cb61219d');

/**
 * Json方式 在线下单
 */
function orderOnlineByJson(){
	$requestData="{'LogisticsWeight':2.0,".
				"'LogisticsVol':2.0,".
				"'HQPOrderDesc':'尽快上门收件,PHP demo测试',".
				"'HQPPayType':1,".
				"'IsNeedPay':2,".
				"'Payment':121.0,".
				"'OrderCode':'test_12345674',".
				"'StartDate':'2015-05-13 21:20:53',".
				"'EndDate':'2015-05-14 21:20:53',".
				"'ShipperCode':'LB',".
				"'LogisticCode':'109932607391',".
				"'ToCompany':'华为科技',".
				"'ToName':'张三',".
				"'ToAddressArea':'深圳市南山区桂庙路555号',".
				"'ToTel':'',".
				"'ToMobile':'13800000000',".
				"'OrderType':2,".
				"'ToPostCode':'518128',".
				"'ToProvinceID':'广东省',".
				"'ToCityID':'深圳市',".
				"'ToExpAreaID':'南山区',".
				"'FromCompany':'小米科技',".
				"'FromName':'李四',".
				"'FromAddressArea':'深圳市福田区华强北路222号',".
				"'FromTel':'88888888',".
				"'FromMobile':'',".
				"'FromPostCode':'529800',".
				"'FromProvinceID':'广东省',".
				"'FromCityID':'深圳市',".
				"'FromExpAreaID':'福田区',".
				"'Cost':21.0,".
				"'OtherCost':2.0,".
				"'Commoditys':".
				"[{".
				"'Goodsquantity':12,".
				"'GoodsName':'手机屏幕',".
				"'GoodsCode':'kjyhu878787',".
				"'GoodsPrice':121.0".
				"}]}";
    $datas = array(
        'EBusinessID' => EBusinessID,
        'RequestType' => '1001',
        'RequestData' => urlencode($requestData) ,
        'DataType' => '2',
    );
    $datas['DataSign'] = encrypt($requestData, AppKey);
	$result=sendPost(ReqURL, $datas);	
	
	//根据公司业务处理返回的信息......
	
	return $result;
}
/**
 *  post提交数据 
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据 
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
    $temps = array();	
    foreach ($datas as $key => $value) {
        $temps[] = sprintf('%s=%s', $key, $value);		
    }	
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader.= "Connection:close\r\n\r\n";
    $httpheader.= $post_data;
    $fd = fsockopen($url_info['host'], 80);
    fwrite($fd, $httpheader);
    $gets = "";
    while (!feof($fd)) {
        $gets.= fread($fd, 128);
    }
    fclose($fd);    
    
    return $gets;
}

/**
 * 电商Sign签名生成
 * @param data 内容   
 * @param appkey Appkey
 * @return DataSign签名
 */
function encrypt($data, $appkey) {
    return urlencode(base64_encode(md5($data.$appkey)));
}

?>