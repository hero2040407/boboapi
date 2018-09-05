<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/19 0019
 * Time: 下午 1:23
 */
namespace app\backstage\service;

use BBExtend\backmodel\RaceRecord;
use BBExtend\Sys;
use think\cache\driver\File;

// 0 等待 1过号 2打分 3晋级 4淘汰
// update_time > 0 过号状态 ,delete_time > 0 评分状态 ,都为空 等待状态
class MatchList
{
    private $area_id;
    private $round;
    private $race_id;
    private $age;
    private $sex;
    private $list;
    private $lost_uids;
    private static $instance;

    public static function getInstance()
    {
        if (!is_object(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * @param mixed $area_id
     */
    public function setAreaId($area_id)
    {
        $this->area_id = $area_id;
    }

    /**
     * @param mixed $race_id
     */
    public function setRaceId($race_id)
    {
        $this->race_id = $race_id;
    }

    /**
     * @param mixed $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @param mixed $sex
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    }

    /**
     * @param mixed $map
     */
    public function setMap()
    {
        if (!empty($this->round))
            $map['round'] = $this->round;
        if (!empty($this->age))
            $map['age'] = ['between',$this->age];
        if (!empty($this->area_id))
            $map['area_id'] = $this->area_id;
        if (!empty($this->race_id)){
            $map['race_id'] = $this->race_id;
            $map['area_id'] = 0;
        }
        if ($this->sex !== null)
            $map['sex'] = $this->sex;
//        if ($this->lost_uids)
//            $map['uid'] = ['in',$this->lost_uids];

        return $map;
    }

    /**
     * Notes: 获取年龄组
     * Date: 2018/8/30 0030
     * Time: 下午 4:51
     * @throws
     * @return mixed
     */
    public function getAgeGroup()
    {
        return (new File())->get($this->race_id.'age_group');
    }

    /**
     * Notes:
     * Date: 2018/8/20 0020
     * Time: 上午 9:32
     * @throws
     */
    public function setMatchList()
    {

        $map = $this->setMap();
        $record_model = new RaceRecord();

        $data = $record_model->where($map)->order('id')->select();

        $this->list = json_decode(json_encode($data),true);

        return $this->list;
    }

    /**
     * Notes: 正常参赛选手列表
     * Date: 2018/8/19 0019
     * Time: 上午 9:42
     * @throws
     */
    public function normalIndex()
    {
        $array = $this->list;
        $data = [];
        $list = array_filter($array, function($map){
            return $map['update_time'] == 0
                & $map['score'] == 0
                & $map['delete_time'] == 0;
        });
//        array_multisort(array_column($list,'id'), SORT_ASC, $list);
        foreach ($list as $item){
            $data[] = $item;
        }
        return $data;
    }

    /**
     * Notes: 过号列表
     * Date: 2018/8/19 0019
     * Time: 上午 11:16
     * @throws
     */
    public function lateIndex()
    {
        $array = $this->list;
        $data = [];
        $list = array_filter($array, function($map){
            return $map['update_time'] > 0
                & $map['delete_time'] == 0
                & $map['score'] == 0;
        });
        array_multisort(array_column($list,'update_time'), SORT_ASC, $list);
        foreach ($list as $item){
            $data[] = $item;
        }
        return $data;
    }

    /**
     * Notes: 选手本轮比分列表
     * Date: 2018/8/19 0019
     * Time: 上午 10:36
     * @throws
     */
    public function scoreIndex()
    {
//        $array = (array)$this->list;
//        $data = [];
//        $list = array_filter($array, function($map){
//            return $map['delete_time'] > 0;
//        });
//        array_multisort(array_column($list,'score'), SORT_DESC, $list);
//        foreach ($list as $item){
//            $data[] = $item;
//        }
        $map = $this->setMap();
        $record_model = new RaceRecord();

        $data = $record_model->field('*,max(id) as id,avg(score) as avg,sum(score) as sum')
            ->where($map)->group('uid')->order('sum desc')->select();

//        if ($this->age){
//            $type = Sys::get_container_redis()->get($this->area_id.$this->age.'finish');
//            if ($type == 1) $data['type'] = 1;
//        }
        $ids = [];
        foreach ($data as $item){
            $ids[] = $item['id'];
        }
        if ($ids){
            $list = $record_model->where([
                'id' => ['in',$ids],
                'delete_time' => ['>',0]
            ])->select();

            foreach ($data as &$item){
                foreach ($list as $value){
                    if ($item['uid'] == $value['uid']){
                        $item['score'] = $value['score'];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Notes: 上一轮比分列表
     * Date: 2018/8/19 0019
     * Time: 下午 3:46
     * @param $round
     * @return false|\PDOStatement|string|\think\Collection
     * @throws
     */
    public function scoreLastIndex()
    {
        $record_model = new RaceRecord();

        $map['round'] = $this->round - 1;
        $map['area_id'] = $this->area_id;

        if ($this->sex !== null)
            $map['sex'] = $this->sex;
        if (!empty($this->race_id)){
            $map['race_id'] = $this->race_id;
            $map['area_id'] = 0;
        }

        $map['age'] = ['between',$this->age];
        $map['delete_time'] = ['>',0];

        $list = $record_model->where($map)->order('score desc')->select();
        return $list;
    }

    /**
     * Notes: 设置本轮比赛轮次
     * Date: 2018/8/19 0019
     * Time: 上午 10:07
     * @throws
     */
    public function setRound($round = '')
    {
        $record_model = new RaceRecord();
        $map = $this->setMap();

        if ($round)
            $this->round = $round;
        else $this->round = $record_model->where($map)
            ->order('round desc')->value('round');

        return $this->round;
    }

    /**
     * Notes: 获取区域淘汰uids
     * Date: 2018/8/20 0020
     * Time: 下午 4:55
     */
    public function lostIndex()
    {
        $model = new RaceRecord();
        $data = [];
        $map = $this->setMap();
        $advance_uids = $model->where([
            'area_id' => $this->area_id,
            'round' => $this->round
        ])->group('uid')->column('uid');

        $all_uids = $model->where([
            'area_id' => $this->area_id,
            'round' => 1
        ])->group('uid')->column('uid');

        $diff_uids = array_diff($advance_uids, $all_uids);

        if ($diff_uids){
            $map['uid'] = ['in', $diff_uids];
            $data = $model->field('*,avg(score) as avg,sum(score) as sum')
                ->where($map)->group('uid,round')->order('avg desc')->select();
        }
        return $data;
    }
}