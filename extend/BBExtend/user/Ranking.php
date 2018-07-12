<?php
namespace BBExtend\user;

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
use BBExtend\Level;
use BBExtend\message\Message;
use BBExtend\BBUser;

class Ranking 
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
    public $exists=0;
    
    // 这4个是redis的键。
    public $key_caifu; //
    public $key_dengji;
    public $key_guaishou;
    public $key_fensi;
    public $key_dashang;
    
    
    /**
     * 
     * @param number $uid
     */
    public function  __construct($uid=0) {
        $uid = intval($uid);
        $this->redis = Sys::getredis_paihangbang();
        $this->uid = $uid;
        $db = Sys::get_container_db();
        $sql="select uid from bb_users where uid = {$uid}";
        $this->exists = $db->fetchOne($sql);
        //1财富，2粉丝，3，等级经验，4，怪兽数量
        $this->key_caifu = "rank:1";
        $this->key_fensi = "rank:2";
        $this->key_dengji = "rank:3";
        $this->key_guaishou = "rank:4";
        $this->key_dashang = "rank:5";
        
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
    
    /**
     * 切记：redis函数中，取得范围第2个参数不是长度，是id，所以
     * 需要把传来的长度减1！！
     * 
     * 
     * 
     * @param unknown $type
     * @param unknown $startid
     * @param unknown $length
     */
    public function get_list($type,$startid=0, $length=10)
    {
        if (!in_array($type, [1,2,3,4,5])) {
            return ['code'=>0, 'message'=>'类型错误'];
        }
        $startid=intval($startid);
        $length = intval($length);
        if (!$length) {
            $length=10;
        }
//         100 就是错误，1
//         a+b-1 >= 100 就是错误
        
        
        $redis = $this->redis;
       // $count = 
        $key = "rank:{$type}";
        $all_count = $redis->zCard($key);
        
        if ($startid) {
            if ($startid  > $all_count){
                return ['code'=>0, 'message'=> '长度错误'];
            }
        }
        
        if ($startid + $length-1 >=100) {
            return ['code'=>0, 'message'=> '超过100'];
        }
        
        
        $is_bottom=0;
        if ( $length-1+ $startid>=99 ){
            $is_bottom=1;
        }
        
        // 现在可以返回了。
        $arr = $redis->zReverseRange($key, $startid, $length-1+ $startid );
        $arr2=[];
        
        $db = Sys::get_container_db();
        foreach ($arr as $uid) {
            $UserDB = \BBExtend\BBUser::get_user($uid);
            
            $DB = array();
            $DB['uid'] = (int)$UserDB['uid'];
            
            //谢烨20160922，加vip返回字段
            $DB['vip'] = \BBExtend\common\User::is_vip($DB['uid']) ;
            
            $DB['nickname'] = $UserDB['nickname'];
            $DB['sex'] = (int)$UserDB['sex'];
            $DB['is_focus'] = \BBExtend\Focus::get_focus_state($this->uid,$DB['uid']);
            $pic = $UserDB['pic'];
            //如果没有http://
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            $DB['pic'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                    $pic, $ServerURL.'/public/toppic/topdefault.png' );
           
            
            $sql = "select count(*) from bb_monster_data where uid=  {$uid}";
            $count11 = $db->fetchOne($sql);
            //$count = intval($count);
            
            $DB['monster_count'] =  intval($count11);
            
            // 谢烨，加几样。
            $temp1 = \BBExtend\Currency::get_currency($uid);
            $DB['gold'] = intval($temp1['gold']);
            //经验等级
            
            $sql = "select level,exp from bb_users_exp where uid=  {$uid}";
            $row = $db->fetchRow($sql);
            if ($row) {
                $DB['level'] = $row['level'];
                $DB['exp']=$row['exp'];
            }else {
               $DB['level'] = 1;
               $DB['exp']=0;
            }
            //粉丝数。
            $DB['fensi_count'] = \BBExtend\user\Focus::getinstance($uid)->get_fensi_count();
            $DB['fensi_count'] =intval($DB['fensi_count']);
            $arr2[]= $DB;
        }
        
        
        return ['code'=>1, 'data'=>$arr2, 'is_bottom'=>$is_bottom];
    }
    
   
    //根据查询分值，再加上id的因素，得到最后的分值，越大排名越靠前！
    public function jisuan($fen, $uid)
    {
        return $fen * 20 * 10000 - $uid;
    }
    
    
    // xieye，打赏
    public function set_dashang_ranking()
    {
        if (!$this->exists ) {
            return $this;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $db = Sys::get_container_db();
        $sql = "select sum(gold) from bb_dashang_log where uid= {$uid}";
        $count = $db->fetchOne($sql);
        $count = intval($count);
        $redis->zAdd($this->key_dashang ,  $count ,$uid );
        return $this;
    }
    
    // xieye，打赏
    public function add_dashang_ranking($v=0)
    {
        if (!$this->exists ) {
            return $this;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        // 先得到
        $score = $this->get_dashang_value();
        $score = intval($score) + intval($v) ;
        
        $redis->zAdd($this->key_dashang ,  $score ,$uid );
        return $score;
    }
    
    // //1财富，2粉丝，3，等级经验
    public function send_message($type, $mingci)
    {
        switch ($type) {
            case 1:
                $col = "caifu_ranking";
                $display ="财富";
                break;
            case 2:
                $col = "fensi_ranking";
                $display ="粉丝";
                break;
            case 3:
                $col = "lv_ranking";
                $display ="等级";
                break;
        }
        if ($mingci > 90) {
            return;
        }
        
        $db = Sys::get_container_db();
        $sql ="select {$col} from bb_currency where 
                uid = {$this->uid}";
        $has_ranking = $db->fetchOne($sql);
        $has_ranking = intval($has_ranking);
        if ($has_ranking) {
            return;
        }
        // 终于需要发送消息了。
        Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content("恭喜你升至"))
            ->add_content(Message::simple()->content("{$display}榜{$mingci}名")->color(0xf4a560)  )
            ->add_content(Message::simple()->content('，请进入'))
            ->add_content(Message::simple()->content('个人中心')->color(0x32c9c9)
                    ->url(json_encode(['type'=>1,  ]) )  )
            ->add_content(Message::simple()->content('查看。'))
            ->set_type(113)
            ->set_uid($this->uid)
            ->send();
       $sql ="update bb_currency set {$col}=1 where uid = {$this->uid}";
       $db->query($sql);
    }
//     caifu_ranking
//     lv_ranking
//     fensi_ranking
    
    //设置一个财富值
    // xieye，这里全部都是倒排。
    public function set_caifu_ranking()
    {
        if (!$this->exists ) {
            return $this;
        }
        $uid = $this->uid;
//         $key = $this->get_key_caifu();
        $redis = $this->redis;
        $db = Sys::get_container_db();
        $sql = "select gold from bb_currency where uid= {$uid}";
        $count = $db->fetchOne($sql);
        $count = intval($count);
        $redis->zAdd($this->key_caifu,  $this->jisuan($count, $uid) ,$uid );
        $this->send_message(1, $this->get_caifu_ranking() );
        return $this;
    }
    
    //设置一个怪兽蛋值。
    public function set_guaishou_ranking()
    {
        if (!$this->exists ) {
            return $this;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $db = Sys::get_container_db();
        $sql = "select count(*) from bb_monster_data where uid=  {$uid}";
        $count = $db->fetchOne($sql);
        $count = intval($count);
        $redis->zAdd($this->key_guaishou,  $this->jisuan($count, $uid) ,$uid );
        return $this;
    }
    
    
    //设置一个等级经验
    public function set_dengji_ranking()
    {
        if (!$this->exists ) {
            return $this;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $db = Sys::get_container_db();
        $sql = "select level,exp from bb_users_exp where uid=  {$uid}";
        $row = $db->fetchRow($sql);
        if (!$row) {
            $count =0;
        } else {
            $count = 100 * 10000 * $row['level'] + $row['exp'];
        }
        $redis->zAdd($this->key_dengji,  $count ,$uid );
        $this->send_message(3, $this->get_dengji_ranking() );
        return $this;
    }
    
    //粉丝
    public function set_fensi_ranking()
    {
        if (!$this->exists ) {
            return $this;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $fensi_count = \BBExtend\user\Focus::getinstance($uid)->get_fensi_count();
        $redis->zAdd($this->key_fensi,  $this->jisuan($fensi_count, $uid) ,$uid );
        $this->send_message(2, $this->get_fensi_ranking() );
        return $this;
    }
    
    // 返回
    public function get_caifu_ranking()
    {
        if (!$this->exists ) {
            return 10000;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $ranking = $redis->zRevRank($this->key_caifu, $uid);
        if ($ranking === false) {
            $this->set_caifu_ranking();
            $ranking = $redis->zRevRank($this->key_caifu, $uid);
        }
        return $ranking+1; //因为redis排序从0开始
    }
    
    public function get_guaishou_ranking()
    {
        if (!$this->exists ) {
            return 10000;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $ranking = $redis->zRevRank($this->key_guaishou, $uid);
        if ($ranking === false) {
            $this->set_guaishou_ranking();
            $ranking = $redis->zRevRank($this->key_guaishou, $uid);
        }
        return $ranking+1; //因为redis排序从0开始
    }
    
    public function get_dengji_ranking()
    {
        if (!$this->exists ) {
            return 10000;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $ranking = $redis->zRevRank($this->key_dengji, $uid);
        if ($ranking === false) {
            $this->set_dengji_ranking();
            $ranking = $redis->zRevRank($this->key_dengji, $uid);
        }
        return $ranking+1; //因为redis排序从0开始
    }
    
    public function get_fensi_ranking()
    {
        if (!$this->exists ) {
            return 10000;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $ranking = $redis->zRevRank($this->key_fensi, $uid);
        if ($ranking === false) {
            $this->set_fensi_ranking();
            $ranking = $redis->zRevRank($this->key_fensi, $uid);
        }
        return $ranking+1; //因为redis排序从0开始
    }
    
    public function get_dashang_ranking()
    {
        if (!$this->exists ) {
            return 10000;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $ranking = $redis->zRevRank($this->key_dashang , $uid);
        if ($ranking === false) {
            $this->set_dashang_ranking();
            $ranking = $redis->zRevRank($this->key_dashang, $uid);
        }
        return $ranking+1; //因为redis排序从0开始
    }
    
    /**
     * 获取打赏总额，某个人的。
     */
    public function get_dashang_value()
    {
        if (!$this->exists ) {
            return 10000;
        }
        $uid = $this->uid;
        $redis = $this->redis;
        $dashang_value = $redis->zScore($this->key_dashang , $uid);
        if ($dashang_value === false) {
            $this->set_dashang_ranking();
            $dashang_value = $redis->zScore($this->key_dashang, $uid);
        }
        return $dashang_value; //因为redis排序从0开始
    }
    
    public function remove()
    {
        // 从4个key当中，删除指定的值。
        $redis = $this->redis;
        $uid = $this->uid;
        $redis->zRem($this->key_caifu, $uid);
        $redis->zRem($this->key_dengji , $uid);
        $redis->zRem($this->key_fensi , $uid);
        $redis->zRem($this->key_guaishou , $uid);
        $redis->zRem($this->key_dashang , $uid);
        
    }
    

}