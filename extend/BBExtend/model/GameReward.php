<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 游戏 奖励发放
 * 
 * User: Hanrea
 */
class GameReward extends Model
{
    protected $table = 'bb_game_rewards';
    protected $primaryKey="id";
    public $timestamps = false;

    
}
