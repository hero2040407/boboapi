<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 游戏排行榜数据（每日一条记录）
 * User: Hanrea
 */
class GameSources extends Model
{
    protected $table = 'bb_game_scores';
    protected $primaryKey="id";
    public $timestamps = false;


}
