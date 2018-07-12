<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class User extends Model 
{
    protected $table = 'bb_users';
    protected $primaryKey="uid";
    
    public $timestamps = false;
    
    protected $tool_user;
    protected $tool_info;
    protected $tool_starmaker;
    protected $tool_brandshop;
    
    
    
    
    protected function get_tool_starmaker()
    {
        if (!$this->tool_starmaker) {
            $this->tool_starmaker = \BBExtend\model\Starmaker::where('uid', $this->uid )->first();
            
        }
        return $this->tool_starmaker;
    }
    
    protected function get_tool_brandshop()
    {
        if (!$this->tool_brandshop) {
            $this->tool_brandshop = \BBExtend\model\BrandShop::where('uid', $this->uid )->first();
            
        }
        return $this->tool_brandshop;
    }
    
    
    protected function get_tool_info()
    {
        if (!$this->tool_info) {
            $this->tool_info = UserInfo::getinfo($this->uid);
            
        }
        return $this->tool_info;
    }
    
    protected function get_tool_user()
    {
        if (!$this->tool_user) {
            $this->tool_user = \app\user\model\UserModel::getinstance($this->uid);
        }
        return $this->tool_user;
    }
    
    
    /**
     * 编辑布局
     */
    public function edit_layout()
    {
        if ($this->role != 1) {
            return $this->role;
        }
        
        
    }
    
    /**
     * 是否，当前用户已经关注我们公司的微信服务号
     * 
     * @return boolean
     */
    public function has_focus_wechat_official_accounts()
    {
        // 首先得有字段值，没有就一定不是，
        // 然后，即使有了，要到微信用户表去查。
        if ($this->unionid != '') {
            $db = Sys::get_container_db_eloquent();
            $count = $db::table('bb_user_weixin_id')->where('unionid', $this->unionid )
                ->where('is_active',1)->count();
                if ($count) {
                    return true;
                }else {
                    return false;
                }
        }else {
            return false;
        }
    }
    
    
    public function get_user_level()
    {
        $user = $this->get_tool_user();
        return $user->get_user_level();
    }
    
    public function get_birthday()
    {
        $day = $this->birthday;
        if (!$day) {
            $day='2017-01-01';
        }
        if (strlen($day) <= 7 ) {
            $day .= "-01";
        }
        return $day;
        //return $user->get_user_level();
    }
    
    public function get_badge()
    {
        $badge='';
        $role = $this->role;
        if ($role== 3) {
            return \BBExtend\fix\Pic::VIP;
        }
        if ($role== 2 ) {
            $db = Sys::get_container_dbreadonly();
            $sql="select level from bb_users_starmaker where uid = ?";
            $level = $db->fetchOne($sql,[ $this->uid ]);
            if ( $level==3 ) {
                return \BBExtend\fix\Pic::TUTOR3;
            }
            if ( $level==4 ) {
                return \BBExtend\fix\Pic::TUTOR4;
            }
            if ( $level==5 ) {
                return \BBExtend\fix\Pic::TUTOR5;
            }
            if ( $level==6 ) {
                return \BBExtend\fix\Pic::TUTOR6;
            }
        }
        return '';
    }
    
    public function get_userpic()
    {
        $user = $this->get_tool_user();
        return $user->get_userpic();
    }
    
    public function get_level()
    {
        $user = $this->get_tool_user();
        return $user->get_user_level();
    }
    
    
    public function get_user_address()
    {
        $user = $this->get_tool_user();
        return $user->get_user_address();
    }
    
    
    public function get_nickname()
    {
        $user = $this->get_tool_user();
        return $user->get_nickname();
    }
    
    public  function get_userage()
    {
        $user = $this->get_tool_user();
        return $user->get_userage();
    }
    
    
    public function get_usersex()
    {
        $user = $this->get_tool_user();
        return $user->get_usersex();
    }
    
    public function get_frame()
    {
//         $user = $this->get_tool_info();
        return '';
    }
    
    /**
     * // 1 普通用户，       2导师，3vip童星，4机构
     * // 1未绑定手机普通用户，2导师，3vip童星，4机构,5绑定手机普通用户，6一般童星，7签约童星，8经纪人。-1 禁止登录。
     * 
     * 谢烨，新角色区分出来，
     * 
     * 
     * -1 禁止登录
     * 11 未绑定手机普通用户 12 绑定手机普通用户，13，一般童星，即直播认证用户。10 经纪人。
     * 
     * 31 vip 32 签约童星。
     * 41 品牌馆机构。
     * 
     */
    public function get_updated_role()
    {
        if ( $this->not_login==1 ){
            return -1;
        }
        
        // 一件一件处理。
        if ( $this->role==1 || $this->role==2 ) {
            
            // 先看经纪人。
            if ( $this->is_agent() ) {
                return 10; //经纪人
            }
            
            //先看手机号。
            //$db = Sys::get_container_dbreadonly();
            if ( !$this->is_bind_phone() ) {
                return 11; // 未绑定手机
            }
            
            // 只有可能是 ，或者是 直播认证。
            if ( $this->attestation==2 ) {
                return 13; // 一般童星
            }
            return 12; // 手机认证用户。
        }
        if ( $this->role==4 ) {
            return 41;
        }
        if ( $this->role==3 ) {
            
            // 只有两种，vip和签约
            $info = $this->get_tool_info();
            if ( $info->has_sign == 1 ) {
                return 32;
            }
            
            return 31;
        }
        // 这句话一般执行不到。
        return -1;
    }
    
    /**
     * 得到权限。
     * 
     * 权限包括
     * 点赞，上传视频，评论，关注，10,12,13,41,31,32
     * 
     * 申请童星认证，12
     * 参加通告大赛，13, 31,32,
     * 申请VIP童星认证，13
     * 模卡，31,32
     * 申请签约童星认证，31
     * 
     */
    public function deny_dianzan()
    {
        $updated_role = $this->get_updated_role();
        if (in_array($updated_role, [10,12,13,41,31,32])) {
            return false;
        }
        return "请绑定手机再进行此操作";
    }
    
    public function deny_tongxing_renzheng()
    {
        $updated_role = $this->get_updated_role();
        if (in_array($updated_role, [12])) {
            return false;
        }
        return "请先绑定手机";
    }
    
    public function deny_tonggao()
    {
        $updated_role = $this->get_updated_role();
        if (in_array($updated_role, [13,31,32])) {
            return false;
        }
        return "请先绑定手机";
    }
    public function deny_vip_renzheng()
    {
        $updated_role = $this->get_updated_role();
        if (in_array($updated_role, [13,])) {
            return false;
        }
        return "请先升级为小童星";
    }
    public function deny_moka()
    {
        $updated_role = $this->get_updated_role();
        if (in_array($updated_role, [31,32])) {
            return false;
        }
        return "请先升级为VIP";
    }
    public function deny_qianyue_renzheng()
    {
        $updated_role = $this->get_updated_role();
        if (in_array($updated_role, [31,])) {
            return false;
        }
        return "请先升级为VIP";
    }
    
    
    
    
    
    
    
    
    
    public function get_gexing()
    {
        $user = $this->get_tool_info();
        return $user->gexing;
    }
    
    public function get_gexing_arr()
    {
        $gexing = $this->get_gexing();
        if ($gexing) {
            $gexing = explode('|', $gexing);
        }else {
            $gexing =[];
        }
        return $gexing;
    }
    
    
    // 获取导师专业
    public function get_tutor_zhuanye_arr()
    {
        $user = $this->get_tool_starmaker();
        if ($user) {
            $gexing = $user->zhuanye;
            if ($gexing) {
                $gexing = explode('|', $gexing);
            }else {
                $gexing =[];
            }
            return $gexing;
        }
        return [];
    }
    
    // 获取获奖情况，
    public function get_tutor_huojiang_arr()
    {
        $user = $this->get_tool_starmaker();
        if ($user) {
            $gexing = $user->huojiang;
            if ($gexing) {
                $gexing = explode('|', $gexing);
            }else {
                $gexing =[];
            }
            return $gexing;
        }
        return [];
    }
    
    // 获取 导师对应机构的id
    public function get_tutor_brandshop_id()
    {
        $user = $this->get_tool_starmaker();
        if ($user) {
           return $user->brandshop_id;
        }
        return 0;
    }
    
    // 获取 导师对应机构的id
    public function get_tutor_brandshop_name()
    {
        $bid = $this->get_tutor_brandshop_id();
        if ($bid) {
            $db = Sys::get_container_dbreadonly();
            $sql="select title from bb_brandshop where id=?";
            $name = $db->fetchOne($sql,[ $bid ]);
            return strval( $name );
        }
        return '';
    }
    
    // 获得机构文字简介。
    public function get_brandshop_word_jianjie()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return strval( $jigou->info );
        }
        return '';
    }
    
    // 获得机构文字简介。
    public function get_brandshop_h5_jianjie()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return strval( $jigou->html_info );
        }
        return '';
    }
    
    // 获得机构电话。
    public function get_brandshop_phone()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return strval( $jigou->phone );
        }
        return '';
    }
    
    // 获得机构免费申请。
    public function get_brandshop_free()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return intval( $jigou->is_free );
        }
        return 0;
    }
    
    // 获取机构荣誉
    public function get_brandshop_rongyu()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return $jigou->rongyu;
        }
        return '';
    }
    
    // 获取机构荣誉图文
    public function get_brandshop_html_rongyu()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return $jigou->html_rongyu;
        }
        return '';
    }
    
    // 获取机构荣誉图文
    public function get_brandshop_html_kecheng()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return $jigou->html_kecheng;
        }
        return '';
    }
    
    
    // 获取机构地址
    public function get_brandshop_address()
    {
        $jigou = $this->get_tool_brandshop();
        if ($jigou) {
            return $jigou->address;
        }
        return '';
    }
    
    
    // 获取 机构的id
    public function get_brandshop_id()
    {
        $user = $this->get_tool_brandshop();
        if ($user) {
            return $user->id;
        }
        return 0;
    }
    
    
    public function get_parent_phone(){
        // 普通用户返回空。
        //导师返回导师手机
        // 机构返回机构手机
        // vip返回vip手机
        $status = $this->get_status();
        if ($status==1) {
            return '';
        }
        if ($status==2) {
            $obj = $this->get_tool_starmaker();
            if ($obj) {
                return $obj->phone;
            }
        }
        if ($status==3) {
            $obj = $this->get_tool_info();
            if ($obj) {
                return $obj->parent_phone;
            }
        }
        if ($status==4) {
            $obj = $this->get_tool_brandshop();
            if ($obj) {
                return $obj->phone;
            }
        }
        return '';
    }
    
    
    
    
    public function get_jingyan()
    {
        $user = $this->get_tool_info();
        return $user->jingyan;
    }
    
    public function get_jingyan_arr()
    {
        $gexing = $this->get_jingyan();
        if ($gexing) {
            $gexing = explode('|', $gexing);
        }else {
            $gexing =[];
        }
        return $gexing;
    }
    
    
    public function get_height()
    {
        $user = $this->get_tool_info();
        if ($user) 
        return $user->height;
        else {
            return 0;
        }
    }
    
    public function get_weight(){
        $user = $this->get_tool_info();
        if ($user)
          return $user->weight;
          else {
              return 0;
          }
    }
    
    public function get_status()
    {
        
        $status = $this->role;
        if ($status== 1 ) {
            $db = Sys::get_container_dbreadonly();
            
            // 查vip
            $sql="select count(*) from bb_vip_application_log
                where uid=? and (status=4 or status=7 ) ";
            $count = $db->fetchOne($sql,[ $this->uid ]);
            if ($count) {
                return 3;
            }
            
            // 查 导师
            $sql="select count(*) from bb_starmaker_application
                where uid=? and status in (1,3)  ";
            $count = $db->fetchOne($sql,[ $this->uid ]);
            if ($count) {
                return 2;
            }
            
            // 查 导师
            $sql="select count(*) from bb_brandshop_application
                where uid=? and status in (1,3)  ";
            $count = $db->fetchOne($sql,[ $this->uid ]);
            if ($count) {
                return 4;
            }
            
        }
        
        return $status;
    }
    
    /**
     * xieye 20171018
     * 方便关联到金钱账户
     * 
     * echo  \BBExtend\model\User::find(6700440)->currency->gold;
      echo "<br>";
      echo  \BBExtend\model\User::find(6700440)->currency->score;
      echo "<br>";
     * 
     */
    public function currency()
    {
        // 重要说明：第1个参数是关联的表名，第2个参数是外键名称（可能是本表或关联表），第3个参数是关联字段，（可能是本表或关联表）。
        return $this->hasOne('BBExtend\model\Currency', 'uid', 'uid');
    }

    /**
     * 已兑换积分，
     */
    public function exchanged_score()
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select sum(count) from bb_currency_log where count < 0 and type= 11 and uid=?";
        $sum = \BBExtend\DbSelect::fetchOne($db, $sql,[ $this->uid ]);
        $sum = intval( abs( $sum));
        return $sum;
    }
    
    /**
     * 鉴定token
     */
    public function check_token($token)
    {
        if (!$token) {
            return false;
        }
        if ($this->not_login != 0) {
            return false;
        }
        if (!$this->userlogin_token ) {
            return false;
        }
        if ( strtoupper( $this->userlogin_token )  == strtoupper( $token )  ) {
            return true;
        }
        return false;
    }

    /**
     * 是否关注另一个。
     * @param unknown $target_uid
     */
    public function is_focus($target_uid)
    {
        $help = \BBExtend\user\Focus::getinstance($this->uid);
        $result = $help->has_focus($target_uid);
        return  boolval($result);
    }
    
    
    
    public function is_starmaker()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select uid from bb_users where uid = ?
