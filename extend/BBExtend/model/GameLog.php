<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 运营活动使用，记录每次游戏数据
 * User: Hanrea
 */
class GameLog extends Model
{
    protected $table = 'bb_game_logs';
    protected $primaryKey="id";
    public $timestamps = false;

    
}
