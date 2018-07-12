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
    private function check_user($uid,$ds_id, $qudao_id)
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
        $row = DbSelect::fetchRow($db, $sql,[ $uid, $ds_id ]);
        if ( $row ) {
            $this->has_register=1;
            // 首先，线上大赛绝对不允许重复报名。
            if ( $this->online_type==1 ) {
                $this->err_msg='重复报名';
                $this->code=0;
                return false;
            }else {
                // 对于线下的大赛，如果
                if ( $row['is_finish']!=2 ) {
                    $this->err_msg='不可重复报名';
                    $this->code=0;
                    return false;
                }
                
            }
            
            
        }
        
        return true;
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
      //      Sys::debugxieye(111);
            
            return ['code'=>0,'message' => $this->err_msg ];
        }
        
        $result = $this->check_race( $ds_id, $qudao_id );
        if (!$result) {
     //       Sys::debugxieye(1112);
            
            return ['code'=>0,'message' => $this->err_msg ];
        }
        
        // 检查用户
        $result = $this->check_user($uid,$ds_id, $qudao_id);
        if (!$result) {
     //       Sys::debugxieye(1113);
            
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