<?php
namespace BBExtend\pay;

use think\Db;

/**
 * 整合快递鸟api的帮助类，通过此类调用快递鸟的api。
 * 
 * 各种接口均以 [code:1，date:数据(或message:错误信息)]格式返回
 * 
 * http://www.kdniao.com
GuaiShouBoBo
GSBBabc123w
 * 
 * @author 谢烨
 *
 */
class Kuaidi {
    private $EBusinessID;
    private $AppKey;
    private $xiadan_ReqURL;
    private $dingyue_ReqURL;
    private $query_ReqURL;
    
    private $pay_type;        //支付方式
    
    private $sender_info;
    
    private $wuliu;            //例如SF代表顺风，YTO代表圆通
    private $order;            //订单号，是怪兽岛公司的订单号。
    private $goods_name;       // 商品名称
    
    private $receiver_name;    //收件人姓名 如张三
    private $receiver_phone;   //收件人电话 如15062288888
    private $receiver_province;//收件人省 如江苏省，不要缺少“省”
    private $receiver_city;    //收件人市， 如南京市，不要缺少“市”
    private $receiver_area;    //收件人区，只有区可填可不填。
    private $receiver_address; //收件人 详细地址。如玄武大道699号怪兽岛公司
    
    private $sender_name;      //发件人姓名 如张三
    private $sender_phone;     //发件人电话 如15062288888
    private $sender_province;  //发件人省 如江苏省，不要缺少“省”
    private $sender_city;      //发件人市， 如南京市，不要缺少“市”
    private $sender_area;      //发件人区，只有区可填可不填。
    private $sender_address;   //发件人 详细地址。如玄武大道699号怪兽岛公司
    
    
    public function  __construct(){
        $this->pay_type = 1; //1现付  2到付 3月付。
        
        $this->EBusinessID = '1262605'; //怪兽岛ID
        $this->AppKey = '08f2e046-fd28-484e-a4fd-7e44cb61219d'; //怪兽岛KEY
       // $this->xiadan_ReqURL ='http://testapi.kdniao.cc:8081/api/OOrderService'; //测试网址，正式使用时改用下面的
        $this->xiadan_ReqURL ='http://api.kdniao.cc/api/OOrderService';        //正式网址，使用就真的下单了
       
        //订阅测试网址
       // $this->dingyue_ReqURL ='http://testapi.kdniao.cc:8081/api/dist'; 
        //订阅正式网址
        $this->dingyue_ReqURL ='http://api.kdniao.cc/api/dist';
        
        //立刻查询
        $this->query_ReqURL ='http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';
        
        
        //查我公司地址。
        $info = Db::table('bb_shop_users')->find();
        if (!$info) {
            $info = [
                'name' => '陈岳',
                'phone' =>'15062288888',
                'tel'   => '15062288888',
                'province' => '江苏省',
                'city'    => '南京市',
                'area'    => '玄武区',
                'street'  => '玄武大道699号研发3区',
                'shop_name' => '怪兽岛网络科技有限公司',
            ];
        }
        $this->sender_info = $info;
        
    }
    
    //设置物流公司代号，例如SF代表顺风，YTO代表圆通，仅下单用
    public function set_wuliuhao($wuliu){
        $this->wuliu = $wuliu;
        return $this;
    }
    //设置订单号,我们公司自己的。，仅下单用
    public function set_order($order){
        $this->order = $order;
        return $this;
    }
    //设置商品名称，仅下单用
    public function set_goods_name($title){
        $this->goods_name = $title;
        return $this;
    }
    //仅下单用
    public function set_receiver_name($name){
        $this->receiver_name = $name;
        return $this;
    }
    //仅下单用
    public function set_receiver_phone($name){
        $this->receiver_phone = $name;
        return $this;
    }
    //仅下单用
    public function set_receiver_province($name){
        $this->receiver_province = $name;
        return $this;
    }
    //仅下单用
    public function set_receiver_city($name){
        $this->receiver_city = $name;
        return $this;
    }
    //仅下单用
    public function set_receiver_area($name){
        $this->receiver_area = $name;
        return $this;
    }
    //仅下单用
    public function set_receiver_address($name){
        $this->receiver_address = $name;
        return $this;
    }
    
