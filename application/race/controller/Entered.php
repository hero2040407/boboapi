<?php
/**
 * Created by PhpStorm.
 * User: tree
 * Date: 2017/3/20 0020
 * Time: 下午 4:58
 */
namespace app\race\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Session;
use \BBExtend\service\Sms;



class Entered extends Controller
{
    //为不同h5页面预留接口
    public function index(){
        //$ds_id 实际为某个大赛下面的某一渠道ID
        $qd_id = input('?param.ds_id')?input('param.ds_id'):0;

        if($qd_id == 0){
            abort(404,'页面不存在!!请检查路径后再试~');
        }else{
            $res = Db::table('ds_race')->where(['id'=>$qd_id])->find();
            Session::set('ds_id',$res['parent']);//记录大赛id
            $race_res = Db::table('ds_race')->where(['id'=>$res['parent']])->find();
            if(empty($res['banner']))$res['banner'] = $race_res['banner'];
            if($race_res['register_end_time'] < time()){
                echo $this->fetch('entered',['res'=>$res,'is_jump'=>$race_res['is_success_jump'],'end'=>1]);
            }else{
                echo $this->fetch('entered',['res'=>$res,'is_jump'=>$race_res['is_success_jump'],'end'=>0]);
            }
        }
        exit;
    }

    public function entered(){
        $qd_id = input('?param.qd_id')?input('param.qd_id'):0;
        if($qd_id == 0){
            abort(404,'页面不存在!!请检查路径后再试~');
        }else{
            $res = Db::table('ds_race')->where(['id'=>$qd_id])->find();
            Session::set('ds_id',$res['parent']);
            $race_res = Db::table('ds_race')->where(['id'=>$res['parent']])->find();
            if(empty($res['banner']))$res['banner'] = $race_res['banner'];
            if($race_res['register_end_time'] < time()){
                echo $this->fetch('',['res'=>$res,'is_jump'=>$race_res['is_success_jump'],'end'=>1]);
            }else{
                echo $this->fetch('',['res'=>$res,'is_jump'=>$race_res['is_success_jump'],'end'=>0]);
            }
        }
        exit;
    }
    //发送短信
    public function sendcode(){
        $phone = input('?param.phone')?(string)input('param.phone'):'0';
        if(Db::table('ds_register_log')->where(['phone'=>$phone,'zong_ds_id'=>Session::get('ds_id')])->find())return ['code'=>0,'message'=>'该手机号已经参加过这个赛事了!' ];
        $sms = new Sms($phone);
        $result = $sms->send_verification_code();
        return $result;
    }

    //检测短信
    public function checkcode(){
        $qd_id = input('?post.qd_id')?(string)input('post.qd_id'):'0';//此为渠道ID
        $code = input('?post.code')?(string)input('post.code'):'0';
        $phone = input('?post.phone')?(string)input('post.phone'):'0';
        $name = input('?post.name')?(string)input('post.name'):'0';
        $year = input('?post.year')?(string)input('post.year'):'0';
        $month = input('?post.month')?(string)input('post.month'):'0';
        $sex = input('?post.sex')?(string)input('post.sex'):'0';
        $captcha = input('?post.captcha')?(string)input('post.captcha'):'0';
        $area1_name = input('?post.area1_name')?(string)input('post.area1_name'):'浙江';
        $area2_name = input('?post.area2_name')?(string)input('post.area2_name'):'杭州市';

        $uid = Session::has('uid')?Session::get('uid'):'0';

        if($qd_id == '0'){
            return ['code'=>0,'message'=>'大赛id信息不全,请检查!'];
        }
        $sms = new Sms($phone);
        $sms->set_must_success_phone('15160005310');
        $sms->set_must_success_phone('18658866486');

        $result = $sms->check($code);

        if($result['code'] == 1){
            $res = \BBExtend\video\Race::register($uid,$phone,$name,$sex,$year.'-'.$month,$qd_id,$captcha,$area1_name,$area2_name);
            Session::set('uid',$res['data']['uid']);
            return $res;
        }else{
            return $result;
        }
    }

