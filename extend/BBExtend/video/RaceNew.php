<?php
namespace BBExtend\video;



use BBExtend\fix\MessageType;
use BBExtend\common\Str;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 大赛报名帮助类
 * 
 * 面向对象。
 * 
 * $reg = new RaceNew();
 * $result = $reg->register(....);
 * 
 * 
 * @author xieye
 *
 */
class RaceNew
{
    public $race=null;      
    public  $online_type;   // 1线上，2线下
    
    public $err_msg='';    // 错误信息
    
    public $has_register=0; // 是否已经有一条报名记录。
    
    public $register_success=false; //是否本次报名成功。注意：报名成功是 特定词汇。
    public $insert_success=false;   //是否插入数据库日志。
    
    public $code=0;
    
    
    private function has_err()
    {
        if ($this->err_msg !='') {
            return true;
        }
        return false;
    }
    
    
    /**
     * 查参数。
     * 
     * @param string $phone
     * @param string $name
     * @param number $sex
     * @param string $birthday
     * @param string $area1_name
     * @param string $area2_name
     * @param number $height
     * @param number $weight
     * @return number[]|string[]
     */
    private function check_param( $phone='',$name='',$sex=1,$birthday='',
            $area1_name='',$area2_name='',$height=0,$weight=0)
    {
        if ( $height < 200 && $height>0 ) {
            //$new['height'] = $height;
        }else {
            $this->err_msg = '身高不正确';
        }
        if ( $weight < 100 && $weight>5 ) {
            //$new['weight'] = $weight;
        }else {
            $this->err_msg = '体重不正确';
        }
        if (!$name) {
            $this->err_msg='姓名错误';
        }
        if (!Str::is_valid_phone($phone)) {
            $this->err_msg='电话错误';
        }
        if (!Str::is_valid_birthday_month($birthday)) { // 必须 2018-01
            $this->err_msg='生日错误';
        }
        
        if ($this->has_err()) {
            return false;
        }
        
        return true;
    }
    
    
    private function check_param_v5($ds_id, $phone='',$name='',$sex=1,$birthday='', $record_url, $pic_list ,$is_upload )
    {
        if ($is_upload==0) {
           
            if (!$name) {
                $this->err_msg='姓名错误';
            }
            if (!Str::is_valid_phone($phone)) {
                $this->err_msg='电话错误';
            }
            if (!Str::is_valid_birthday_month($birthday)) { // 必须 2018-01
                $this->err_msg='生日错误';
            }
        }
        if ($is_upload) {
        
            $race = $this->race;
            if ( $race->upload_type==1 && empty( $record_url ) ) {
                $this->err_msg='请上传短视频';
            }
            
            if ( $race->upload_type==2 && empty( $pic_list ) ) {
                $this->err_msg='请上传多张照片';
            }
        }
        
        
        if ($this->has_err()) {
            return false;
        }
        
        
        
        return true;
    }
    
    
    
    private function check_race_v5( $ds_id, $qudao_id,$is_upload )
    {
        $race = $this->race = \BBExtend\backmodel\Race::find( $ds_id );
        if (!$race) {
            $this->err_msg='大赛不存在';
            return false;
        }
        
        if ($race->is_active==0) {
            $this->err_msg='大赛无效';
            return false;
        }
        
        
        $online_type = $this->online_type = $race->online_type;// 1线上， 2线下。
        
        if ( $is_upload==0 ) {
            if ( $online_type == 1  && $qudao_id != 0 ) {
                $this->err_msg='大赛和渠道参数配置错误';
                return false;
            }
            if ( $online_type == 2  && $qudao_id ==0 ) {// 线下必须有赛区。
                $this->err_msg='大赛和渠道参数配置错误';
                return false;
            }
            $time=time();
            if ( $time > $race->register_start_time && $time < $race->register_end_time ) {
                
            }else {
                $this->err_msg='现在不在大赛报名时间内，无法报名';
                return false;
            }
            
            
            if ( $online_type==2 ) {
                // 验证 赛区id是否真实准确。
                $db = Sys::get_container_db_eloquent();
                $sql="select count(*) from ds_race_field where race_id=? and id=?";
                $result = DbSelect::fetchOne($db, $sql,[ $ds_id, $qudao_id ]);
                if (!$result) {
                    $this->err_msg='大赛和渠道参数配置错误';
                    return false;
                }
                
            }
        }
        return true;
        
    }
    
    
    
