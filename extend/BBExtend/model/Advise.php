<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;


/**
 * 
 * 
 * User: 谢烨
 */
class Advise extends Model 
{
    protected $table = 'bb_advise';
    
    public $timestamps = false;
    
    /**
     * 得到通告详情。
     * 
     * (end_time - time())/( 24 * 3600 )
     * 最后取整。
     * ceil() 
     * 
     * @return string[]|NULL[]
     */
    public function get_index_info()
    {
        
        $time_info = ( $this->end_time - time() )/( 24 * 3600 );
        $time_info = ceil( $time_info );
        $time_info = "剩余{$time_info}天截止报名";
        
        $db = Sys::get_container_dbreadonly();
        $sql="select name from  bb_advise_type where id=?";
        $type_name = $db->fetchOne($sql,[ $this->type ]);
        
        $sql="select count(*) from bb_advise_join 
              where  advise_id=? ";
        $join_count = $db->fetchOne($sql, [ $this->id ]);
       // audition_card_type
       
        $audition_card_name='';
        
        if ($this->audition_card_type) {
            $sql="select name from  bb_audition_card_type where id=?";
            $card_name = $db->fetchOne($sql,[ $this->audition_card_type ]);
            $card_name=strval($card_name);
        }
        
        $sql="select * from  bb_advise_type where id=?";
        $type_name = $db->fetchOne($sql,[ $this->type ]);
        
        return [
           'address' =>$this->address,
                'time'=> $time_info,
                'reward' => '报酬面议',
                'title' =>$this->title,
                'id'=>$this->id,
                'pic'  => $this->pic,
                'pic2' => $this->pic2,
                'is_recommend'=>$this->is_recommend,
                'type_name' =>$type_name,
                'join_count' =>$join_count,
                'auth' =>$this->auth,
                'card_name' =>$card_name,
                'card_id'   => $this->audition_card_type,
        ];
    }
    
    
    
}
