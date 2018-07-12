<?php
namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\EmojiData;
use BBExtend\user\Comment;

class Robotnews
{
    
    /**
     * 机器人点赞
     * 
     *  随机挑选$people_count个类型为10,11的用户（批量导入的用户）作为机器人
     * 随机挑选$record_count个新闻（概率 1周内 50%   一月内40%  一个月前10%）
     * 对于每条新闻，每人插入浏览记录（概率100%），每个人的浏览次数（1 -100随机）
     * 添加评论（概率3%）
     * 
     * 
     * @return number[]|number[]
     */
    public function index($record_count=1, $people_count=1)
    {
        $db = Sys::get_container_db_eloquent();
        $dbzend = Sys::get_container_dbreadonly();
        Sys::debugxieye("record_count:{$record_count},people_count:{$people_count}");
        $redis = Sys::getredis11();
        $key = "index:recommend:list:news";
        
//         $sql="update bb_alitemp set test1=test1+1 where id=1";
//         $db::update($sql);
//         $db::update($sql);
        $people_count = intval( $people_count );
        $record_count = intval( $record_count );
//         if ($people_count >10) {
//             $people_count=10;
//         }
        if ($people_count <1) {
            $people_count=1;
        }
        
//         if ($record_count > 5) {
//             $record_count =5;
//         }
        if ($record_count <1) {
            $record_count =1;
        }
        
        $people_arr=[];
        for ($i=0;$i<$people_count*2;$i++ ) {
           $sql="
SELECT t1.uid
FROM bb_users AS t1 inner JOIN 
(SELECT ROUND(RAND() * 5000000+ 3000000) AS uid) AS t2
WHERE t1.uid >= t2.uid
and t1.permissions=10
ORDER BY t1.uid LIMIT 1;
";
             $uid = $dbzend->fetchOne($sql);
             if ($uid) 
                 $people_arr[]= $uid;
        }
        
        $rand = mt_rand(1,100);
        if ($rand < 80) {
            $time = time() - 7*24 * 3600;
        }
        if ($rand < 90) {
            $time = time() - 30*24 * 3600;
        }else {
            $time = time() - 3 * 30*24 * 3600;
        }
        $temp_click_count = mt_rand(20000,32000);
        $sql="
          select * from web_article 
where is_remove=0
and status=1
and click_count < {$temp_click_count}
and create_time > {$time}
order by rand() 
limit {$record_count}
";
//         if ($rand < 30) {
            
//             $records  = $redis->get($key);
//             $records = unserialize($records);
//             shuffle($records);
//             $record_arr=[];
//             foreach ( range(1, $record_count)  as $v) {
//                 $record_arr[]= array_pop( $records );
//             }
//         }
//         else {
            $record_arr = $dbzend->fetchAll($sql);
//         }
        
        
        
        foreach ($record_arr as $record ) {
            shuffle($people_arr );
            
            $i=0;
            foreach ($people_arr as $uid) {
                $i++;
                if ($i> $people_count) {
                    break;
                }
                
//                 $db::table('bb_moive_view_log')->insert( [
//                         'uid' => intval($uid),
//                         'target_uid' => $record['uid'],
                        
//                         'movie_id'   => $record['id'],
//                         'create_time' => time(),
//                         'is_robot' =>1,
//                 ]);
                $look_random = mt_rand(10,100);
                $db::table('web_article')->where('id', $record['id'] )->update([
                   'click_count' => $db::raw( 'click_count + '.$look_random ),     
                ]);
                echo "uid:{$uid} view news id:{$record['id']},count:{$look_random}\n";
//                 $record_model = new \BBExtend\model\Record();
//                 $record_model->add_views(intval( $record['id'] )); 
                
               // echo "read: uid:{$uid}, record_id:{$record['id']} \n";
              
                
                
                // 注意，这里重新定义随机数，这样就分散了，评论的视频不一定点赞。
                $rand = mt_rand(1,100);
                if ($rand <= 3) {
                    // 机器人评论。/
                    $this->comment(intval($uid),  $record['id'] );
                    echo "uid:{$uid} commented news id:{$record['id']}\n";
//                     $db::table('bb_alitemp')->insert([
//                             'create_time' => date("Y-m-d H:i:s"),
//                             'url' => "comment: uid:{$uid}, record_id:{$record['id']} ",
//                             'test1' => 445,
//                             ]);
                    
                }
            }
            
        }
        return ['code'=>1];
        
    }
    
    
    private function test(){
        $db = Sys::get_container_db_eloquent();
        $sql="select content from bb_record_comments 
order by id desc 
limit 10";
        $result = DbSelect::fetchCol($db, $sql);
        return $result;
    }
    
    /**
     * 每10个点赞里有大概0.5~1.5个评论，

     */
    private function comment($uid, $record_id)
    {
      //  $db = Sys::get_container_db_eloquent();
        $content = $this->get_comment_content();
        
        
        $comment = new \BBExtend\model\WebArticleComment();
        $comment->create_time = time();
        $comment->uid = $uid;
        $comment->status=1; // 设置为已审核
        $comment->content = $content;
        $comment->article_id = $record_id;
        $comment->is_reply = 0;
        $comment->save();
        
       // $article = \BBExtend\model\WebArticle::find( $record_id );
       // $article->incr(  );
        
        $db = Sys::get_container_db_eloquent();
        
        $db::table('web_article')->where("id",'=', $record_id )->increment('comment_count');
        
        return $$comment->id;
    }
    
    /**

内容概率（）

纯文字                 20%
文字+ 颜文字随机组合    30%
文字+ emoji表情组合     20%   （emoji 随机 1~5 重复）
纯颜文字        20%
纯emoji表情     10%   （随机1~5个重复）

     */
    private function get_comment_content()
    {
        $arr = range(1,5);
        $emoji_one = EmojiData::get_one_str();
        $emoji_str ='';
        $count = $arr[ array_rand( $arr ) ];
        
        for($i=0; $i< $count;$i++) {
            $emoji_str .= $emoji_one;
        }
        
        // 纯文字。
        $db = Sys::get_container_db_eloquent();
        $dbzend = Sys::get_container_dbreadonly();
        $sql="select content from  bb_comment_public order by rand() limit 1";
        //$content_str = DbSelect::fetchOne($db, $sql);
        $content_str = $dbzend->fetchOne($sql);
        
        
        $content_str = trim( $content_str );
        
        // 颜文字
        $yan_str = Comment::get_one();
//         $result='嗯嗯';
        $rand = mt_rand(1,100);
        if ($rand< 20) {
            $result = $content_str;
        }elseif ($rand < ( 20+ 30 ) ) {
            $result = $content_str . $yan_str;
        }elseif ($rand < ( 20+ 30 + 20 ) ) {
            $result = $content_str . $emoji_str;
        }elseif ($rand < ( 20+ 30 + 20 + 20 ) ) {
            $result = $yan_str;
        }else {
            $result = $emoji_str;
        }
        return $result;
        
    }
    
    
}
