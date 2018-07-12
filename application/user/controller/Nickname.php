<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Date;
use BBExtend\BBUser;
use BBExtend\common\Str;
use BBExtend\model\User;

class Nickname
{
    /**
     * 检测名称是否可修改
     * @param unknown $uid
     * @return number[]|string[]|number[]
     */
    public function check($uid)
    {
        $user = User::find($uid);
        if (!$user) {
            return ['message'=>'用户不存在','code'=>0];
        }
        
        $apply_date = $this->has_apply($uid);
        if ($apply_date) {
            return ['code'=>0,'message' =>'您在'. date("Y-m-d", $apply_date ) . '日已修改过昵称' ];
        }else {
            return ['code'=>1];
        }
    }
    
    /**
     * 大于0表示已申请
     * @param unknown $uid
     */
    private function has_apply($uid)
    {
        $db = Sys::get_container_db_eloquent();
        //本月1日的起始时间戳。
        $day_start = Date::get_current_month_start();
        $sql="select * from bb_users_nickname_change where uid=? and create_time >?";
        $row = DbSelect::fetchRow($db, $sql,[$uid, $day_start ]);
        if ($row) {
            return $row['create_time'];
        }else {
            return false;
        }
    }
    
    /**
     * 用户申请修改昵称，注意不是立刻生效的。等后台审核。
     * @param unknown $uid
     * @param unknown $userlogin_token
     * @param unknown $new_nickname
     * @return string[]|number[]|number[]|string[]
     */
    public function update($uid, $userlogin_token, $new_nickname)
    {
        $user = User::find($uid);
        if (!$user) {
            return ['message'=>'用户不存在','code'=>0];
        }
        
        if (!BBUser::validation_token($uid,$userlogin_token)) {
            return ['message'=>'非法的令牌，请重新登录帐号','code'=>-201];
        }
        //检测新名称是否合法
        $name = trim( strval($new_nickname));
        if ($name=='') {
            return ['code'=>0, 'message'=>'昵称不能为空' ];
        }
        if (Str::strlen($name) > 20 ) {
            return ['code'=>0, 'message'=>'昵称过长' ];
        }
        if ($user->nickname == $name ) {
            return ['code'=>0, 'message'=>'昵称没有改变，不需提交' ];
        }
        
        //检测是否申请过本月
        $apply_date = $this->has_apply($uid);
        if ($apply_date) {
            return ['code'=>0,'message' =>'您在'. date("Y-m-d", $apply_date ) . '日已修改过昵称' ];
        }
        // 检测昵称重名
        $user_repeat = User::where("nickname", $name)->first();
        if ($user_repeat) {
            return ['code'=>0,'message' =>'您的输入与已有用户昵称重复，请重新输入' ];
        }
        
        $db = Sys::get_container_db_eloquent();
//         $db = Sys::get_container_db();
        $sql ="select * from bb_minganci where name =?";
        $result = DbSelect::fetchRow( $db, $sql, [$name]);
        if ($result) {
            return ['code'=>0,'message'=>'您的昵称不合适'];
        }
        
        
        
        $db::table("bb_users_nickname_change")->insert([
            'uid' => intval($uid),
            'create_time' => time(),
            'old_nickname' => $user->nickname,
            'new_nickname' => $name,
            'status' =>0,
        ]);
        return ['code'=>1,'message'=>'您的昵称已提交，后台审核完成后昵称将生效'];
    }
   
}