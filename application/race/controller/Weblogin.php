<?php
namespace app\race\controller;


use BBExtend\service\Sms;

use BBExtend\Sys;
// 必须要引用，因为重定向
use think\Controller;
use BBExtend\video\RaceStatus;

/**
 * 大赛首页 app
 * 
 * @author xieye
 * 2017 03
 */
class Weblogin extends Controller
{
    private function is_login()
    {
        $login = session('is_login');
        if ($login && $login==1 ) {
            return true;
        }
        return false;
    }
    
    private function is_login_and_uid()
    {
        $login = session('is_login');
        if ($login && $login==1 ) {
           // return true;
            $uid = session('uid');
            if ($uid && $uid >0) {
                return $uid;
            }
        }
        return false;
    }
    
    private function get_start_col()
    {
        $data = [
                'id' => session('id'),
                'type' => session('type'),
                'success_url' => session('success_url'),
                'fail_url' => session('fail_url'),
                'pk' => session('pk'),
                
        ];
        return $data;
    }
    
    
//     public function weixin_code_check($code='')
//     {
//         $result = \BBExtend\user\Weixin::code_check($code);
//         if ($result['code']==1  ) {
//             $data = $result['data'];
            
//             $access_token = $data['access_toekn'];
//             $expires_in   = $data['expires_in'];
//             $refresh_token =$data['refresh_token'];
//             $openid       = $data['openid'];
//             $scope        = $data['scope'];
//             $unionid      = $data['unionid'];
//             session( 'is_login',1 );
//             session( 'access_toekn',$access_toekn );
//             session( 'openid',$openid );
//             session( 'unionid',$unionid );
            
            
//             // 谢烨，这里我要查一下，假设
            
//             return ['code' =>1 ];
//         }
//         session( 'is_login',0 );
//         return ['code'=>0, 'data'=>'解析错误' ];
//     }
    
    /**
     * 根据code 获得 openid
     * 
     * @param unknown $code
     * @return number[]|unknown[][]|number[]|string[]
     */
    public function get_open_id($code)
    {
        $result = \BBExtend\user\Weixin::code_check($code);
        if ($result['code']==1  ) {
            $data = $result['data'];
            
            $access_token = $data['access_token'];
            $expires_in   = $data['expires_in'];
            $refresh_token =$data['refresh_token'];
            $openid       = $data['openid'];
            return ['code'=>1,'data' =>['openid' => $openid ] ];
        }
        else {
            return ['code'=>0,'message' =>'code error' ];
        }
        
    }
    
    // 重定向接口,接口1
    public function index($code='',$return='')
    {
     //   Sys::display_all_error();
        $return = base64_decode($return);
        $url_json = json_decode($return ,1);
        if ($url_json && isset( $url_json['success_url']  ) && isset( $url_json['id']  )  
                && isset( $url_json['type']  )   ){
            
        }else {
            return ['code'=>0, 'message'=>'解析错误'];
        }
        
        
      // Sys::debugxieye( var_export($url_json,1 )   );
        
        $success_url = $url_json['success_url'];
        $fail_url = $url_json['fail_url'];
        $type = $url_json['type'];
        $id = $url_json['id'];
        
        $pk = false;
        if (isset($url_json['pk']) ) {
            $pk = $url_json['pk'];
        }
        
        
        
        
        $result = \BBExtend\user\Weixin::code_check($code);
        if ($result['code']==1  ) {
            $data = $result['data'];
            
       //     Sys::debugxieye( var_export($data,1 ) );
            
            
            
            $access_token = $data['access_token'];
            $expires_in   = $data['expires_in'];
            $refresh_token =$data['refresh_token'];
            $openid       = $data['openid'];
            
        //    Sys::debugxieye("接口1 openid ：".$openid);
            
            $scope        = $data['scope'];
       //     Sys::debugxieye("接口1 scope ：".$scope);
            $unionid      = $data['unionid'];
            session( 'is_login',1 );
            session( 'access_token',$access_token );
            session( 'openid',$openid );
            session( 'unionid',$unionid );
            
            session( 'type',$type );
            session( 'id',$id );
            
            session( 'fail_url',$fail_url );
            session( 'success_url',$success_url );
            session( 'pk',$pk );
            
            
            
//             Sys::debugxieye('--');
//             Sys::debugxieye($success_url);
//             Sys::debugxieye($fail_url);
//             Sys::debugxieye('--');
            
            $this->redirect($success_url);
            return;
        }
        
        session( 'is_login',0 );
        $this->redirect($fail_url);
    }
    
    
    // html 登录
    public function html_login()
    {
        $result = ['abc'=>$abc, 'session_abc'=> session('abc')  ];
        return ['code'=>1, 'data'=>$result ];
    }
    
