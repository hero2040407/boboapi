<?php
namespace BBExtend\backmodel;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户
 * 
 * User: 谢烨
 */
class Group extends Model 
{
    protected $table = 'bb_group';
    public $timestamps = false;
    
   
    
    
    public function display()
    {
//         return [
//           'id' =>$this->id,
//                 'title' =>$this->title,
//         ];
    }
    
}
