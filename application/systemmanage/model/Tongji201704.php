<?php
namespace app\systemmanage\model;

use BBExtend\Sys;
use BBExtend\common\Date;
use BBExtend\common\Numeric;
// use app\systemmanage\model\Tongji2;

/**
 * 定时统计帮助类，配合 systemmanager / controller / Tongji.php 使用。
 *
 */
class Tongji201704
{
    
    public $register_count=0; // 注册数
    public $login1_count=0;// 登陆数
    public $login2_count=0;  // 2日登陆
    public $login3_count=0;  // 7日登陆
    public $login4_count=0;
    public $online_time=0;  // 平均在线时长
    public $movie_view_count_today=0; // 23.	短视频浏览次数
    public $movie_view_count_all=0;        // 24 波币消费总额。
    public $movie_view_avg_today=0;
    public $movie_view_avg_all=0;
    public $push_view_count_today=0; // 直播浏览数
    public $push_view_count_all=0;
    public $push_view_avg_today=0;   // 直播浏览平均时长
    public $push_view_avg_all=0;    // 平均直播时长
    
    public $push_time_today=0;     // 直播人数
    public $push_time_all=0;      // 直播次数
    public $liucun2=0;            // 次日留存
    public $liucun3=0;            // 3日留存
    public $liucun7=0;            // 7日留存
    public $liucun30=0;            // 30日留存
    
    
    public $shipin_count=0;// 上传短视频数量。 短视频数。13
    public $renzheng_shipin_count=0; // 认证视频数量 有效短视频数 14
    public $huodong_count=0;   // 认证活动视频。 15 有效活动视频
    public $renzheng_user_count=0; //个人认证次数，所有的。 16 个人认证数
    public $renzheng_rate=0;     // 个人认证成功率。              17 个人认证成功率。
    public $pinglun_count=0;     // 评论总数        18
    public $zan_count=0;         // 点赞总数        19
    public $share_count=0;       // 分享数            20
    
    public $money1=0;            // 24 bo币消费总额
    public $money2=0;            // 25 bo币获取数
    public $money3=0;            // 26 bo豆提现数
    public $money4=0;            // 27 充值金额元。
    
    
    public $debug=0;
    
    // 2018 03 添加
    public $money_dashang_zhibo    = 0; // 给直播打赏总数，   统计日期当日观看直播所打赏的BO币
    public $money_dashang_record   = 0; // 给短视频打赏总数，统计日期当日平台内短视频送礼物消费的BO币总额
    public $money_dashang_fenxiang = 0; // 给分享打赏总数，   统计日期当日短视频分享后送礼物消费的BO币总额
    public $push_fayan_count       = 0; // 发言人数，               统计日期当日观看直播且发言的用户人数
    public $money_yaoqing          = 0; // 统计当日发送申请导师鉴定所消费的BO币；
    
