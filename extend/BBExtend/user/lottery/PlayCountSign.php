<?php
namespace BBExtend\user\lottery;

/**
 * 判断是否能抽转盘的类
 * 以及对抽奖次数做处理。
 * 
 * PlayCountSign 判断签到转盘抽奖的次数，以及修改次数
 * PlaySign：         抽奖类，调用此类对象，进行签到抽奖
 * StandardSign：返回最近7日签到的状况，连续7日表示可能抽奖。
 * 
 * 谢烨
 */

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\fix\TableType;

class PlayCountSign
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
    public $datestr;// 类似20170801
    
    private $count_key;
    
    
    /**
     * lt_type:  1波币，2表情包, 3谢谢参与，4再来一次，5实物奖品
     * @param number $uid
     */
    public function  __construct($uid=0) 
    {
        $uid = intval($uid);
        $this->redis = Sys::getredis11();
        $this->uid = $uid;
        $datestr = $this->datestr = date("Ymd");
        $this->count_key = "count_sign_key:{$uid}:{$datestr}";
       
    }
    
    /**
     * 创建缓存，要点是要考虑各种情况！
     * 今日未签到，7天的条件。
     * 今天是否抽奖
     * 今天是否抽奖为再来一次。
     * 
     */
    private function create_count_key()
    {
        $uid = $this->uid;
        $result = $this->redis->get($this->count_key);
        if ($result===false) {
        
        
            // 如果没有就创建。有效期一个月。
            $standard = new StandardSign($this->uid);
            $can_lottery = $standard->can_lottery();
            $count=0;
            if ($can_lottery) {// 可能为1，包括未抽奖或全是再来一次。
                // 
                $db = Sys::get_container_db_eloquent();
                $sql="select * from lt_draw_log where uid=? and datestr=? and type=?";
                $result = DbSelect::fetchAll($db, $sql,[ $uid, date("Ymd"), 
                    TableType::lt_roulette__type_qiandao  ]);
                if (!$result) {
                    $count=1;
                }else {
                    $count =1;
                    foreach ($result as $v) {
                        if ($v['lt_type'] != TableType::lt_roulette__lt_type_zailai ){
                            $count=0;
                        }
                    }
                }
            }
            // 设置缓存
            $this->redis->setEx($this->count_key, 14*24*3600, $count);
        }
    }
    
    /**
     * 
     * 被签到时调用，清除缓存。重要。
     */
    public function clean_count()
    {
        $this->redis->delete( $this->count_key );
    }
    
    
    /**
     * 获得本类的对象
     *
     * @param unknown $uid
     */
    public static function getinstance($uid)
    {
        return new self($uid);
    }
    
    /**
     * 得到使用次数，公用
     */
    public function get_valid_count()
    {
        if (in_array( $this->uid, [ 12700,3025547,10914 ,10003] )){
            return 100;
        }
        
        $this->create_count_key();
        return $this->redis->get($this->count_key);
    }
    
    /**
     * 减去次数，公用
     */
    public function sub_valid_count()
    {
        $this->create_count_key();
        return $this->redis->decr($this->count_key);
    }
   
    /**
     * +次数，公用
     */
    public function add_valid_count()
    {
        $this->create_count_key();
        return $this->redis->incr($this->count_key);
    }

}