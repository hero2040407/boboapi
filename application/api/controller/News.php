<?php

namespace app\api\controller;

use think\Config;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 234
 * @author xieye
 *
 */
class News 
{
    
    public function indexall($uid=10000, $startid=0, $length=10)
    {
        $uid = intval($uid);
        $startid = intval($startid);
        $length = intval($length);
        
//         $db = Sys::get_container_db_eloquent();
//         $result = $db::table('web_article')
//         ->where('is_remove',0)
        
//         ->orderBy( 'sort','desc' )
//         ->orderBy( 'create_time','desc' )
//         ->offset($startid)->limit($length)->get();
        $db = Sys::get_container_db();
        $select = $db->select()->from('web_article')
        ->where("is_remove=0")
        ->order("sort desc")
        ->order("create_time desc")
        ->limit($length, $startid);
        
        $result = $db->fetchAll($select ); 
        
        
        
        $new=[];
        foreach ($result as $v) {
            $obj = \BBExtend\model\WebArticle::find( $v['id'] );
            $new[]= $obj->info();
        }
        $sql="
                  select bb_record.*,bb_subject_movie.subject_id from bb_record
                    left join bb_subject_movie
                    on bb_subject_movie.room_id = bb_record.room_id
                    where bb_subject_movie.id >0
                     and  bb_record.audit=1
                     and bb_record.is_remove=0
                     and bb_subject_movie.is_recommend = 1
                     and exists(
                       select 1 from bb_subject
                         where bb_subject.is_show=1
                           and bb_subject.id = bb_subject_movie.subject_id
                       )
                   order by bb_record.time desc
                   
                    limit {$startid}, 2
                    ";
        $videos = DbSelect::fetchAll($db, $sql);
        $new2=[];
        foreach ($videos as $video) {
            //  Sys::debugxieye($videos);
            
            $new2[]= $this->get_video($video, $uid)  ;
        }
        return [
                'code'=>1,
                'data'=>[
                        'is_bottom' =>( count($new )==$length )? 0:1,
                        'news_list' => $new,
                        'video_list' =>$new2,
                ]
        ];
    }
    
    
    
    /**
     * 返回新闻分页
     * 
     * 谢烨，现在找到大赛首页大赛列表。
     * 
     * 
     * 201807 
     */
    public function index_include_race($uid=10000, $startid=0, $length=10)
    {
        $uid = intval($uid);
        $startid = intval($startid);
        $length = intval($length);
        
        if ($length==0) {
            return ['code'=>0];
        }
        
        $page = $startid / $length ;
        $page = intval($page);
        
        $db = Sys::get_container_dbreadonly();
        $select = $db->select()->from('web_article')
          ->where("is_remove=0")
          ->where("status=1")
          ->order("sort desc")
          ->order("create_time desc")
          ->limit($length, $startid);
        
        $result = $db->fetchAll($select );
        
        $new=[];
        foreach ($result as $v) {
            $obj = \BBExtend\model\WebArticle::find( $v['id'] );
            $temp = $obj->info();
            $temp['bigtype'] ='news';
            $new[]= $temp;
        }
        $is_bottom = ( count($new )==$length )? 0:1;
        $sql ="
                select * from ds_race
                where is_active=1 and parent=0
and id not between 198 and 203
                order by has_end asc, sort desc , start_time desc
                limit {$page},1
                ";
        $race = $db->fetchRow($sql);
        if ($race) {
            $id = $race['id'];
            $result = \BBExtend\model\Race::find( $id );
            $race_row = $result->info();
            $race_row['bigtype']='race';
            array_splice($new, mt_rand(1, count($new)-1 ),0, [$race_row] );
        }
        
        return [
                'code'=>1,
                'data'=>[
                        'is_bottom' =>$is_bottom,
                        'list' => $new,
                    //    'video_list' =>$new2,
                    //    'can_play' => $can_play,
                ]
        ];
    }
    
    
    
