<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/23 0023
 * Time: 上午 9:40
 */
namespace app\backstage\service;

use BBExtend\backmodel\RaceField;
use BBExtend\backmodel\RaceRecord;
use BBExtend\backmodel\Race;
use BBExtend\Sys;

class MessageAndRedis
{
    private $area_id;
    private $age;
    private $uids;

    /**
     * @param mixed $area_id
     */
    public function setAreaId($area_id)
    {
        $this->area_id = $area_id;
        return $this;
    }

    /**
     * @param mixed $age
     */
    public function setAge($age)
    {
        $this->age = $age;
        return $this;
    }

    /**
     * @param mixed $uids
     */
    public function setUids($uids)
    {
        $this->uids = $uids;
        return $this;
    }



    /**
     * Notes: 把发送的短信数据储存到hash表和list中
     * Date: 2018/8/23 0023
     * Time: 上午 9:42
     * @throws
     */
    public function save($list_key, $data)
    {
        $redis = Sys::get_container_redis();
        $area = RaceField::find($this->area_id);
        $race = Race::find($area->race_id);

        if (empty($area))
            return false;

        foreach ($data as $item){
            $item['race_id'] = $area->race_id;
            $item['race_name'] = $race->title;
            $item['area_name'] = $area->title;
            $item['id'] = $this->area_id.'/'.$item['uid'];

            $json_item = json_encode($item);
            $arr[$item['uid']] = $json_item;

            $redis->rPush($list_key, $json_item);
        }
        $res = $redis->hMset($this->area_id.'message', $arr);

        return $res;
    }

    /**
     * Notes: 设置发送短信的人数
     * Date: 2018/8/24 0024
     * Time: 下午 2:30
     * @throws
     */
    public function setMessageSum()
    {
        $redis = Sys::get_container_redis();
        $sum = count($this->uids);
        if (!empty($this->age)){
            $redis->set($this->area_id.$this->age.'sum', $sum);
        }
        else $redis->set($this->area_id.'sum', $sum);
    }

    /**
     * Notes: 是否重复发送
     * Date: 2018/8/24 0024
     * Time: 下午 2:31
     * @throws
     * @return bool
     */
    public function sendAgain()
    {
        $redis = Sys::get_container_redis();
        $sum = count($this->uids);
        if (!empty($this->age)){
            $count = $redis->get($this->area_id.$this->age.'sum');
        }
        else $count = $redis->get($this->area_id.'sum');

        if ($sum == $count) return true;
        return false;
    }
}