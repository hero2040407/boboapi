<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 上午 9:37
 */
namespace BBExtend\backmodel;

use think\Model;

class RaceFormPublicConfig extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $table = 'ds_public_config';
}