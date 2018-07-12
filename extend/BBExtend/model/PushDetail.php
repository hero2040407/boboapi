<?php
namespace BBExtend\model;
//use \Illuminate\Database\Eloquent\Model;
/**
 * 
 * 
 * : 谢烨
 */
class PushDetail extends Push 
{
    public $self_uid=0;
    
  
    
    public function set_self_uid($uid)
    {
        $this->self_uid = $uid;
    }
    
    
    public function get_all(){
        return [
                'video'=> $this->get_video(),
                'author'=> $this->get_author(),
                'comment' => null,
                'join' => null,
                
        ];
    }
    
    private function get_author()
    {
        $user = $this->user;
        $uid = $user->uid;
        $is_focus = \BBExtend\Focus::get_focus_state($this->self_uid, $uid);
        $lahei_help = new \BBExtend\user\Relation();
        return [
                'uid' => $uid,
                'user_pic' => $user->get_userpic(),
                'nickname' => $user->get_nickname(),
                'role' => $user->role,
                'frame'=>null,
                'is_focus' => $is_focus,
                'badge' =>$user->get_badge(),
                'is_lahei' => $lahei_help->has_lahei($uid, $this->self_uid) ,
        ];
    }
    
    private function get_video()
    {
        $like =  $this->like;
        $people = $this->people;
        if ($people < $like) {
            $people = intval( $like * 1.05 );
        }
        
      
        
        $content_type=10;
        if ($this->price_type==2) {
            $content_type = 11;
        }
        
        
        
        return [
                'publish_time' => intval( $this->time ),
                'bigpic' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(
                        $this->bigpic ),
                'title' =>   $this->title,
                'content_type' => $content_type ,
                
                'people' => $people,
                'like'   => $like,
                'comment_count' =>0,
                'word_like' => '',
                'record_id' => $this->id,
                'room_id' =>$this->room_id,
                'audit' =>1 ,  //   0：未审核 1：通过审核 2：未通过
                'pull_url' =>$this->pull_url, // 拉流地址。
                
        ];
    }
    
   

}