    private function check_race( $ds_id, $qudao_id )
    {
        $race = $this->race = \BBExtend\backmodel\Race::find( $ds_id );
        if (!$race) {
            $this->err_msg='大赛不存在';
            return false;
        }
        
        if ($race->is_active==0) {
            $this->err_msg='大赛无效';
            return false;
        }
        
        
        $online_type = $this->online_type = $race->online_type;// 1线上， 2线下。
        
        if ( $online_type == 1  && $qudao_id != 0 ) {
            $this->err_msg='大赛和渠道参数配置错误';
            return false;
        }
        if ( $online_type == 2  && $qudao_id ==0 ) {// 线下必须有赛区。
            $this->err_msg='大赛和渠道参数配置错误';
            return false;
        }
        $time=time();
        if ( $time > $race->register_start_time && $time < $race->register_end_time ) {
            
        }else {
            $this->err_msg='大赛报名时间错误';
            return false;
        }
        
        
        if ( $online_type==2 ) {
            // 验证 赛区id是否真实准确。
            $db = Sys::get_container_db_eloquent();
            $sql="select count(*) from ds_race_field where race_id=? and id=?";
            $result = DbSelect::fetchOne($db, $sql,[ $ds_id, $qudao_id ]);
            if (!$result) {
                $this->err_msg='大赛和渠道参数配置错误';
                return false;
            }
            
        }
        return true;
        
    }
    
    
    
    // 谢烨，不可公开，因为使用了私有变量。
    private function check_user_v5($uid,$ds_id, $qudao_id,$is_upload)
    {
        $user = \BBExtend\model\User::find( $uid );
        if (!$user) {
            $this->err_msg='uid错误';
            return false;
        }
        
        if ( !$user->is_bind_phone() ) {
            $this->err_msg='大赛报名前需要先绑定手机号';
            $this->code = -204;
            return false;
        }
        
        
        $db = Sys::get_container_db_eloquent();
        // 查是否以前已经报过名
        if ($is_upload==0) {
            $sql="select * from ds_register_log where uid=? and zong_ds_id=? ";
            $row = DbSelect::fetchRow($db, $sql,[ $uid, $ds_id ]);
            if ( $row ) {
                $this->code = 0;
                $this->err_msg = '您已填写过信息，请上传视频或图片';
                return false;
                
            }
        }else {
            $sql="select * from ds_register_log where uid=? and zong_ds_id=? ";
            $row = DbSelect::fetchRow($db, $sql,[ $uid, $ds_id ]);
            if ( !$row ) {
                $this->code = 0;
                $this->err_msg = '上传之前请先填写报名信息';
                return false;
                
            }
            
        }
        return true;
    
    }
    
    
    // 谢烨，不可公开，因为使用了私有变量。
    private function check_user($uid,$ds_id, $qudao_id,$is_upload)
    {
        $user = \BBExtend\model\User::find( $uid );
        if (!$user) {
            $this->err_msg='uid错误';
            return false;
        }
        
        
        $bind_help = new \BBExtend\user\BindPhone($uid);
        if (!$bind_help->check()) {
            $temp =  $bind_help->get_result_arr();
            $code= $temp['code']  ; // 未报名
            $this->err_msg='大赛报名前需要先绑定手机号';
            $this->code = -204;
            return false;
        }
        
        $db = Sys::get_container_db_eloquent();
        // 查是否以前已经报过名
        $sql="select * from ds_register_log where uid=? and zong_ds_id=? and has_pay=1 and has_dangan=1";
        $register_log = DbSelect::fetchRow($db, $sql,[ $uid, $ds_id ]);
        if ($is_upload)
            $result = $this->condition_is_upload_1($register_log) ;
        else {
            $result = $this->condition_is_upload_0($register_log) ;
        }
        
        if ($result) {
            return true;
        }
        
        $this->err_msg='大赛报名条件错误';
        $this->code =0;
        return false;
    }
    
