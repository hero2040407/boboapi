<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class Currency extends Model 
{
    protected $table = 'bb_currency';
    protected $primaryKey="id";
    
    public $timestamps = false;
    


    
    
}
