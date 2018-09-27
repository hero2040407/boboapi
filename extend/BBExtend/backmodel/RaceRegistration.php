<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 下午 3:01
 */
namespace BBExtend\backmodel;

use app\backstage\service\SetRaceStatus;
use BBExtend\Sys;
use think\exception\HttpResponseException;
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

    /**
     * Notes:
     * Date: 2018/9/19 0019
     * Time: 上午 11:09
     * @param $ds_id
     * @param $zong_ds_id
     * @param $uid
     * @param $data
     * @throws
     * @return int
     */
    public function createRegister($ds_id, $zong_ds_id, $uid, $data)
    {
        $res = self::where([
            'uid' => $uid,
            'zong_ds_id' => $zong_ds_id
        ])->find();
        if ($res) throwErrorMessage('此用户已报名');
        $redis = Sys::get_container_redis();
        $data['birthday'] = date('Y-m',$data['birthday']);
        $group = (new SetRaceStatus())->getGroup($zong_ds_id, $data['birthday']);
        if (!$group) throwErrorMessage('此用户的年龄不能参加大赛');
        $sort = $redis->get($ds_id.$group['age'].'sort');
        $data['age'] = date('Y') - substr($data['birthday'], 0, 4);
        $data['register_info'] = [
            '身高' => $data['height'],
            '体重' => $data['weight']
        ];
        $data['uid'] = $uid;
        $data['has_pay'] = 1;
        $data['has_dangan'] = 1;
        $data['create_time'] = time();
        if ($sort){
            $sort = $redis->incr($ds_id.$group['age'].'sort');
            $data['sort'] = $group['key'].$sort;
        }

        return self::save($data);
    }
}