    /***
     * 谢烨特别注意，
     * 这里什么叫做报名成功， 如果 无需支付，现在就报名成功了。
     * 
     * @param number $ds_id
     * @param number $qudao_id
     * @param number $uid
     * @param string $phone
     * @param string $name
     * @param number $sex
     * @param string $birthday
     * @param string $area1_name
     * @param string $area2_name
     * @param number $height
     * @param number $weight
     * @return boolean|string
     */
    private function insert($ds_id=0, $qudao_id=0,
            $uid=0,$phone='',$name='',$sex=1,$birthday='',
            $area1_name='',$area2_name='',$height=0,$weight=0,$pic)
    {
        if ($this->has_err() ) {
            return false;
        }
        
        $race = $this->race;
        
        if ($race->money >= 0.001) { // 如果表中为1，则表示需要付钱。
            $has_pay = 0;
        } else {
            $has_pay = 1;
        }
        $db = Sys::get_container_db();
        // 先删除过去
      //  if ( $this->has_register ) {
      
        if ($qudao_id==0) {// 说明是线上，
        
            $sql="delete from ds_register_log where uid=? and zong_ds_id=?";
            $db->query( $sql,[ $uid,$ds_id ] );
        }else {// 否则是线下。
            $sql="delete from ds_register_log where uid=? and ds_id=?";
            $db->query( $sql,[ $uid, $qudao_id ] );
        }
            
        
      //  }
        
        $db->insert("ds_register_log", [
                'ds_id' =>$qudao_id,
                'zong_ds_id' => $ds_id,
                'uid' =>$uid,
                'create_time' => time(),
                'money' =>0,// 未支付过，固定0
                'phone' => $phone,
                'sex' => $sex,
                'birthday' => $birthday,
                'name' => $name,
                'has_pay' => $has_pay,
                'has_dangan' =>1, // xieye 20180416 ，这里固定填写为1
                'area1_name' =>$area1_name,
                'area2_name' =>$area2_name,
                'height' => $height,
                'weight' => $weight,
                'is_web_baoming' =>1,
                'pic' =>$pic,
        ]);
        $last_id = $db->lastInsertId();
        
        $this->insert_success = true;
        
        
        //谢烨，这里啊，要发个插入日志。
        if ( $has_pay ) {  // 如果无需支付，就报名成功了。
            $this->register_success=true;
           //
        }
        return $last_id;
    }
   
    
    private function set_history($uid, $addi_info){
        $info = json_decode($addi_info,1);
        if ($info) {
            foreach ($info as $k =>$v) {
                \BBExtend\model\DsDanganUser::update_uid($uid, $k, $v);
            }
            
        }
    }
    