and role=2
and exists (
  select 1 from bb_users_starmaker where bb_users_starmaker.uid = bb_users.uid
)";
        $result = $db->fetchOne($sql,[ $this->uid ]);
        if ($result) {
            return true;
        }
        return false;
    }
    
    public function is_show_starmaker()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select uid from bb_users where uid = ?
and role=2
and exists (
  select 1 from bb_users_starmaker where bb_users_starmaker.uid = bb_users.uid
    and bb_users_starmaker.is_show=1
)";
        $result = $db->fetchOne($sql,[ $this->uid ]);
        if ($result) {
            return true;
        }
        return false;
    }
    
    /**
     * 是显示的 机构
     * @return boolean
     */
    public function is_show_brandshop()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select uid from bb_users where uid = ?
and role=4
and exists (
  select 1 from bb_brandshop where bb_brandshop.uid = bb_users.uid
    and bb_brandshop.is_show=1
)";
        $result = $db->fetchOne($sql,[ $this->uid ]);
        if ($result) {
            return true;
        }
        return false;
    }
    
    /**
     * 是否是经纪人
     */
    public function is_agent()
    {
        $uid = $this->uid;
        $db = Sys::get_container_dbreadonly();
        $sql="select count(*) from bb_users_agent
               where  uid =? ";
        $count = $db->fetchOne($sql,[$uid]);
        
        $success = ( $count >0 ) ? true: false;
        return $success;
    }
    
    /**
     * 是否绑定手机号
     * @return bool
     */
    public function is_bind_phone()
    {
        $uid = $this->uid;
        $db = Sys::get_container_dbreadonly();
        $sql="select count(*) from bb_users_platform
               where type=3 and uid =? ";
        $count = $db->fetchOne($sql,[$uid]);
        
        $success = ( $count >0 ) ? true: false;
//         if (!$success) {
//             $sql="select count(*) from bb_users_test
//                where  uid =? ";
//             $count2 = $db->fetchOne($sql,[$uid]);
//             if ($count2) {
//                       $success=true;
//             }
//         }
        return  $success;
    }
    
    
    
    
    public static function is_test($uid)
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select count(*) from  bb_users_test  where uid=? ";
        $result = $db->fetchOne($sql,[ $uid ]);
        if ($result) {
            return true;
        }else {
            return false;
        }
        
    }
    
    
    
    public function hobby_arr()
    {
        $db = Sys::get_container_dbreadonly();
        $sql = "select name from bb_label
  where id in (
    select hobby_id from bb_user_hobby 
     where uid =? )
limit 3";
        
        $result  = $db->fetchCol($sql,[ $this->uid ]);
        if (!$result) {
            return [];
        }else {
            return $result ;
            
        }
    }
    
    
    public function hobby_arr_id_name()
    {
        $db = Sys::get_container_dbreadonly();
        $sql = "select id, name from bb_label
  where id in (
    select hobby_id from bb_user_hobby
     where uid =? )
limit 3";
        
        $result  = $db->fetchAll($sql,[ $this->uid ]);
        if (!$result) {
            return [];
        }else {
            return $result ;
            
        }
    }
    
    
    //粉丝总数
    public function get_count_about_fans()
    {
        $help = \BBExtend\user\Focus::getinstance($this->uid);
        $count = $help->get_fensi_count();
        return intval($count    );
    }
    
    
    //偶像总数
    public function get_count_about_follow()
    {
        $help = \BBExtend\user\Focus::getinstance($this->uid);
        $count = $help->get_guanzhu_count();
        return intval($count    );
    }
    
    
    //视频总数
    public function get_count_for_record()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select count(*) from bb_record where uid=? and audit=1 and is_remove=0";
        $count = $db->fetchOne($sql,[ $this->uid ]);
        return intval($count    );
    }
    
    //大赛次数
    public function get_count_for_race()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select count(distinct zong_ds_id) from ds_register_log where uid=? 
              and has_pay=1 and has_dangan=1
         ";
        $count = $db->fetchOne($sql,[ $this->uid ]);
        return intval($count    );
        
    }
    
    //通告次数
    public function get_count_for_advise()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select count(*) from bb_advise_join where uid=?         ";
        $count = $db->fetchOne($sql,[ $this->uid ]);
        return intval($count    );
    }
    
    // 人气 = 视频数+ 大赛次数 × 10 + 通告次数 × 15
    public function get_count_for_renqi()
    {
        return $this->get_count_for_record() +
        10*( $this->get_count_for_race() )+
        15*( $this->get_count_for_advise() );
        
    }
    
    
    
    /**
     * 查用户和活动的状态。  
     */
    public function act_status($act_id)
    {
        $uid = $this->uid;
        $act_id = intval($act_id);
        
        
        // 
        
     //   $db = Sys::get_container_db_eloquent();
        
        $act = \BBExtend\model\Act::find( $act_id ); 
        if (!$act) {
            throw new \Exception("活动不存在");
        }
        
//         视频状态，1未上传未审核，2上传审核中，3成功，4失败，
        $record_status = $act->record_status( $uid );
        $describe = '';
        if ( $record_status==1 ) {
            $describe='';
            
            return $describe;  // 强行终止。
            
        }
        
        // 注意特别之处，如果活动已经结束，就
        $time_start = intval($act->start_time );
        $time_end = intval($act->end_time );
        $time = time();
        if(($time_start < $time) && ( $time_end > $time )) { // 活动进行中。
            if ( $record_status==2 ) {
                $describe='报名成功，视频审核中，请耐心等待';
            }
            if ( $record_status==3 ) {
                $describe='视频审核已通过，可分享视频提高排名';
            }
            if ( $record_status==4 ) {
                $describe='视频审核未通过，请重新上传';
            }
            
            
        }else {
            if ( $record_status==2 ) {
                $describe='报名成功，视频审核中，请耐心等待';
            }
            if ( $record_status==3 ) {
                
                $paiming = $act->record_paiming($uid);
                
                $describe='您的最终排名为：'. $paiming;
            }
            if ( $record_status==4 ) {
                $describe='视频审核未通过';
            }
        }
        
        // 特殊情况，返回空。
        return $describe;
    }
    
    
    
    
}
