<?php
namespace BBExtend\user;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 微信公众号相关类
 *
 */
class MoneyRain
{
  //  const date_start = '2018-02-09';
    
    
    const date_start = '2018-02-15';
    const date_end   = '2018-03-03';
    
    
    /**
     * 根据序号返回日期
     * @param string $day
     * @return string
     */
    public static function get_datestr_by_day($day='')
    {
        if ($day=='') {
            $temp = time() - 24 * 3600;
            return date( "Ymd", $temp );
        }
        $day = intval($day);
        $temp = strtotime( self::date_start. ' 00:00:10'  );
        $temp += $day* 24 * 3600;
        return date( "Ymd", $temp );
    }
    
    
    
    
    public static function is_valid_time()
    {
        $can_play=0;
        $time = time();
        $time1 = strtotime(self::date_start. ' 00:00:00'  );
        $time2 = strtotime(self::date_end  . ' 00:00:00'  );
        if ($time < $time2 && $time > $time1 ) {
            $can_play=1;
        }
        
        if (!Sys::is_product_server()) {
            $can_play=1;
        }
        
        return $can_play;
    }
    
    /**
     * 获取当天第几天。
     */
    public static function get_day(){
        $date1 = self::date_start;
        $date2 = date("Y-m-d");
        return  intval( \BBExtend\common\Date::diff($date1, $date2));
        
    }
    
    public static function get_day_by_datestr($datestr)
    {
        $date1 =self::date_start;
        $datestr = strval( $datestr );
        
        $date2 = substr($datestr, 0,4).'-'.substr($datestr, 4,2) .'-'.substr($datestr, 6,2) ;
        
        
        return  intval( \BBExtend\common\Date::diff($date1, $date2));
    }
    
    
    public static function top50()
    {
        $db = Sys::get_container_db_eloquent();
        $datestr = date("Ymd");
        
        $sql="
select * from bb_money_rain_log
where datestr=?
and score >0
order by score desc, balance_time desc
limit 50
";
        $result = DbSelect::fetchAll($db, $sql,[ $datestr ]);
        $new=[];
        $i=0;
        foreach ($result as $v) {
            $i++;
            $temp=[];
            $user = \BBExtend\model\User::find( $v['uid'] );
            $temp['nickname'] = $user->get_nickname();
            $temp['pic']      = $user->get_userpic();
            $temp['ranking'] = $i;
            $temp['score'] = $v['score'];
            
            $new[]= $temp;
        }
        return $new;
    }
    
    
    /**
     * 计算排名时，得给出你超过了谁，需要查一下。
     * @param unknown $uid
     * @param unknown $score
     * @return string
     */
    public static function get_compare_target($uid,$score)
    {
        $uid = intval($uid);
        $datestr = date("Ymd");
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_money_rain_log where uid !=? and datestr= ? and score < ? 
           order by score desc limit 1";
        $row  = DbSelect::fetchRow($db, $sql,[ $uid,  $datestr, $score ]);
        
        
        if (!$row) {
            return "怪兽BOBO";
        }else {
            $user = \BBExtend\model\User::find( $row['uid'] );
            return $user->get_nickname();  
        }
    }
    
    
    public static function get_today_my_info($uid)
    {
        $uid = intval($uid);
        $datestr = date("Ymd");
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_money_rain_log where uid=? and datestr= ?";
        $row  = DbSelect::fetchRow($db, $sql,[ $uid,  $datestr]);
        if (!$row) {
            return null;
        }
        
        $sql="select count(*) from bb_money_rain_log 
             where datestr= ? and score>= ? ";
        $ranking  = DbSelect::fetchOne($db, $sql,[ $datestr, $row['score']]);
        
        return ['score' =>$row['score'],'ranking' => $ranking  ];
        
    }
    
    
}