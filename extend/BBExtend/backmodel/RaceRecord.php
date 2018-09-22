<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/19 0019
 * Time: 上午 9:23
 */
namespace BBExtend\backmodel;

use think\Model;

class RaceRecord extends Model
{
    protected $updateTime = false;
    protected $autoWriteTimestamp = true;
    protected $table = 'ds_match_record';
}