    /**
     * 返回新闻分页
     */
    public function index($uid, $startid=0, $length=10,$random=0)
    {
        $uid = intval($uid);
        $startid = intval($startid);
        $length = intval($length);
        
//         $db = Sys::get_container_db_eloquent();
//         $result = $db::table('web_article')
//              ->where('is_remove',0)
//              ->where('status',1)
//              ->orderBy( 'sort','desc' )
//              ->orderBy( 'create_time','desc' )
//              ->offset($startid)->limit($length)->get();
        
         $db = Sys::get_container_dbreadonly();
         $select = $db->select()->from('web_article')
             ->where("is_remove=0")
             ->where("status=1")
             ->order("sort desc")
             ->order("create_time desc")
             ->limit($length, $startid);
             
        $result = $db->fetchAll($select ); 
             
        $new=[];
        foreach ($result as $v) {
            $obj = \BBExtend\model\WebArticle::find( $v['id'] );
            
            $new[]= $obj->info();
        }
//         $sql="
//                   select bb_record.*,bb_subject_movie.subject_id from bb_record
//                     left join bb_subject_movie
//                     on bb_subject_movie.room_id = bb_record.room_id
//                     where bb_subject_movie.id >0
//                      and  bb_record.audit=1
//                      and bb_record.is_remove=0
//                      and bb_subject_movie.is_recommend = 1
//                      and exists(
//                        select 1 from bb_subject
//                          where bb_subject.is_show=1
//                            and bb_subject.id = bb_subject_movie.subject_id
//                        )
//                    order by bb_record.time desc

//                     limit {$startid}, 2
//                     ";
      //  $videos = DbSelect::fetchAll($db, $sql);
        
        $redis = Sys::getredis11();
        $key = "index:recommend:list";
        $records  = $redis->get($key);
        $records = unserialize($records);
        $vol1=[];
        foreach ($records as $key => $row) {
            $vol1[$key]  = abs( crc32( $row['id'] + $random));
        }
        
        // 将数据根据 volume 降序排列，根据 edition 升序排列
        // 把 $data 作为最后一个参数，以通用键排序
        array_multisort($vol1, SORT_DESC,  $records);
        $videos = array_slice( $records,$startid,10 ); 
        
        
        $new2=[];
        foreach ($videos as $video) {
          //  Sys::debugxieye($videos);
            
            $new2[]= $this->get_video($video, $uid)  ;
        }
        
        $can_play= \BBExtend\user\MoneyRain::is_valid_time();
        return [
                'code'=>1,
                'data'=>[
                        'is_bottom' =>( count($new )==$length )? 0:1,
                        'news_list' => $new,
                        'video_list' =>$new2,
                        'can_play' => $can_play,
                ]
        ];
    }
    
    
    /**
     * 根据短视频表行，给出新闻需要的字段。
     * 
     * @param unknown $record
     * @param unknown $uid
     * @return string[]|unknown[]|number[]|boolean[]|NULL[]|mixed[]|unknown[][]|string[][]
     */
    private function get_video($record, $uid)
    {
        $temp= \BBExtend\BBRecord::get_subject_detail_by_row($record,$uid);
//         Sys::debugxieye('改造后');
//         Sys::debugxieye($temp);
        
        $new =[];
        $new['id'] = $temp['id'];
        $new['title'] = $temp['title'];
        $new['type']  = 6;
        $new['source'] = $temp['nickname'];
        
        $new['header_pic'] = $temp['pic'];
        $new['view_count'] = $temp['people'];
        
        
        
        $new['comments'] = isset( $temp['comment_count'])? $temp['comment_count']:0 ;
        $new['time'] = intval( $temp['publish_time'] );
        
        $new['video']=[
                'url' => $temp['pull_url'],
        ] ;
        $new['pic'] =[[
            'url' => $temp['bigpic'],      
        ]
        ];
        return $new;
        
    }
    

