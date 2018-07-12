<?php
namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\EmojiData;
use BBExtend\user\Comment;

class Robot
{
    
    /**
     * 机器人点赞
     * 
     *  随机挑选100个类型为10,11的用户（批量导入的用户）作为机器人
     * 随机挑选100个视频（概率 1周内 30%   一月内50%  一个月前20%）
     * 遍历视频，对每个视频随机挑选10个机器人插入浏览记录（观看时间60s范围内随机）
     * 同时在选中的机器人中随机挑选1~3个人 插入点赞记录
     * 预留添加评论接口（概率5%~10%）
     * （上述记录最好有个标识，区别正常日志数据）
     * 
     * @return number[]|number[]
     */
    public function index($record_count=1, $people_count=1)
    {
        $db = Sys::get_container_db_eloquent();
        $dbzend = Sys::get_container_dbreadonly();
        
        $redis = Sys::getredis11();
        $key = "index:recommend:list";
        
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
        if ($rand < 30) {
            $time = time() - 7*24 * 3600;
        }
        if ($rand < 80) {
            $time = time() - 30*24 * 3600;
        }else {
            $time = time() - 3 * 30*24 * 3600;
        }
        
        $sql="
          select * from bb_record 
where is_remove=0
and audit=1
and time > {$time}
and type !=3
order by rand() 
limit {$record_count}
";
        if ($rand < 30) {
            
            $records  = $redis->get($key);
            $records = unserialize($records);
            shuffle($records);
            $record_arr=[];
            foreach ( range(1, $record_count)  as $v) {
                $record_arr[]= array_pop( $records );
            }
        }
        else {
            $record_arr = $dbzend->fetchAll($sql);
        }
        
        $count  = count($record_arr);
        echo "record_count:{$count}\n";
        
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
                $look_random = mt_rand(3,10);
                $db::table('bb_record')->where('id', $record['id'] )->update([
                   'look' => $db::raw( 'look + '.$look_random ),     
                ]);
                
                $record_model = new \BBExtend\model\Record();
                $record_model->add_views(intval( $record['id'] )); 
                
                echo "read: uid:{$uid}, record_id:{$record['id']} \n";
                $rand = mt_rand(1,100); 
                if ($rand < 30) {
                    // 赞之前检查 这个人有没有赞过这个视频。
                    $has_zan = $db::table('bb_record_like')->where('uid', $uid)
                        ->where( 'room_id', $record['room_id']  )->count() ;
                    if ( !$has_zan ) {
                        $Data = array();
                        $Data['uid'] = $uid;
                        $Data['room_id'] = $record['room_id'];
                        $Data['time'] = time();
                        $Data['count'] = 1;
                        $Data['is_robot'] = 1;
                        $db::table('bb_record_like')->insert($Data);
                        
                        $db::table('bb_record')->where('id', $record['id'] )->update([
                                'like' => $db::raw( '`like` + 1' ),
                        ]);
//                         $db::table('bb_alitemp')->insert([
//                                 'create_time' => date("Y-m-d H:i:s"),
//                                 'url' => "zan: uid:{$uid}, record_id:{$record['id']} ",
//                                 'test1' => 444,
//                         ]);
                        // 被点赞成就
                        $ach = new \BBExtend\user\achievement\Zhubo($record['uid']);
                        $ach->update(1);
                        
                    }
                 //   echo "zan: uid:{$uid}, record_id:{$record['id']} \n";
                }
                
                // 注意，这里重新定义随机数，这样就分散了，评论的视频不一定点赞。
                $rand = mt_rand(1,100);
                if ($rand <= 3) {
                    // 机器人评论。/
                    $this->comment(intval($uid),  $record['id'] );
                    
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
        $db = Sys::get_container_db_eloquent();
        $content = $this->get_comment_content();
        
        
        $Data = array();
        $Data['content'] = $content;
        $Data['activity_id'] = $record_id;
        $Data['time'] = time();
        $Data['uid'] = $uid;
        $Data['reply_count'] = 0;
        $Data['audit'] = 1;
        $Data['is_remove'] = 0;
        $Data['score'] = 0;
        $Data['is_robot'] = 1;
        
        
        //self::add_user_exp($uid,LEVEL_COMMENTS);
     //   Exp::getinstance($uid)->set_typeint(Exp::LEVEL_COMMENTS)->add_exp();
        // xieye 201708 成就系统
        //         $ach = new \BBExtend\user\achievement\Pinglun($uid);
        //         $ach->update(1);
        $id= $db::table('bb_record_comments')->insertGetId($Data);
        return $id;
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