    /**
     * 用快递鸟api在线下单发送请求，并返回结果。
     */
    public function send_request()
    {
        $order = \app\shop\model\Order::get(['serial' => $this->order]);
        if (!$order) {
            return ['code'=>0,'message'=>'订单不存在啊'];
        }
        if ( $order->getData('logistics_is_order')) {
            return ['code'=>0,'message'=>'自查错误，物流订单已下，请勿重复'];
        }
        
        $requestData = $this->get_request();
        

        $temp = new \app\pay\model\Alitemp();
        $temp->data('url', '127.0.0.1send_request');
        $temp->data('content', $requestData );
        $temp->data('create_time',date("Y:m:d H-i-s"));
        $temp->save();
        
        
      //  echo 11333;return;
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1001',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $jsonResult=  $this->sendPost($this->xiadan_ReqURL, $datas);
    
        //根据公司业务处理返回的信息......

        //解析在线下单返回结果
        $result = json_decode($jsonResult, true);
        if (!$result) {
            return ['code'=>0,'message'=>'json解析错误'];
            
        }
        
        /**
OrderCode    String    订单编号    R
ShipperCode    String    快递公司编码    R
LogisticCode    String    快递单号    O
         */
        
        $temp = new \app\pay\model\Alitemp();
        $temp->data('url', $this->xiadan_ReqURL);
        $temp->data('content', $jsonResult );
        $temp->data('create_time',date("Y:m:d H-i-s"));
        $temp->save();
        
        if($result["ResultCode"] == "100" && $result["Success"]==true) {
            $order->setAttr('logistics_company', $result['Order']['ShipperCode']);
            $order->setAttr('logistics_is_order', 1);
            $order->save();
            return ['code'=>1,"data"=> ['company' => $result['Order']['ShipperCode'],] ];
        }
        else {
            return ['code'=>0, 'message' => "错误代码:{$result["ResultCode"]}，".
                "错误原因：{$result["Reason"]}"];
//            echo "<br/>在线下单失败，错误代码:{$result["ResultCode"]}，错误信息：{$result["Reason"]}";
        }
        return $result;
    }
    
    /**
     * 用快递鸟api订阅
     * 
     * @param unknown $company 物流公司代号
     * @param unknown $logistics 物流号
     */
    public function dingyue($company,$logistics)
    {
        //构造在线下单提交信息
        //选择物流公司
        $eorder = [];
        $eorder["ShipperCode"] = $company ;
        $eorder["LogisticCode"] = $logistics ;
        $requestData = json_encode($eorder, JSON_UNESCAPED_UNICODE);
        
        $order = \app\shop\model\Order::get(['logistics_company' => $company, 'logistics'=>$logistics ]);
        if (!$order) {
            return ['code'=>0, 'message'=>'订单不存在'];
        }
        
        $temp = new \app\pay\model\Alitemp();
        $temp->data('url', '127.0.0.1_dingyue');
        $temp->data('content', $requestData );
        $temp->data('create_time',date("Y:m:d H-i-s"));
        $temp->save();
        
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1008',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $jsonResult=  $this->sendPost($this->dingyue_ReqURL, $datas);
        
        //解析在线下单返回结果
        $result = json_decode($jsonResult, true);
        if (!$result) {
            return ['code'=>0,'message'=>'json解析错误'];
        }
        //能够解析
        
        $temp = new \app\pay\model\Alitemp();
        $temp->data('url', $this->dingyue_ReqURL);
        $temp->data('content', $jsonResult );
        $temp->data('create_time',date("Y:m:d H-i-s"));
        $temp->save();
        
        if($result["Success"] == true) {
            $order->setAttr('logistics_is_subscribe', 1);
            $order->save();
            return ['code'=>1,'message'=>'ok'];
        }
        else {
            return ['code'=>0, 'message' =>  $result["Reason"]];
        }
        
    }
    
