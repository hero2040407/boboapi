<?php
namespace app\apptest\controller;

// use BBExtend\BBRedis;
use  think\Db;
use BBExtend\common\MysqlTool;
use BBExtend\Sys;
class Create
{
    
    public function clean_redis11()
    {
        $redis = Sys::getredis11();
      //  $redis->flushDB();
      //  echo "clean redis 11 ok\n";
        
        $db = Sys::get_container_db();
        $sql="truncate table  mysql.general_log";
        $db->query($sql);
    }
    
    
    /**
     * 201708 加成就表
     */
    public function acheivement()
    {
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users 
                where not exists (select 1 from bb_users_achievement
                  where bb_users_achievement.uid=bb_users.uid
                )
                
                order by uid asc";
        $query = $db->query($sql);
        $arr=[];
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $i++;
            $j++;
            $arr[]= $row["uid"];
            if ($i==100) {
                $str =[];
                foreach (range(1, count($arr)) as $v ) {
                    $str[]= "(?)";
                }
                $str = implode(",", $str);
               $sql="insert into bb_users_achievement (uid) values {$str}";
                $db2->query($sql, $arr);
                
                
                $arr=[];
                $i=0;
                echo "current: ".$j."\n";
            }
            if ($j%10000==0){
              //  echo "current: ".$j."\n";
            }
                //break;
        }
        if (count($arr)) {
            $str =[];
            foreach (range(1, count($arr)) as $v ) {
                $str[]= "(?)";
            }
            $str = implode(",", $str);
            $sql="insert into bb_users_achievement (uid) values {$str}";
            $db2->query($sql, $arr);
        }
        echo "all ok\n";
    }
    
    /**
     * 201708 加成就汇总表
     */
    public function acheivementsummary()
    {
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users
                where not exists (select 1 from bb_users_achievement_summary
                  where bb_users_achievement_summary.uid=bb_users.uid
                )
    
                order by uid asc";
        $query = $db->query($sql);
        $arr=[];
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $i++;
            $j++;
            $arr[]= $row["uid"];
            if ($i==100) {
                $str =[];
                foreach (range(1, count($arr)) as $v ) {
                    $str[]= "(?)";
                }
                $str = implode(",", $str);
                $sql="insert into bb_users_achievement_summary (uid) values {$str}";
                $db2->query($sql, $arr);
    
    
                $arr=[];
                $i=0;
                echo "current: ".$j."\n";
            }
            if ($j%10000==0){
                //  echo "current: ".$j."\n";
            }
            //break;
        }
        if (count($arr)) {
            $str =[];
            foreach (range(1, count($arr)) as $v ) {
                $str[]= "(?)";
            }
            $str = implode(",", $str);
            $sql="insert into bb_users_achievement_summary (uid) values {$str}";
            $db2->query($sql, $arr);
        }
        echo "all ok\n";
    }
    
    
    /**
     * 
     * 名称                  内容                        初级                   中级                高级
等级达人            等级                        LV5                    LV10               LV20
直播达人            直播时长                 10小时                100小时          500小时
评论达人            评论次数                  50次                   200次             500次
点赞达人            点赞次数                  100次                 300次             800次
优质主播            被点赞次数               500次                 2000次           6000次
BOBO小红人      粉丝数                     100人                 500人             2000人
活动达人            参加活动次数            5次                    20次               100次
大赛达人            参加大赛次数            5次                    20次               100次
内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_dengji()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $arr=[];
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select level from bb_users_exp where uid={$uid}";
            $level = intval( $db2->fetchOne($sql) );
            if ($level <5) {
                $result = 0;
            }elseif ($level < 10) {
                $result = 1;
            }elseif ($level < 20) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set dengji=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
              echo  "--{$i}--". $uid."\n";
            }
        }
        
        echo 22;
    }
    
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_zhibo()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select sum( data2) from  bb_tongji_log
where  type =2
  and uid =?";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set zhibo=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
            
            if ($zhibo <10* 3600) {
                $result = 0;
            }elseif ($zhibo < 100* 3600) {
                $result = 1;
            }elseif ($zhibo < 500* 3600) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set zhibo=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_pinglun()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select count(*) from  bb_tongji_log
where  type =4
  and uid =?";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set pinglun=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
    
            if ($zhibo <50) {
                $result = 0;
            }elseif ($zhibo < 200) {
                $result = 1;
            }elseif ($zhibo < 500) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set pinglun=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_dianzan()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select count(*) from  bb_tongji_log
where  type =18
  and uid =?";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set dianzan=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
    
            if ($zhibo <100) {
                $result = 0;
            }elseif ($zhibo < 300) {
                $result = 1;
            }elseif ($zhibo < 800) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set dianzan=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_zhubo()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select count(*) from  bb_tongji_log
where  type =18
  and data2 =?";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set zhubo=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
    
            if ($zhibo <500) {
                $result = 0;
            }elseif ($zhibo < 2000) {
                $result = 1;
            }elseif ($zhibo < 6000) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set zhubo=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_hongren()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select count(*) from bb_focus where focus_uid=?";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set hongren=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
    
            if ($zhibo <100) {
                $result = 0;
            }elseif ($zhibo < 500) {
                $result = 1;
            }elseif ($zhibo < 2000) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set hongren=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_huodong()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select count(*) from bb_user_activity where uid=?";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set huodong=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
    
            if ($zhibo <5) {
                $result = 0;
            }elseif ($zhibo < 20) {
                $result = 1;
            }elseif ($zhibo < 100) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set huodong=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_dasai()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="select  count(distinct ds_id) from ds_record where uid=?";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set dasai=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
    
            if ($zhibo <5) {
                $result = 0;
            }elseif ($zhibo < 20) {
                $result = 1;
            }elseif ($zhibo < 100) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set dasai=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    
    /**
     *
     * 名称                  内容                        初级                   中级                高级
     等级达人            等级                        LV5                    LV10               LV20
     直播达人            直播时长                 10小时                100小时          500小时
     评论达人            评论次数                  50次                   200次             500次
     点赞达人            点赞次数                  100次                 300次             800次
     优质主播            被点赞次数               500次                 2000次           6000次
     BOBO小红人      粉丝数                     100人                 500人             2000人
     活动达人            参加活动次数            5次                    20次               100次
     大赛达人            参加大赛次数            5次                    20次               100次
     内容缔造者         短视频发布次数         10个                  30个               100个
     */
    public function batch_update_achiev_neirong()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select uid from bb_users where permissions < 6
                order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $sql ="
                    select count(*) 
                      from bb_record
                     where uid =?
                       and is_remove=0
                       and type !=3
                       and audit=1";
            $zhibo = intval( $db2->fetchOne($sql, $uid) );
            $sql ="update bb_users_achievement_summary set neirong=? where uid=?";
            $db2->query($sql,[$zhibo, $uid]);
    
            if ($zhibo <10) {
                $result = 0;
            }elseif ($zhibo < 30) {
                $result = 1;
            }elseif ($zhibo < 100) {
                $result = 2;
            }else {
                $result = 3;
            }
            $sql ="update bb_users_achievement set neirong=? where uid=?";
            $db2->query($sql,[$result, $uid]);
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
    
        echo "all ok\n";
    }
    
    
    public function batch_update_msg_user_config()
    {
        $db  = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select  uid from bb_msg_user_config  group by uid order by uid asc";
        $query = $db->query($sql);
        $i=0;$j=0;
        while($row = $query->fetch()) {
            $uid = $row["uid"];
            $i++;
            $j++;
            $db2->insert("bb_msg_user_config", [
                    "bigtype" =>0,
                    "uid" =>$uid,
                    "value"=>1,
                    "type" => 152,
                    'title' => "好友成就动态",
                    'sort'  => 35,
            ]);
           
            if ($i % 100==0) {
                echo  "--{$i}--". $uid."\n";
            }
        }
       
        echo "all ok\n";
    }
    
    
    
    
    
    public function setmingci()
    {
        $redis = Sys::getredis_paihangbang();
        $db = Sys::get_container_db();
      //  echo 3333;
        $key = "rank:1";  //1财富，2粉丝，3，等级经验，4，怪兽数量
//         $this->key_caifu = "rank:1";
//         $this->key_fensi = "rank:2";
//         $this->key_dengji = "rank:3";
        $arr = $redis->zReverseRange($key, 0, 89 );
        foreach ($arr as $uid) {
          $sql ="update bb_currency set caifu_ranking=1 where uid ={$uid}";
          $db->query($sql);
        }
       // echo 1;
        $key = "rank:2";
        $arr = $redis->zReverseRange($key, 0, 89 );
        foreach ($arr as $uid) {
            $sql ="update bb_currency set fensi_ranking=1 where uid ={$uid}";
            $db->query($sql);
        }
        $key = "rank:3";
        $arr = $redis->zReverseRange($key, 0, 89 );
        foreach ($arr as $uid) {
            $sql ="update bb_currency set lv_ranking=1 where uid ={$uid}";
            $db->query($sql);
        }
        echo "all ok\n";
    }
    
    public function rank_redis()
    {
        return;
        $redis = Sys::getredis_paihangbang();
        // 1财富，2粉丝，3，等级经验，4，怪兽数量
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql ="select uid from bb_users order by uid asc";
        $query = $db2->query($sql);
        
        $redis->delete("rank:1");
        $redis->delete("rank:2");
        $redis->delete("rank:3");
        $redis->delete("rank:4");
        $redis->delete("rank:5");
        $i=0;
        while ($row = $query->fetch()) {
            $i++;
            if ($i%100==0) {
                echo "$i ... ... \n";
            }
            $uid = $row['uid'];
            $help = new \BBExtend\user\Ranking($uid);
            $help->set_caifu_ranking()->set_dengji_ranking()
                 ->set_fensi_ranking()->set_guaishou_ranking();
//                  ->set_dashang_ranking();
        }
        echo "all ok \n";
        
    }
    
    public function rank_database()
    {
        $table = 'bb_ranking';
        MysqlTool::clear_table($table);
       // 1财富，2粉丝，3，等级经验，4，怪兽数量
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        
        // 按怪兽数量
         $sql = "select * from bb_users order by monster_count desc, register_time asc";
         $query = $db->query($sql);
         $i=0;
         while ($row = $query->fetch()) {
             $i++;
             $db2->insert("bb_ranking", ['uid'=> $row['uid'], 'ranking'=>$i, 'type'=>4 ]);
             if ($i%100==0) {
                 echo $i ."... ... \n";
             }
         }
         echo "\n\n";
         
         //按等级
         $sql = "
                 select bb_users.uid
from bb_users
left join bb_users_exp
on bb_users_exp.uid = bb_users.uid
order by bb_users_exp.level desc, bb_users_exp.exp desc
                 ";
         $query = $db->query($sql);
         $i=0;
         while ($row = $query->fetch()) {
             $i++;
             $db2->insert("bb_ranking", ['uid'=> $row['uid'], 'ranking'=>$i, 'type'=>3 ]);
             if ($i%100==0) {
                 echo $i ."... ... \n";
             }
         }
         echo "\n\n";
         
         //  按粉丝数量
         $sql = "
                select bb_users.uid,(select count(*) 
  from bb_focus where focus_uid=bb_users.uid) count1
from bb_users
order by count1 desc ,uid asc
                 ";
         $query = $db->query($sql);
         $i=0;
         while ($row = $query->fetch()) {
             $i++;
             $db2->insert("bb_ranking", ['uid'=> $row['uid'], 'ranking'=>$i, 'type'=>2 ]);
             if ($i%100==0) {
                 echo $i ."... ... \n";
             }
         }
         echo "\n\n";
         
         
         $sql = "
               select bb_users.uid
from bb_users
left join bb_currency
on bb_currency.uid = bb_users.uid
order by bb_currency.gold desc, bb_users.uid asc
                 ";
         $query = $db->query($sql);
         $i=0;
         while ($row = $query->fetch()) {
             $i++;
             $db2->insert("bb_ranking", ['uid'=> $row['uid'], 'ranking'=>$i, 'type'=>1 ]);
             if ($i%100==0) {
                 echo $i ."... ... \n";
             }
         }
         echo "\n\n";
         
      //  
        echo "all ok\n";
    }
   
    
   
}
