<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class MoneyRainLog extends Model 
{
    protected $table = 'bb_money_rain_log';
    protected $primaryKey="id";
    
    public $timestamps = false;
    

//     public function user()
//     {
//         // 重要说明：第1个参数是关联的表名，第2个参数是外键名称（可能是本表或关联表），第3个参数是关联字段，（可能是本表或关联表）。
//         return $this->hasOne('BBExtend\model\Currency', 'uid', 'uid');
//     }
    
    
}