    //添加个人档案页面
    public function add(){
        $uid = Session::has('uid')?Session::get('uid'):'0';
        $ds_id = Session::has('ds_id')?Session::get('ds_id'):'0';
        $is_type1 =0;
        $is_type5 =0;

        $add_type1 =array();
        $add_type5 =array();

        if($ds_id == '0'){
            return ['code'=>0,'message'=>'信息不全,请检查!'];
        }
        $res = Db::table('ds_dangan_config')->where(['ds_id'=>$ds_id])->order('type')->select();

        //以下为多个复选框和单选下拉框处理
        //处理成如下数组
//        $check_result=[
//           "擅长才艺"=>[
//            ['id'=>1,'title'=>'唱歌'],
//            ['id'=>1,'title'=>'唱歌'],
//           ],
//            "擅长才艺2"=>[
//                ['id'=>2,'title'=>'唱歌2'],
//                ['id'=>3,'title'=>'唱歌3'],
//            ],
//        ];

        $check_ti1=[];
        $check_ti5=[];
        foreach ($res as $v) {
            if($v['type'] == 1){
                $check_ti1[]= $v['info'];
            }
            if($v['type'] == 5){
                $check_ti5[]= $v['info'];
            }
        }
        //info字段分组去重复
        $check_ti1 = array_unique($check_ti1);
        $check_ti5 = array_unique($check_ti5);

        //组合成模板需要的数组
        $check_result1=[];
        foreach ($check_ti1 as $info) {
            $check_result1[$info]=[];
            foreach ($res as $v){
                if ($v['type']==1 && $v['info'] == $info) {
                    $check_result1[$info][] = $v;
                }
            }
        }
        $check_result5=[];
        foreach ($check_ti5 as $info) {
            $check_result5[$info]=[];
            foreach ($res as $v){
                if ($v['type']==5 && $v['info'] == $info) {
                    $check_result5[$info][] = $v;
                }
            }
        }
        $race_res = Db::table('ds_race')->where(['id'=>$ds_id])->find();
        echo $this->fetch('',[
            'res'=>$res,
            'uid'=>$uid,
            'ds_id'=>$ds_id,
            'has_pic'=>$race_res['has_pic'],
            'banner'=>$race_res['banner'],
            'check_result1'=>$check_result1,
            'check_result5'=>$check_result5]);
        exit;
    }

    //提示上传视频界面
    public function upload_info(){
        $uid = Session::has('uid')?Session::get('uid'):'0';
        $ds_id = Session::has('ds_id')?Session::get('ds_id'):'0';
        if($ds_id == '0'){
            return ['code'=>0,'message'=>'信息不全,请检查!'];
        }
        $race_res = Db::table('ds_race')->where(['id'=>$ds_id])->find();
        echo $this->fetch('',['uid'=>$uid,'ds_id'=>$ds_id,'has_pic'=>$race_res['has_pic'],'banner'=>$race_res['banner']]);
        exit;
    }

    public function ajaxupload(){
        $base64_string = input('?post.base64_string')?(string)input('post.base64_string'):'0';
        $ds_id = input('?post.ds_id')?(string)input('post.ds_id'):'0';
        $uid = input('?post.uid')?(string)input('post.uid'):'0';

        if ($uid == '0')
        {
            $uid = Session::has('uid')?Session::get('uid'):'12345';
            if ($uid == '0'){
                return ['code'=>0,'message'=>'非法访问!'];
            }else{
                $upath = $uid.'/';
            }
        }else{
            $upath = $uid.'/';
        }
        $savepath = '/uploads/race/' . $ds_id . '/' . $upath;
        $savename = uniqid().'.jpg';
        if (!is_dir('.'.$savepath)) {
            mkdir('.'.$savepath, 0775, true);
            chmod('.'.$savepath, 0777);
        }

        $imageurl = $this->base64_to_img( $base64_string, $savepath.$savename );

        if($imageurl){
            return ['code'=>1,'message'=>'文件上传成功!','url'=>$imageurl];
        }else{
            return ['code'=>0,'message'=>'文件上传失败!请复制链接之后换个浏览器试试!"}'];
        }
    }

    private function base64_to_img( $base64_string ,$output_file ) {
        $ifp = fopen('.'.$output_file, "wb");
        fwrite( $ifp, base64_decode($base64_string));
        fclose( $ifp );
        return $output_file;
    }

    private function postRequest($api, array $params = array(), $timeout = 30 ) {
        $ch = curl_init();
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_URL, $api );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        // 发送数据
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/x-www-form-urlencoded;charset=UTF-8', 'Accept: application/json', ) );
        $response = curl_exec( $ch );
        // 不要忘记释放资源
        curl_close( $ch );
        return $response;
    }
    public function app_index(){

        $qd_id = input('?param.qd_id')?input('param.qd_id'):0;
        $uid = input('?param.uid')?input('param.uid'):0;

        if($qd_id == 0){
            abort(404,'页面不存在!!请检查路径后再试~');
        }else{
            if($uid != 0){
                $res = Db::table('ds_race')->where(['id'=>$qd_id])->find();
                $race_res = Db::table('ds_race')->where(['id'=>$res['parent']])->find();
                if(empty($res['banner']))$res['banner'] = $race_res['banner'];
                $state = \BBExtend\video\Race::get_user_race_status($uid, $res['parent']);
                Session::set('uid',$uid);
                Session::set('ds_id',$res['parent']);
                if ($state['code'] == 1 && $state['data'] == 17){
                    $this->redirect('app_add', ['uid' => $uid ,'ds_id' => $res['parent']]);
                }else{
                    $phone = Db::table('bb_users')->where(['uid'=>$uid])->find()['phone'];
                    $phone=$phone==0?'':$phone;
                    echo $this->fetch('',['res'=>$res,'uid'=>$uid,'phone'=>$phone]);
                }
            }
        }
        exit;
    }
