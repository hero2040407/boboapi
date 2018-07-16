<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 
 * 
 * 
 */
class UserUpdates extends Model 
{
    protected $table = 'bb_users_updates';
//     protected $primaryKey="uid";
    
    public $timestamps = false;
    public static $err='';
    
    
    public function is_like($uid){
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_users_updates_like_log where type=1 and uid=? and updates_id=? limit 1";
        $row = $db->fetchRow($sql,[ $uid, $this->id ]);
        if ($row) {
            return true;
        }
        return false;
    }
    
    
    public function incr_click_count()
    {
        $db = Sys::get_container_db();
        $sql="update bb_users_updates set click_count = click_count+1 where id=?";
        $db->query($sql, [ $this->id ]);
        
    }
    
    /**
     * 这里列表详情。
     * 
     * uid 这里是手机当前用户
     */
    public function list_info($uid)
    {
        $result=[];
        $result['create_time']= $this->create_time;
        $result['comment_count']= $this->comment_count;
        $result['click_count']= $this->click_count;
        $result['like_count']= $this->like_count;
        $result['style']= $this->style;
        $result['id']= $this->id;
        $result['is_like']= $this->is_like($uid);
        
        
        
        
        
        $db = Sys::get_container_dbreadonly();
        
        
        // 文字处理。
        $result['word_part'] ='';
        if ( in_array($this->style,[2,5,6]  )   ) {
            $sql="select word from bb_users_updates_media where bb_users_updates_id=? and type=1
  order by id asc
";
            $result['word_part'] = $db->fetchOne($sql, [ $this->id ]);
        }
        
        // 图片，必须放在模卡前面，初始化数组。
        $result['pic_part']= [];
        if ($this->style==3 || $this->style==5 ) { 
            $sql="select url,pic_width,pic_height from bb_users_updates_media where bb_users_updates_id=? and type=2
  order by id asc
";
            $result['pic_part'] = $db->fetchAll($sql, [ $this->id ]);
        }
        
        // 模卡
        $result['card_part']="";
        if ($this->style==1 ) { 
            $sql="select bb_users_card_id from bb_users_updates_media where bb_users_updates_id=? and type=4";
            $card_id =  $db->fetchOne($sql, [ $this->id ]);
            if ($card_id){
//                 Sys::debugxieye("card_id:{$card_id}");
              $sql="select pic as url , pic_width,pic_height from bb_users_card where id = ?";
              $temp1= $db->fetchRow($sql,[ $card_id ]) ;
              
//               Sys::debugxieye($temp1);
              
              $result['pic_part'][]= $temp1;
              $result['card_part']=$temp1['url'];
            }
            
        }

    //    Sys::debugxieye("视频id3434343");
        // 视频。
        $result['video_part'] =null;
        if ( in_array($this->style,[4,6]  )   ) {
       //     Sys::debugxieye("视频id");
            
            $sql="select bb_record_id from bb_users_updates_media where bb_users_updates_id=? and type=3";
         //   $row = $db->fetchRow($sql);
            $record_id =  $db->fetchOne($sql, [ $this->id ]);
            
        //    Sys::debugxieye("视频id：{$record_id}");
            
            if ($record_id) {
                $sql="select * from bb_record where id = ?";
                $result2 = $db->fetchRow($sql,[ $record_id ]) ;
                $result['video_part']=[];
                $result['video_part']['video_path']  = $result2['video_path'];
                $result['video_part']['big_pic']     = $result2['big_pic'];
                $result['video_part']['time_length'] =  \BBExtend\common\Date::time_length_display( 
                          $result2['time_length_second'] );
                
            }
        }
        
        $user = \BBExtend\model\UserDetail::find( $this->uid );
        $result['user'] = $user->get_info_201807_extend();
        
        return $result;
    }
    
    
    public function add_like($uid)
    {
        //$this->
        
        $dbread = Sys::get_container_dbreadonly();
        $sql="select * from bb_users_updates_like_log where uid=? and updates_id=? and type=1";
        $row = $dbread->fetchRow($sql, [ $uid, $this->id ] );
        if ($row) {
            return false;
        }
        
        
        $db = Sys::get_container_db();
        
        
        
        $bind=[
                "create_time" =>time(),
                "uid" =>$uid,
                "type" =>1,
                "updates_id"=> $this->id,
        ];
        $db->insert("bb_users_updates_like_log", $bind);
        
        $sql="update bb_users_updates set like_count = like_count+ 1 
               where id = ". $this->id;
        $db->query($sql);
        return true;
    }
    
    
    
