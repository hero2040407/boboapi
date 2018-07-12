<?php
/**
 * 签到
 */

namespace app\user\controller;


use BBExtend\model\User;
use BBExtend\model\UserSigninLog;
use BBExtend\Currency;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\user\lottery\StandardSign;
use BBExtend\user\lottery\PlayCountSign;

class Signin
{
    /**
     * 签到。
     * 
     * 首先查用户是否存在，token是否正确
     * 然后查 用户签到，是否已经签到过。
     * 假如没有，则ok，签到成功。
     * 
     **/
    public function index($uid,$token)
    {
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        // 查是否已经签到过。
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_users_signin_log where uid=? and datestr=?";
        $result = DbSelect::fetchRow($db, $sql,[ $uid, date("Ymd") ]);
        if ($result) {
            return ['code'=>0,'message'=>'您今日已经签到过'];
        }
        
        // 现在开始计算 number，应该从1开始，除非，昨天有，从昨天的算起。
        $yestoday = date("Ymd", time() - 24* 3600 );
        $number = 1; // 如果昨天不存在，设置一个初值。
        $sql="select * from bb_users_signin_log where uid=? and datestr=?";
        $result = DbSelect::fetchRow($db, $sql,[ $uid, $yestoday ]);
        if ($result) {
            // 既然昨天存在，以昨天的为准。
            $number = $result['order_number'];
            $number++; //每天加1
            if ($number > 7) {
                $number = 1;
            }
        }
        
        $standard = new StandardSign($uid);
        
        $bonus = $standard->get_default_bonus();
        $obj = new UserSigninLog();
        $obj->uid = intval($uid);
        $obj->datestr = date("Ymd");
        $obj->create_time = time();
        $obj->played_count=0;
        $obj->order_number = $number;
        $obj->bonus= $bonus;
        $obj->save();
        
        // 现在，给用户加5个波币。
        Currency::add_bobi($uid, $bonus, '每日签到');
        // 重要，抽奖次数必须清除缓存
        $count_sign = new PlayCountSign($uid);
        $count_sign->clean_count();
        
        
        
        return ['code' => 1,'data'=>$standard->get_cache() ];
    }
    
    
    /**
     * 签到查询。
     * 
     * 谢烨：这有几种情况：
     * 实际是查昨天的情况，只有昨天有，才能继续查，7指连续签到的第7天。
     * 
     * 
     * 
     *
     **/
    public function query($uid,$token)
    {
        
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        $standard = new StandardSign($uid);
        return ['code' => 1,
            'data'=> $standard->get_cache(),
        ];
    }
    
    
    
    
    
    /**
     * 调试用
     * @param unknown $uid
     * @param unknown $date
     * @param unknown $number
     */
    public function falsedata_add($uid,$date,$number)
    {
        $date22='2017-11-21 00:00:00';
        $time = strtotime($date22);
        if (time() > $time ) {
            return false;
        }
        
        $obj = new UserSigninLog();
        $obj->uid = intval($uid);
        $obj->datestr = $date;
        
        $year  = preg_replace('/^(\d{4})\d{2}\d{2}$/','$1', $date);
        $month = preg_replace('/^\d{4}(\d{2})\d{2}$/','$1', $date);
        $day   = preg_replace('/^\d{4}\d{2}(\d{2})$/','$1', $date);
        $day  ="{$year}-{$month}-{$day} 00:00:01";
        $obj->create_time = strtotime( $day )  ;// gai 
        $obj->played_count=0;
        $obj->order_number = $number;
        $obj->bonus= 5;
        $obj->save();
        echo "insert ok";
    }
    
    
    /**
     * 调试用
     * @param unknown $uid
     * @param unknown $date
     */
    public function falsedata_del($uid,$date)
    {
        $date22='2017-11-21 00:00:00';
        $time = strtotime($date22);
        if (time() > $time ) {
            return false;
        }
        
        $db = Sys::get_container_db_eloquent();
        $sql ="delete from bb_users_signin_log
                where uid=?
                and datestr=?
                ";
        $db::delete($sql,[ $uid,$date ]);
        echo "delete ok";
    }
    
    
}