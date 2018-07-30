<?php
namespace BBExtend\model;



/**
 * 全部静态方法，确认用户身份。
 * 
 * User: 谢烨
 */
class UserCheck extends User
{
    
    /**
     * 权限上能否发布模卡,真实性还要考证是否审核是否提交过等等。
     */
    public static function can_auth_moka($uid)
    {
        // 是vip即可。不管手机和直播认证。
        // 是
        
        $user = \BBExtend\model\User::find($uid);
        if ($user && $user->role==3 ) {
            return true;
        }else {
            return false;
        }
        
    }
    
    
    // 直播
    public static function can_auth_push(){
        
    }
    
    // 动态视频
    public static function can_auth_updates_record(){
        
    }
    
    // 动态图片
    public static function can_auth_updates_pic(){
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * 能登录。
     */
    public static function is_user_login($uid){
        $user = \BBExtend\model\User::find($uid);
        if ($user && $user->not_login==0 ) {
            return $user;
        }else {
            return false;
        }
    }
    
    // 是否经纪人。
    public static function is_agent_check($uid){
        $user = \BBExtend\model\User::find($uid);
        
        
        
        if ($user && $user->is_agent() ) {
            return $user;
        }else {
            return false;
        }
    }
    
    
    /**
     * 是否手机认证
     * 
     */
    public static function is_phone_renzheng($uid){
        $user = self::is_user_login($uid);
        if ($user && $user->is_bind_phone()){
            return $user;
        }
        return false;
    }
    
    /**
     * 是否直播认证用户，即普通童星
     * vip和签约也算。
     */
    public static function is_zhibo_renzheng($uid){
        $user = self::is_phone_renzheng( $uid);
        if ($user && $user->attestation==2 ) {
            return $user;
        }
        return false;
    }
    
    // 是否vip，但签约也是vip
    public static function is_vip_or_high($uid){
        $user = self::is_zhibo_renzheng( $uid);
        if ($user && $user->role==3 ) {
            return $user;
        }
        return false;
    }
    
    // 是否签约
    public static function is_sign_check($uid){
        
        $user = \BBExtend\model\User::find($uid);
        if ($user && $user->is_sign() ) {
            return $user;
        }else {
            return false;
        }
        
        
//         $user = self::is_vip_or_high( $uid);
//         if ($user && $user->is_sign() ) {
//             return $user;
//         }
        
        return false;
    }
    
//     public static function can_join_advise($uid,$advise)
//     {
        
//     }
    
    
       
}