    /**
     * 新闻详情
     * 
     * @param unknown $id
     * @return number[]|string[]|number[]|NULL[][][]
     */
    public function info($id,$uid=0)
    {
        $db = Sys::get_container_db_eloquent();
        //try{
        $result = $db::table('web_article')->where('id', $id)->first();
//         }catch ( \Exception $e ) {
//             return ['code'=>0, 'result' => 'id err' ];
//         }
        
       // $count = $db::table('web_article_comment')->where('article_id', $id)->count();
        
        $new=[];
        $new['id']            = $result->id;
        $new['title']         = $result->title;
        $new['content']       = $result->content;
        $new['create_time']   = $result->create_time;
        $new['style']         = $result->style;
        $new['comment_count'] = $result->comment_count;
        $new['source']        = $result->source;
        
        if ($result->click_count< 100) {
       //     $result->click_count = mt_rand(100,1000);
        //    $result->save();
        }
        
        
        $sql="select * from web_article_media
               where article_id = ?
                 and media_type=2
  ";
        $row = DbSelect::fetchRow($db, $sql,[ $id ]);
        if ($row) {
            $video = $row['url'];
            $sql="select url from web_article_media
               where article_id = ?
                 and media_type=1
                 and sort>0
                 order by sort
                 limit 1
  ";
            $video_pic = DbSelect::fetchOne($db, $sql,[ $id ]);
            $video_pic =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $video_pic  );
            
        }else {
            $video ='';
            $video_pic='';
        }
        $new['video']        = $video;
        $new['video_pic']        = $video_pic;
        
        
        $db::table('web_article_click')->insert([
          'uid'         => $uid,
          'news_id'     => $id,
          'create_time' => time(),
                
                ]);
        $redis = Sys::get_container_redis();
        $key = "news:click:{$id}";
        $new['click_count']  = $redis->incr($key) + $result->click_count ;
        
        return [
                'code'=>1,
                'data'=>[
                        'info' =>$new,
                ]
        ];
        
    }
    
    /**
     * 对新闻评论，对评论回复
     * 
     * @param unknown $id
     * @param unknown $uid
     * @param unknown $content
     * @return number[]|string[]|number[]|NULL[][][]
     */
    public function comment( $id,$uid, $content='content',$is_reply=0,$token='')
    {
        $db = Sys::get_container_db_eloquent();
        $content = strip_tags($content);
         
        $user = \BBExtend\model\User::find($uid );
        if (!$user) {
            return ['code'=>0, 'result' => 'uid err' ];
        }
        
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        
        if (!$is_reply) {
        
            $news = $db::table('web_article')->where('id', $id)->first();
            if (!$news) {
                return ['code'=>0, 'result' => 'id err' ];
            }
            
            $comment = new \BBExtend\model\WebArticleComment();
            $comment->create_time = time();
            $comment->uid = $uid;
            $comment->status=0;
            $comment->content = $content;
            $comment->article_id = $id;
            $comment->is_reply = 0;
            $comment->save();
            
            $comment_count = $db::table('web_article_comment')->where('article_id', $id)->count();
            $article = $comment->article;
            $article->comment_count = $comment_count;
           // $article->save();
        }else {
            
            $news = $db::table('web_article_comment')->where('id', $id)
              ->where('is_reply',0)  
              ->first();
            if (!$news) {
                return ['code'=>0, 'result' => 'id err' ];
            }
            
            $comment = new \BBExtend\model\WebArticleComment();
            $comment->create_time = time();
            $comment->uid = $uid;
            $comment->status=0;
            $comment->content = $content;
            $comment->article_id = $id;
            $comment->is_reply = 1;
            $comment->save();
            
            $comment_count = $db::table('web_article_comment')->where('article_id', $id)
              ->where('is_reply',1)
              ->count();
            $article = $comment->comment;
          //  $article->reply_count = $comment_count;
            $article->reply_time = time(); // 对于回复时，给评论加上最新回复时间。
            $article->save();
            
        }
        return [
                'code'=>1,
        ];
    }
    
    /**
     * 点赞，和取消点赞
     * 
     * @param unknown $uid
     * @param unknown $id
     * @param number $like
     * @return number[]|string[]|number[]
     */
    public function like($uid, $id,$like=1)
    {
        $db = Sys::get_container_db_eloquent();
        
        $comment = \BBExtend\model\WebArticleComment::find($id );
        if (!$comment) {
            return ['code'=>0,'message'=>'id err'];
        }
        
        $count = $db::table('web_article_like')->where('comment_id', $id)->where('uid',$uid)->count();
        if ($like) {
            // 这是要点赞
            if ($count) {
                return ['code'=>0,'message'=>'您已经点过赞'];
                
            }else {
                $db::table('web_article_like')->insert([
                        'bigtype'=>1,
                        'create_time'=>time(),
                        'uid' => $uid,
                        'comment_id'=> $id,
                ]);
                $db::table('web_article_comment')->where('id',$id)->increment('like_count');
                return ['code'=>1,];
            }
            
        }else {
            // 这是要取消点赞
            if (!$count) {
                return ['code'=>0,'message'=>'没有点赞，无需取消'];
            }else {
                $db::table('web_article_like')->where('comment_id', $id)->where('uid',$uid)->delete();
                $db::table('web_article_comment')->where('id',$id)->decrement('like_count');
                return ['code'=>1,];
            }
        }
    }
    
    /**
     * 是否取消
     * 
     * @param unknown $uid
     * @param unknown $id
     */
    private function is_like($uid,$id)
    {
        $db = Sys::get_container_db_eloquent();
        $count = $db::table('web_article_like')->where('comment_id', $id)->where('uid',$uid)->count();
        
        return (bool)$count;
    }
    
    
    
    private function get_parent($id,$uid=0)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="
         select * from web_article_comment
          where id=?
