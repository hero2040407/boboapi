<?php
namespace BBExtend\user;

/**
 * 删除类
 * 
 * 数量用最简单的set单变量！
 * 关注人列表用集合
 * 
 * 谢烨
 */

use BBExtend\Sys;
///use think\Db;


class Remove 
{
    /**
     * redis
     * @var \Redis
     */
    public $redis;
    
    public $uid;
  
    public $result = true;
    
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
    
    /**
     * 删除用户
     */
    public function del()
    {
        // 首先鉴定是否可以删除
        $uid = $this->uid;
        // 12264 被去除。
        if (in_array($uid, [11106, 12127, 11957,
            12119, 12264, 12159,
            12184, 11069, 12174,
            
        ])) {
            exit("测试人员的正式帐号是需要一直保留的。");
        }
        
        $db = Sys::get_container_db();
        $sql ="select nickname from bb_users where uid={$uid}";
        $nickname = $db->fetchOne($sql);
        if ($nickname && $nickname=='x测试帐号2'){
            
        }else {
            exit("非法操作");
        }
        // 先删除redis数据
        \BBExtend\user\Ranking::getinstance($this->uid)->remove();
        \BBExtend\user\Focus::getinstance($uid)->remove();
        
        \BBExtend\user\Relation::getinstance($uid)->remove();
        
        \BBExtend\BBRedis::getInstance('user')->Del($uid);
        
        //特别处理，发奖排名。
        $this->remove_act_paiming();
        //删除所有表
        $this->del_database();
    }
    
    private function remove_act_paiming()
    {
        $uid = $this->uid;
        $db = Sys::get_container_db();
        $sql ="select distinct activity_id from bb_user_activity_reward where has_reward=1
                and uid = {$uid}
                ";
        $col = $db->fetchCol($sql);
        $col = array_unique($col);
        foreach ( $col as $v ) {
            $sql ="select paiming from bb_user_activity_reward 
                    where activity_id ={$v}
                      and uid = {$uid}
                    ";
            $paiming = $db->fetchOne($sql);
            if ($paiming) {
                $sql ="update bb_user_activity_reward set paiming=paiming-1 where 
                        activity_id = {$v}
                       and paiming > {$paiming}
                        ";
                $db->query($sql);
            }
        }
    }
    
    private function del_database()
    {
        $db = Sys::get_container_db();
        $id = $uid = $this->uid;
        
        // 谢烨 20171020，最重要的一句话，最妙之处就是保证了新的uid不会重复使用老的！
        $sql="delete from bb_user_suiji where id=?";
        $db->query($sql,$id);
        
        
//         $sql="delete from bb_activity_comments where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_activity_comments_like where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_activity_comments_reply where uid=?";
//         $db->query($sql,$id);
        $sql="delete from bb_address where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_buy where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_baoming where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_baoming_order where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_baoming_order_prepare where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_comment_public_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_dashang_day_ranking where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_dashang_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_dashang_ranking where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_lahei where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_live_device where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_moive_view_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_moive_view_stats where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_moive_view_unique_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_msg where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_msg_answer where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_msg_cache where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_msg_push_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_msg_user_config where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_otherpush where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_ranking where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_record_invite_starmaker where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_system_task where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_task_user where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_tixian_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_tixian_apply where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_umeng_push_msg where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_user_hobby where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_user_weixin_id where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_invite_register where target_uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_register_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_signin_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_dangan where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_money_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_money_prepare where uid=?";
        $db->query($sql,$id);
        $sql="delete from lt_user_task where uid=?";
        $db->query($sql,$id);
        $sql="delete from lt_user_owner where uid=?";
        $db->query($sql,$id);
        $sql="delete from lt_exchange_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from lt_draw_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_sponsor where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_show_video where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_register_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_record where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_money_prepare where uid=?";
        $db->query($sql,$id);
                
        
        $sql="delete from bb_buy_video where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_currency where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_currency_log where uid=?";
        $db->query($sql,$id);
//         $sql="delete from bb_dashang_log where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_dashang_log where target_uid=?";
//         $db->query($sql,$id);
        $sql="delete from bb_expression_buy where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_feedback where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_focus where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_focus where focus_uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_jubao_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_lahei where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_lahei where target_uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_live_device where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_monster_data where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_msg where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_push where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_push_like where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_ranking where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_record where uid=?";
        $db->query($sql,$id);
//         $sql="delete from bb_record_comments where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_record_comments_like where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_record_like where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_rewind where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_rewind_comments where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_rewind_comments_like where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_rewind_like where uid=?";
//         $db->query($sql,$id);
        $sql="delete from bb_shop_comments where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_shop_logistics_trace where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_shop_order where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_shop_order_prepare where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_shop_users where uid=?";
        $db->query($sql,$id);
//         $sql="delete from bb_task_comments where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_task_comments_like where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_task_comments_reply where uid=?";
//         $db->query($sql,$id);
        $sql="delete from bb_task_user where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_umeng_push_msg where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_user_activity where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_exp where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_exp_log where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_platform where uid=?";
        $db->query($sql,$id);
        
        $sql="delete from bb_ranking where uid=?";
        $db->query($sql,$id);
//         $sql="delete from bb_tongji_log where uid=?";
//         $db->query($sql,$id);
//         $sql="delete from bb_record_comments_reply where uid=?";
//         $db->query($sql,$id);
        $sql="delete from bb_user_activity_reward where uid=?";
        $db->query($sql,$id);
        
        $sql="delete from bb_dashang_ranking where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_dashang_ranking where target_uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_register_log where uid=?";
        $db->query($sql,$id);
        
        $sql="delete from ds_race where uid=?";
        $db->query($sql,$id);
        $sql="delete from ds_record where uid=?";
        $db->query($sql,$id);
        
        //成就删除
        $sql="delete from bb_users_achievement where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_achievement_summary where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_achievement_msg where uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_achievement_bonus where uid=?";
        $db->query($sql,$id);
        // 注册邀请。
        $sql="delete from bb_users_invite_register where target_uid=?";
        $db->query($sql,$id);
        $sql="delete from bb_users_shanghu_invite_register where target_uid=?";
        $db->query($sql,$id);
        
        
        
        
        
        
    }
    
