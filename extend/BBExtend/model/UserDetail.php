<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\User;


/**
 * 
 * 
 * : 谢烨
 */
class UserDetail extends User
{
    protected $table = 'bb_users';
    public $timestamps = false;
    
    private $follow_help=null;
    
    
    /**
     * 这是现在的新首页，需要的用户数据
     */
    public function get_info_201807()
    {
        return [
                'pic' =>$this->get_userpic(),
                'uid' =>$this->uid,
                'nickname' =>$this->get_nickname(),
                'badge' => $this->get_badge(),
        ];
        
    }
    
    /**
     * 主打童星列表
     * @return NULL[]|string[]
     */
    public function get_info_201807_focus($uid)
    {
        $arr = $this->get_info_201807();
        $arr['sex'] = $this->get_usersex();
        $arr['age'] = $this->get_userage();
        $arr['height'] = $this->get_height();
        $arr['weight'] = $this->get_weight();
        $arr['level'] = $this->get_level();
        $arr['fans_count'] = $this->get_count_about_fans();
        $arr['follow_count'] = $this->get_count_about_follow();
        
        
        $user_from = \BBExtend\model\User::find( $uid );
        $arr['is_focus'] = $user_from->is_focus( $this->uid );
        
        return $arr;
        
    }
    
    public function get_info_201807_extend()
    {
        $arr = $this->get_info_201807();
        $arr['sex'] = $this->get_usersex();
        $arr['age'] = $this->get_userage();
        $arr['height'] = $this->get_height();
        $arr['weight'] = $this->get_weight();
        
        
        
        $db = Sys::get_container_dbreadonly();
        
        $time = time() - 7 * 24 * 3600;
        $sql="
             select count(*) from bb_users_info where vip_time > {$time} or sign_time >{$time}
 and uid=?

";
        $arr['is_upgrade'] = $db->fetchOne($sql,[ $this->uid ]);
        
        return $arr;
        
        
    }
    
    
    public function get_jiav_focus($self_uid)
    {
        $result = $this->get_jingyan();
        $result['is_focus'] = \BBExtend\Focus::get_focus_state($self_uid, $result['uid']);//布尔返回
        
        return $result;
        
    }
    
    public function get_follow_count(){
        if ($this->follow_help==null) {
            $this->follow_help = \BBExtend\user\Focus::getinstance($this->uid);
        }
        $follow_count = $this->follow_help->get_guanzhu_count();
        return intval( $follow_count );
    }
    
    public function get_fans_count(){
        if ($this->follow_help==null) {
            $this->follow_help = \BBExtend\user\Focus::getinstance($this->uid);
        }
        $follow_count = $this->follow_help->get_fensi_count();
        return intval( $follow_count );
    }
    
    
    public function get_jiav()
    {
        $nickname = $this->get_nickname();
        $pic = $this->get_userpic();
        $lv = $this->get_user_level();
        $signature =  strval( $this->signature );
//         $help = \BBExtend\user\Focus::getinstance($this->uid);
//         $fans_count = $help->get_fensi_count();
        $fans_count = $this->get_fans_count();
        
        $follow_count = $this->get_follow_count();
        
        return [
                'nickname' =>$nickname,
                'pic' =>$pic,
                'level' =>$lv,
                'signature' =>$signature,
                'fans_count' =>$fans_count,
                'follow_count' =>$follow_count,
                'sex' =>$this->get_usersex(),
                'uid' => $this->uid,
                'badge' =>$this->get_badge(),
        ];
        
    }
   
    // 加上增加follow_count和fans_count字段
    public static function correct201804($info_arr)
    {
        $help = \BBExtend\user\Focus::getinstance($info_arr['uid']);
        $fans_count = $help->get_fensi_count();
        $fans_count = intval( $fans_count );
        
        
        $follow_count = $help->get_guanzhu_count();
        $follow_count = intval( $follow_count );
        
        $info_arr['follow_count'] = $follow_count;
        $info_arr['fans_count'] = $fans_count;
        return $info_arr;
        
    }
    
   
    
    

}