//提示上传视频界面
    public function app_upload_info(){
        $uid = Session::has('uid')?Session::get('uid'):'0';
        $ds_id = Session::has('ds_id')?Session::get('ds_id'):'0';

        if($ds_id == '0'){
            $ds_id = input('?param.ds_id')?input('param.ds_id'):'0';
            if($ds_id == '0'){
                return ['code'=>0,'message'=>'档案添加-大赛参数信息不全,请检查!'];
            }
        }
        if($uid == '0'){
            $uid = input('?param.uid')?input('param.uid'):'0';
            if($uid == '0'){
                return ['code'=>0,'message'=>'档案添加-UID参数信息不全,请检查!'];
            }
        }
        $race_res = Db::table('ds_race')->where(['id'=>$ds_id])->find();
        echo $this->fetch('',['uid'=>$uid,'ds_id'=>$ds_id,'has_pic'=>$race_res['has_pic'],'banner'=>$race_res['banner']]);
        exit;
    }

    //添加个人档案页面
    public function app_add(){
        $uid = Session::has('uid')?Session::get('uid'):'0';
        $ds_id = Session::has('ds_id')?Session::get('ds_id'):'0';

        if($ds_id == '0'){
            $ds_id = input('?param.ds_id')?input('param.ds_id'):'0';
            if($ds_id == '0'){
                return ['code'=>0,'message'=>'档案添加-大赛参数信息不全,请检查!'];
            }
        }
        if($uid == '0'){
            $uid = input('?param.uid')?input('param.uid'):'0';
            if($uid == '0'){
                return ['code'=>0,'message'=>'档案添加-UID参数信息不全,请检查!'];
            }
        }
        $res = Db::table('ds_dangan_config')->where(['ds_id'=>$ds_id])->order('type')->select();

        $check_ti1=array();
        $check_ti5=array();
        foreach ($res as $v) {
            if($v['type'] == 1){
                $check_ti1[]= $v['info'];
            }
            if($v['type'] == 5){
                $check_ti5[]= $v['info'];
            }
        }
        //info字段分组去重复
        $check_ti1 = array_unique($check_ti1);
        $check_ti5 = array_unique($check_ti5);

        //组合成模板需要的数组
        $check_result1=[];
        foreach ($check_ti1 as $info) {
            $check_result1[$info]=[];
            foreach ($res as $v){
                if ($v['type']==1 && $v['info'] == $info) {
                    $check_result1[$info][] = $v;
                }
            }
        }
        $check_result5=[];
        foreach ($check_ti5 as $info) {
            $check_result5[$info]=[];
            foreach ($res as $v){
                if ($v['type']==5 && $v['info'] == $info) {
                    $check_result5[$info][] = $v;
                }
            }
        }
        $race_res = Db::table('ds_race')->where(['id'=>$ds_id])->find();
        echo $this->fetch('',[
            'res'=>$res,
            'uid'=>$uid,
            'ds_id'=>$ds_id,
            'has_pic'=>$race_res['has_pic'],
            'banner'=>$race_res['banner'],
            'check_result1'=>$check_result1,
            'check_result5'=>$check_result5]);
        exit;
    }

    //检测短信
    public function app_checkcode(){
        $qd_id = input('?post.qd_id')?(string)input('post.qd_id'):'0';
        $code = input('?post.code')?(string)input('post.code'):'0';
        $phone = input('?post.phone')?(string)input('post.phone'):'0';
        $name = input('?post.name')?(string)input('post.name'):'0';
        $year = input('?post.year')?(string)input('post.year'):'0';
        $month = input('?post.month')?(string)input('post.month'):'0';
        $sex = input('?post.sex')?(string)input('post.sex'):'0';
        $captcha = input('?post.captcha')?(string)input('post.captcha'):'0';
        $area1_name = input('?post.area1_name')?(string)input('post.area1_name'):'浙江';
        $area2_name = input('?post.area2_name')?(string)input('post.area2_name'):'杭州市';

        $uid = Session::has('uid')?Session::get('uid'):'0';

        if($qd_id == '0'){
            return ['code'=>0,'message'=>'大赛id信息不全,请检查!'];
        }
        $sms = new Sms($phone);
        $sms->set_must_success_phone('15160005310');
        $sms->set_must_success_phone('18658866486');

        $result = $sms->check($code);

        if($result['code'] == 1){
            $res = \BBExtend\video\Race::register_app($uid,$phone,$name,$sex,$year.'-'.$month,$qd_id,$captcha,$area1_name,$area2_name);
            if(isset($res['code'])){
                return $res;
            }else{
                return ['code'=>0,'message'=>'注册功能失效!'];
            }
        }else{
            return $result;
        }

    }
    
}