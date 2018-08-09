<?php
namespace app\user\model;

use BBExtend\Sys;
use BBExtend\Focus;

/**
 * 注意，这不是thinkphp5的模型类，只是普通的类。
 * 用于以面向对象的方法得到用户数据。
 * 
 * @author Administrator
 *
 */
class UserModel 
{
    private $user;
    private $uid;
    
    private $currency_row;
    
    public $error=0;
    
    private function  __construct($uid) 
    {
        $this->uid = $uid = intval($uid);
        $db = Sys::get_container_db();
        $sql="select * from bb_users where uid={$this->uid}";
        $UserDB= $db->fetchRow($sql);
        if ($UserDB) {
            $UserDB['uid'] = (int)$UserDB['uid'];
            $UserDB['sex'] = (int)$UserDB['sex'];
            $UserDB['vip'] = (int)$UserDB['vip'];
            $UserDB['min_record_time'] = (int)$UserDB['min_record_time'];
            $UserDB['max_record_time'] = (int)$UserDB['max_record_time'];
            //缓存中有，则必须每次都核查
           
        }else {
            $this->error=1;
        }
        $this->user = $UserDB;
    }
    
    public function has_error()
    {
        return $this->error==1;
    }
    
    /**
     * 
     * @param unknown $uid
     * 
     */
    public static function getinstance($uid)
    {
        return new self($uid);
    }
    
    public function has_user()
    {
        if (!$this->user) {
            return false;
        }
        return true;
    }
    
    public function can_zhibo()
    {
        if (!$this->user) {
            return false;
        }
        if  ($this->user['not_zhibo'] ==1 ) {
            return false;
        }else {
            return true;
        }
    }
    // 是否认证过,attestation=2 表示认证过。
    public function has_zhibo_renzheng()
    {
        if (!$this->user) {
            return false;
        }
        if  ($this->user['attestation'] ==2 ) {
            return true;
        }else {
            return false;
        }
    }
    
    
    public function get_specialty()
    {
        if (!$this->user) {
            return '';
        }
        return $this->user['specialty'];
    }
    
    // 兴趣
    public function get_hobbys()
    {
        if (!$this->user) {
            return '';
        }
        $db = Sys::get_container_db();
        $UserDB = $this->user;
        // xieye 2018 03 最多3个。
        $sql = "select hobby_id from bb_user_hobby where uid ={$UserDB['uid']} limit 3";
        $result  = $db->fetchCol($sql);
        if (!$result) {
            return '';
        }else {
            return "[".  implode(',', $result)."]";
            
        }
        
    }
    
    
    public function get_hobbys_word()
    {
        if (!$this->user) {
            return '';
        }
        $db = Sys::get_container_db();
        $UserDB = $this->user;
        $sql = "select name from bb_label where id in (
select hobby_id 
from bb_user_hobby 
where uid ={$UserDB['uid']}
)";
        $result  = $db->fetchCol($sql);
        
        if (!$result) {
            return '';
        }else {
            return   implode(',', $result);
            
        }
        
    }
    
    
    //是否关注某人
    public  function is_focus($another_uid)
    {
        if (!$this->user) {
            return false;
        }
        $UserDB = $this->user;
        $uid = $UserDB['uid'];
        return Focus::get_focus_state($uid,$another_uid);
    }
    
    //是否粉丝
    public  function is_fensi($another_uid)
    {
        if (!$this->user) {
            return false;
        }
        $UserDB = $this->user;
        $uid = $UserDB['uid'];
        return Focus::get_focus_state($another_uid,$uid);
    }
    
    
    //返回用户头像地址
    public  function get_user_pic_no_http()
    {
        if (!$this->user) {
            return '';
        }
        $UserDB = $this->user;
        $pic = $UserDB['pic'];
        if (!$pic)
        {
            $pic ='/public/toppic/topdefault.png';
        }
        return $pic;
    }
    
    //返回用户头像地址
    public function get_userpic()
    {
        if (!$this->user) {
            return '';
        }
        $UserDB = $this->user;
        $pic = $UserDB['pic'];
        //如果没有http://
        $ServerURL = \BBExtend\common\BBConfig::get_server_url();
        if (!$pic)
        {
            $pic =$ServerURL.'/public/toppic/topdefault.png';
        }
        if ( !preg_match('/^http/', $pic) )
        {
            $pic =$ServerURL.$pic;
        }
        return $pic;
    }
    
    //返回用户昵称
    public function get_nickname()
    {
        if (!$this->user) {
            return '';
        }
        $UserDB = $this->user;
//         $nickname = self::get_user($uid)['nickname'];
        return $UserDB['nickname'];
    }
    
    /**
     * 返回用户性别
     * 
     * @return int 
     */
    public function get_usersex()
    {
        if (!$this->user) {
            return 0;
        }
        $UserDB = $this->user;
//         $age=(int)self::get_user($uid)['sex'];
        return intval( $UserDB['sex'] );
    }
    
    //返回个性签名
    public function get_signature()
    {
        if (!$this->user) {
            return '';
        }
        $UserDB = $this->user;
        //         $age=(int)self::get_user($uid)['sex'];
        return  $UserDB['signature'] ;
    }
    
    
    /**
     * 返回用户年龄
     * 
     * @return int
     */
    public  function get_userage()
    {
        if (!$this->user) {
            return 0;
        }
        $UserDB = $this->user;
        return date('Y') - substr($UserDB['birthday'],0,4);
    }
    
    //返回用户类型。
    public  function get_permission()
    {
        if (!$this->user) {
            return 0;
        }
        $UserDB = $this->user;
        return $UserDB['permissions'];
    }
    
    //返回成就个数
    public  function get_ach_count()
    {
        if (!$this->user) {
            return 0;
        }
        $ach_obj =  \BBExtend\model\Achievement::where("uid", $this->uid)->first();
        if (!$ach_obj) {
            return 0;
        }else {
            return $ach_obj->get_ach_count();
        }
    }
    
    //返回用户地址
    public  function get_user_address()
    {
        if (!$this->user) {
            return '';
        }
        $UserDB = $this->user;
        
        if ( $UserDB['address']=='null' ) {
            return '';
        }
        
        return $UserDB['address'];
    }
    
    
    /**
     * 返回用户级别
     * 
     * @return int
     */
    public function get_user_level()
    {
        if (!$this->user) {
            return 0;
        }
        $db = Sys::get_container_db();
        $UserDB = $this->user;
        $sql = "select level from bb_users_exp where uid ={$UserDB['uid']}";
        return $db->fetchOne($sql);
    }
    
    //得到用户数据，vip是准确的。
    public  function get_user_vip()
    {
        if (!$this->user) {
            return 0;
        }
        $UserDB = $this->user;
        
//         if ($UserDB['vip'])
//         {
//             if ($UserDB['vip_time'] < time())
//             {
//                 $UserDB['vip'] = 0;
//                 self::update($UserDB);
//                 return $UserDB;
//             }
//         }
        
        return intval( $UserDB['vip']  );
    }
    
    private function set_currency()
    {
        if (!$this->user) {
            throw  new \Exception("user not exists");
        }
        if (!$this->currency_row) {
            $db = Sys::get_container_db();
            $sql ="select * from bb_currency where uid=".$this->uid;
            $this->currency_row = $db->fetchRow($sql);
        }
    }
    
    public  function get_gold()
    {
        $this->set_currency();
        $row = $this->currency_row;
        return $row["gold"];
    }
    
    public  function get_bean()
    {
        $this->set_currency();
        $row = $this->currency_row;
        return $row["gold_bean"];
    }
    
    
    
}