    private function insert_v5($ds_id=0, $qudao_id=0,
            $uid=0,$phone='',$name='',$sex=1,$birthday='',
            $pic,$record_url, $pic_list, $addi_info,$is_upload, $record_pic)
    {
        if ($this->has_err() ) {
            return false;
        }
        
        $race = $this->race;
        
       
        $db = Sys::get_container_db();
        // 先删除过去
        //  if ( $this->has_register ) {
        
//             if ($qudao_id==0) {// 说明是线上，
        if ($is_upload==0) {
            
            if ($race->money >= 0.001) { // 如果表中为1，则表示需要付钱。
                $has_pay = 0;
            } else {
                // 谢烨，这里检查一下。
                if ($race->upload_type==1 || $race->upload_type==2 ) {// 填基本信息，且必传视频，当然has_pay=0
                    $has_pay=0;
                }else {
                
                
                  $has_pay = 1;
                }
            }
            $has_upload=1;
            if ( $race->upload_type==1 || $race->upload_type==2 ) {
                $has_upload=0;
            }
            
           $sql="delete from ds_register_log where uid=? and zong_ds_id=?";
           $db->query( $sql,[ $uid,$ds_id ] );
            
            $db->insert("ds_register_log", [
                    'ds_id' =>$qudao_id,
                    'zong_ds_id' => $ds_id,
                    'uid' =>$uid,
                    'create_time' => time(),
                    'money' =>0,// 未支付过，固定0
                    'phone' => $phone,
                    'sex' => $sex,
                    'birthday' => $birthday,
                    'name' => $name,
                    'has_pay' => $has_pay,
                    'has_upload' =>$has_upload,
                    'has_dangan' =>1, // xieye 20180416 ，这里固定填写为1
                    'register_info' => $addi_info,
                    'is_web_baoming' =>1,
                    'pic' =>$pic,
                    //'record_url' =>$record_url,
                    'age' => date("Y") - substr( $birthday,0,4 ),
            ]);
            
            // 谢烨，保存到 历史表里。
            $this->set_history($uid, $addi_info);
            
            
            $last_id = $db->lastInsertId();
            return $last_id;
        }else {
            
            
            $record_url = \BBExtend\common\Oss::alihuidiao_mov_to_mp4( $record_url );
            
            if ($race->money >= 0.001) { // 如果表中为1，则表示需要付钱。
                $has_pay = 0;
            } else {
                // 谢烨，这里检查一下。
                
                    
                    
                    $has_pay = 1;
                
            }
            $has_upload=1;
           
            $db2 = Sys::get_container_db_eloquent();
            // 查是否以前已经报过名
            $sql="select * from ds_register_log where uid=? and zong_ds_id=? ";
            $row = DbSelect::fetchRow($db2, $sql,[ $uid, $ds_id ]);
           $last_id = $row['id'];
            
            // 谢烨，找到原来的记录，补充。
           if ( $race->upload_type==1 || $race->upload_type==3  ) {// 1表示必传视频。
                $sql="update ds_register_log set 
                  has_pay=?,has_upload=?,
                 record_url=?, record_cover=? where id = ? ";
                $db->query( $sql,[ $has_pay, $has_upload, $record_url,$record_pic, $last_id  ] );
            }else {
                if ( $pic_list  ) {
                    $id_arr=[];
                    $pic_list_arr = explode(',', $pic_list);
                    //$temp_id_arr=[];
                    foreach ($pic_list_arr  as $pic2) {
                        $pic2 = trim($pic2);
                        $height_width = \BBExtend\common\Image::get_aliyun_pic_width_height($pic2);
                        $bind=[
                                'url'=>$pic2,
                                'type'=>1,
                                'uid'=>$uid,
                                'act_id'=>$race->id,
                                'create_time'=>time(),
                                'height' =>$height_width['height'],
                                'width'  =>$height_width['width'],
                        ];
                        $db->insert("bb_pic", $bind);
                        $id_arr[]= $db->lastInsertId();
                    }
                    $db->update( 'ds_register_log',['pic_id_list' => implode(",", $id_arr ) , 
                            'has_pay' =>$has_pay,
                            'has_upload' =>$has_upload,
                            
                            
                    ], 'id='.$last_id );
                    
                }
            }
            
        }
            
            
            
            
    }
    
    
    /**
     * 善后工作，发送通知。
     * 
     * 谢烨特别注意：不是说插入日志就算“报名成功”，必须检查register_success 字段 ！！
     * 
     * @return boolean
     */
    public function insert_post($ds_id, $qudao_id,  $uid)
    {
        
        \BBExtend\backmodel\RaceLog::register($ds_id, $uid);
        // Type180类具体逻辑。
        $client = new \BBExtend\service\pheanstalk\Client();
        $client->add(
                new \BBExtend\service\pheanstalk\Data($uid ,
                        MessageType::dasai_message  ,
                        ['small_type' => 'race_msg_register', // 这是报名的消息类型
                                'field_id'   => $qudao_id,
                                'ds_id'   => $ds_id,
                        ],
                        time()  )
                );  
            
        return true;
    }
   
