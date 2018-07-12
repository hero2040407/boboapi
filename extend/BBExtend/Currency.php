<?php
namespace BBExtend;
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/21
 * Time: 22:08
 */
use think\Db;

use BBExtend\Sys;
use BBExtend\fix\TableType;
use BBExtend\DbSelect;

define('CURRENCY_FREE',0);
define('CURRENCY_GOLD',1);
define('CURRENCY_MONSTER',2);
define('CURRENCY_BUY',3);

class Currency
{
     const huilv = 32; // 人民币元 与 波豆的兑换比例。
     const huilv2 = 10; // 这个汇率是人民币打赏，用户收到多少波豆。
     
     public static $last_id=0;// 传参用的东西201803
    
     
    public static function get_currency($uid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql = "select id,gold, monster, lahei_count,gold_bean,score
                  from bb_currency where uid =?";
        $UserCurDB = DbSelect::fetchRow($db, $sql, [$uid ]);
        if (!$UserCurDB)
        {
            $UserCurDB = array();
            $UserCurDB['uid'] = $uid;
            $UserCurDB['gold'] = 0;
            $UserCurDB['gold_income'] = 0;
            $UserCurDB['flower'] = 0;
            $UserCurDB['discount'] = 0;
            $UserCurDB['monster'] = 0;
            Db::table('bb_currency')->insert($UserCurDB);
            
            $UserCurDB = DbSelect::fetchRow($db, $sql, [$uid ]);
        }
        return $UserCurDB;
    }
    
    
    /**
     * 
     * @param unknown $gold
     */
    public static function present_to_bean($gold)
    {
       // $temp = $gold * 2.1;
       
        $temp = $gold * 1;
        
        $temp = floor($temp);
        return (int)$temp;
    }
    
    
    public static function bean_to_cny($bean)
    {
        $temp = $bean / self::huilv;
    
        $temp = floor($temp);
        return (int)$temp;
    }
    
    
    public static function bean_to_cny_float($bean)
    {
        $temp = $bean / self::huilv;
    
        $temp = floor($temp);
        return $temp;
    }
    
    
    public static function cny_to_bean($cny)
    {
         
        $temp =  $cny * self::huilv;
    
        return (int)$temp;
    }
    
    public static function cny_to_bean_for_dashang($cny)
    {
         
        $temp =  $cny * self::huilv2;
    
        return (int)$temp;
    }
    
