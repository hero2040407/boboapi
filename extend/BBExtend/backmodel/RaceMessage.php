<?php
namespace BBExtend\backmodel;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户
 * 
 * User: 谢烨
 */
class RaceMessage extends Model 
{
    protected $table = 'ds_race_message';
    public $timestamps = false;
    
   
    
    
    public function display()
    {
        $ds_id = $this->ds_id;
        $field_id = $this->field_id;
        $field_title ='';        
        $race  = \BBExtend\backmodel\Race::find( $ds_id );
        
        if ( $field_id ) {
            $field = \BBExtend\backmodel\RaceField::find( $field_id );
            $field_title = $field->title;
            
        }
     //   \BBExtend\Sys::debugxieye($race->title);
        
        return [
                'id' =>$this->id,
                'race_title' =>$race->title,
                'field_title' =>$field_title,
                'content' =>$this->content,
                'create_time' => $this->create_time * 1000,
                'target_type' => $this->target_type,
                'is_valid'    => $this->is_valid,
                'content'     =>$this->content,
        ];
    }
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
