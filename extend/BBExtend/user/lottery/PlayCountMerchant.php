<?php
namespace BBExtend\user\lottery;

/**
 * 商户抽奖次数类。
 * 
 * 谢烨
 */

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\fix\TableType;

class PlayCountMerchant
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
        $this->count_key = "count_shanghu_key:{$uid}";
       
    }
    
//     /**
//      * 创建缓存，要点是要考虑各种情况！
//      * 今日未签到，7天的条件。
//      * 今天是否抽奖
//      * 今天是否抽奖为再来一次。
//      * 
//      */
//     private function create_count_key()
//     {
//         $uid = $this->uid;
//         $result = $this->redis->get($this->count_key);
//         if ($result===false) {
        
        
//                 $db = Sys::get_container_db_eloquent();
//                 $sql="select * from lt_draw_log where uid=? and datestr=? and type=?";
//                 $result = DbSelect::fetchAll($db, $sql,[ $uid, date("Ymd"), 
//                     TableType::lt_roulette__type_qiandao  ]);
//                 if (!$result) {
//                     $count=1;
//                 }else {
//                     $count =1;
//                     foreach ($result as $v) {
//                         if ($v['lt_type'] != TableType::lt_roulette__lt_type_zailai ){
//                             $count=0;
//                         }
//                     }
//                 }
            
//             // 设置缓存
//             $this->redis->setEx($this->count_key, 30*24*3600, $count);
//         }
//     }
    
    
    
    
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
     * 
     * 返回结果大于0，表示可以抽奖
     */
    public function get_valid_count()
    {
        if (in_array( $this->uid, [ 12700,3025547,10914,10010 ] )){
            return 100;
        }
       
        $db = Sys::get_container_db_eloquent();
        
        $sql = "select count(*) from bb_users_shanghu_invite_register
                where is_complete=1 and target_uid = ?
                ";
        $has_shanghu_invite = DbSelect::fetchOne($db, $sql,[ $this->uid ]);
        // 如果不是被商户邀请成功，首先排除。
        if (!$has_shanghu_invite) {
            return 0;
        }
        
        
        $sql="select shanghu_lottery_count from bb_currency where uid=".
                $this->uid ;
        $result  = DbSelect::fetchOne($db, $sql);
        if ($result > 0 ) {
            return 0;
        }
        return 1;
    }
    
    /**
     * 抽到再来一次。
     */
    public function add_valid_count()
    {
        $db = Sys::get_container_db_eloquent();
        $sql="update bb_currency set shanghu_lottery_count=shanghu_lottery_count-1 where uid=".
                $this->uid ;
                $db::update($sql  );
    
                // $this->create_count_key();
                //return $this->redis->incr($this->count_key);
    }
   
    /**
     * 每次玩一次，就执行此方法
     */
    public function sub_valid_count()
    {
        $db = Sys::get_container_db_eloquent();
        $sql="update bb_currency set shanghu_lottery_count=shanghu_lottery_count+1 where uid=".
           $this->uid ;
        $db::update($sql  );
        
       // $this->create_count_key();
        //return $this->redis->incr($this->count_key);
    }

}