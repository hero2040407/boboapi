<?php
namespace app\systemmanage\controller;
/**
 * 定时统计，每天晚上自动执行一次，
 * 统计昨日的数据。
 * 
 * @author 谢烨
 */


use BBExtend\Sys;
use BBExtend\common\Date;
use BBExtend\common\Numeric;
use app\systemmanage\model\Tongji201704;
use app\systemmanage\model\TongjiUser;
use BBExtend\DbSelect;

class Userchange 
{ 
    
    /**
     * 谢烨，这是每日定时任务。
     * 表bb_tongji_huizong_register，先删后加。
     * 上表内容，包括统计每天第一次打开时，和从哪个渠道下载的数量
     * 
     * 表bb_tongji_huizong
     * 最重要的汇总表。
     * 
     * 表bb_tongji_user_huizong
     * 这是单个用户的记录。会有所有有效用户的记录。
     * 
     */
    public function start($uid,$touid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select count(*) from bb_users where uid=?";
        $count1  = DbSelect::fetchOne($db, $sql,[ $touid ]);
        $sql="select count(*) from bb_users_platform where uid=?";
        $count2  = DbSelect::fetchOne($db, $sql,[ $touid ]);
        
        if ($count1 || $count2 ) {
            echo "已有bobo号 {$touid}";
            exit;
        }
        
        $arr=[
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity_comments','column'=> 'uid' ],
                ['table'=> 'bb_activity_comments_like','column'=> 'uid' ],
                ['table'=> 'bb_activity_comments_reply','column'=> 'uid' ],
                ['table'=> 'bb_address','column'=> 'uid' ],
                ['table'=> 'bb_aliyun_kill_log','column'=> 'uid' ],
                ['table'=> 'bb_baoming','column'=> 'uid' ],
                ['table'=> 'bb_baoming_order','column'=> 'uid' ],
                ['table'=> 'bb_baoming_order_prepare','column'=> 'uid' ],
                ['table'=> 'bb_brandshop','column'=> 'uid' ],
                ['table'=> 'bb_brandshop_application','column'=> 'uid' ],
                ['table'=> 'bb_buy','column'=> 'uid' ],
                ['table'=> 'bb_buy_video','column'=> 'uid' ],
                ['table'=> 'bb_chat_log','column'=> 'uid' ],
                ['table'=> 'bb_comment_public_log','column'=> 'uid' ],
                ['table'=> 'bb_comment_public_log','column'=> 'target_uid' ],
                ['table'=> 'bb_currency','column'=> 'uid' ],
                ['table'=> 'bb_currency_log','column'=> 'uid' ],
                ['table'=> 'bb_dashang_day_ranking','column'=> 'uid' ],
                ['table'=> 'bb_dashang_day_ranking','column'=> 'target_uid' ],
                ['table'=> 'bb_dashang_log','column'=> 'uid' ],
                ['table'=> 'bb_dashang_log','column'=> 'target_uid' ],
                ['table'=> 'bb_dashang_prepare','column'=> 'target_uid' ],
                ['table'=> 'bb_dashang_ranking','column'=> 'uid' ],
                ['table'=> 'bb_dashang_ranking','column'=> 'target_uid' ],
                ['table'=> 'bb_expression_buy','column'=> 'uid' ],
                ['table'=> 'bb_feedback','column'=> 'uid' ],
                ['table'=> 'bb_focus','column'=> 'uid' ],
                ['table'=> 'bb_focus','column'=> 'target_uid' ],
                ['table'=> 'bb_jubao_log','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                ['table'=> 'bb_activity','column'=> 'uid' ],
                
        ];
        
    }
    
}