    /**
     * 图片，这时，未审核。
     * @param unknown $card_id
     */
    public static function insert_pic($uid,$word,$pic_arr)
    {
        $db = Sys::get_container_db_eloquent();
        
        $updates = new self();
        $updates->uid = $uid;
        $updates->create_time = time();
        $updates->is_remove = 0;
        $updates->status = 0; // 因为审核过，再调用此接口，所以固定为完成状态。
        if ( $word ){
          $updates->style = 5;
        }  else {
            $updates->style = 3;
        }
        $updates->save();
        
        if ( $word ) {
            
            $media = new UserUpdatesMedia();
            $media->bb_users_updates_id = $updates->id;
            $media->type = 1;
            $media->word = $word ;
            $media->save();
            
        }
        
        foreach ($pic_arr as $pic) {
            $media = new UserUpdatesMedia();
            $media->bb_users_updates_id = $updates->id;
            $media->type = 2;
            $media->url = $pic['url'];
            $media->pic_width = $pic['pic_width'];
            $media->pic_height = $pic['pic_height'];
            
            $media->save();
        }
    }
    
    
    /**
     * 文字，这时，未审核。
     * @param unknown $card_id
     */
    public static function insert_word($uid,$word)
    {
        $db = Sys::get_container_db_eloquent();
        
        $updates = new self();
        $updates->uid = $uid;
        $updates->create_time = time();
        $updates->is_remove = 0;
        $updates->status = 0; // 未审核
        
        $updates->style = 2;
        $updates->save();
        
        $media = new UserUpdatesMedia();
        $media->bb_users_updates_id = $updates->id;
        $media->type = 1;
        $content = strip_tags($word);
        $media->word = $content;
        $media->save();
    }
    
    
    
    /**
     * 模卡审核成功。
     * @param unknown $card_id
     */
    public static function insert_card($card_id)
    {
        $db = Sys::get_container_db_eloquent();
        
        $sql="select * from bb_users_card where id=?";
        $row = DbSelect::fetchRow($db, $sql,[ $card_id ]);
        
        
        $updates = new self();
        $updates->uid = $row['uid'];
        $updates->create_time = $row['create_time'];
        $updates->is_remove = 0;
        $updates->status = 1; // 因为审核过，再调用此接口，所以固定为完成状态。
        
        $updates->style = 1;
        $updates->save();

        $media = new UserUpdatesMedia();
        $media->bb_users_updates_id = $updates->id;
        $media->type = 4;
        $media->bb_users_card_id = $card_id;
        $media->save();
    }
    
    
    /**
     * 短视频审核成功
     * 
     * @param unknown $record_arr
     */
    public static function insert_record($record_arr)
    {
        $db = Sys::get_container_db_eloquent();
        
        $updates = new self();
        $updates->uid = $record_arr['uid'];
        $updates->create_time = $record_arr['time'];
        $updates->is_remove = 0;
        $updates->status = 1; // 因为审核过，再调用此接口，所以固定为完成状态。
        
        if ($record_arr['title']) {
            $updates->style = 6;
        }else {
            $updates->style = 4;
        }
        $updates->save();
        
        if ( $record_arr['title'] ) {
        
          $media = new UserUpdatesMedia();
          $media->bb_users_updates_id = $updates->id;
          $media->type = 1;
          $media->word = $record_arr['title'] ;
          $media->save();
          
        }
        
        $media = new UserUpdatesMedia();
        $media->bb_users_updates_id = $updates->id;
        $media->type = 3;
        $media->url = $record_arr['video_path'];
        $media->bb_record_id = $record_arr['id'];
        $media->time_length =  \BBExtend\common\Date::time_length_display( 
                $record_arr['time_length_second'] );
        $media->save();
        
        
    }

   

}