";
        $v = DbSelect::fetchRow($db, $sql,[$id]);
        $temp=[];
        
        $comment_user = \BBExtend\model\User::find($v['uid']);
        
        $temp['id'] = $v['id'];
        $temp['uid'] = $v['uid'];
        $temp['vip'] = 0;
        $temp['nickname'] = $comment_user->get_nickname();
        $temp['pic'] = $comment_user->get_userpic();
        $temp['like_count'] = $v['like_count'];
        $temp['is_like'] = $this->is_like($uid, $temp['id']);
        $temp['reply_count'] = $v['reply_count'];
        $temp['reply_time'] = $v['reply_time'] ? strval( $v['reply_time'] ) : null  ;
        
        $temp['permissions'] = $comment_user->permissions;
        
        $temp['time'] = strval( $v['create_time'] );
        $temp['content'] = $v['content'];
        $temp['score'] = 0;
        $temp['age'] = $comment_user->get_userage();
        $temp['sex'] = $comment_user->get_usersex();
        return $temp;
    }
    
    /**
     * 得到评论列表
     * 
     * @param unknown $id
     * @param number $startid
     * @param number $length
     * @return number[]|string[]|number[]|number[][]|array[][]
     */
    public function comment_list( $id,$startid=0,$length=10,$uid=0,$is_reply=0 )
    {
        $db = Sys::get_container_db_eloquent();
        $parent=null;
        // 查评论
        if (!$is_reply) {
        
            $news = $db::table('web_article')->where('id', $id)->first();
            if (!$news) {
                return ['code'=>0, 'result' => 'id err' ];
            }
    
            $sql="
         select * from web_article_comment 
          where article_id=?
            and is_reply=0
            and status=1
           order by id desc
           limit ?,?

";
        } else {
            $parent = $this->get_parent($id,$uid);
            // 查回复
            $news = $db::table('web_article_comment')->where('id', $id)
              ->where('is_reply',0)
              ->first();
            if (!$news) {
                return ['code'=>0, 'result' => 'id err' ];
            }
            
            $sql="
         select * from web_article_comment
          where article_id=?
            and is_reply=1
            and status=1
           order by id desc
           limit ?,?
                    
";
        }
        $result = DbSelect::fetchAll($db, $sql,[ $id, $startid, $length ]);
        $new=[];
        foreach ($result as $k=> $v) {
            $temp=[];
            
            $comment_user = \BBExtend\model\User::find($v['uid']);
            
            $temp['id'] = $v['id'];
            $temp['uid'] = $v['uid'];
            $temp['vip'] = 0;
            $temp['nickname'] = $comment_user->get_nickname();
            $temp['pic'] = $comment_user->get_userpic();
            $temp['like_count'] = $v['like_count'];
            $temp['is_like'] = $this->is_like($uid, $temp['id']);
            $temp['reply_count'] = $v['reply_count'];
            $temp['reply_time'] = $v['reply_time'] ? strval( $v['reply_time'] ) : null  ;
            
            $temp['permissions'] = $comment_user->permissions;
            
            $temp['time'] = strval( $v['create_time'] );
            $temp['content'] = $v['content'];
            $temp['score'] = 0;
            $temp['age'] = $comment_user->get_userage();
            $temp['sex'] = $comment_user->get_usersex();
            
            $new[]= $temp;
        }
        return [
                'code'=>1,
                'data'=>[
                        'is_bottom' => ( count( $new ) == $length  )?0:1,
                        'list' => $new,
                        'parent' =>$parent,
                ]
        ];
        
    }
    
   
    
    
}