    private function condition_is_upload_0($register_log )
    {
//         当is_upload=0
//         * 要求记录存在，且未支付。
//         * 或者 记录不存在。
        if ( !$register_log ) {
            return true;
        }
        if ( $this->condition_is_upload_1($register_log) ) {
            return true;
        }
        
        return false;
    }
    
    private function condition_is_upload_1($register_log )
    {
       // 要求 记录必须存在，且未支付。
        if ( $register_log && $register_log['has_pay']==0 ) {
            return true;
        }
        
        return false;
    }
    
    
    
    
    // 大赛报名注册。
    /**
     * 怎么检查参数呢？当已报名成功，不允许再次调用此接口。
     * 当未报名成功，可以调用此接口。
     * 
     * 当is_upload=0
     * 要求记录存在，且未支付。
     * 或者 记录不存在。
     * 
     * 当is_upload = 1
     * 要求 记录必须存在，且未支付。
     * 
     * 
     * @param number $ds_id
     * @param number $qudao_id
     * @param number $uid
     * @param string $phone
     * @param string $name
     * @param number $sex
     * @param string $birthday
     * @param string $pic
     * @param unknown $record_url
     * @param unknown $pic_list
     * @param unknown $addi_info
     * @param unknown $is_upload
     * @return number[]|string[]|number[]
     */
    public  function register_v5($ds_id=0, $qudao_id=0,
            $uid=0,$phone='',$name='',$sex=1,$birthday='',
            $pic='',$record_url, $pic_list , $addi_info,$is_upload,$record_pic)
    {
        
        // 必须先验 大赛存在，
        $result = $this->check_race_v5( $ds_id, $qudao_id,$is_upload );
        if (!$result) {
            return ['code'=>0,'message' => $this->err_msg ];
        }
        
        // 再验参数正确。
        $result = $this->check_param_v5($ds_id,$phone, $name, $sex, $birthday, $record_url, $pic_list,$is_upload );
        if (!$result) {
            return ['code'=>0,'message' => $this->err_msg ];
        }
        
        // 检查用户
        $result = $this->check_user_v5( $uid,$ds_id, $qudao_id ,$is_upload);
        if ( !$result ) {
            return ['code'=>$this->code, 'message' => $this->err_msg ];
        }
        
        $this->insert_v5($ds_id, $qudao_id,
                $uid,$phone,$name,$sex,$birthday,
                $pic, $record_url, $pic_list , $addi_info ,$is_upload,$record_pic);
        
        // 报名成功 的后续操作。
        if ( $this->register_success  ) {
            $this->insert_post($ds_id, $qudao_id, $uid);
        }else {
            return ['code'=>1, ];
        }
        return ['code'=>1];
    }
    
    
    
    
    /**
     * 
     * 报名 
     * 
     * 
     */
    public  function register($ds_id=0, $qudao_id=0, 
            $uid=0,$phone='',$name='',$sex=1,$birthday='',
            $area1_name='',$area2_name='',$height=0,$weight=0,$pic='' )
    {
        $result = $this->check_param($phone,$name,$sex,$birthday,
                $area1_name,$area2_name,$height,$weight);
        if (!$result) {
            return ['code'=>0,'message' => $this->err_msg ];
        }
        
        $result = $this->check_race( $ds_id, $qudao_id );
        if (!$result) {
            return ['code'=>0,'message' => $this->err_msg ];
        }
        
        // 检查用户
        $result = $this->check_user($uid,$ds_id, $qudao_id);
        if (!$result) {
            
            return ['code'=>$this->code, 'message' => $this->err_msg ];
        }
     //   Sys::debugxieye(1114);
        
        $this->insert($ds_id, $qudao_id,
                $uid,$phone,$name,$sex,$birthday,
                $area1_name,$area2_name,$height,$weight,$pic);
        
        // 报名成功 的后续操作。
        if ( $this->register_success  ){
            $this->insert_post($ds_id, $qudao_id, $uid);
        }else {
            return ['code'=>1, ];
        }
        return ['code'=>1];
    }
    
    
    
    
    

}