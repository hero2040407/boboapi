<?php
namespace BBExtend\user\exp;

/**
 * 经验值类，新的，老的Level逐渐废止。
 * 
 * 谢烨 2016 12
 */

use BBExtend\Sys;
use think\Db;
use BBExtend\fix\LevelFix;
use BBExtend\BBRedis;
use BBExtend\message\Message;
use BBExtend\Currency;

class Exp 
{
    // 下面定义的是全部的加经验的类型。
    const LEVEL_LOGIN                   = 0;  //每日登录
    const LEVEL_COMPLETE_UPLOAD_PIC     = 1;  //上传头像
    const LEVEL_COMPLETE_ATTESTAION     = 2;  //完成直播认证
    const LEVEL_COMPLETE_CHANG_USERINFO = 3;  //资料完善
    const LEVEL_PUSH                    = 4;  //发起直播
    const LEVEL_RECORD                  = 5;  //发布短视频
    const LEVEL_COMMENTS                = 6;  //发布文字评论
    const LEVEL_SHARE                   = 7;  //分享
    const LEVEL_SHARE_OTHER_USER        = 8;  //内容被他人分享
    const LEVEL_INVITATION_REGISTER     = 9;  //邀请好友注册
    const LEVEL_ACTIVITY_LIKE           = 10; //活动点赞
    const LEVEL_COMPLETE_TASK           = 11; //完成任务
    const LEVEL_LIKE                    = 12; //被关注
    const LEVEL_SHOW_LIVE_COURSE        = 13; //点播课程
    const LEVEL_SHOP                    = 14; //商城购买/兑换
    const LEVEL_DASHANG                 = 15; //打赏
    
    public $uid;
    public $who_uid;
    public $typeint;
    public $type_name;
    public $exp_config; // 这是文档中定义的该加的经验值，直播例外
    public $is_test=0;// 这个主要测试用，勿修改。
    
    public $shi_cha=0; //直播时差。但是打赏也用了，表示奖励波币。
    public $present_id=0; // 打赏礼物id
    