    // 201807 ,活跃用户数
    public $huoyue_count           = 0; // 统计当日活跃用户；
    
    
    public function  __construct($start, $end)
    {
        $time_start = $start;
        $time_end = $end;
        $date = date("Ymd", $time_start);
        $start2 = $start - (24 * 3600 * 1);
        $start3 = $start - (24 * 3600 * 2);
        $start4 = $start - (24 * 3600 * 3);
        $start5 = $start - (24 * 3600 * 4);
        $start6 = $start - (24 * 3600 * 5);
        $start7 = $start - (24 * 3600 * 6);
        $start30 = $start - (24 * 3600 * 29 );
        
        $end2 = $end - (24 * 3600 * 1);
        $end3 = $end - (24 * 3600 * 2);
        $end4 = $end - (24 * 3600 * 3);
        $end5 = $end - (24 * 3600 * 4);
        $end6 = $end - (24 * 3600 * 5);
        $end7 = $end - (24 * 3600 * 6);
        $end30 = $end - (24 * 3600 * 29 );
        
        
        $db = Sys::get_container_db();
        
        // 注册数
        $sql ="select count(*) from bb_users_register_log where
          (create_time between {$start} and {$end})
         and exists(select 1 from bb_users where 
            bb_users.uid = bb_users_register_log.uid
              and bb_users.permissions in (1,3)
          )
          ";
        $this->register_count = $db->fetchOne($sql) ;
        
        // 登录数
        $sql="
          select count(*) from bb_users
where exists(select 1 from bb_tongji_log
 where bb_tongji_log.type=11
   and bb_tongji_log.uid = bb_users.uid
   and bb_tongji_log.create_time between {$start} and {$end}
)
and permissions in (1,3)
      
                ";
        $this->login1_count = $user_count= $db->fetchOne($sql);
        
        // 平均在线时长
        $sql ="select sum(login_time) from bb_tongji_user_login_time
        where dateint = {$start}
        and  exists(select 1 from bb_users
        where bb_users.uid = bb_tongji_user_login_time.uid
        and bb_users.permissions in (1,3)
        )";
        $all_login_time = $db->fetchOne($sql);
        $all_login_time = intval($all_login_time);
        $this->online_time = Numeric::div_int($all_login_time, $this->login1_count);
        
        // 次日留存，查$start2注册数，再查start2注册数 在 统计日期当日登录次数 在 start2的占比。
        $sql = "select count(*) from bb_users_register_log where
          (create_time between {$start2} and {$end2})
         and exists(select 1 from bb_users where 
            bb_users.uid = bb_users_register_log.uid
              and bb_users.permissions in (1,3)
          )
          ";
        $count = $db->fetchOne($sql);
        $sql="
           select count(*) from bb_users
            where exists(select 1 from bb_tongji_log
                   where bb_tongji_log.type=11
                     and bb_tongji_log.uid = bb_users.uid
                     and bb_tongji_log.create_time between {$start} and {$end}
                   )
            and permissions in (1,3)
            and exists(select 1 from bb_users_register_log
                   where bb_users_register_log.uid = bb_users.uid
                     and (bb_users_register_log.create_time between {$start2} and {$end2})
            )
        ";
        $count2 = $db->fetchOne($sql);
        $this->liucun2 = Numeric::div($count2,$count,3);
        
        // 3日留存，查$start3注册数，再查start3注册数 在 统计日期当日登录次数 在 start3的占比。
        $sql = "select count(*) from bb_users_register_log where
        (create_time between {$start3} and {$end3})
        and exists(select 1 from bb_users where
        bb_users.uid = bb_users_register_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $count = $db->fetchOne($sql);
        $sql="
        select count(*) from bb_users
        where exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start} and {$end}
        )
        and permissions in (1,3)
        and exists(select 1 from bb_users_register_log
        where bb_users_register_log.uid = bb_users.uid
        and (bb_users_register_log.create_time between {$start3} and {$end3})
        )
        ";
        $count2 = $db->fetchOne($sql);
        $this->liucun3 = Numeric::div($count2,$count,3);
        
        // 7日留存，查$start7注册数，再查start7注册数 在 统计日期当日登录次数 在 start7的占比。
        $sql = "select count(*) from bb_users_register_log where
        (create_time between {$start7} and {$end7})
        and exists(select 1 from bb_users where
        bb_users.uid = bb_users_register_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $count = $db->fetchOne($sql);
        $sql="
        select count(*) from bb_users
        where exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start} and {$end}
        )
        and permissions in (1,3)
        and exists(select 1 from bb_users_register_log
        where bb_users_register_log.uid = bb_users.uid
        and (bb_users_register_log.create_time between {$start7} and {$end7})
        )
        ";
        $count2 = $db->fetchOne($sql);
        $this->liucun7 = Numeric::div($count2,$count,3);
        
        
        // 30日留存，查$start30注册数，再查start30注册数 在 统计日期当日登录次数 在 start30的占比。
        $sql = "select count(*) from bb_users_register_log where
        (create_time between {$start30} and {$end30})
        and exists(select 1 from bb_users where
        bb_users.uid = bb_users_register_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $count = $db->fetchOne($sql);
        $sql="
        select count(*) from bb_users
        where exists(select 1 from bb_tongji_log
        where bb_tongji_log.type=11
        and bb_tongji_log.uid = bb_users.uid
        and bb_tongji_log.create_time between {$start} and {$end}
        )
        and permissions in (1,3)
        and exists(select 1 from bb_users_register_log
        where bb_users_register_log.uid = bb_users.uid
        and (bb_users_register_log.create_time between {$start30} and {$end30})
        )
        ";
        $count2 = $db->fetchOne($sql);
        $this->liucun30 = Numeric::div($count2,$count,3);
        
        
        // 8.	二日登陆：统计日期为止，登陆天数>=2的用户总数
        $sql ="
           select count(* ) from (
select bb_tongji_log.uid  
from  bb_tongji_log where  bb_tongji_log.type=11
  and exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
     )
group by bb_tongji_log.uid
having count( distinct bb_tongji_log.datestr) >=2) aa     
                ";
        $this->login2_count = $db->fetchOne($sql);
     echo "task1 half success\n";
        // 7日登陆
        $sql ="
           select count(* ) from (
select bb_tongji_log.uid
from  bb_tongji_log where  bb_tongji_log.type=11
  and exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
     )
group by bb_tongji_log.uid
having count( distinct bb_tongji_log.datestr) >=7) aa
                ";
        $this->login3_count = $db->fetchOne($sql);
        
