<?php
namespace app\systemmanage\model;

use BBExtend\Sys;
use BBExtend\common\Date;
use BBExtend\common\Excel;

/**
 * 定时统计帮助类，配合 systemmanager / controller / Tongji.php 使用。
 *
 */
class TongjiUser 
{
    public static function index()
    {
        ini_set ('memory_limit', '1280M');
         $time_start = Date::pre_day_start(1);
        // echo $time_start;
         $time_end = Date::pre_day_end(1);
        $date = date("Ymd", $time_start);
        
        
        $urlname= \BBExtend\common\BBConfig::get_server_url() . "/uploads/tongji/{$date}.xlsx";
        $filename= "/var/www/html/public/uploads/tongji/{$date}.xlsx";
        
        
        require_once 'BBExtend/PHPExcel.php';
        $obj = new Excel($filename);
        $obj->write([
            '统计日期','注册日期', 'UID', '渠道','登录类型',
            '直播总时长(秒)',  '上传视频总数','评论总数','点赞数', '在线时长(秒)', 
            '看直播总时长(秒)', '观看视频次数', '分享数',
            
            
        ]);
//         $obj->write([3,44,'你好']);
//         $obj->save();
//         echo "ok";
        
        
        
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $time = time();
        // 遍历所有用户
        $sql = "select * from bb_users   where bb_users.permissions in (1,3)
                order by uid asc
                ";
        $query = $db2->query($sql);
        $i=0;
        while ($row = $query->fetch()) {
            $uid = $row['uid'];
            
            $register_time = $row['register_time'];
            $register_date = date("Ymd", $register_time);
            $reg_cha = $time - $register_time;
            if ($reg_cha < 9 * 24 * 3600) {
                // 考虑是否修改 currency 表，的loging2，login3，login7字段。
                $day2 = Date::next_day_str($register_date ,1);
                $day3 = Date::next_day_str($register_date ,2);
                $day4 = Date::next_day_str($register_date ,3);
                $day5 = Date::next_day_str($register_date ,4);
                $day6 = Date::next_day_str($register_date ,5);
                $day7 = Date::next_day_str($register_date ,6);
                
//                 $result2 = $db->fetchOne( "select count(*) from bb_tongji_log where 
//                         uid = {$uid} and type = 11 and datestr='{$day2}'");
//                 $result3 = $db->fetchOne( "select count(*) from bb_tongji_log where
//                         uid = {$uid} and type = 11 and datestr='{$day3}'");
//                 $result4 = $db->fetchOne( "select count(*) from bb_tongji_log where
//                         uid = {$uid} and type = 11 and datestr='{$day4}'");
//                 $result5 = $db->fetchOne( "select count(*) from bb_tongji_log where
//                         uid = {$uid} and type = 11 and datestr='{$day5}'");
//                 $result6 = $db->fetchOne( "select count(*) from bb_tongji_log where
//                         uid = {$uid} and type = 11 and datestr='{$day6}'");
//                 $result7 = $db->fetchOne( "select count(*) from bb_tongji_log where
//                         uid = {$uid} and type = 11 and datestr='{$day7}'");
//                 if ($result2) {
//                     $db->update("bb_currency", ['login2'=>1], "uid = {$uid}");
//                 }
//                 if ($result2 && $result3) {
//                     $db->update("bb_currency", ['login3'=>1], "uid = {$uid}");
//                 }
//                 if ($result2 && $result3 && $result4 && $result5 && $result6 && $result7 ) {
//                     $db->update("bb_currency", ['login7'=>1], "uid = {$uid}");
//                 }
                
            }
            // 现在处理 其他数据。遍历每个用户，把昨天的数据全部单独记录下来！！
            $sql ="delete from bb_tongji_user_huizong where uid={$uid} and datestr='{$date}'";
            $db->query($sql);
            // 直播总时长
            $sql="select sum(data2) from bb_tongji_log
              where uid={$uid} and datestr='{$date}' and type=2";
            $zhibo_time = intval( $db->fetchOne($sql) );
            // 上传视频次数
            $sql="select count(*) from bb_tongji_log
            where uid={$uid} and datestr='{$date}' and type=3";
            $shipin_count =  $db->fetchOne($sql) ;
            // 评论次数
            $sql="select count(*) from bb_tongji_log
            where uid={$uid} and datestr='{$date}' and type=4";
            $pinglun_count =  $db->fetchOne($sql) ;
            // 活动次数-> 点赞数
//             $sql="select count(*) from bb_tongji_log
//             where uid={$uid} and datestr='{$date}' and type=7";
//             $huodong_count =  $db->fetchOne($sql) ;
            
            // 点赞数=短视频点赞，加评论点赞，加评论回复的点赞。 再减去取消赞的数量
            $sql ="select count(*) from bb_tongji_log where
            datestr='{$date}'
            and type =18
            and  uid= {$uid}
            ";
            $count1 = $db->fetchOne($sql);
            $sql ="select count(*) from bb_tongji_log where
             datestr='{$date}'
            and type =19
            and   uid= {$uid}
            ";
            $count2 = $db->fetchOne($sql);
            $huodong_count= $count1-$count2;
            
    //echo 2222222222222;        
            // 在线时长
            $sql="select sum(data2) from bb_tongji_log
            where uid={$uid} and datestr='{$date}' and type=12";
            $online_time = intval( $db->fetchOne($sql) );
            // 看直播时长
            $sql="select sum(data2) from bb_tongji_log
            where uid={$uid} and datestr='{$date}' and type=15";
            $view_zhibo_time = intval( $db->fetchOne($sql) );
            // 看短视频个数
            $sql="select count(*) from bb_tongji_log
            where uid={$uid} and datestr='{$date}' and type=13";
            $view_record_count =  $db->fetchOne($sql) ;
            
            //分享次数
            $sql="select count(*) from bb_tongji_log
            where uid={$uid} and datestr='{$date}' and type=16";
            $share_count =  $db->fetchOne($sql) ;
            
            $sql="select qudao from bb_users where uid = {$uid}";
            $qudao = $db->fetchOne($sql);
            
            $db->insert("bb_tongji_user_huizong", [
                'uid' => $uid,
                'qudao' => strval($qudao),
                'datestr'=>$date,
                'zhibo_time' => $zhibo_time,
                'shipin_count' => $shipin_count,
                'pinglun_count'=> $pinglun_count,
                'huodong_count' => $huodong_count,
                'create_time' => time(),
                'online_time' =>$online_time,
                'view_zhibo_time' => $view_zhibo_time,
                'view_record_count'=> $view_record_count,
                'share_count'      => $share_count,
            ]);
            $i++;
            
            
//             $obj->write([
//                 '统计日期','注册日期', 'UID', '渠道','登录类型',
//                 '直播总时长(秒)',  '上传视频总数','评论总数','参与活动次数', '在线时长(秒)',
//                 '看直播总时长(秒)', '观看视频次数', '分享数',
            
            
//             ]);
            $temp33 = [1=>'微信',2=>'QQ',3=>'手机',4=>'微博',];
            $row['register_time'] = intval($row['register_time']);
            if (!in_array($row['login_type'], [1,2,3,4])) {
                $row['login_type'] = 3;
            }
            
            
            $obj->write([
                $date,date("Y-m-d", $row['register_time'] ),$row['uid'],$row['qudao'],
                $temp33[$row['login_type']],
                $zhibo_time, $shipin_count, $pinglun_count, $huodong_count, $online_time,
                $view_zhibo_time,  $view_record_count, $share_count,
            ]);
            
           // $db->insert("bb_ranking", ['uid'=> $row['uid'], 'ranking'=>$i, 'type'=>4 ]);
            if ($i%100==0) {
                echo $i ."... ... \n";
            }
         //   if ($i >100)break;
        }
        $obj->save();
        $db2->insert(
                'bb_tongji_excel',
                [
                    'datestr' => $date,
                    'filename' => $urlname,
                ]
        );
        
        echo "\n\n";
         
        
    }
   
    
    
}