<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\CommentPublicLog;


class Comment {
    /**
     * 这是一个定时任务，每小时执行一次。
     * 然后，会给bb_comment_public_log 随机评论日志表添加数据，但内容字段为空，表示先占位。
     */
   public function index()
   {
       $hour = date("H");
       $hour = intval($hour);
       $db = Sys::get_container_db_eloquent();
       $from = 9;
       $to = 18;
       $comment_count=5;
       $sql="select * from bb_config_str where type=3";
       $result = DbSelect::fetchAll($db, $sql);
       foreach ($result as $v) {
           if ($v['config'] == 'comment_from_hour' ) {
               $from = $v['val'];
           }
           if ($v['config'] == 'comment_to_hour' ) {
               $to = $v['val'];
           }
           if ($v['config'] == 'comment_count_per_hour' ) {
               $comment_count = $v['val'];
           }
       }
       // 先判断 当前小时，是否在定义的时段内。
       if ($hour < $from || $hour > $to ) {
           return;
       }
       // 插入$comment_count条数据。
       for($i =0 ; $i < $comment_count; $i++) {
           $obj = new CommentPublicLog();
           $obj->create_time =time() + mt_rand(1,3600) ;// 当前一小时内的随机值。
           $obj->save();
       }
       
   }
       
   /**
    * 每分钟，定时 任务开启。
    * 随机发表评论。
    */
   public function add_comment()
   {
       Sys::display_all_error();
       $db = Sys::get_container_db_eloquent();
       $time = time();
       $arr = CommentPublicLog::where('create_time','>=' ,$time)->where( "create_time",'<' ,$time +60  
               )->where('has_comment',0)->get();
       // 如果没有每小时的预设值，就不做。
       if (!$arr ) {
           return;
       }
               
       // 给哪些短视频加评论
       $id_range=100;
       $sql="select val from bb_config_str where type=3 and config='comment_record_id_range'";
       $result = DbSelect::fetchOne($db, $sql);
       if ($result) {
           $id_range = intval( $result);
       }
       //define('id_range', $id_range);  
       // 寻找前100条，随机抽取一条
       $sql ="
           select id
             from bb_record
            where bb_record.audit=1
              and bb_record.is_remove=0
             order by id desc
              limit ". $id_range ."
           ";// 寻找前100条，随机抽取一条
       $ids = DbSelect::fetchCol($db, $sql);
       // 寻找500个机器人
       $sql ="
               select uid from bb_users where login_type = 
               ". \BBExtend\fix\TableType::bb_users__login_type_jiqiren ."
                 limit 500      
                       ";
       $uids = DbSelect::fetchCol($db, $sql);
       // 寻找一条评论
       $sql ="SELECT *   
FROM bb_comment_public AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM bb_comment_public)) AS id) AS t2   
WHERE t1.id >= t2.id   
ORDER BY t1.id ASC LIMIT 1";
       $comment = DbSelect::fetchRow($db, $sql);
       
       
       if ((!$ids) || (!$uids) || (!$comment) ) {
           return;
       }
       $content = $comment['content'];
       foreach ($arr as $item) {
           $selected_id = $ids[ array_rand($ids) ];
           $selected_id_jiqiren = $uids[ array_rand( $uids ) ];
           // 构造请求
           $data =[
               'uid' => $selected_id_jiqiren,
               'id'  => $selected_id,
               'content' =>$content,
           ];
           $url =  "http://bobo.yimwing.com/record/comments/comments";
           $response = \Requests::post( $url ,[ ], $data);// 发送请求。
           
            // 日志记录
           $item->uid = $selected_id_jiqiren;
           $item->record_id = $selected_id;
           $item->has_comment=1;
           $item->content = $content;
           $item->save();
       }
       
           
       
       echo "ok";
       
   }
   
   
    
}

