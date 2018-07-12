<?php
namespace BBExtend\user\lottery;

/**
 * 关注类
 * 
 * 数量用最简单的set单变量！
 * 关注人列表用集合
 * 
 * 谢烨
 */

use BBExtend\Sys;
use think\Db;


class Task
{
    
    public $uid;
    public $datestr;// 类似20170801
    /**
     * 
     * @param number $uid
     */
    public function  __construct($uid=0) {
        $uid = intval($uid);
        $this->uid = $uid;
        $datestr = $this->datestr = date("Ymd");
    }
    
    public static function getInstance($uid)
    {
        return new self($uid);
    }
    
    /**
     * ①今日抽中奖励“再来一次“
     ②今日分享达到5次
     ③今日直播时长累积满30分钟
     ④今日上传小视频认证成功
     ⑤今日在线时长累积满60分钟
     ⑥今日观看直播满30分钟
     ⑦今日被其他用户点赞10次
     ⑧今日点赞其他用户20次
     ⑨今日成功发布评论10条
     ⑩今日关注20位不同用户
     ⑪今日被10位不同用户关注
     */
    public function check($type)
    {
        $uid = $this->uid;    
        $db = Sys::get_container_db(); 
        $datestr = date("Ymd");
        $result = false;
        switch ($type) {
            case 2: //今日分享达到5次
                $sql = "select count(*) from  bb_tongji_log_today  where  type =16 and uid={$uid}";
                $count = $db->fetchOne($sql);
                $result = ($count >= 5); 
                break;
            case 3: //今日直播时长累积满30分钟
                $sql = "select sum( data2) from  bb_tongji_log_today
                where  type =2 and uid={$uid}";
                $count = $db->fetchOne($sql);
                $result = ($count >= 30 * 60);
                break;
            case 4: //今日上传小视频认证成功
                $sql = "select count(*) from  bb_tongji_log_today
                where  type =9 and uid={$uid}";
                $count = $db->fetchOne($sql);
                $result = ($count >= 1);
                break;
            case 5://今日在线时长累积满60分钟
                $sql = "select sum(login_time) from bb_tongji_user_login_time
                         where  uid={$uid} and datestr='{$datestr}' ";
                $count = $db->fetchOne($sql);
                $result = ($count >= 60* 60);
                break;
            case 6:  //今日观看直播满30分钟
                $sql = "select sum( data2) from  bb_tongji_log_today
                         where  type =15 and uid={$uid}";
                $count = $db->fetchOne($sql);
                $result = ($count >= 30 * 60);
                break;
            case 7:  //今日被其他用户点赞10次
                $sql = "select count(*) from  bb_tongji_log_today
                where  type =18 and data2={$uid}";
                $count = $db->fetchOne($sql);
                $result =( $count >= 10);
                break;
            case 8: //今日点赞其他用户20次
                $sql = "select count(*) from  bb_tongji_log_today
                where  type =18 and uid={$uid}";
                $count = $db->fetchOne($sql);
                $result =( $count >= 20);
                break;
            case 9:  //今日成功发布评论10条
                $sql = "select count(*) from  bb_tongji_log_today
                where  type =4 and uid={$uid}";
                $count = $db->fetchOne($sql);
                $result = ($count >= 10);
                break;
            case 10: //今日关注20位不同用户
                $sql = "select count(distinct data2) from  bb_tongji_log_today 
                where type=30  and uid={$uid}";
                $count = $db->fetchOne($sql);
                $result = ($count >= 20);
                break;
            case 11: //今日被10位不同用户关注
                $sql = "select count( distinct uid) from  bb_tongji_log_today
                where  type =30 and data2={$uid}";
                $count = $db->fetchOne($sql);
                $result = ($count >= 10);
                break;
            default:
                $result = false;
        }
        return $result? 1:0;
    }
    

}