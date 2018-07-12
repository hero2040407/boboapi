<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 
 * 
 * : 谢烨
 */
class Rewind extends Model 
{
    protected $table = 'bb_rewind';
    public $timestamps = false;
    
    
    public $self_uid=0;
    
    // 查关联的用户
    public function user()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\User', 'uid', 'uid');
    }
    
    public function get_simple(){
        return $this->get_all();
    }

    public function get_all(){
        return [
        'video'=> $this->get_rewind(),
        'author'=> $this->get_author(),
        'comment' => null,
        'join' => null,
                ];
        //return
    }
    
    public function set_self_uid($uid)
    {
        $this->self_uid = $uid;
    }
    
    private function get_author()
    {
        $user = $this->user;
        $uid = $user->uid;
        $is_focus = \BBExtend\Focus::get_focus_state($this->self_uid, $uid);
        return [
                'uid' => $uid,
                'user_pic' => $user->get_userpic(),
                'nickname' => $user->get_nickname(),
                'role' => $user->role,
                'frame'=>null,
                'is_focus' => $is_focus,
                'badge' =>$user->get_badge(),
        ];
    }
    
    
    private function get_rewind()
    {
        $like = $this->like;
        $people = $this->people;
        if ($people < $like) {
            $people = intval( $like * 1.05 );
        }
        $comment_count = \BBExtend\BBComments::Get_comments_count(
                "bb_rewind_comments", $this->id );
        
        $word='';
        $redis = Sys::get_container_redis();
        $key  ="record:like:room_id:". $this->room_id ;
        $result = $redis->lRange($key, 0, -1);
        
        
        if ( $result ) {
            $count = count($result);
            foreach ( $result as $name ) {
                //                 $sql="select nickname from bb_users where uid=?";
                //                 $name = DbSelect::fetchOne($db, $sql,[ $uid ]);
                $word.=$name ."，";
            }
        //    $word = trim($word, '，' )  ;
            if ($count > 1) {
                $word .="等人";
            }
            $word.="觉得此内容很赞";
            
        }
        
        return [
                'publish_time' => intval( $this->end_time),
                'bigpic' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https($this->bigpic ),
                'title' =>   $this->title,
                'content_type' => 20,
                'people' => $people,
                'like'   => $like,
                'comment_count' =>$comment_count,
                'word_like' => $word,
                'record_id' => $this->id,
                'room_id' =>$this->room_id,
                'audit' =>1,
        ];
    }
    
    
    
    
    
    
    
    
    
    
    
}