    /**
     * 
     * @param number $uid
     */
    public function  __construct($uid=0) {
        $uid = intval($uid);
        $this->uid = $uid;
       $this->who_uid=0;
       $this->typeint=0;
       $this->type_name='';
       $this->exp_config=0;
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

    public function set_who_uid($who_uid)
    {
        $this->who_uid=intval($who_uid);
        return $this;
    }
    
    public function set_is_test($is_test)
    {
        $this->is_test =$is_test;
        return $this;
    }
   
    public function set_shi_cha($shi_cha)
    {
        $this->shi_cha = intval($shi_cha);
        return $this;
    }
    
    public function set_present_id($present_id)
    {
        $this->present_id = intval($present_id);
        return $this;
    }
    
    
    public function set_typeint($typeint)
    {
        $this->typeint =intval($typeint);
        $this->type_name = $this->get_type_name($this->typeint);
        $this->exp_config = $this->get_exp_config($this->typeint);
        return $this;
    }
    
    /**
     * 这个方法是主方法，加用户的经验值
     */
    public function add_exp()
    {
        $worker = $this->get_worker_obj();
        $worker->add_exp($this);
        
    }
    
    /**
     * 谢烨，目前分4种
     * 
     * 1. 直播单独算
     * 2. 一次性，初始3个任务。
     * 3. 常规限制，以天计。
     * 4. 无限制的，每次都加。
     * 
     */
    private function get_worker_obj()
    {
        if ($this->typeint == self::LEVEL_PUSH  ) {
            return new ExpPush();
        }
        if ($this->typeint == self::LEVEL_DASHANG ) {
            return new ExpDashang();
        }
        if (in_array($this->typeint, [
            self::LEVEL_COMPLETE_UPLOAD_PIC,    // 上传头像
            self::LEVEL_COMPLETE_ATTESTAION,    // 认证
            self::LEVEL_COMPLETE_CHANG_USERINFO,// 资料完善
            
        ])) {
            return new ExpOneTime();
        }
        if (in_array($this->typeint, [
            self::LEVEL_LOGIN,  //登录    
            self::LEVEL_RECORD, // 发布短视频 
            self::LEVEL_COMMENTS, // 发布文字评论
            self::LEVEL_SHARE, //分享
            self::LEVEL_SHARE_OTHER_USER, // 被分享
            //self::LEVEL_INVITATION_REGISTER, //邀请他人注册
            self::LEVEL_ACTIVITY_LIKE, //活动点赞
            self::LEVEL_COMPLETE_TASK, //完成任务
            self::LEVEL_LIKE, //被关注
        
        ])) {
            return new ExpLimit();
        }
        
        return new ExpNoLimit(); // 这是无限制的。
    }

    /**
     * 给加经验值这事情记录日志。
     * @param number $exp_count
     */
    private function log($exp_count=0)
    {
        $db = Sys::get_container_db();
        $time = time();
        $db->insert('bb_users_exp_log', [
            'uid' => $this->uid,
            'type' => $this->type_name,
            'exp' => $exp_count?$exp_count : $this->exp_config,
            'who_uid' => $this->who_uid,
            'time' => $time,
            'create_time' => $time,
            'datestr' => date("Ymd"),
            'typeint' =>$this->typeint,
        ]);
        
    }
    
    /**
     * 这里面名堂很多，目前，经验值不会减少，只会增加！2016 12
     * 该函数主要修改经验值表，如果升级，做调用处理
     * 
     * 
     * @param number $exp_count
     */
    public function update($exp_count=0)
    {
        $db = Sys::get_container_db();
        $time = time();
        $uid = $this->uid;
        // 首先查出当前的数据。
        $sql="select * from bb_users_exp where uid=?";
        $row = $db->fetchRow($sql, $this->uid);
        if (!$row) {
            return false;
        }
        $old_level = $row['level'];
        $old_exp =$row['exp'];
        $old_next_exp = $row['next_exp'];
        if ($old_level>=100) {
            return false;
        }
        
        //对的话，先日志
        $this->log($exp_count);
        
        //先得到当前的总经验
        $fix_all = LevelFix::get_all();    // 从1到100
        $old_sum = $fix_all[$old_level] + $old_exp; // 得到总经验，计算的依据
        $exp_increment = $exp_count?$exp_count : $this->exp_config;
        
        //计算新的总经验
        $new_sum = abs($exp_increment) + $old_sum;
        list($new_level, $new_exp, $new_next_exp) = $this->reckon_exp($new_sum);
        $db->update("bb_users_exp", [
            'level' => $new_level,
            'exp'   => $new_exp,
            'next_exp' => $new_next_exp,
            ], "uid = {$uid}" );
        // 重要，既然经验值变了，即使level不变，排名也会更新的！！
       // \BBExtend\user\Ranking::getinstance($uid)->set_dengji_ranking();
        // 为了兼容老代码，redis也更新。
        $arr = Db::table('bb_users_exp')->where('uid',$uid)->find();
        BBRedis::getInstance('user')->hMset($uid.'level', $arr);
        
        if ($new_level > $old_level) {
            $this->levelup($new_level);
            // 谢烨，现在还要更新成就2017 08
            $obj = new \BBExtend\user\achievement\Dengji($uid);
            $obj->update($new_level - $old_level);
            
            
        }
        return true;
    }
    
    /**
     * 用户升级要做哪些事。
     * @param int $new_level
     */
    private function levelup($new_level)
    {
        $uid = $this->uid;
        if ($this->is_test == 0) {
          Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content("恭喜你升至"))
            ->add_content(Message::simple()->content("LV{$new_level}")->color(0xf4a560)  )
            ->add_content(Message::simple()->content('，请进入'))
            ->add_content(Message::simple()->content('我的等级')->color(0x32c9c9)
                ->url(json_encode(['type'=>5,  ]) )
                )
            ->add_content(Message::simple()->content('查看。'))
            ->set_type(125)
            ->set_uid($uid)
            ->send();
        }
        Currency::add_currency($uid,CURRENCY_MONSTER,1,'用户升级');
    }
    
