<?php
namespace app\systemmanage\controller;
/**
 * 定时统计，每天晚上自动执行一次，
 * 统计昨日的数据。
 * 
 * @author 谢烨
 */


use BBExtend\Sys;
use BBExtend\common\Date;
use BBExtend\common\Numeric;
use app\systemmanage\model\Tongji201704;
use app\systemmanage\model\TongjiUser;
class Tongji 
{ 
    public $start;
    public $end;
    
    /**
     * 谢烨，这是每日定时任务。
     * 表bb_tongji_huizong_register，先删后加。
     * 上表内容，包括统计每天第一次打开时，和从哪个渠道下载的数量
     * 
     * 表bb_tongji_huizong
     * 最重要的汇总表。
     * 
     * 表bb_tongji_user_huizong
     * 这是单个用户的记录。会有所有有效用户的记录。
     * 
     */
    public function start()
    {
        Sys::display_all_error();
        
        $this->del_yestoday();
        $pre_count = 1;
        
         $this->start = $time_start = Date::pre_day_start($pre_count);
        // echo $time_start;
        $this->end = $time_end = Date::pre_day_end($pre_count);
        $date = date("Ymd", $time_start);
        
      //  $help = new Tongji2();
        $help = new Tongji201704($time_start, $time_end);
  echo "task 70%...\n";      
        $result =[
            'create_time' => time(),
            'datestr' => $date,
            'login1_count'           => $help->login1_count,   // 登陆数2，整型
            'online_time'            => $help->online_time,    // 平均在线时长3  ，整型
            'register_count'         => $help->register_count, // 4注册数  ，整型
            'liucun2'                => $help->liucun2,        // 5   次日留存，浮点型
            'liucun3'                => $help->liucun3,        // 6   3日留存，浮点型
            'liucun7'                => $help->liucun7,        // 7   7日留存，浮点型
            
            'liucun30'                => $help->liucun30,        // 30日留存，浮点型
            'money_yaoqing'           => $help->money_yaoqing,        // 邀请花费波币总额
            'money_dashang_zhibo'     => $help->money_dashang_zhibo,  // 打赏直播总额
            'money_dashang_record'    => $help->money_dashang_record, // 打赏短视频总额
            'push_fayan_count'        => $help->push_fayan_count, // 发言人数
                 
                
            'login2_count'           => $help->login2_count,   // 8   2日登陆，整型
            'login3_count'           => $help->login3_count,   //9    7日登陆，整型
            'push_time_all'          => $help->push_time_all,  // 10  直播次数，整型
            'push_time_today'        => $help->push_time_today,//11 直播人数，整型
            'push_view_avg_all'      => $help->push_view_avg_all, // 12  平均直播时长，整型
            'shipin_count'           => $help->shipin_count,   // 13  短视频数，整型
            'renzheng_shipin_count'  => $help->renzheng_shipin_count, // 14 有效短视频数，整型
            'huodong_count'          => $help->huodong_count,  // 15  有效活动视频，整型
            'renzheng_user_count'    => $help->renzheng_user_count,   // 16  个人认证数，整型
            'renzheng_rate'          => $help->renzheng_rate,  // 17 个人认证成功率，浮点型
            'pinglun_count'          => $help->pinglun_count,  // 18 评论数，整型
            'zan_count'              => $help->zan_count,      // 19 点赞数，整型
            'share_count'            => $help->share_count,    // 20 分享数，整型
            'push_view_count_today'  => $help->push_view_count_today, // 21 直播浏览数，整型
            'push_view_avg_today'    => $help->push_view_avg_today,   // 22 直播浏览平均时长，整型
            'movie_view_count_today' => $help->movie_view_count_today,// 23 短视频浏览次数，整型
            'money1'                 => $help->money1,         // 24 波币消费总额，浮点型
            'money2'                 => $help->money2,         // 25 波币获取数，浮点型
            'money3'                 => $help->money3,         // 26 波豆提现数，浮点型
            'money4'                 => $help->money4,         // 27 充值金额，浮点型
                'huoyue_count'                 => $help->huoyue_count,         // 27 充值金额，浮点型
        ];
        
        
        $db = Sys::get_container_db();
        $sql ="delete from bb_tongji_huizong where datestr='{$date}'";
        $db->query($sql);
        $db->insert('bb_tongji_huizong', $result);
        //$this->start2();
        echo "tongji_huizong1 ok\n";
  //  return;    
        // 注册汇总
        $help->tongji_register();
        
        echo "tongji_register_huizong ok\n";
        TongjiUser::index();
    
        echo "tongji_user ok\n";
       // return $result;
        
    }
    
