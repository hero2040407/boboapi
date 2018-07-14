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
     * 是否取消
     *
     * @param unknown $uid
     * @param unknown $id
     */
    private function is_like($uid,$id)
    {
        return false;
//         $db = Sys::get_container_db_eloquent();
//         $count = $db::table('web_article_like')->where('comment_id', $id)->where('uid',$uid)->count();
        
//         return (bool)$count;
    }
    
    
    
    private function get_parent($id,$uid=0)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="
         select * from bb_users_updates_comment
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
    public function index( $id,$startid=0,$length=10,$uid=0,$is_reply=0 )
    {
        $db = Sys::get_container_db_eloquent();
        $parent=null;
        // 查评论
        if (!$is_reply) {
            
            $news = $db::table('bb_users_updates')->where('id', $id)->first();
            if (!$news) {
                return ['code'=>0, 'result' => 'id err' ];
            }
            
            $sql="
         select * from bb_users_updates_comment
          where updates_id=?
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
         select * from bb_users_updates_comment
          where updates_id=?
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
            
         //   $temp['permissions'] = $comment_user->permissions;
            
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