    /**
     * 对于一个给定的经验值总和，返回其level，exp，next_exp
     * xieye ,已单独对本函数单元测试过。请勿修改 2016 12
     *  
     * @param integer $exp
     */
    private function reckon_exp($exp_sum)
    {
        $fix_arr = LevelFix::get_all();
        $fix_every = LevelFix::get_every();
        if ($exp_sum >= $fix_arr[100]) {
            return [100, 0, 0];
        }
        
        $level = 1;
        $exp = $next_exp = 0;
        for ($i = 100; $i > 0; $i--) {
            if ($exp_sum >= $fix_arr[$i-1] && $exp_sum < $fix_arr[$i] ) {
                $level = $i-1;
                break;
            }
        }
        if ($level < 100) {
            $exp = $exp_sum - $fix_arr[$level];
            $next_exp = $fix_every[$level+1] ;
        }
        return [$level, $exp, $next_exp];
    }
    
    /**
     * 返回每个类型的汉字描述
     * @param unknown $typint
     * @return string
     */
    public function get_type_name($typint)
    {
        $arr = [
            self::LEVEL_LOGIN => '每日登录',
            self::LEVEL_COMPLETE_UPLOAD_PIC => '上传头像',
            self::LEVEL_COMPLETE_ATTESTAION => '完成直播认证',
            self::LEVEL_COMPLETE_CHANG_USERINFO => '资料完善',
            self::LEVEL_PUSH => '发起直播',
            self::LEVEL_RECORD => '发布短视频',
            self::LEVEL_COMMENTS => '评论',
            self::LEVEL_SHARE => '分享',
            self::LEVEL_SHARE_OTHER_USER => '他人分享',
            self::LEVEL_INVITATION_REGISTER => '邀请注册',
            self::LEVEL_ACTIVITY_LIKE => '活动点赞',
            self::LEVEL_COMPLETE_TASK => '完成任务',
            self::LEVEL_LIKE => '被关注',
            self::LEVEL_SHOW_LIVE_COURSE => '点播课程',
            self::LEVEL_SHOP => '商城购买',
            self::LEVEL_DASHANG => '打赏主播',
        ];
        return $arr[$typint];
    }
    
    /**
     * 返回每个类型的 增加的经验值。
     * @param unknown $typint
     */
    public function get_exp_config($typint)
    {
        $arr = [
            self::LEVEL_LOGIN => 10, //登录
            self::LEVEL_COMPLETE_UPLOAD_PIC => 5, //头像上传
            self::LEVEL_COMPLETE_ATTESTAION => 30, //直播认证
            self::LEVEL_COMPLETE_CHANG_USERINFO => 30, //资料完善
            self::LEVEL_PUSH => 5, //直播发起
            self::LEVEL_RECORD => 3,//发布短视频
            self::LEVEL_COMMENTS => 1, //发布文字评论
            self::LEVEL_SHARE => 5,    //共享
            self::LEVEL_SHARE_OTHER_USER => 1, //他人共享
            self::LEVEL_INVITATION_REGISTER => 5, //邀请注册
            self::LEVEL_ACTIVITY_LIKE => 2,       //活动点赞
            self::LEVEL_COMPLETE_TASK => 2,       //完成任务
            self::LEVEL_LIKE => 2,                //被关注
            self::LEVEL_SHOW_LIVE_COURSE => 15,   //点播课程
            self::LEVEL_SHOP => 5,                // 商城购买
            self::LEVEL_DASHANG => 0,                // 打赏主播
        ];
        return $arr[$typint];
    }
    
    

}