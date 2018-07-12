<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 成就汇总
 * 
 */
class AchievementSummary extends Model 
{
    protected $table = 'bb_users_achievement_summary';
    protected $primaryKey="id";
    
    public $timestamps = false;
    
   
    

}
