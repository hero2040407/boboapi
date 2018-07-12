<?php
namespace BBExtend\user\lottery;

/**
 * PlayCountSign 判断签到转盘抽奖的次数，以及修改次数
 * PlaySign：         抽奖类，调用此类对象，进行签到抽奖
 * StandardSign：返回最近7日签到的状况，连续7日表示可能抽奖。
 * 
 * 谢烨
 */

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\message\Message;

use BBExtend\Currency;
use BBExtend\fix\TableType;


class PlaySign
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
    public $datestr;// 类似20170801
    
    private $play_count_obj;
    
    
    private $lt_type; // 类型
    private $bonus_id; //奖品id
    
    
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
       
       // 检查用户
        $db = Sys::get_container_db();
        $sql = "select uid from bb_users where uid={$uid}";
        $row = $db->fetchOne($sql);
        if (!$row) {
            die("user error");
        }
        // 设置次数帮助对象。
        $this->play_count_obj = new PlayCountSign($uid);
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
     * 使用一次幸运转盘，抽奖
     * @return number[]|string[]
     */
    public function start()
    {
        $valid_count = $this->play_count_obj->get_valid_count();
        
        if ($valid_count<=0 ) {
            return ["code"=>0,'message'=> '您的抽奖次数已经使用完了' ];
            
        }
        $this->play_count_obj->sub_valid_count();   //扣减次数
        
        $lt_id = $this->get_lottery_point(); // 这步就是抽奖
        $this->complete_lottery($lt_id);     // 记录日志
        return ["code"=>1, "data" => $lt_id ];
    }
    
    /**
     * 获取转盘奖励，
     */
    public function get_lottery_point()
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select * from lt_roulette where type=? and current_count> 0 ";
        $result = DbSelect::fetchAll($db, $sql,[ TableType::lt_roulette__type_qiandao ]);
    
        $sql = "select package_id from bb_expression_buy where uid=?";
        $package_arr = DbSelect::fetchCol($db, $sql,[$this->uid ]);
    
        $arr = [];
        foreach ($result as $v) {
            //这个判断排除了中奖为已得到表情包的潜在bug
            if ($v['lt_type'] ==TableType::lt_roulette__lt_type_biaoqingbao 
                    && in_array($v['bonus_id'], $package_arr) ) {
    
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
     * 
     * 完成善后工作
     * lt_type: 1波币，2表情包, 3谢谢参与，4再来一次，5实物奖品
     * bonus_id: 实物奖品。
     * 
     * @param unknown $lt_id
     */
    private function complete_lottery($lt_id)
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
            'type'         => TableType::lt_roulette__type_qiandao,
        ]);
        // 谢烨，新改动，必须把当前数量减1，重要啊！
        if (in_array( $row['lt_type'], [
            TableType::lt_roulette__lt_type_bobi,
            TableType::lt_roulette__lt_type_shiwu
        ] )){
            $sql = "update lt_roulette set current_count = current_count-1 where id = ".
                    intval($lt_id);
            $db->query($sql);
        }
        
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
    private function get_bonus_expression_package($package_id)
    {
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
            ->set_type(170)
            ->send();
    }
    
    /**
     * 抽到再来一次
     */
    private function get_bonus_again ()
    {
        $this->play_count_obj->add_valid_count();
        
        $uid = $this->uid;
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_user_pic_no_http();
//         Message::get_instance()
//             ->set_title('系统消息')
//             ->set_img($pic)
//             ->add_content(Message::simple()->content('恭喜你在'))
//             ->add_content(Message::simple()->content('幸运大转盘')
//                     ->url(json_encode(['type'=>8,  ]  )))
//             ->add_content(Message::simple()->content('中获得"再来一次"奖励!'))
//             ->set_type(170)
//             ->set_uid($uid)
//             ->send();
    }
    
    
   
  

   

}