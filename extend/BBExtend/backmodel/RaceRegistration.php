<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 下午 3:01
 */
namespace BBExtend\backmodel;

use think\Model;

class RaceRegistration extends Model
{
    protected $type = [
        'register_info' => 'json'
    ];
    protected $table = 'ds_register_log';

}