// 10.	直播次数：统计日期当日用户开启直播的总数；push_time_all
// 11.	直播人数：统计日期当日开启直播的用户数；   push_time_today
// 12.	平均直播时长：统计日期当日直播总时长比直播次数；(谢烨修改：应该是用户数)push_view_avg_all
// 21.	直播浏览数：统计日期当日点击观看直播次数；push_view_count_today
// 22.	直播浏览平均时长：统计日期当日观看直播总时长除以观看次数；push_view_avg_today
        $sql ="
          select count(*) from  bb_tongji_log
where create_time between {$start} and {$end}
  and type =1
  and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
     )      
                ";
        $this->push_time_all = $db->fetchOne($sql);
        
        // 直播人数
        $sql ="
        select count(distinct uid) from  bb_tongji_log
        where create_time between {$start} and {$end}
        and type =1
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->push_time_today = $db->fetchOne($sql);
      
        // 平均直播时长。
        //  必须先查总直播时长
        $sql="
            select sum( data2) from  bb_tongji_log
where create_time between {$start} and {$end}
  and type =2
  and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
     )    
                ";
        $all = $db->fetchOne($sql);
        $all = intval($all);
        $this->push_view_avg_all = Numeric::div_int($all, $this->push_time_today);
        
       
        
        
        
        // 短视频上传次数
        $sql ="
        select count(*) from  bb_tongji_log
        where create_time between {$start} and {$end}
        and type =3
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->shipin_count = $db->fetchOne($sql);
        
        //短视频认证成功数。
        $sql ="
          select count(*) from bb_record where 
(audit_success_time between {$start} and {$end})
and (`time` between {$start} and {$end})
and exists (
 select 1 from bb_users where bb_users.uid = bb_record.uid
        and bb_users.permissions in (1,3)
)      
                ";
        $this->renzheng_shipin_count = $db->fetchOne($sql);
        
        //短视频认证成功数。只统计活动
        $sql ="
        select count(*) from bb_record where
        (audit_success_time between {$start} and {$end})
        and (`time` between {$start} and {$end})
        and exists (
        select 1 from bb_users where bb_users.uid = bb_record.uid
        and bb_users.permissions in (1,3)
        )
        and type =2
        ";
        $this->huodong_count = $db->fetchOne($sql);
        
        // 所有个人认证数。
        $sql ="
        select count(*) from bb_record where
        (`time` between {$start} and {$end})
        and exists (
        select 1 from bb_users where bb_users.uid = bb_record.uid
        and bb_users.permissions in (1,3)
        )
        and type =3
        ";
        $this->renzheng_user_count = $db->fetchOne($sql);
        
        //个人认证成功率。//先查17
        $sql ="
        select count(*) from  bb_tongji_log
        where create_time between {$start} and {$end}
        and type =17
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $count = $db->fetchOne($sql);
        $this->renzheng_rate = Numeric::div_int($count, $this->renzheng_user_count);
        
        // 评论数
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =4 
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->pinglun_count= $db->fetchOne($sql);
        
        // 点赞数=短视频点赞，加评论点赞，加评论回复的点赞。 再减去取消赞的数量
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =18
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $count1 = $db->fetchOne($sql);
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =19
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $count2 = $db->fetchOne($sql);
        $this->zan_count= $count1-$count2;
        
        //分享数。
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =16
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->share_count= $db->fetchOne($sql);
        
        // 直播浏览数(201706 改成人头数)
        $sql ="
        select count(distinct uid) from  bb_tongji_log
        where create_time between {$start} and {$end}
        and type =14
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
         $this->push_view_count_today = $db->fetchOne($sql);
//        $temp_count = $db->fetchOne($sql);
        
        //直播浏览平均时长
        $sql="
        select sum( data2) from  bb_tongji_log
        where create_time between {$start} and {$end}
        and type =15
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $all = $db->fetchOne($sql);
        $all = intval($all);
        $this->push_view_avg_today = Numeric::div_int($all, $this->push_view_count_today);
        
        // 短视频浏览数
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =13
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->movie_view_count_today= $db->fetchOne($sql);
        
        // 24 波币消费总额。
        $sql ="select sum(money) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =24
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->money1= floatval( $db->fetchOne($sql) );
        
        // 25 波币获取数。
        $sql ="select sum(money) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =25
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->money2= floatval( $db->fetchOne($sql) );
        
        //27 充值金额
        $sql ="select sum(money) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =27
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->money4= floatval( $db->fetchOne($sql) );
        
        
        
