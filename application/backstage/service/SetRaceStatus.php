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

// 设置ds_register_log 中的 race_status
class SetRaceStatus
{
    const SING_UP = 0;
    // 签到
    const SING_IN = 11;
    // 晋级
    const ADVANCE = 12;
    // 淘汰
    const LOST = 13;

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

        if (!$groups) throwErrorMessage('请先设置大赛的年龄组');
        foreach($groups as $item){
            $ages = explode(',',$item['age']);
            if ($age >= $ages[0] && $age <= $ages[1]){
                return $item;
            }
        }
        return false;
    }

    /**
     * Notes:给所有的比赛选手排序
     * Date: 2018/9/17 0017
     * Time: 下午 12:04
     * @throws
     */
    public function sort(){
        $redis = Sys::get_container_redis();
        if ($this->area_id) $map['ds_id'] = $this->area_id;
        if ($this->race_id) $map['zong_ds_id'] = $this->race_id;
        $map['race_status'] = SetRaceStatus::SING_UP;
        $data = $this->log_model->field('id,zong_ds_id,birthday,ds_id')
            ->where($map)->order('height')->select();
        try{
            foreach ($data as &$item){
                $item = $item->getData();
                $group = $this->getGroup($item['zong_ds_id'], $item['birthday']);
                if (!$group) continue;
                $redis->incr($item['ds_id'].$group['age'].'sort');
                $sort = $redis->get($item['ds_id'].$group['age'].'sort');
                $item['sort'] = $group['key'].$sort;
                $this->log_model->isUpdate()->save($item);
            }
        }
        catch (\Exception $exception){
            throwErrorMessage($exception->getMessage());
        }
        return true;
    }

    /**
     * Notes: 设为报名
     * Date: 2018/9/7 0007
     * Time: 下午 3:00
     * @throws
     */
    public function signUp($ids)
    {
        return $this->log_model->where('id','in',$ids)->update([
            'race_status' => self::ADVANCE
        ]);
    }

    /**
     * Notes: 复赛设置
     * Date: 2018/9/7 0007
     * Time: 下午 3:00
     * @throws
     */
    public function repeat()
    {
        return $this->log_model->where([
            'ds_id' => $this->area_id,
            'race_status' => self::ADVANCE
        ])->update([
            'race_status' => self::SING_UP
        ]);
    }

    /**
     * Notes: 清除所有redis数据
     * Date: 2018/8/29 0029
     * Time: 下午 2:14
     * @throws
     */
    public function clearAllRedis()
    {
        $groups = (new File())->get($this->race_id.'age_group');
        if ($groups){
            foreach ($groups as $item){
                $res = Sys::get_container_redis()->del($this->area_id.$item['age'].'sort');
//                if (!$res) throwErrorMessage('error');
            }
        }
    }

    /**
     * Notes: 把数据移动到历史数据中
     * Date: 2018/8/29 0029
     * Time: 下午 6:46
     * @throws
     * return $res
     */
    public function moveToRecordHistory($map)
    {
        $history_model = new RaceRecordHistory();
        $model = new RaceRecord();
        $area = new RaceField();

        $data = $model->field('name,uid,round,age,sex,sort,race_id,area_id,sum(score) as score')
            ->where($map)->group('uid,round')->select();

        $race_time = $area->where('id',$this->area_id)->value('round');
        
        $time = time();
        foreach ($data as &$item){
            $item = $item->getData();
            $item['time'] = $race_time;
            $item['create_time'] = $time;
        }

        $res = $history_model->insertAll($data);

        if ($res) $model->where($map)->delete();

        return $res;
    }
}