<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 
 * 
 * : 谢烨
 */
class RecordInviteStarmaker extends Model 
{
    protected $table = 'bb_record_invite_starmaker';
    public $timestamps = false;
    
    // 查关联的用户
    public function user()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\User', 'uid', 'uid');
    }
    
    // 查星推官
    public function user_starmaker()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\User', 'starmaker_uid', 'uid');
    }
}
