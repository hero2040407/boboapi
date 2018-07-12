<?php
namespace BBExtend\common;
// use think\Db;
// use BBExtend\Sys;

/**
 * 通用
 * 
 * 
 * @author 谢烨
 */
class Date
{
    
    /**
     * $datetime1 = date_create('2009-10-11');
        $datetime2 = date_create('2009-10-13');
        $interval = date_diff($datetime1, $datetime2);
        echo $interval->format('%a');
     */
    public static function diff($date1, $date2)
    {
        $datetime1 = date_create($date1);
        $datetime2 = date_create($date2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format('%a');
        
    }
    
    
    // getTimestamp() 返回时间戳
    // 下面是重置日期或时间！
    //         $datetime = new \DateTime();
    //         $datetime->setDate(2015, 2, 28);
    //         $datetime = new \DateTime();
    //         $datetime->setTime(20, 20, 24);
    // 增加日期用  add
    // 减少日期用sub
    public static function pre_day_start($day=0)
    {
       
        $date = new \DateTime();
        if ($day)
            $date->sub(new \DateInterval("P{$day}D") );
        $date->setTime(0,0,0);
        $time = $date->getTimestamp();
        return $time;
       // echo date("Ymd His", $time);
    }
    
    public static function pre_day_end($day=0)
    {
        $date = new \DateTime();
        if ($day)
            $date->sub(new \DateInterval("P{$day}D") );
        $date->setTime(23,59,59);
        $time = $date->getTimestamp();
        return $time;
    
    }
    
    public static function post_day_start($day=0)
    {
         
        $date = new \DateTime();
        $date->add(new \DateInterval("P{$day}D") );
        $date->setTime(0,0,0);
        $time = $date->getTimestamp();
        return $time;
        // echo date("Ymd His", $time);
    }
    
    public static function post_day_end($day=0)
    {
        $date = new \DateTime();
        $date->add(new \DateInterval("P{$day}D") );
        $date->setTime(23,59,59);
        $time = $date->getTimestamp();
        return $time;
    
    }
    
    /**
     * 输入 20160101
     * 返回 20160102 ，
     * 第2个参数是天数。
     * @param number $day
     */
    public static function next_day_str($datestr="20160101",$day=1)
    {
        $date = \DateTime::createFromFormat("Ymd", $datestr);
        
        
        $date->add(new \DateInterval("P{$day}D") );
        $date->setTime(0,0,1);
       // $time = $date->getTimestamp();
        return $date->format("Ymd");
    
    }
    
    
    
    /**
     * 返回第几天前的凌晨(默认当天)  ($isend=0)
     * 
     */
    public static function get_day_start($day=0)
    {
        $year  = intval(date("Y", time()));
        $month = intval(date("m", time()));
        $day   = intval(date("j", time()) - intval($day));
       // if (intval($isend) == 0) {
            return mktime(0, 0, 0, $month, $day, $year);
        
    }
    
    /**
     * 
     * 返回第几天前结束的时间戳      ($isend=1)
     */
    public static function get_day_end($day=0)
    {
        $year  = intval(date("Y", time()));
        $month = intval(date("m", time()));
        $day   = intval(date("j", time()) - intval($day));
       
            return mktime(23, 59, 59, $month, $day, $year);
       // }
    }
    
    
    /**
     * 返回当月最开始的时间戳
     *
     */
    public static function get_current_month_start()
    {
        $year  = intval(date("Y", time()));
        $month = intval(date("m", time()));
        $day   = 1;
        // if (intval($isend) == 0) {
        return mktime(0, 0, 0, $month, $day, $year);
    
    }
    
  
}//end class

