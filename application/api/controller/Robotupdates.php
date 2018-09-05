<?php
namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\EmojiData;
use BBExtend\user\Comment;


class Robotupdates
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
    public function index($record_count=1 )
    {
        Sys::debugxieye("日志：增加动态点击");
         
        $people_count=1;
        
        $db = Sys::get_container_db_eloquent();
        $dbzend = Sys::get_container_dbreadonly();
     //   Sys::debugxieye("record_count:{$record_count},people_count:{$people_count}");
        $redis = Sys::getredis11();
        $key = "index:recommend:list:news";
        
        $people_count = intval( $people_count );
        $record_count = intval( $record_count );

        if ($people_count <1) {
            $people_count=1;
        }
        
        if ($record_count <1) {
            $record_count =1;
        }
        
        $people_arr=[];
        for ($i=0;$i<$people_count*2;$i++ ) {
           $sql="
select uid 
from bb_users 
where permissions=99
ORDER BY rand() LIMIT 1
";
             $uid = $dbzend->fetchOne($sql);
             if ($uid) 
                 $people_arr[]= $uid;
        }
        
        $rand = mt_rand(1,100);
        if ($rand < 70) {
            $time = time() - 7*24 * 3600;
        }
        if ($rand < 90) {
            $time = time() - 30*24 * 3600;
        }else {
            $time = time() - 3 * 30*24 * 3600;
        }
        $temp_click_count = 400000;
        $record_count=300;
        
        $sql="
          select * from bb_users_updates 
where is_remove=0
and status=1
and click_count < {$temp_click_count}
and create_time > {$time}

limit {$record_count}
";
//         else {
            $record_arr = $dbzend->fetchAll($sql);
//         }
        
        
        
        foreach ($record_arr as $record ) {
           // shuffle($people_arr );
            
            $i=0;
            
            $type='updates';
            if ($record['agent_uid']) {
                $type='star_updates';
            }
            
            
           // foreach ($people_arr as $uid) {
            $current_click_count = $record['click_count'];
            $max_click_count = \BBExtend\user\MaxCount::get_max($type ,  $record['id']);
            if ($current_click_count > $max_click_count) {
                continue;
            }
                
            
                $look_random = mt_rand(10,60);
                $db::table('bb_users_updates')->where('id', $record['id'] )->update([
                   'click_count' => $db::raw( 'click_count + '.$look_random ),     
                ]);
           //     echo "uid:{$uid} view news id:{$record['id']},count:{$look_random}\n";
                
                // 点赞。
                $rand = mt_rand(1,100);
                if ($rand < 30) {
                    $db::table('bb_users_updates')->where('id', $record['id'] )->update([
                            'like_count' => $db::raw( 'like_count + 1' ),
                    ]);
                    
                }
                
                
                // 注意，这里重新定义随机数，这样就分散了，评论的视频不一定点赞。
                $rand = mt_rand(1,100);
                if ($rand <= 3) {
                    // 机器人评论。/
                    $this->comment(intval($uid),  $record['id'] );
                 //   echo "uid:{$uid} commented news id:{$record['id']}\n";
                    
                }
           // }
            
        }
        return ['code'=>1,'data' =>['list' => $record_arr ] ];
        
    }
    
    
    
    
    /**
     * 每10个点赞里有大概0.5~1.5个评论，

     */
    private function comment($uid, $record_id)
    {
        $content = $this->get_comment_content();
        
        $comment = new \BBExtend\model\UserUpdatesComment();
        $comment->create_time = time();
        $comment->uid = $uid;
        $comment->status=1; // 设置为已审核
        $comment->content = $content;
        $comment->updates_id = $record_id;
        $comment->is_reply = 0;
        $comment->save();
        
        $db = Sys::get_container_db_eloquent();
        
        $db::table('bb_users_updates')->where("id",'=', $record_id )->increment('comment_count');
        
        return $comment->id;
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
