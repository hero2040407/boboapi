<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\Sys;

use BBExtend\common\Image;

use BBExtend\BBUser;

/**
 * 用户
 * 
 * User: 谢烨
 */
class Race extends Model 
{
    protected $table = 'ds_race';
    public $timestamps = false;
    
    
    public function info()
    {
        $banner =Image::geturl($this->banner_bignew);
        $photo = BBUser::get_userpic($this->uid);
        return [
            'current_time' =>time(),    
            'title' =>$this->title,
            'photo' =>$photo,
                'banner' =>$banner,
                'start_time' =>$this->start_time,
                'end_time'   => $this->end_time,
                'register_start_time'=>$this->register_start_time,
                'register_end_time'=>  $this->register_end_time,
                'id' =>$this->id,
        ];
    }
    
    
    /**
     * 是否成功报名
     * 
     * @param unknown $uid
     * @return string
     */
    public function has_success_register( $uid )
    {
        $db = \BBExtend\Sys::get_container_dbreadonly();
        $sql="select count(*) from ds_register_log 
               where zong_ds_id=? and uid = ?
                and has_dangan=1
                and has_pay = 1
";
        return $db->fetchOne($sql,[ $this->id, $uid ]);
    }
    
    /**
     * 视频状态，1未上传未审核，2上传审核中，3成功，4失败，
     * @param unknown $uid
     * @return string
     */
    public function record_status( $uid )
    {
        $db = \BBExtend\Sys::get_container_dbreadonly();
        $sql="select record_id from ds_record
where uid=? and ds_id=?
order by id desc limit 1

";
        $record_id =$db->fetchOne($sql,[ $uid, $this->id ]);
        
        $record_id = intval( $record_id );
        if (!$record_id) {
            return 1;
        }
        $record = Record::find( $record_id );
        if (!$record) {
            return 4;
        }
        
        if ($record->audit==0) {
            return 2;
        }
        if ($record->audit==1) {
            return 3;
        }
        if ($record->audit==2) {
            return 4;
        }
        
        return 1;
    }
    
    /**
     * 晋级状态，
     * 1 成功，2失败，0 未选拔
     * @param unknown $uid
     * @return number
     */
    public function upgrade_status($uid) 
    {
        if ( $this->online_type==1 ) {
            return 0;
        }
        
        $db = Sys::get_container_dbreadonly();
        $sql="select * from ds_register_log where uid=? and zong_ds_id=?";
        $row = $db->fetchRow($sql,[ $uid, $this->id ]);
        if (!$row) {
            return 0;
        }
        return $row['is_finish'] ;
    }
    
    
    
    public function has_live_video()
    {
        $id  = $this->id;
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $sql="
        select ds_show_video.* from ds_show_video
        where ds_id ={$id}
        and  exists    (select 1 from bb_push where bb_push.event='publish'
                and ds_show_video.type=1
                and ds_show_video.room_id = bb_push.room_id
                )
        ";
        $result = \BBExtend\DbSelect::fetchOne($db, $sql);
        return boolval( $result); // 切勿修改强制转换。
    }
    
    
    public function backstage_display()
    {
        return [
          'id' =>$this->id,
                'title' =>$this->title,
        ];
    }
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