    /**
     * 接口 2 填手机号接口
     * 
     * @param string $phone
     * @param string $check_code
     */
    public function login( $phone='',$check_code='')
    {
        
        if (!$this->is_login()) {
            return ['code'=>0,'message'=>'not login!'];
        }
        
        if (!$phone) {
            return ['code'=>0,'message'=>'phone not exists','data' =>$this->get_start_col(), ];
        }
        
        if (!$check_code) {
            return ['code'=>0,'message'=>'c code not exists','data' =>$this->get_start_col(),];
        }
        
        $sms = new Sms( $phone );
        $result = $sms->check( $check_code );
        if (isset( $result['code'] ) && $result['code'] == 1) {
            
        } else {
            $temp = $result;
            $temp['data'] = $this->get_start_col();
            return $temp;
        }
        // 到现在为止，已经确认是微信登录了，已经确认手机号真实性了。
        
        $openid = session( 'openid' );
        $unionid = session( 'unionid' );
        $access_token = session( 'access_token' );
        
        $result = \BBExtend\user\Weixin::weixin_phone_login($openid, $unionid, $phone, $access_token);
        if ($result['code']==0) {
            $temp = $result;
            $temp['data'] = $this->get_start_col();
            return $temp;
        }
        // 超级重要。
        session( 'uid', $result['data']['uid'] );
        $data = $this->get_start_col();
        $data['uid'] = $result['data']['uid'];
        $data['token'] = $result['data']['token'];
        
        return ['code'=>1, 'data'=>$data  ];
    }

    
    public function html_register()
    {
        
    }
    
    /**
     * 孙函予指定逻辑：
     * 1、假如 在已有报名信息，但未支付时，覆盖上次信息
     * 2、假如已支付，即报名成功，则  拒绝。
     * 
     * @return number[]|NULL[]|string[]|number[]|number[][]|string[][]|mixed[][]
     */
    public function register_new_v5()
    {
        $uid= input("param.uid/d");
        $phone = input("param.phone/s");
        $name  = input("param.name/s");
        $sex = input("param.sex/d");
        $birthday = input("param.birthday/s");
        $ds_id = input("param.ds_id/d");
        $qudao_id = input("param.qudao_id/d");
        $pic = input("param.pic/s");
        
        $record_url = input("param.record_url/s");
        $pic_list = input("param.pic_list/s");
        $addi_info = input("param.addi_info/s");
        $is_upload = input("param.is_upload/d");
        
        
        $reg = new \BBExtend\video\RaceNew(  );
        $result = $reg->register_v5($ds_id, $qudao_id,
                $uid,$phone,$name,$sex,$birthday,
                $pic, $record_url, $pic_list,$addi_info,$is_upload );
        
        if ( $result['code']!=1 ) {
            return $result;
        }
        
       
        
        $arr= RaceStatus::get_status_v5($uid, $ds_id);
        return [ 'code'=>1,
                'data'=>['status'=>  $arr['data']['status'],
                        'money' => intval( $race->money * 100),  ] ];
        
    }
    
    
    // 微信报名。
    public function register_new($v=1, $phone='',$name='',$sex=1,$birthday='',$ds_id=0,$qudao_id=0,
            $area1_name='',$area2_name='',$height=0,$weight=0,$pic='',$uid)
    {
        if ($v>=5) {
            return $this->register_new_v5();
        }
     //   Sys::debugxieye("pic:{$pic}");
        
        $reg = new \BBExtend\video\RaceNew(  );
        $result = $reg->register($ds_id, $qudao_id,
                $uid,$phone,$name,$sex,$birthday,
                $area1_name,$area2_name,$height,$weight,$pic);
        
        if ( $result['code']!=1 ) {
            return $result;
        }
        
        $race = \BBExtend\model\Race::find( $ds_id );
        
        if ($race->money >=0.001) { // 如果表中为1，则表示需要付钱。
            $has_pay = 0;
        } else {
            $has_pay = 1;
        }
        
        $arr= RaceStatus::get_status($uid, $ds_id);
        $code = $arr['data']['status'];
        
        return [ 'code'=>1,
                'data'=>['status'=>  $code,
                        'has_pay' => $has_pay,
                        'money' => $race->money ,  ] ];
    }
    
    
    
    // 微信报名。
    public function register($phone='',$name='',$sex=1,$birthday='',$ds_id=0,$qudao_id=0,
            $area1_name='',$area2_name='',$height=0,$weight=0,$pic='')
    {
        $uid = $this->is_login_and_uid();
        if (!$uid) {
            return ['code'=>0,'message'=>'请先登录' ];
        }
        
       // $uid= 3018192;
        $openid = session( 'openid' );
        
       // $openid='oFERUwEGLBH_8z6eTIhC11WlnsQY'; 
        
        $db = Sys::get_container_dbreadonly();
        $sql="select money from ds_race where id =?";
        $money = $db->fetchOne($sql,[ $ds_id ]);
        
        $status = \BBExtend\video\Race::get_user_race_status_code($uid, $ds_id);
        
        
        
        
    //    $status=18;
    //    $money=0.01;
        
//         return [
//             'code'=>1,
//                 'data'=>['status'=>  $status,
//                         'money' => $money,'openid'=> $openid,  ]
//         ];
        return \BBExtend\video\Race::register_web( $uid, $phone,$name,$sex,$birthday,$ds_id,
                $area1_name,$area2_name,$height,$weight, $openid, $qudao_id,$pic);
    }
    
    
    
    
    
}
