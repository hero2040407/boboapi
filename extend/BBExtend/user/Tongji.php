<?php
namespace BBExtend\user;

/**
 * 统计日志类，把数据插入到bb_tongji_log表里
 * 
 * 数量用最简单的set单变量！
 * 关注人列表用集合
 * 
 * 1直播开始时间，
 * 2直播结束时间，
 * 3用户上传视频，
 * 4评论，
 * 5观看直播数，
 * 6认证人数，
 * 7活动次数
 * 8 vip续费
 * 9 视频认证数
 * 
 * 10 观看直播
 * 11 用户上线
 * 12 用户下线
 * 
 * 13 观看短视频数。
 * 14 进入直播房间
 * 15 退出直播房间
 * 16 分享一次
 * 17 视频认证，属于个人认证。
 * 18 zan一次 点赞
 * 19 un_zan一次，取消点赞
 * 
 * 20 登录
 * 24 bo币消费总额
 * 25 bo币获取数
 * 26 bo豆提现数
 * 27 充值金额元。
 * 
 * 28 注册
 * 29 注册前第一次使用
 * 30关注
 * 
 * 31  给直播打赏总数，   统计日期当日观看直播所打赏的BO币
 * 32  给短视频打赏总数，统计日期当日平台内短视频送礼物消费的BO币总额
 * 33  给分享打赏总数，   统计日期当日短视频分享后送礼物消费的BO币总额
 * 34  统计当日发送申请导师鉴定所
 * 
 * 谢烨
 */