    /**
     * 用快递鸟api立即查询
     *
     * @param unknown $company 物流公司代号
     * @param unknown $logistics 物流号
     */
    public function query_at_once($company,$logistics)
    {
        //构造在线下单提交信息
        //选择物流公司
        
        $company_list = $this->get_company();
        
        $eorder = [];
        $eorder["ShipperCode"] = $company ;
        $eorder["LogisticCode"] = $logistics ;
        $requestData = json_encode($eorder, JSON_UNESCAPED_UNICODE);
    
        $order = \app\shop\model\Order::get(['logistics_company' => $company, 'logistics'=>$logistics ]);
        if (!$order) {
            return ['code'=>0, 'message'=>'订单不存在'];
        }
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $jsonResult=  $this->sendPost($this->query_ReqURL, $datas);
    
        //解析在线下单返回结果
        $result = json_decode($jsonResult, true);
        if (!$result) {
            return ['code'=>0,'message'=>'json解析错误'];
        }
        //能够解析
        if($result["Success"] == true) {
           $trace = $result['Traces'];
           
           if (count( $trace )>0 ) {
               $trace  = array_reverse($trace,false);
           }
           
           $temp = [0=>'未知',2=>'在途中',3=>'已签收',4=>'问题件'];
           
           if (is_array($trace) && count($trace) > 1 ) {
               return ['code'=>1,'data'=>
                   [
                       'company' => $company_list[$company],
                       'logistics'=>$logistics,
                       'state'=> $temp[ intval($result["State"]) ],
                       'trace' => $trace,
                   ]
               ];
           }
            return ['code'=>1,'data'=>
                   [
                       'company' => $company_list[$company],
                       'logistics'=>$logistics,
                       'state'=>'未知',
                       'trace' => [],
                   ]];
        }
        else {
            return ['code'=>0, 'message' =>  $result["Reason"]];
        }
    
    }
    
