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

    /**
     * Notes:
     * Date: 2018/8/22 0022
     * Time: 下午 5:12
     * @param $map
     * @throws
     */
    public function mobileMessageList($map)
    {
        $data = self::where($map)->field('name,uid,phone,age')->select();
        return json_decode(json_encode($data), true);
    }
}