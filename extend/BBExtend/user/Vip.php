<?php
namespace BBExtend\user;

/**
 * 关注类
 * 
 * 数量用最简单的set单变量！
 * 关注人列表用集合
 * 
 * @author 谢烨
 */

use think\Db;
use BBExtend\Sys;
use BBExtend\Level;
use BBExtend\message\Message;


use BBExtend\model\Achievement as Ach;
use BBExtend\DbSelect;


class Vip 
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
    
    public $dengji=0;
    public $chengjiu = 0;
    public $record = 0;
    public $guanzhu =0;
    public $fensi = 0;
    public $huodong = 0;
    
    public $max_dengji   = 10;
    public $max_chengjiu = 3;
    public $max_record   = 20;
    public $max_guanzhu  = 50;
    public $max_fensi    = 50;
    public $max_huodong  = 3;
    
    public $user;
    
    /**
     * @param int $uid
     */
    public function  __construct($uid=0) 
    {
        $uid = intval($uid);
        $this->redis = Sys::getredis11();
        $this->uid = $uid;
//        $this->message = '您已经关注过这个用户了';
    }
    
    /**
     * 查status，给客户端按钮显示的提示
     * 
     * 注意，这里写的不好，必须后执行这个函数，必须先查complete
     * 
     * 
     * 6个条件页面
     * 
     * 假如我已经是童星或机构或导师，则status=0，不显示按钮。
     * 
     * 假如不是童星
     *    假如6个条件满足，
     *       假如未更新过个人资料，则“完善资料，更新个人主页”， 点击跳转到个人主页， status=1，
     *       假如已更新过资料，则“请等待审核完成”，不能点击，status=2，
     *    假如6条件没有都满足
     *       假如我连手机号都填过了，   则“请等待审核完成”，不能点击，status=3，
     *       假如手机号未填过
     *         假如钱付过了，则“连线导师进入快速认证通道”，点击跳转到手机号填写页面，status = 4，
     *         假如钱没付过，则“连线导师进入快速认证通道”，点击跳转到显示价格50元页面。status= 5，
     * 
     *
     * 
     */
    public function status($complete)
    {
        if ($this->user->role > 1) {
            return 0;
        }
        $db = Sys::get_container_db_eloquent();
        if ($complete) {
            $sql="select count(*) from bb_vip_application_log where uid=? and status=3";
            $result = DbSelect::fetchOne($db, $sql,[ $this->uid ]);
            if ($result) {
                return 2;
            }else {
                return 1;
            }
            
        }else {// 6个条件没满足。
            $sql="select count(*) from bb_vip_application_log where uid=? and status=2";
            $result = DbSelect::fetchOne($db, $sql,[ $this->uid ]);
            if ($result) { // 手机号填过了。
                $sql="select count(*) from bb_vip_application_log where uid=? and status=4";
                $result = DbSelect::fetchOne($db, $sql,[ $this->uid ]);
                if ($result) {
                    return 7;
                }
                $sql="select count(*) from bb_vip_application_log where uid=? and status=5";
                $result = DbSelect::fetchOne($db, $sql,[ $this->uid ]);
                if ($result) {
                    return 6;
                }
                
                return 3;
            }else {         // 手机号未填过。
                $sql="select count(*) from bb_vip_application_log where uid=? and status=1";
                $result = DbSelect::fetchOne($db, $sql,[ $this->uid ]);
                if ($result) { //钱付过
                    return 4;
                }else {        // 钱没付过。
                    return 5;
                }
            }
        }
    }
    
    public function jisuan($num, $max_num){
        $temp = $num / $max_num;
        if ($temp >= 1) {
            $temp = 100;
        }else {
            $temp = $temp * 100;
            $temp = intval($temp);
        }
        return $temp;
    }
    
    /**
     * 统计
     */
    public function statistic()   
    {
        $db = Sys::get_container_dbreadonly();
        $user = \BBExtend\model\User::find( $this->uid );
        if (!$user) {
            throw new \Exception("uid error");
        }
        
        if ($this->uid==7049564) {
         //   return true;
        }
        
        
        $this->user = $user;
        
        $level = $user->get_user_level();
        $this->dengji = $this->jisuan($level, $this->max_dengji);
        
        // 成就。
        $ach2 = new Ach();
        $ach = $ach2->create_default_by_user($user);
        $this->chengjiu = $this->jisuan($ach->get_ach_count(), $this->max_chengjiu);
        
        //审核视频
        $sql="select count(*) from bb_record where 
               uid=? and type != 3 and is_remove=0 and audit=1
 ";
        $this->record = $this->jisuan($db->fetchOne($sql,[ $this->uid ]), $this->max_record );
        // 关注用户
        $focus = \BBExtend\user\Focus::getinstance($this->uid);
        $this->guanzhu = $this->jisuan($focus->get_guanzhu_count()  , $this->max_guanzhu  );
        
        // 粉丝
        $this->fensi = $this->jisuan($focus->get_fensi_count() , $this->max_fensi);
        
        $sql="
select count(*)
from bb_record 
where uid = ?
and is_remove=0
and audit=1
and type !=3
and exists(
  select 1 from bb_task_activity
   where bb_task_activity.id = bb_record.activity_id
)
";
        
        //活动
        $this->huodong = $this->jisuan($db->fetchOne($sql,[ $this->uid ])  , $this->max_huodong  ) ;
        if ($this->dengji==100 &&  $this->chengjiu==100 && $this->record==100  &&
            $this->guanzhu ==100 && $this->fensi == 100 && $this->huodong ==100    
                ){
            return true;
        }else {
            return false;
        }
    }
    
    
    /**
     * 看情况用，如果想调用多个方法，则不用此函数
     * 
     * @param int $uid
     * @return Focus
     */
    public static function getinstance($uid)
    {
        return new self($uid); 
    }


}

