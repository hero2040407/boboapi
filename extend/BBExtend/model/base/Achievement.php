<?php
namespace BBExtend\model\base;
use \Illuminate\Database\Eloquent\Model;
/**
 * 成就
 * 
 */
class Achievement extends Model 
{
    protected $table = 'bb_users_achievement';
    protected $primaryKey="id";
    
    public $timestamps = false;
    
    
}
