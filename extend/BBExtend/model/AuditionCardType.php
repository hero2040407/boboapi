<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;


/**
 * 
 * 
 * User: 谢烨
 */
class AuditionCardType extends Model 
{
    protected $table = 'bb_audition_card_type';
    
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
    public function get_info()
    {

        $address = '全国';
        $time_info = '';
        
        $advise = \BBExtend\model\Advise::where('audition_card_type',$this->id)->first();
        if ($advise  && $this->bigtype==2) {
            $address = $advise->address;
            
            $time_info = ( $advise->end_time - time() )/( 24 * 3600 );
            $time_info = ceil( $time_info );
            $time_info = "剩余{$time_info}天截止报名";
        }
        
        return [
           'title' =>$this->name,
                'summary'=> $this->summary,
                'id'=>$this->id,
                'time_info' =>$time_info,
                'address' =>$address,
        ];
    }
    
    
    
}
