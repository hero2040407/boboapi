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
        return [
           'address' =>$this->address,
                'time'=> $time_info,
                'reward' => '报酬面议',
                'title' =>$this->title,
                'id'=>$this->id,
                'pic' =>$this->pic,
                
                
        ];
    }
    
    
    
}
