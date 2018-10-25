<?php
namespace BBExtend\backmodel;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 用户
 * 
 * User: 谢烨
 */
class Race extends Model 
{
    protected $table = 'ds_race';
    public $timestamps = false;
    
    
    /**
     * 当前排名，指某人在大赛中的视频的排名，分为两种，大赛结束前，变化，大赛结束后固定。
     * 
     */
    public function rank($uid)
    {
        //首先，查有没有短视频。
        $db = Sys::get_container_dbreadonly();
        
        // 这里的条件是 大赛未结束。
        if ( time() < $this->end_time  ) {
        
           $sql="
select uid from bb_record
where 
audit=1 and is_remove=0
and 
exists(
 select 1 from  ds_record
  where bb_record.id = ds_record.record_id
  and ds_record.ds_id=?
)
order by bb_record.`like` desc
";
            $uid_arr = $db->fetchCol($sql,[ $this->id ]);
        
            
        // 这里的情况是大赛结束后，排名应该固定。
        }else {
            $sql="
select uid from ds_record
where ds_id = ? and
exists(
 select 1 from bb_record
  where bb_record.id = ds_record.record_id
    and bb_record.is_remove=0 and bb_record.audit=1

)
order by like_count desc
";
            $uid_arr = $db->fetchCol($sql,[ $this->id ]);
        }
        
        $result = array_search($uid, $uid_arr);
        if ($result ===false) {
            return 0;
        }
        return $result+1;// 因为数组以0 为索引。
    }
    
    
   public function detail()
   {
       $result = $this->display();
       
       // 谢烨，这里查询 群信息
       $db = Sys::get_container_dbreadonly();
       $sql ="select *  from bb_group where bb_type=2 and ds_id=?";
       $row =$db->fetchRow($sql,[ $this->id ]);
       
       if ($row) {
           $result['has_group']=1;
           $result['group'] =[
                   'group_code' => $row['code'],
                   'group_title' => $row['title'],
                   'group_content' => $row['summary'],
                   'group_or_person' => $row['group_or_person'],
                   'group_pic' => $row['pic'],
                   'group_qrcode_pic' => $row['qrcode_pic'],
                   
           ];
           
           
       }else {
           $result['has_group']=0;
       }
       
       return $result;
   }
    
    
    public function display()
    {
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $sql="select pic_bignew,sort from ds_lunbo where ds_id=? ";
        $result = \BBExtend\DbSelect::fetchAll($db, $sql,[$this->id]);
        $time=time();
        return [
                'id' =>$this->id,
                'title' =>$this->title,
                'proxy_id'=>$this->proxy_id,
                'register_start_time'=>$this->register_start_time,
                'register_end_time'=>$this->register_end_time,
                'start_time'=>$this->start_time,
                'end_time'=>$this->end_time,
                'online_type'=>$this->online_type,
                'banner'=>$this->banner_bignew,
                'is_active'=>$this->is_active,
                'uid'=>$this->uid,
                'money'=>$this->money,
                'prize' => $this->prize,

                'summary' => $this->summary,
                'detail' => $this->detail,
                'slide_show' => $result,
                'current_time' => $time,
                'min_age' =>$this->min_age,
                'max_age' =>$this->max_age,
                'reward' =>$this->reward,
                'upload_type' => $this->upload_type,
        ];
    }
    
    
    // 谢烨，得到消息列表。我现在做。
    public function get_log_list()
    {
        
        
    }
    
    
    
    public function get_child(){
        if ($this->online_type==1) {
            return new RaceOnLine();
        }else {
            return new RaceOffLine();
        }
        
    }
    
    
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
