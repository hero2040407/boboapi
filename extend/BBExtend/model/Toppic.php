<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class Toppic extends Model 
{
    protected $table = 'bb_toppic';
    public $timestamps = false;
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