    private function sub_sql($table,$column='uid')
    {
        $db = Sys::get_container_db();
        $sql = "select count(*) from {$table} where ". 
                  " not exists(select 1 from bb_users ".
                "where bb_users.uid = {$table}.{$column} ) ";
        $count = $db->fetchOne($sql);
        $i=0;
        $username = get_cfg_var('guaishou.username');
        
        if ($count) {
            $i++;
            echo "{$table}.{$column} has " . $count . "<br>\n";
            $sql ="
                  delete from   {$table} where ". 
                  " not exists(select 1 from bb_users ".
                "where bb_users.uid = {$table}.{$column} ) ";
            echo "{$sql}<br>\n<br>\n";
            if ($username=='245') {
                $db->query($sql);
            }
            
            
            $sql ="
            select *  from   {$table} where ".
            " not exists(select 1 from bb_users ".
            "where bb_users.uid = {$table}.{$column} ) ";
            $result = $db->fetchAll($sql);
            dump($result);
         //   $db->query($sql);
            $this->result=false;
        }
        if (!$i) {
           // echo " all clear";
        }
    }
    
    public function query_bad_database()
    {
       
//         $this->sub_sql("bb_activity_comments");
//         $this->sub_sql("bb_activity_comments_like");
//         $this->sub_sql("bb_activity_comments_reply");
        $this->sub_sql("bb_address");
        $this->sub_sql("bb_buy");
        $this->sub_sql("bb_buy_video");
        $this->sub_sql("bb_currency");
        $this->sub_sql("bb_currency_log");
//         $this->sub_sql("bb_dashang_log");
//         $this->sub_sql("bb_dashang_log",'target_uid');
        $this->sub_sql("bb_expression_buy");
        $this->sub_sql("bb_feedback");
        $this->sub_sql("bb_focus");
        $this->sub_sql("bb_focus","focus_uid");
        $this->sub_sql("bb_jubao_log");
        $this->sub_sql("bb_lahei");
        $this->sub_sql("bb_lahei","target_uid");
        $this->sub_sql("bb_live_device");
        $this->sub_sql("bb_monster_data");
        $this->sub_sql("bb_msg");
        $this->sub_sql("bb_push");
        $this->sub_sql("bb_push_like");
        $this->sub_sql("bb_ranking");
        $this->sub_sql("bb_record");
//         $this->sub_sql("bb_record_comments");
//         $this->sub_sql("bb_record_comments_like");
//         $this->sub_sql("bb_record_like");
        $this->sub_sql("bb_rewind");
//         $this->sub_sql("bb_rewind_comments");
//         $this->sub_sql("bb_rewind_comments_like");
//         $this->sub_sql("bb_rewind_like");
        $this->sub_sql("bb_shop_comments");
        $this->sub_sql("bb_shop_logistics_trace");
        $this->sub_sql("bb_shop_order");
        $this->sub_sql("bb_shop_order_prepare");
        $this->sub_sql("bb_shop_users");
//         $this->sub_sql("bb_task_comments");
//         $this->sub_sql("bb_task_comments_like");
//         $this->sub_sql("bb_task_comments_reply");
        $this->sub_sql("bb_task_user");
        $this->sub_sql("bb_umeng_push_msg");
        $this->sub_sql("bb_users_exp");
        $this->sub_sql("bb_users_exp_log");
        $this->sub_sql("bb_users_platform");
        
    //    $this->sub_sql("bb_tongji_log");
        $this->sub_sql("bb_user_activity");
   //     $this->sub_sql("bb_record_comments_reply");
        $this->sub_sql("bb_user_activity_reward");
        $this->sub_sql("bb_users_register_log");
        if ($this->result) {
            echo "all ok";
        }
    }
    
  
}