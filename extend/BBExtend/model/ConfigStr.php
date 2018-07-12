<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 
 * 
 * User: 谢烨
 */
class ConfigStr extends Model 
{
    protected $table = 'bb_config_str';
    protected $primaryKey="id";
    
    public $timestamps = false;
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
