<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;


/**
 * 
 * 
 * User: 谢烨
 */
class AdviseRole extends Model 
{
    protected $table = 'bb_advise_role';
    
    public $timestamps = false;
    
    
    
    public function index_info()
    {
        return [
                'title'=>$this->title,
                'content' =>$this->content,
                'identity'=>$this->identity,
                'reward' =>$this->reward,
                'min_height'=>$this->min_height,
                'max_height'=>$this->max_height,
                'min_age'=>$this->min_age,
                'max_age'=>$this->max_age,
                'sex'=>$this->sex,
                'role_id' =>$this->id,
                
        ];
        
    }
    
    
    
}
