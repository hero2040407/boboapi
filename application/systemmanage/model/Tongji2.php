<?php
namespace app\systemmanage\model;

use BBExtend\Sys;
use BBExtend\common\Date;
use BBExtend\common\Numeric;
use app\systemmanage\model\Tongji2;

/**
 * 定时统计帮助类，配合 systemmanager / controller / Tongji.php 使用。
 *
 */
class Tongji2 
{
    
    public $register_count=0;
    public $login1_count=0;
    public $login2_count=0;
    public $login3_count=0;
    public $login4_count=0;
    public $online_time=0;
    public $movie_view_count_today=0;
    public $movie_view_count_all=0;
    public $movie_view_avg_today=0;
    public $movie_view_avg_all=0;
    public $push_view_count_today=0;
    public $push_view_count_all=0;
    public $push_view_avg_today=0;
    public $push_view_avg_all=0;
    
    public $push_time_today=0;
    public $push_time_all=0;
    
    public $debug=0;
    public $share_count=0;
    
    public function  __construct()
    {
        $start = $time_start = Date::pre_day_start(1);
        $end = $time_end = Date::pre_day_end(1);
        $date = date("Ymd", $time_start);
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_users_register_log where
          (create_time between {$start} and {$end})";
        $this->register_count = $db->fetchOne($sql) ;
        
        $start2 = Date::pre_day_start(2);
        $start3 = Date::pre_day_start(3);
        $start4 = Date::pre_day_start(4);
        $start5 = Date::pre_day_start(5);
        $start6 = Date::pre_day_start(6);
        $start7 = Date::pre_day_start(7);
        
        $end2 = Date::pre_day_end(2);
        $end3 = Date::pre_day_end(3);
        $end4 = Date::pre_day_end(4);
        $end5 = Date::pre_day_end(5);
        $end6 = Date::pre_day_end(6);
        $end7 = Date::pre_day_end(7);
        // 次日登录数
        $sql="
          select count(*) from bb_users
where exists(select 1 from bb_tongji_log
 where bb_tongji_log.type=11
   and bb_tongji_log.uid = bb_users.uid
   and bb_tongji_log.create_time between {$start} and {$end}
)
and exists(select 1 from bb_tongji_log
 where bb_tongji_log.type=11
   and bb_tongji_log.uid = bb_users.uid
   and bb_tongji_log.create_time between {$start2} and {$end2}
)      
                ";
        $this->login1_count = $db->fetchOne($sql);
        
        $sql="
        select count(*) from bb_users
        where exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start} and {$end}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start2} and {$end2}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start3} and {$end3}
        )
        ";
        $this->login2_count = $db->fetchOne($sql); // 3日登录
        
        $sql="
        select count(*) from bb_users
        where exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start} and {$end}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start2} and {$end2}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start3} and {$end3}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start4} and {$end4}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start5} and {$end5}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start6} and {$end6}
        )
        and exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start7} and {$end7}
        )
        ";
        $this->login3_count = $db->fetchOne($sql); // 7日登录
        if ($this->debug) {
            echo $sql;
        }
        
        $sql="
        select count(*) from bb_users
         where exists(select 1 from bb_tongji_log
                    where bb_tongji_log.type=11
                    and bb_tongji_log.uid = bb_users.uid
                    and bb_tongji_log.create_time between {$start} and {$end}
                )
        and exists(select 1 from bb_users_register_log
                     where bb_users_register_log.uid = bb_users.uid
                     and bb_users_register_log.create_time < {$end7}
                )
        ";
        $this->login4_count = $db->fetchOne($sql); // 7日2次登录
        
        // 平均在线时长
        $sql ="select count(*) from bb_users where permissions!=99";
        $user_count = $db->fetchOne($sql);
        $sql ="select sum(all_login_time) from bb_currency
where exists(select 1 from bb_users 
where bb_users.uid = bb_currency.uid
   and bb_users.permissions!=99
)";
        $all_login_time = $db->fetchOne($sql);
        $this->online_time = Numeric::div_int($all_login_time, $user_count);
        
        // 当日视频浏览次数。
        $sql ="
                select count(*) from bb_tongji_log
where type=13 and
create_time BETWEEN {$start} and {$end}
                ";
        $this->movie_view_count_today = $db->fetchOne($sql);
        
        // 总视频浏览次数。
        $sql ="
        select count(*) from bb_tongji_log
        where type=13 
        ";
        $this->movie_view_count_all = $db->fetchOne($sql);
        
        // 当日平均视频浏览次数
        $this->movie_view_avg_today = Numeric::div($this->movie_view_count_today, $user_count);
        // 总平均浏览次数
        $this->movie_view_avg_all = Numeric::div($this->movie_view_count_all, $user_count);
        //
        
        // 当日观看直播时长。
        $this->push_time_today = $this->get_view_time();
        // 查昨天的总时长。
        $date2 = date("Ymd", $start2);
        
        $sql ="select push_time_all from bb_tongji_huizong
              where datestr='{$date2}'";
        $time_y_all = $db->fetchOne($sql);
        $time_y_all = intval($time_y_all);
        $this->push_time_all = $time_y_all + $this->push_time_today;
        if ($this->debug) {
            echo $sql;
        }
        // 当日平均时长
        $this->push_view_avg_today = Numeric::div_int($this->push_time_today, $user_count);
        if ($this->debug) {
            echo "\n".$this->push_time_today .$user_count ."\n" ;
        }
        
        // 总平均时长
        $this->push_view_avg_all = Numeric::div_int($this->push_time_all, $user_count);
        // 
        // 观看次数自动加了。
        
        // 2016 12 分享
        $sql ="
        select count(*) from bb_tongji_log
        where type=16 and
        create_time BETWEEN {$start} and {$end}
        ";
        $this->share_count = $db->fetchOne($sql);
        
    }
    
//     protected $table = 'bb_alitemp';
    public function get_view_time()
    {
         $start = $time_start = Date::pre_day_start(1);
        $end = $time_end = Date::pre_day_end(1);
        $date = date("Ymd", $time_start);
        $db = Sys::get_container_db();
        
        $sum=0;
        // 首先统计平均直播时长
        $sql ="select * from bb_tongji_log where  (create_time between {$time_start} and {$time_end})
        and type in (14,15)
        ";
        $result =$db->fetchAll($sql);
        $stream_name_arr =[];
        foreach ($result as $v) {
            $stream_name_arr[]= $v['info'];
        }
        $stream_name_arr = array_unique($stream_name_arr);
        if ($stream_name_arr) {
            
        
            foreach ($stream_name_arr as $v) {
                $shichang = $this->get_right_zhibo($result, $v) ;
                if ($shichang) {
                    $sum+= $shichang;
                    
                    $this->push_view_count_today++;
                    
                }
            }
           // $avg = Numeric::div_int($sum, count($stream_name_arr));
        }
        return $sum;
    
    }
    
    private function get_right_zhibo($arr, $stream_name)
    {
        $temp1 = 0;
        $temp2 =0;
        foreach ($arr as $v) {
            if ($v['info'] == $stream_name) {
                if ($v['type']==14){
                    $temp1 = $v['create_time'];
                }
                if ($v['type']==15){
                    $temp2 = $v['create_time'];
                }
    
            }
        }
        if ($temp1 && $temp2){
            if ($temp2-$temp1 >0 ) {
              return $temp2-$temp1;
            }else {
                return 0;
            }
        }
        return 0;
    }
    
    
}