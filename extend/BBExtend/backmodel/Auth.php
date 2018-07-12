<?php
namespace BBExtend\backmodel;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户
 * 
 * User: 谢烨
 */
class Auth extends Model 
{
    protected $table = 'backstage_auth';
    public $timestamps = false;
    
   
    
    
//     public function display()
//     {
//         return [
//           'id' =>$this->id,
//                 'title' =>$this->title,
//         ];
//     }
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
