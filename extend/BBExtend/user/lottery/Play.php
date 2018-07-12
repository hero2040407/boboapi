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

use BBExtend\message\Message;

use BBExtend\user\lottery\Task;
use BBExtend\Currency;
class Play
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
    public $datestr;// 类似20170801
    private $user_obj;
    
    private $valid_key;
    
    private $lt_type; // 类型
    private $bonus_id; //奖品id
    
    
//     public $message;
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
        $this->valid_key = "valid_key:{$uid}:{$datestr}";
       // 创建任务
        $db = Sys::get_container_db();
        $sql = "select uid from bb_users where uid={$uid}";
        $row = $db->fetchOne($sql);
        if (!$row) {
            die("user error");
        }
        $task_arr = $this->get_task();
        if (!$task_arr) {
            $this->create_task();
            $this->set_valid_count(1000);//每天免费送1次
        }
//        $this->check_task(); // 名堂多。
       
        
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
     * 得到免费使用次数
     */
    public function get_valid_count()
    {
        return $this->redis->get($this->valid_key);
    }
    
    /**
     * 根据数据库查出所有的当前任务的完成情况
     */
    public function get_task()
    {
        $uid = $this->uid;
        $datestr = $this->datestr;
        $db = Sys::get_container_db();
        $sql ="select * from lt_user_task where uid={$uid} and datestr='{$datestr}'";
        $task_arr = $db->fetchAll($sql);
        return $task_arr;
    }
    
    
    
    /**
     * 使用一次幸运转盘，抽奖
     * @return number[]|string[]
     */
    public function start()
    {
        $user = \app\user\model\UserModel::getinstance($this->uid);
        $gold = $user->get_gold();
        $valid_count = $this->get_valid_count();
        if ($valid_count<=0 && $gold <=0) {
            return ["code"=>0,'message'=> '次数不足' ];
            
        }
        if ($valid_count>0) {
            $this->sub_valid_count(); //扣减免费次数
        }else { //或者扣钱
            Currency::add_currency($this->uid,CURRENCY_GOLD,-1,'幸运大转盘');
        }
        $lt_id = $this->get_lottery_point(); // 这步就是抽奖
        $this->complete_task($lt_id);
        //return $lt_id;
        return ["code"=>1, "data" => $lt_id ];
    }
    
    /**
     * 
     * 完成善后工作
     * lt_type: 1波币，2表情包, 3谢谢参与，4再来一次，5实物奖品
     * bonus_id: 实物奖品。
     * 
     * @param unknown $lt_id
     */
    private function complete_task($lt_id)
    {
        $db = Sys::get_container_db();
        $sql="select * from lt_roulette where id = ".intval($lt_id);
        $row = $db->fetchRow($sql);
        //先日志
        $db->insert("lt_draw_log", [
            'uid'          => $this->uid,
            'lt_type'      => $row['lt_type'],
            'bonus_id'     =>$row['bonus_id'],
            'create_time'  =>time(),
            'datestr'      => date("Ymd"),
            'has_exchange' => 0,
            'bonus_name'   => $row['title'],
        ]);
        
        
        switch ($row['lt_type']) {
            case 3:   //谢谢参与
                break;
            case 4:  // 送一次机会
                $this->get_bonus_again();
                break;
            case 1:  //钱
                $this->get_bonus_money($row['bonus_id']);
                break;
            case 2:  //表情包
                $this->get_bonus_expression_package($row['bonus_id']);
                break;
                
            case 5: //这里都是实物兑换券
                $this->get_bonus_goods($row['bonus_id']);
                break;
                
        }
    }
    
    
    /**
     * 得到实物兑换券
     * @param unknown $package_id
     */
    private function get_bonus_goods($goods_id){
        //先往购买表加一条记录。
        $db = Sys::get_container_db();
        $table= 'lt_user_owner';
        $db->insert($table, [
            'uid' => $this->uid,
            'lt_type' => 5,
            'bonus_id' => $goods_id,
            'is_use' =>0,
            'create_time' =>time(),
        ]);
        $uid = $this->uid;
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_user_pic_no_http();
         
        $sql="select title from bb_shop_goods where id ={$goods_id}";
        $shop_title =$db->fetchOne($sql); 
        
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content('恭喜你在幸运大转盘中获得'.$shop_title.'兑换券一张!'))
            ->set_type(170)
            ->set_uid($uid)
            ->send();
    }
    
    /**
     * 得到表情包
     * @param unknown $package_id
     */
    private function get_bonus_expression_package($package_id){
        //先往购买表加一条记录。
        $db = Sys::get_container_db();
        $table= 'bb_expression_buy';
        $db->insert($table, [
            'time' => time(),
            'uid' => $this->uid,
            'package_id' => $package_id,
        ]);
        $uid = $this->uid;
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_user_pic_no_http();
        
        $sql="select title from bb_expression_package where id=".intval($package_id);
        $ptitle=$db->fetchOne($sql);
        
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content('恭喜你在幸运大转盘中获得'))
            ->add_content(Message::simple()->content('"'. $ptitle .'"')
                    ->url(json_encode(['type'=>7,  ]  )))
            ->add_content(Message::simple()->content('奖励！'))
            ->set_type(170)
            ->set_uid($uid)
            ->send();
        
    }
    
    /**
     * 抽到波币奖励。
     */
    private function get_bonus_money ($money)
    {
        Currency::add_currency($this->uid,CURRENCY_GOLD,$money,'幸运大转盘');
    
        $uid = $this->uid;
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_user_pic_no_http();
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content('恭喜你在幸运大转盘中获得'))
            ->add_content(Message::simple()->content('"'.$money .'BO币"')
                    ->url(json_encode(['type'=>6,  ]  )))
            ->add_content(Message::simple()->content('奖励！'))
            ->set_uid($uid)
            ->send();
    }
    
    /**
     * 抽到再来一次
     */
    private function get_bonus_again ()
    {
        $this->add_valid_count();
        
        $uid = $this->uid;
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_user_pic_no_http();
        Message::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(Message::simple()->content('恭喜你在'))
            ->add_content(Message::simple()->content('幸运大转盘')
                    ->url(json_encode(['type'=>8,  ]  )))
            ->add_content(Message::simple()->content('中获得"再来一次"奖励!'))
            ->set_type(170)
            ->set_uid($uid)
            ->send();
    }
    
    
    /**
     * 获取转盘奖励，
     */
    private function get_lottery_point()
    {
        $db = Sys::get_container_db();
        $sql="select * from lt_roulette";
        $result = $db->fetchAll($sql);
        
        $sql = "select package_id from bb_expression_buy where uid=".$this->uid;
        $package_arr = $db->fetchCol($sql);
        $package_arr=(array)$package_arr;
        
        $arr = [];
        foreach ($result as $v) { 
            //这个判断排除了中奖为已得到表情包的潜在bug
            if ($v['lt_type'] ==2 && in_array($v['bonus_id'], $package_arr) ) { 
                
            }else {
              $arr[$v['id']] = $v['rate'];
            }
        }
        return  $this->getrand($arr);
    }
    
    /**
     * 辅助方法，获取随机值
     * @param unknown $arr
     * @return unknown
     */
    private function getrand($arr)
    {
        $db = Sys::get_container_db();
        
        $new=[];
        foreach ($arr as $k=> $v) {
            $temp=0;
            foreach ($arr as $k2=> $v2) {
                $temp+= $v2;
                if ($k2 == $k) {
                    break;
                }
            }
            $new[$k] = $temp;
        }
         
        $rand = mt_rand(1,array_sum($arr));
        foreach ($new as $k=>$v) {
            if ($rand <= $v ) {
                return $k;
            }
        }
    }
   
   
    /**
     * 创建任务，随机挑3个。
     * 
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
    private function create_task()
    {
        $redis = $this->redis;
        $datestr=date("Ymd");
        $key = "lt:public_task:{$datestr}";
        $result = $redis->get($key);
        
     //   $result = "9,10,11";
        
        if (!$result) {
            $arr = range(2,11);// 2到11，是任务id
            shuffle($arr);
            $result_arr=[];
             
            foreach (range(1,3) as $v  ) {
                $result_arr[]= array_pop($arr);
            }
            $result = implode(',', $result_arr);
            $redis->set($key, $result);
            $redis->setTimeout($key, 3*24*3600);
        }
        $result = explode(',', $result);
     
       $db = Sys::get_container_db();
       $time=time();
       foreach ($result as $type) {
           $db->insert('lt_user_task', [
               'uid'=>$this->uid,
               'type'=>$type,
               'create_time'=>$time,
               'datestr'=>$this->datestr,
               'game_count'=>0,
               'has_complete'=>0,
           ]);
       }
        
    }
    
    /**
     * 刷新任务完成情况
     * 如果有完成，加免费使用次数，
     */
    private function check_task()
    {
        $db = Sys::get_container_db();
        $task_arr = $this->get_task();
        $uid = $this->uid;
        
        $user = Task::getInstance($uid);
        
        foreach ($task_arr as $row) {
            $type = $row['type'];
            $has_complete = $row['has_complete'];
            if (!$has_complete) {//如果表中的状态是未完成，才需
                $result = $user->check($type);
                if ($result  ){ //如果当前的状态是已完成，才需修改数据库
                    $db->update("lt_user_task", ['has_complete'=> $result],"id=".$row['id']);
                    $this->add_valid_count();
                }
                
            }
        }
    }
    
    /**
     * 设置免费使用次数
     * @param number $value
     */
    private function set_valid_count($value=0)
    {
        $redis = $this->redis;
        $redis->set($this->valid_key,$value);
        $redis->setTimeout($this->valid_key,  2* 24 * 3600);
    }
    
    /**
     * 增加免费使用次数
     */
    private function add_valid_count()
    {
        $redis = $this->redis;
        $redis->incr($this->valid_key);
        $redis->setTimeout($this->valid_key,  2* 24 * 3600);
    }
    
    /**
     * 减少免费使用次数
     */
    private function sub_valid_count()
    {
        $redis = $this->redis;
        $redis->decr($this->valid_key);
        $redis->setTimeout($this->valid_key,  2* 24 * 3600);
    }
    
   
   

   

}