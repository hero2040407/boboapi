<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 
 * 
 * : 谢烨
 */
class Push extends Model 
{
    protected $table = 'bb_push';
    public $timestamps = false;
    
    // 查关联的用户
    public function user()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\User', 'uid', 'uid');
    }
    

}