//         public $money_dashang_zhibo    = 0; // 给直播打赏总数，   统计日期当日观看直播所打赏的BO币
//         public $money_dashang_record   = 0; // 给短视频打赏总数，统计日期当日平台内短视频送礼物消费的BO币总额
//         public $money_dashang_fenxiang = 0; // 给分享打赏总数，   统计日期当日短视频分享后送礼物消费的BO币总额
//         public $push_fayan_count       = 0; // 发言人数，               统计日期当日观看直播且发言的用户人数
//         public $money_yaoqing          = 0; // 统计当日发送申请导师鉴定所消费的BO币；
        //直播打赏总数
        $sql ="
        select sum(gold) from bb_dashang_log
         where (create_time between {$start} and {$end})
           and record_type=1
           and is_robot = 0
        ";
        $this->money_dashang_zhibo= intval( $db->fetchOne($sql) );
        
        //短视频打赏总数
        $sql ="
        select sum(gold) from bb_dashang_log
         where (create_time between {$start} and {$end})
           and record_type=2
           and is_robot = 0
        ";
        $this->money_dashang_record = intval( $db->fetchOne($sql) );
        
        // 发言人数
        $redis = Sys::get_container_redis();
        $key = "push:fayan:". $date ;
        $count= $redis->sCard($key);
        $redis->setTimeout($key, 24*3*3600);
        $this->push_fayan_count =intval( $count);
        
        // 邀请导师消耗波币
        $sql="
select sum(gold) from bb_record_invite_starmaker_log
where (create_time between {$start} and {$end})
        and status=1
";
        $this->money_yaoqing = intval( $db->fetchOne($sql) );
        
        
        // 活跃数
        $sql ="select count(distinct uid) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and  exists (select 1 from bb_users where bb_users.uid = bb_tongji_log.uid
        and bb_users.permissions in (1,3)
        )
        ";
        $this->huoyue_count= $db->fetchOne($sql);
        
        
        
         echo "task1 nearly success\n";
        
        
//         echo "task2  success\n";
    }
    
    public function tongji_register()
    {
        $db = Sys::get_container_db();
        $start = $time_start = Date::pre_day_start(1);
        $end = $time_end = Date::pre_day_end(1);
        $date = date("Ymd", $time_start);
        // 下面是注册汇总统计
        $sql ="delete from bb_tongji_huizong_register where datestr='{$date}'";
        $db->query($sql);
        $db->insert('bb_tongji_huizong_register', ['datestr'=>$date ]);
        $this->_tongji_register($date,$start,$end);
    }
    
    // 注册汇总统计函数
    private function _tongji_register($date,$start,$end)
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $arr = [
            "jifeng","anzhi","zhangshang"
            ,"youyi"
            ,"mumayi"
            
            ,"3ganzhuo"
            ,"baidu"
            ,"anbei"
            ,"tengxun"
            ,"leshangdian"
            
            ,"vivo"
            ,"leshi"
            ,"aliyun"
            ,"zhihuiyun"
            ,"oppo"
            
            ,"sougou"
            ,"yingyongjie"
            ,"360"
            ,"xiaomi"
            ,"meizu"
            
            ,"ios"
        ];
        
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =28";
        $all_count = $db->fetchOne($sql);
        
//         $sql ="update bb_tongji_huizong_register set all_count  ={$all_count}
//          where datestr = '{$date}'
//         ";
//         $db->query($sql);
        
        $sql ="select info from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =28
        order by id asc
        ";
        $temp33 = $db2->query($sql);
        
       // $qudao_arr = $db->fetchCol($sql);
        
        $i=0;
        
        while ($temp2 = $temp33->fetch()){
            $qudao = $temp2['info'];
//         foreach ($qudao_arr as $qudao) {
            $temp = strtolower($qudao);
            if (in_array($temp, $arr)){
                $sql ="update bb_tongji_huizong_register set name_{$temp}  = 
                  name_{$temp} + 1 where datestr = '{$date}'
                ";
            }else {
                $sql ="update bb_tongji_huizong_register set other_count  = 
                  other_count + 1 where datestr = '{$date}'
                        ";
            }
            $db->query($sql);
            $sql ="update bb_tongji_huizong_register set all_count  =
            all_count + 1 where datestr = '{$date}'
            ";
            
            $db->query($sql);
        }
        
        
        
        $sql ="select info from bb_tongji_log where
        (create_time between {$start} and {$end})
        and type =29
        order by id asc
        ";
        $temp44 = $db2->query($sql);
        
        //$qudao_arr = $db->fetchCol($sql);
        $i=0;
        
        while ($temp2 = $temp44->fetch()){
            $qudao = $temp2['info'];
       //foreach ($qudao_arr as $qudao) {
            $temp = strtolower($qudao);
            if (in_array($temp, $arr)){
                $sql ="update bb_tongji_huizong_register set first_{$temp}  =
                first_{$temp} + 1 where datestr = '{$date}'
                ";
                $db->query($sql);
            }
            
        }
        
        
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
    
  
    
    
}