use BBExtend\Sys;
use app\user\model\Tongji as tj;
use app\user\model\TongjiToday as tj2;
use BBExtend\common\Date;
use think\Config;
class Tongji 
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
    public $message;
    /**
     * 
     * @param number $uid
     */
    public function  __construct($uid=0) {
        $uid = intval($uid);
        $this->redis = Sys::getredis11();
        $this->uid = $uid;
        
    }
    
    /**
     * 看情况用，如果想调用多个方法，则不用此函数
     * 
     * @param unknown $uid
     */
    public static function getinstance($uid)
    {
        return new self($uid); 
    }
    
    //1直播开始时间，2直播结束时间，3用户上传视频，4评论，5观看直播数，6认证人数，7活动次数
    public function zhibo_start($streamName='')
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', $time);
        $log->data('info', $streamName);
        $log->data('type', 1); 
        $log->data('create_time', $time); 
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', $time);
        $log->data('info', $streamName);
        $log->data('type', 1);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    

    //1直播开始时间，2直播结束时间，3用户上传视频，4评论，5观看直播数，6认证人数，7活动次数
    public function zhibo_end($streamName='')  
    {
        $db = Sys::get_container_db();
        $time = time();
        $time_start = Date::pre_day_start(0);
        // echo $time_start;
        $time_end = Date::pre_day_end(0);
        $shi_cha = 0;// 时差
        if ($streamName) {
            // 谢烨，先查出 该直播开始的时间。
            $sql="select * from bb_tongji_log where  (create_time between {$time_start} and {$time_end})
            and type =1 and uid={$this->uid} and info=?";
            $row = $db->fetchRow($sql, $streamName);
            if ($row) {
                $shi_cha = $time - $row['create_time'];
            }
        }
        $log = new tj();
        
        $log->data('uid', $this->uid);
        $log->data('data', $time);
        $log->data('data2', $shi_cha);
        $log->data('info', $streamName);
        $log->data('type', 2); //
        $log->data('create_time', $time); 
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $log->data('uid', $this->uid);
        $log->data('data', $time);
        $log->data('data2', $shi_cha);
        $log->data('info', $streamName);
        $log->data('type', 2); //
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        return  $shi_cha;// xieye 2016 12 单位秒
    }
    
    //1直播开始时间，2直播结束时间，3用户上传视频，4评论，5观看直播数，6认证人数，7活动次数
    public function upload_movie()  // 论次数
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1);  // 论次数
        $log->data('type', 3); 
        $log->data('create_time', $time); 
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1);  // 论次数
        $log->data('type', 3);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    //4 评论
    public function comment()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 4); 
        $log->data('create_time', $time); 
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 4);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    //7 活动次数
    public function activity()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 7);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 7);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    //6 认证用户
    public function renzheng_yonghu()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 6);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 6);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    //17 认证用户成功，是后台审核的。
    public function geren_renzheng_success()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 17);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 17);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 8 vip申请
    public function vip()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 8);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 8);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 9 认证视频
    public function renzheng_movie()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 9);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1); // 论次数
        $log->data('type', 9);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 10 观看直播数
    public function view_count($count=1)
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', intval($count) ); // 论次数
        $log->data('type', 10);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', intval($count) ); // 论次数
        $log->data('type', 10);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    // 11 用户上线
    public function login()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', $time ); // 论次数
        $log->data('type', 11);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', $time ); // 论次数
        $log->data('type', 11);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 12 用户下线
    public function logout()
    {
        $log = new tj();
        $time = time();
        $start0 = Date::pre_day_start(0);
        
        $db = Sys::get_container_db();
        $sql ="select create_time from bb_tongji_log where uid={$this->uid} and type=11
          order by id desc limit 1
        ";
        $login_time = $db->fetchOne($sql);
        if (!$login_time) {
            $login_time = $time;
        }
        
        $cha = $time - $login_time;
//         $sql ="update bb_currency set all_login_time=all_login_time+{$cha} where uid={$this->uid}";
//         $db->query($sql);
        
        // 谢烨，查询日统计表
        $sql ="select id from bb_tongji_user_login_time
                where uid = {$this->uid} and dateint = {$start0}
                ";
        $id  = $db->fetchOne($sql);
        if ($id) {
            $sql ="update bb_tongji_user_login_time 
                   set login_time=login_time+{$cha} where id={$id}";
            $db->query($sql);
        }else {
            $db->insert('bb_tongji_user_login_time', [
                'uid' => $this->uid,
                'datestr' => date("Ymd"),
                'dateint' => $start0,
                'login_time' => $cha,
            ]);
        }
        
        
//         $db->update("bb_currency", ['all_login_time'], "uid={$this->uid}");
        $log->data('uid', $this->uid);
        $log->data('data', $time ); // 论次数
        $log->data('data2', $cha ); // 
        $log->data('type', 12);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $log->data('uid', $this->uid);
        $log->data('data', $time ); // 论次数
        $log->data('data2', $cha ); //
        $log->data('type', 12);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 13 观看短视频数，BBrecord::notify_enterroom,163行
    public function view_record()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 13);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 13);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 18 赞一次
    public function zan($target_uid=0)
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('data2', $target_uid ); 
        $log->data('type', 18);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('data2', $target_uid );
        $log->data('type', 18);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 19 un赞一次
    public function un_zan()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 19);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 19);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    
    // 14 进入观看直播房间
    /**
     * 运营单独要求，别统计自己就是主播的情况，于是，我就在本函数里判断。
     * @param unknown $room_id
     */
    public function enter_room($room_id)
    {
        $log = new tj();
        $time = time();
        
        $db = Sys::get_container_db();
        
        $sql="select login_type from bb_users where uid={$this->uid}";
        $login_type = $db->fetchOne($sql);
        if ($login_type && $login_type==5) {
            return;
        }
        
        $sql = "select uid from bb_push where room_id=?";
        $result = $db->fetchOne($sql, $room_id);
        if ($result && $result == $this->uid) {
            return;
        }
        
        $log->data('uid', $this->uid);
        $log->data('data', $time ); // 
        $log->data('type', 14);
        $log->data('create_time', $time);
        $log->data('info', $room_id);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $log->data('uid', $this->uid);
        $log->data('data', $time ); //
        $log->data('type', 14);
        $log->data('create_time', $time);
        $log->data('info', $room_id);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 15 退出观看直播房间
    /**
     * 运营单独要求，别统计自己就是主播的情况，于是，我就在本函数里判断。
     * @param unknown $room_id
     */
    public function exit_room($room_id)
    {
        $log = new tj();
        $time = time();
        $db = Sys::get_container_db();
        
        
        $sql="select login_type from bb_users where uid={$this->uid}";
        $login_type = $db->fetchOne($sql);
        if ($login_type && $login_type==5) {
            return;
        }
        
        $sql = "select uid from bb_push where room_id=?";
        $result = $db->fetchOne($sql, $room_id);
        if ($result && $result == $this->uid) {
            return;
        }
        
        $time_start = Date::pre_day_start(0);
        // echo $time_start;
        $time_end = Date::pre_day_end(0);
        $shi_cha = 0;// 时差
        if ($room_id) {
            // 谢烨，先查出 该直播开始的时间。
            $sql="select * from bb_tongji_log where  (create_time between {$time_start} and {$time_end})
            and type =14 and uid={$this->uid} and info=? 
            order by id desc 
            limit 1
            ";
            $row = $db->fetchRow($sql, $room_id);
            if ($row) {
                $shi_cha = $time - $row['create_time'];
            }
        }
        $log = new tj();
        
        $log->data('uid', $this->uid);
        $log->data('data', $time ); // 
        $log->data('data2', $shi_cha);
        $log->data('type', 15);
        $log->data('create_time', $time);
        $log->data('info', $room_id);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        
        $log->data('uid', $this->uid);
        $log->data('data', $time ); //
        $log->data('data2', $shi_cha);
        $log->data('type', 15);
        $log->data('create_time', $time);
        $log->data('info', $room_id);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 16 分享一次
    /**
     * 分享 
     * 
     */
    public function share()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 16);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 16);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 20 真正的登录
    /**
     * 分享
     *
     */
    public function otherlogin()
    {
        $request = \think\Request::instance();
        $user_agent =$request->header("user-agent");
        if (!$user_agent){
            $user_agent='';
        }
        
        
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 20);
        $log->data('info', $user_agent);
        
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 20);
        $log->data('info', $user_agent);
        
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    /**
     * 24 24.	BO币消费总额：统计日期当日用户送礼总共消费的BO币数量
     *
     */
    public function money24($money)
    {
        $log = new tj();
        $money = floatval($money);
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('money', $money ); // 
        $log->data('type', 24);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $money = floatval($money);
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('money', $money ); //
        $log->data('type', 24);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    /**
     * .	25.	BO币获取数：
     *
     */
    public function money25($money)
    {
        $log = new tj();
        $money = floatval($money);
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('money', $money ); //
        $log->data('type', 25);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $money = floatval($money);
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('money', $money ); //
        $log->data('type', 25);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    /**
     * .	27.	充值金额：
     *
     */
    public function money27($money)
    {
        $log = new tj();
        $money = floatval($money);
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('money', $money ); //
        $log->data('type', 27);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $money = floatval($money);
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('money', $money ); //
        $log->data('type', 27);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    }
    
    // 28 注册
    public function register($qudao='')
    {
        $user = \app\user\model\UserModel::getinstance($this->uid);
        if ($user->get_permission()>9) {
            return;
        }
        
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 28);
        $log->data('info', $qudao);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 28);
        $log->data('info', $qudao);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
    }
    
    // 29 app安装后第一次使用
    public function first_use()
    {
        $log = new tj();
        $time = time();
        $log->data('uid', 0);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 29);
        $qudao = \BBExtend\user\Common::get_qudao();
        
        $log->data('info', $qudao);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    
        $log = new tj2();
        $time = time();
        $log->data('uid', 0);
        $log->data('data', 1 ); // 论次数
        $log->data('type', 29);
        $qudao = \BBExtend\user\Common::get_qudao();
        
        $log->data('info', $qudao);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
    
    }
    
    
    // 30 关注
    /**
     * 分享
     *
     */
    public function focus($target_uid=0)
    {
        $log = new tj();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('data2', $target_uid ); // 论次数
        $log->data('type', 30);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
        $log = new tj2();
        $time = time();
        $log->data('uid', $this->uid);
        $log->data('data', 1 ); // 论次数
        $log->data('data2', $target_uid ); // 论次数
        $log->data('type', 30);
        $log->data('create_time', $time);
        $log->data('datestr', date("Ymd"));
        $log->save();
        
    }
    

}