    /**
     * 增加或者减少积分。
     * 
     * @author xieye 2017 10 16
     * @param unknown $uid
     * @param unknown $count
     * @param string $way
     * @param number $msg_type
     * @return boolean|unknown
     */
    public static function add_score($uid,$count,$way = '未知',$msg_type=0)
    {
        $uid = intval($uid);
        if ($count==0) {
            return false;
        }
    
        $UserCurDB = self::get_currency($uid);
        $gold = $UserCurDB['score'];
        if ($count > 0) {
            //增加金币
            $UserCurDB['score'] += $count;
        }else {
            //减少金币
            if ($gold+$count < 0) {
                //购买失败 金币不足
                return false;
            }
            $UserCurDB['score'] += $count;
        }
         
        $db = Sys::get_container_db();
    
        $count = intval($count);
        if ($count>0) {
            $sql = "update bb_currency set score=score+{$count}  where uid ={$uid}";
        }else {
            $temp = abs($count);
            $sql = "update bb_currency set score=score-{$temp}  where uid ={$uid}";
        }
        $db->query($sql);
         
        $LogUser = array();
        $LogUser['uid'] = $uid;
        $LogUser['type'] = TableType::bb_currency_log__type_jifen; 
        $LogUser['count'] = $count;
        $LogUser['time'] = time();
        $LogUser['way'] = $way;
        $LogUser['msg_type'] = $msg_type;
        Db::table('bb_currency_log')->insert($LogUser);
    
        return $UserCurDB;
    }
    
    
    /**
     * 增加或者减少波豆
     * 
     * @param unknown $uid
     * @param unknown $count
     * @param string $way
     * @param number $msg_type
     * @return boolean|unknown
     */
    public static function add_bean($uid,$count,$way = '未知',$msg_type=0)
    {
        $uid = intval($uid);
        if ($count==0) {
            return false;
        }
    
        $UserCurDB = self::get_currency($uid);
        $gold = $UserCurDB['gold_bean'];
        if ($count > 0) {
            //增加金币
            $UserCurDB['gold_bean'] += $count;
        }else {
            //减少金币
            if ($gold+$count < 0) {
                //购买失败 金币不足
                return false;
            }
            $UserCurDB['gold_bean'] += $count;
        }
       
        $db = Sys::get_container_db();
    
        $count = intval($count);
        if ($count>0) {
            $sql = "update bb_currency set gold_bean=gold_bean+{$count}  where uid ={$uid}";
        }else {
            $temp = abs($count);
            $sql = "update bb_currency set gold_bean=gold_bean-{$temp}  where uid ={$uid}";
        }
        $db->query($sql);
       
        $LogUser = array();
        $LogUser['uid'] = $uid;
        $LogUser['type'] = TableType::bb_currency_log__type_bodou;
        $LogUser['count'] = $count;
        $LogUser['time'] = time();
        $LogUser['way'] = $way;
        $LogUser['msg_type'] = $msg_type;
        
        $db->insert('bb_currency_log', $LogUser);
      //  Db::table('bb_currency_log')->insert($LogUser);
        self::$last_id = $db->lastInsertId();
        
        return $UserCurDB;
    }
    
    
    /**
     * 便利的函数，取代add_currency 这个老函数
     * 
     * @param unknown $uid
     * @param number $count
     * @param string $way
     * @param number $msg_type
     */
    public static function add_bobi($uid, $count=1,$way='',$msg_type=0)
    {
        return self::add_currency($uid, 
                TableType::bb_currency_log__type_bobi, $count, $way, $msg_type);
    }

    
    /**
     * 增加或者减少金币数量
     * 
     * @param unknown $uid
     * @param int $type 注意，因为怪兽蛋不用，所以这里必须为1，其他 所有值都不对。
     * @param unknown $count
     * @param string $way
     * @param number $msg_type
     * @return boolean|unknown
     */
    public static function add_currency($uid,$type=1,$count,$way = '未知',$msg_type=0)
    {
        $uid = intval($uid);
        $count = intval($count);
        if ($count==0) {
            return false;
        }
        if ($type != TableType::bb_currency_log__type_bobi) {
            return false;
        }
        
        // 先做判断
        $UserCurDB = self::get_currency($uid);
        $gold = $UserCurDB['gold'];
        if ($count > 0)
        {
            //增加金币
            $UserCurDB['gold'] += $count;
        }else
        {
            //减少金币
            if ($gold+$count < 0)
            {
                //购买失败 金币不足
                return false;
            }
            $UserCurDB['gold'] += $count;
        }
        $db = Sys::get_container_db();
        
        if ($count>0) {
            $sql = "update bb_currency set gold=gold+{$count}  where uid ={$uid}";
            \BBExtend\user\Tongji::getinstance($uid)->money25($count);
        } else {
            $temp = abs($count);
            $sql = "update bb_currency set gold=gold-{$temp}  where uid ={$uid}";
            \BBExtend\user\Tongji::getinstance($uid)->money24($temp);
        }
        $db->query($sql);
        
        $LogUser = array();
        $LogUser['uid'] = $uid;
        $LogUser['type'] = TableType::bb_currency_log__type_bobi;
        $LogUser['count'] = $count;
        $LogUser['time'] = time();
        $LogUser['way'] = $way;
        $LogUser['msg_type'] = $msg_type;
        Db::table('bb_currency_log')->insert($LogUser);
        return $UserCurDB;
    }
    
    
}