    public function del_yestoday(){
        $datestr=date("Ymd");
        $db = Sys::get_container_db();
        $sql="delete from bb_tongji_log_today where datestr != '{$datestr}'";
        $db->query($sql);
    }
   
    
    public function tongji_zhibo()
    {
        $time_start = $this->start;
        $time_end = $this->end;
        $db = Sys::get_container_db();
        $avg=0;
        // 首先统计平均直播时长
        $sql ="select * from bb_tongji_log where  (create_time between {$time_start} and {$time_end})
           and type in (1,2)
        ";
        $result =$db->fetchAll($sql);
        $stream_name_arr =[];
        foreach ($result as $v) {
            $stream_name_arr[]= $v['info'];
        }
        $stream_name_arr = array_unique($stream_name_arr);
        if ($stream_name_arr) {
            $sum=0;
            
            foreach ($stream_name_arr as $v) {
                $shichang = $this->get_right_zhibo($result, $v) ;
                if ($shichang) {
             //     echo $shichang ."<br>";
                  $sum+= $shichang;
                }
            }
            $avg = Numeric::div_int($sum, count($stream_name_arr));
        }
       return $avg;
    }
    
//     public function get_upload_movie_count()
//     {
//         $db = Sys::get_container_db();
//         $sql ="select count(*) from bb_tongji_log where  
//           (create_time between {$this->start} and {$this->end})
//         and type =3 ";
//         return $db->fetchOne($sql);
//     }
    
    public function get_comment_count()
    {
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_tongji_log where  
          (create_time between {$this->start} and {$this->end})
        and type =4 ";
        return $db->fetchOne($sql);
    }
    
    public function get_activity_count()
    {
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_tongji_log where  
          (create_time between {$this->start} and {$this->end})
        and type =7 ";
        return $db->fetchOne($sql);
    }
    
    
    public function get_renzheng_user_count()
    {
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$this->start} and {$this->end})
        and type =6 ";
        return $db->fetchOne($sql);
    }
    
    
    public function get_vip_count()
    {
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$this->start} and {$this->end})
        and type =8 ";
        return $db->fetchOne($sql);
    }
    
    public function get_renzheng_movie_count()
    {
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_tongji_log where
        (create_time between {$this->start} and {$this->end})
        and type =9 ";
        return $db->fetchOne($sql);
    }
    
    
    public function get_view_count()
    {
        $db = Sys::get_container_db();
        $sql ="select sum(data) from bb_tongji_log where
        (create_time between {$this->start} and {$this->end})
        and type =10 ";
        return intval($db->fetchOne($sql));
    }
    
    private function get_right_zhibo($arr, $stream_name)
    {
        $temp1 = 0;
        $temp2 =0;
        foreach ($arr as $v) {
            if ($v['info'] == $stream_name) {
                if ($v['type']==1){
                    $temp1 = $v['create_time'];
                }
                if ($v['type']==2){
                    $temp2 = $v['create_time'];
                }
    
            }
        }
        if ($temp1 && $temp2){
            if ($temp2 > $temp1) {
              return $temp2-$temp1;
            }else {
                return 0;
            }
        }
        return 0;
    }
    
}