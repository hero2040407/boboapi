<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\UserUpdates;
use BBExtend\model\UserUpdatesMedia;
use BBExtend\model\UserUpdatesComment;
use BBExtend\model\UserUpdatesLike;



/**
 * 评论。
 * 
 * @author xieye
 *
 */
class UpdatesComment
{
    /**
     * 对新闻评论，对评论回复
     * 
     * @param unknown $id
     * @param unknown $uid
     * @param unknown $content
     * @return number[]|string[]|number[]|NULL[][][]
     */
    public function add( $id,$uid, $content='content',$is_reply=0,$token='')
    {
        $db = Sys::get_container_db_eloquent();
        $content = strip_tags($content);
         
        $user = \BBExtend\model\User::find($uid );
        if (!$user) {
            return ['code'=>0, 'result' => 'uid err' ];
        }
        
//         if ( !$user->check_token($token ) ) {
//             return ['code'=>0,'message'=>'uid error'];
//         }
        
        if (!$is_reply) {
        
            $news = $db::table('bb_users_updates')->where('id', $id)->first();
            if (!$news) {
                return ['code'=>0, 'result' => 'id err' ];
            }
            
            $comment = new UserUpdatesComment();
            $comment->create_time = time();
            $comment->uid = $uid;
            $comment->status=0;
            $comment->content = $content;
            $comment->updates_id = $id;
            $comment->is_reply = 0;
            $comment->save();
            
            $comment_count = $db::table('bb_users_updates_comment')->where('updates_id', $id)->count();
            $db2 = Sys::get_container_db();
            $sql="update bb_users_updates set comment_count = {$comment_count} where id=?";
            $db2->query($sql, $id);
            
            //$article = 
           // $article->comment_count = $comment_count;
           // $article->save();
        }else {
            
            $news = $db::table('bb_users_updates_comment')->where('id', $id)
              ->where('is_reply',0)  
              ->first();
            if (!$news) {
                return ['code'=>0, 'result' => 'id err' ];
            }
            
            $comment = new UserUpdatesComment();
            $comment->create_time = time();
            $comment->uid = $uid;
            $comment->status=0;
            $comment->content = $content;
            $comment->updates_id = $id;
            $comment->is_reply = 1;
            $comment->save();
            
            $comment_count = $db::table('bb_users_updates_comment')->where('updates_id', $id)
              ->where('is_reply',1)
              ->count();
            
              $db2 = Sys::get_container_db();
              $sql="update bb_users_updates_comment set comment_count = {$comment_count} where id=?";
              $db2->query($sql, $id);
              
           // $article = $comment->comment;
          //  $article->reply_count = $comment_count;
           // $article->reply_time = time(); // 对于回复时，给评论加上最新回复时间。
            //$article->save();
            
        }
        return [
                'code'=>1,
        ];
    }
    

}


