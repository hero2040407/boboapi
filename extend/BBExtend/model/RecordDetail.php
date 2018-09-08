<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\Record;
use BBExtend\model\RecordInviteStarmaker;


/**
 * 
 * 
 * : 谢烨
 */
class RecordDetail extends Record
{
    protected $table = 'bb_record';
    public $timestamps = false;
    
    public $_is_show = false;
    
    
   // private $view_cache_redis_time = 300;
    
    public $self_uid=0;
   
    public function get_simple(){
        return [
                'video'=> $this->get_record(),
                'author'=> $this->get_author(),
                'comment' => null,
                'join' => null,
                
        ];
    }
   
    
    public function set_self_uid($uid)
    {
        $this->self_uid = $uid;
    }
    
    
    public function get_all_subject_redis()
    {
        
        
        $DataDB = $this->get_all_redis() ;
        if ( $this->subject_title ) {
            $DataDB['video']['title'] = $this->subject_title;
        }
        if ( $this->subject_pic ) {
            $DataDB['video']['bigpic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(
                    $this->subject_pic );
        }
        
        return $DataDB;
        
    }
    
    public function get_all_redis()
    {
        $key = "record:detail2:v2:" . $this->id;
        $redis = Sys::getredis11( );
        $result = $redis->get( $key );
        if (! $result) {
            $DataDB = $this->get_all();
            
            $redis->set( $key, serialize( $DataDB ) );
            $redis->setTimeout( $key, 3600 );
        } else {
            $DataDB = unserialize( $result );
            $DataDB['video']['like'] = intval( $this->like ) ;
            
        }
      //  $DataDB['is_like'] = self::get_is_like( $uid, $DataDB['room_id'] );
        $DataDB['author']['is_focus'] = \BBExtend\Focus::get_focus_state($this->self_uid, $DataDB['author']['uid'] );
        
        $people = $this->get_views($this->id);
        if ($people < $DataDB['video']['like']) {
            $people = intval( $DataDB['video']['like'] * 1.05 );
        }
        
        $DataDB['video']['people'] = $people;
        
        return $DataDB;
        
    }
    
    
    public function get_all(){
        return [
                'video'=> $this->get_record(),
                'author'=> $this->get_author(),
                'comment' => $this->get_comment(),
                'join' => $this->get_join(),
                
        ];
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
    
    private function get_record()
    {
        $like = $this->get_updates_like_count() ;
        $people = $this->get_updates_view_count() ;
        $comment_count =  $this->get_updates_comment_count() ;
        
        
        $word='';
        $redis = Sys::get_container_redis();
        $key  ="record:like:room_id:display_id:". $this->room_id ;
        $result = $redis->lRange($key, 0, -1);
        
        $i=0;
        if ( $result ) {
            $db = Sys::get_container_dbreadonly();
            $count = count($result);
            foreach ( $result as $uid ) {
                $i++;
                $sql="select nickname from bb_users where uid=?";
                $name = $db->fetchOne($sql,[ $uid ]);
                $word.=$name ."，";
                if ($i  >= 10) {
                    break;
                }
            }
        // 谢烨，201803 下面这句话千万不能写，否则有些名字就会500报错！！
        //    $word = trim($word, '，' )  ;
            if ($count > 1) {
                $word .="等人";
            }
            $word.="觉得此内容很赞";
            
        }
        $updates_id=0;
        if ($this->type == 7) {
            $updates_id = $this->activity_id;
        }
        
        return [
           'publish_time' => $this->time, 
           'bigpic' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                        $this->big_pic, $this->thumbnailpath ),
           'title' =>   $this->title,  
           'content_type' => \BBExtend\BBRecord::get_content_type( $this->room_id, $this->usersort,
                        $this->type ),
           'people' => $people,
           'like'   => $like,
           'comment_count' =>$comment_count,
           'word_like' => $word,
           'record_id' => $this->id,
                'room_id' =>$this->room_id,
                'audit' =>$this->audit ,  //   0：未审核 1：通过审核 2：未通过
                'updates_id' => $updates_id,
        ];
    }
    
    private function get_comment(){
        $db = Sys::get_container_db_eloquent();
        $answer = RecordInviteStarmaker::where( "record_id",$this->id )->where('new_status',4)
          ->first();
        $result=null;
        if ($answer) {
//             $result=[];
            $user = \BBExtend\model\User::find($answer->starmaker_uid);
            if ($user) {
               $result = [
            //    'create_time' => $answer->create_time,
                'zan_count'   =>$answer->zan_count,
                'answer_time' => $answer->answer_time,
                'answer_type' => $answer->answer_type,
                'answer'      => $answer->answer,
                'uid'         => $answer->starmaker_uid,
                'nickname'    => $user->get_nickname(),
                'pic'         => $user->get_userpic(),
                    'frame'   => $user->get_frame(),
                    'badge' =>$user->get_badge(),
                    
                'media_url'   =>$answer->media_url,
                'media_duration' => $answer->media_duration,
                'media_pic'   =>$answer->media_pic,
                'is_show' => $this->_is_show ,
              ];
            }
            
        }
        return $result;
        
    }
    
    
    private function get_join()
    {
//         $result =null;
        // 先查大赛
        $db = Sys::get_container_db_eloquent();
        if ($this->type==2 && $this->activity_id  >0) {
            $sql="select * from bb_task_activity where id=? and is_show=1 and is_remove=0";
            $row = DbSelect::fetchRow( $db, $sql, [ $this->activity_id ] );
            if ($row) {
                return [
                        'type'=>'act',
                        'id'  => $row['id'],
                        'title' => $row['title'],
                        'is_show' => $row['is_show'],
                ];
            }
        }
        
        if ($this->type==6 && $this->activity_id  >0) {
            $sql="select * from bb_advise where id=?  and is_active=1";
            $row = DbSelect::fetchRow( $db, $sql, [ $this->activity_id ] );
            if ($row) {
                return [
                        'type'=>'advise',
                        'id'  => $row['id'],
                        'title' => $row['title'],
                        'is_show' => $row['is_active'],
                ];
            }
        }
        
        
        $sql="select * from ds_record where record_id=?";
        $row = DbSelect::fetchRow( $db, $sql, [ $this->id ] );
        if ($row) {
            $sql="select * from ds_race where id=? and is_active=1";
            $row2 = DbSelect::fetchRow( $db, $sql, [ $row['ds_id'] ] );
            if ($row && $row2) {
              return [
                'type'=>'race',
                'id'  => $row2['id'],
                'title' => $row2['title'],
                'is_show' => $row2['is_active'],
              ];
            }
        }
        return null;
        
    }
    

}


