<?php
namespace BBExtend\service;

/**
 * 短信接口
 * 
 * 使用demo
 * $phone='15000000011'; // 这是待发送验证码的手机号
 * $sms = new \BBExtend\service\Sms($phone);
 * 
 * //假设我现在想测试一个手机号，保证验证码正确，就需要加下面这句。
 * // $sms->set_must_success_phone('15062280000');
 * 
 * //发送验证码
 * // 判断返回的code为1，表示发送成功。
 * $result = $sms->send_verification_code();
 * 
 * // 验证验证码
 * // 判断返回的code是否为1，为1是正确的，否则显示message
 * $code = 123456; // 这是用户填写的验证码
 * $result = $sms->check($code);
 * 
 * 
 * @author xieye
 *
 */
class Sms
{
    private $phone;
    private $err=false;
    
    // 这是测试手机号。
    public $test_phone=[];
    
    public function __construct($phone)
    {
        $this->phone = $phone;
        $this->test_phone=[
            //  ios 测试账号
            '13100000011',// ios审核使用
            '13100000012',//
            '13100000013',//
            '13100000014',//
            '13100000015',//

            //  安卓测试账号
            '13100000021',
            '13100000022',
            '13100000023',
            '13100000024',
            '13100000025',
            '13100000029', //原来沈德志重复绑定手机账号
            '15822224562', // 沈德志  绑定QQ  所有服务器都可以通过
            '15850502439', // 沈德志

            // web 测试账号
            '13100000031',
            '13100000032',
            '13100000033',
            '13100000034',
            '13100000035',

            //测试账号组
            '12345678910',
            '12345678911',
            '12345678912',
            '12345678913',
            '12345678914',
            '12345678915',
            '12345678916',
            '12345678917',
            '12345678918',
            '12345678919',

        ];
    }
    
    public function get_err()
    {
        return true;
    }
    
    /**
     * 设置测试手机号
     * @param unknown $phone
     */
    public function set_must_success_phone($phone='')
    {
        $this->test_phone[] = $phone;
    }
    
    /**
     * 发送验证码
     */
    public function send_verification_code()
    {
        if (in_array( $this->phone,$this->test_phone )) {
            return ['code'=>1,'message'=>'发送成功,请检查手机短信!'];
        }
        
        $phone = $this->phone;
        $data=[];
        $data['appkey']= config('APPKEY');
        $data['zone']= '86';
        $data['phone']= $this->phone;
        
        $error= array(
            '500'=>'远程服务器错误（短信发送失败）',
            '405'=>'请求参数中的appkey为空',
            '406'=>'非法的appkey',
            '456'=>'请求参数中的手机号码或者国家代码为空',
            '457'=>'手机号码格式错误',
            '458'=>'AppKey或手机号码在黑名单中',
            '460'=>'未开启发送短信功能，请联系我们',
            '462'=>'同一手机号1分钟内发送短信的次数不能超过2次',
            '463'=>'手机号码超出当天发送短信的限额',
            '467'=>'请求校验验证码频繁（5分钟校验超过3次）',
            '468'=>'用户提交校验的验证码错误 ',
            '469'=>'没有打开发送Http-api的开关 ',
            '470'=>'账户短信余额不足 ',
            '471'=>'请求ip和绑定ip不符',
            '475'=>'应用信息不存在，检查appKey是否低于2.0版本',
            '477'=>'当前手机号码在怪兽BOBO每天最多可发送短信10条!',
            '478'=>'当前手机号码在当前应用下12小时内最多可发送文本验证码5条. ',
        );
        if($phone!='0'){
            $res = $this->postRequest(config('SENDURL'),$data);
            $json = json_decode($res,true);
            $status = $json['status'];
            if($status == 200){
                return ['code'=>1,'message'=>'发送成功,请检查手机短信!'];
            }else{
                return ['code'=>0,'message'=>$error[ $status ]|| $error[ '500' ] ]  ;
            }
        }else {
            return ['code'=>0,'message'=>'手机号错误' ];
        }
    }

    /**
     * 检测密码是否用户填写正确。
     * @param unknown $code
     */
    public function check($code='')
    {
        
        if (in_array( $this->phone,$this->test_phone )) {
            return ['code'=>1,'message'=>'校验成功!'];
        }
        
        $data=[];
        $phone = $this->phone;
        $data['appkey']= config('APPKEY');
        $data['zone']= '86';
        $data['phone']= $phone;
        $data['code']= $code;
        $error= array(
            '500'=>'远程服务器错误（短信发送失败）',
            '405'=>'请求参数中的appkey为空',
            '406'=>'非法的appkey',
            '408'=>'提交的验证码格式错误 ',
            '456'=>'国家代码或者手机号码为空',
            '457'=>'国家代码不存在或手机号码格式错误 ',
            '458'=>'appKey或手机号码在黑名单中',
            '466'=>'手机验证码为空',
            '467'=>'5分钟内此应用下此手机号验证超过3次，对应的验证码失效',
            '468'=>'手机验证码错误',
            '469'=>'没有开启发送WebApi的开关',
            '471'=>'请求ip和绑定ip不符 ',
            '475'=>'应用信息不存在，检查appKey是否低于2.0版本',
            '477'=>'当前手机号码在怪兽BOBO每天最多可发送短信10条!',
            '478'=>'当前手机号码在当前应用下12小时内最多可发送文本验证码5条. '
        );
        if($code && $phone ){
            $res = $this->postRequest(config('CHECKURL'),$data);
            $json = json_decode($res,true);
            $status = $json['status'];
            if($status == 200){
                return ['code'=>1,'message'=>'校验成功' ];
            }else{
                return ['code'=>0,'message'=>$error[ $status ]|| $error[ '500' ] ]  ;
            };
        } else {
            return ['code'=>0,'message'=>'手机号和验证码都必须传'];
        }
        
    }
    
    private function postRequest($api, array $params = array(), $timeout = 30 ) 
    {
        $ch = curl_init();
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_URL, $api );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        // 发送数据
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8', 
            'Accept: application/json', ) );
        $response = curl_exec( $ch );
        // 不要忘记释放资源
        curl_close( $ch );
        return $response;
    }
    
}