    /**
     * 这是在线下单用的，收集下单数据。
     */
    private function get_request()
    {
        //构造在线下单提交信息
        //选择物流公司
        $eorder = [];
        $eorder["ShipperCode"] = $this->wuliu ;
        $eorder["OrderCode"] = $this->order ;
        $eorder["PayType"] = $this->pay_type; //邮费支付方式:1-现付，2-到付，3-月结，4-第三方支付
        $eorder["ExpType"] = 1; //标准快件
        // 发送人，就是本公司
        $info = $this->sender_info;
        $sender = [];
        $sender["Name"] = $info['name'];
      //  $sender['Tel'] = $info['tel'];
        $sender['Mobile'] = $info['phone'];
        
        $sender["ProvinceName"] = $info['province'];
        $sender["CityName"] = $info['city'];
        $sender["ExpAreaName"] = $info['area'];
        $sender["Address"] = strval($info['street']) . $info['shop_name'] ;
        
        // 接收者，即买家
        $receiver = [];
        $receiver["Name"] = $this->receiver_name;
        if (preg_match('#^[0-9]{11}$#', $this->receiver_phone)) {
            $receiver["Mobile"] = $this->receiver_phone;
        } else {
            $receiver["Tel"] = $this->receiver_phone;
        }
        $receiver["ProvinceName"] = $this->receiver_province;
        $receiver["CityName"] = $this->receiver_city;
        if ($this->receiver_area) {
            $receiver["ExpAreaName"] = $this->receiver_area;
        }
        $receiver["Address"] = $this->receiver_address;
        //商品名称
        $commodityOne = [];
        $commodityOne["GoodsName"] = $this->goods_name;
        $commodity = [];
        $commodity[] = $commodityOne;
        
        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;
        
        $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);
        
        
        return $jsonParam;
    }
    
    
    /**
     * 这是使用快递鸟api的必须方法，被在线下单，订阅，立即查询共同调用
     * 
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    private function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = $this->my_parse_url($url);
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
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
    private function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }
    
    /**
     * php的parse_url函数升级版，补上端口号
     * @param unknown $url
     */
    private function my_parse_url($url) {
        $result = parse_url($url);
        if (!isset($result['port'])) {
            $result['port']=80;
        }
        return $result;
    }
    
    
    
    
    
    
    /**
     * 返回快递鸟支持的所有物流公司代号
     */
    public function g22et_company()
    {
        $arr =[
            'SF' => '顺丰快递',
            'YTO' => '圆通速递',
            'ZTO' => '中通速递',
            'YD' => '韵达快递',
            'DBL' => '德邦',
            'ZJS' => '宅急送',
            'LB' => '龙邦快递',
            'STWL' => '速腾',
        ];
        return $arr;
    }
    
    
    
    
    /**
     * 返回快递鸟支持的所有物流公司代号
     */
    public function get_company()
    {
        $arr =[
            'ANE' => '安能物流',
            'AXD' => '安信达快递',
            'BFDF' => '百福东方',
            'BQXHM' => '北青小红帽',
            'CCES' => 'CCES快递',
            'CITY100' => '城市100',
            'COE' => 'COE东方快递',
            'CSCY' => '长沙创一',
            'DBL' => '德邦',
            'DHL' => 'DHL',
            'DSWL' => 'D速物流',
            'DTWL' => '大田物流',
            'EMS' => 'EMS',
            'FAST' => '快捷速递',
            'FEDEX' => 'FedEx联邦快递',
            'FKD' => '飞康达',
            'GDEMS' => '广东邮政',
            'GSD' => '共速达',
            'GTO' => '国通快递',
            'GTSD' => '高铁速递',
            'HFWL' => '汇丰物流',
            'HHTT' => '天天快递',
            'HLWL' => '恒路物流',
            'HOAU' => '天地华宇',
            'hq568' => '华强物流',
            'HTKY' => '百世汇通',
            'HXLWL' => '华夏龙物流',
            'HYLSD' => '好来运快递',
            'JD' => '京东快递',
            'JGSD' => '京广速递',
            'JJKY' => '佳吉快运',
            'JTKD' => '捷特快递',
            'JXD' => '急先达',
            'JYKD' => '晋越快递',
            'JYM' => '加运美',
            'JYWL' => '佳怡物流',
            'LB' => '龙邦快递',
            'LHT' => '联昊通速递',
            'MHKD' => '民航快递',
            'MLWL' => '明亮物流',
            'NEDA' => '能达速递',
            'QCKD' => '全晨快递',
            'QFKD' => '全峰快递',
            'QRT' => '全日通快递',
            'SAWL' => '圣安物流',
            'SDWL' => '上大物流',
            'SF' => '顺丰快递',
            'SFWL' => '盛丰物流',
            'SHWL' => '盛辉物流',
            'ST' => '速通物流',
            'STO' => '申通快递',
            'SURE' => '速尔快递',
            'TSSTO' => '唐山申通',
            'UAPEX' => '全一快递',
            'UC' => '优速快递',
            'WJWL' => '万家物流',
            'WXWL' => '万象物流',
            'XBWL' => '新邦物流',
            'XFEX' => '信丰快递',
            'XYT' => '希优特',
            'YADEX' => '源安达快递',
            'YCWL' => '远成物流',
            'YD' => '韵达快递',
            'YFEX' => '越丰物流',
            'YFHEX' => '原飞航物流',
            'YFSD' => '亚风快递',
            'YTKD' => '运通快递',
            'YTO' => '圆通速递',
            'YZPY' => '邮政平邮/小包',
            'ZENY' => '增益快递',
            'ZHQKD' => '汇强快递',
            'ZJS' => '宅急送',
            'ZTE' => '众通快递',
            'ZTKY' => '中铁快运',
            'ZTO' => '中通速递',
            'ZTWL' => '中铁物流',
            'ZYWL' => '中邮物流',
        ];
        return $arr;
    }
    
}
