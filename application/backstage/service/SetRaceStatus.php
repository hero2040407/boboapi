<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20 0020
 * Time: 下午 3:56
 */
namespace app\backstage\service;

use BBExtend\backmodel\RaceField;
use BBExtend\backmodel\RaceRecord;
use BBExtend\backmodel\RaceRecordHistory;
use BBExtend\backmodel\RaceRegistration;
use think\cache\driver\File;
use BBExtend\Sys;
use think\Exception;
use think\exception\HttpResponseException;
use think\Response;

// 设置ds_register_log 中的 race_status
class SetRaceStatus
{
    // 签到
    const SING_IN = 11;
// 晋级
    const ADVANCE = 12;
// 淘汰
    const LOST = 13;

    private $advanceUids;
    private $race_id;
    private $area_id;
    private $log_model;

    public function __construct()
    {
        $this->log_model = new RaceRegistration();
    }

    /**
     * @param mixed $area_id
     */
    public function setAreaId($area_id)
    {
        $this->area_id = $area_id;
        return $this;
    }

    public function setRaceId($race_id)
    {
        $this->race_id = $race_id;
        return $this;
    }

    public function getGroup($race_id, $birthday)
    {
        $groups = (new File())->get($race_id.'age_group');
        $age = date("Y") - substr($birthday, 0, 4);
        foreach($groups as $item){
            $ages = explode(',',$item['age']);
            if ($age >= $ages[0] && $age <= $ages[1]){
                return $item;
            }
        }

        return false;
    }
    /**
     * Notes: 签到设置
     * Date: 2018/8/20 0020
     * Time: 下午 4:06
     * @param $id
     * @throws
     */
    public function signIn($id, $user_info)
    {

        $redis = Sys::get_container_redis();

        $group = $this->getGroup($user_info['zong_ds_id'], $user_info['birthday']);

        if (!$group) return false;

        $redis->incr($user_info['ds_id'].$group['age'].'sign');

        $sort = $redis->get($user_info['ds_id'].$group['age'].'sign');

        $this->log_model->save(
            ['race_status' => self::SING_IN, 'signin_time' =>time()],
            ['id' => $id]
        );

//        $max_sort =  (new RaceRecord())->where([
//            'area_id' => $user_info['ds_id'],
//            'age' => ['between',$age_group]
//        ])->order('sort desc')->value('sort');
//        if ($max_sort) return $max_sort;

        return $group['key'].$sort;
    }

    /**
     * Notes: 获取区域晋级uids
     * Date: 2018/8/20 0020
     * Time: 下午 4:55
     */
    public function getAdvanceUids()
    {
        return (new RaceRecord())->where([
            'area_id' => $this->area_id,
            'delete_time' => 0
        ])->column('uid');
    }

    /**
     * Notes: 最终晋级设置
     * Date: 2018/8/20 0020
     * Time: 下午 4:05
     * @throws
     */
    public function advance()
    {
        $uids = $this->getAdvanceUids();

        if ($uids)
        $this->log_model->where([
            'uid' => ['in',$uids],
            'ds_id' => $this->area_id
        ])->update([
            'race_status' => self::ADVANCE,
            'is_finish' => 1
        ]);
    }

    /**
     * Notes: 最终淘汰
     * Date: 2018/8/20 0020
     * Time: 下午 4:07
     * @throws
     */
    public function lost()
    {
        $this->log_model->where([
            'race_status' => ['<',self::ADVANCE],
            'ds_id' => $this->area_id
        ])->update([
            'race_status' => self::LOST
        ]);
    }

    /**
     * Notes: 获取大赛晋级uids
     * Date: 2018/8/21 0021
     * Time: 上午 11:17
     * @throws
     */
    public function getRaceAdvanceUids()
    {
        return (new RaceRecord())->where([
            'race_id' => $this->race_id,
            'delete_time' => 0,
            'area_id' => 0
        ])->column('uid');
    }

    /**
     * Notes: 大赛晋级
     * Date: 2018/8/21 0021
     * Time: 上午 11:20
     * @throws
     */
    public function raceAdvance()
    {
        $uids = $this->getRaceAdvanceUids();

        if ($uids){
            $this->log_model->where([
                'uid' => ['in',$uids],
                'zong_ds_id' => $this->race_id
            ])->update([
                'is_finish' => 1,
                'race_status' => self::ADVANCE
            ]);
        }
    }

    /**
     * Notes: 最终淘汰
     * Date: 2018/8/20 0020
     * Time: 下午 4:07
     * @throws
     */
    public function raceLost()
    {
        $this->log_model->save(
            ['race_status' => self::LOST],
            [
                'zong_ds_id' => $this->race_id,
                'is_finish' => 0
            ]
        );
    }

    /**
     * Notes: 清楚所有redis数据
     * Date: 2018/8/29 0029
     * Time: 下午 2:14
     * @throws
     */
    public function clearAllRedis()
    {
        $groups = (new File())->get($this->race_id.'age_group');
        if ($groups){
            foreach ($groups as $item){
                Sys::get_container_redis()->del($this->area_id.$item['age'].'sign');
            }
        }
    }

    /**
     * Notes: 把数据移动到历史数据中
     * Date: 2018/8/29 0029
     * Time: 下午 6:46
     * @param $type //类型 0 结束当日比赛 1 结束区域比赛
     * @throws
     */
    public function moveToRecordHistory($type = 0)
    {
        $history_model = new RaceRecordHistory();
        $model = new RaceRecord();

        $data = $model->field('name,uid,round,age,sex,sort,race_id,area_id,avg(score) as score')->where([
            'area_id' => $this->area_id,
            'delete_time' => ['>',0]
        ])->group('uid,round')->select();

        $list = json_decode(json_encode($data),true);

        $time = $history_model->where('area_id',$this->area_id)->order('time desc')->value('time');

        if ($type){
            if(!$time) $time = 1;
            else $time = $time + 1;
        }
        elseif(!$time) $time = 1;


        foreach ($list as &$item){
            $item['time'] = $time;
        }

        $res = $history_model->insertAll($list);

        if ($res) $model->where('area_id',$this->area_id)->delete();
    }
}