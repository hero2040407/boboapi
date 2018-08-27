<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/22 0022
 * Time: 下午 4:30
 */
namespace app\backstage\controller;

use app\backstage\service\MatchList;
use app\backstage\service\MessageAndRedis;
use BBExtend\backmodel\RaceField;
use BBExtend\backmodel\RaceRecord;
use BBExtend\backmodel\RaceRegistration;
use BBExtend\Sys;

class Mobilemessage extends Common
{

    /**
     * Notes: 发送给报名的人
     * Date: 2018/8/22 0022
     * Time: 下午 5:05
     * @param int $area_id
     * @param string $uid
     * @throws
     */
    public function sendToSingUp($area_id = '')
    {
        if (empty($area_id))
            $this->error('area_id必须');
        $map = [
            'ds_id' => $area_id,
            'has_pay' => 1
        ];
        $list = (new RaceRegistration())->mobileMessageList($map);

        $res = (new MessageAndRedis())->setAreaId($area_id)
            ->save('race_register_message', $list);

        if ($res) $this->success('发送成功');
        $this->error('发送失败');
    }

    /**
     * Notes: 一次性发送给区域赛区中晋级的所有人
     * Date: 2018/8/22 0022
     * Time: 下午 5:06
     * @throws
     */
    public function sendToAdvance($area_id = '', $age = '')
    {
        if (empty($area_id))
            $this->error('area_id必须');
        if (!empty($age))
            $map['age'] = ['between',$age];

        $map['area_id'] = $area_id;
        $map['delete_time'] = 0;

        $users = (new RaceRecord())->where($map)->select();

        foreach ($users as $item){
//      刚晋级的选手的比分必然为0
            if ($item['score'] > 0)
                $this->error('该类型的比赛还未结束,不能发送');
            $uids[] = $item['uid'];
        }

//      获取上次发送的人数
        if (empty($uids))
            $this->error('没有晋级的用户');

        $msg_and_redis = (new MessageAndRedis())->setAreaId($area_id)
            ->setAge($age)->setUids($uids);

        $res = $msg_and_redis->sendAgain();

        if ($res)
            $this->error('请勿重复发送');

        $list = (new RaceRegistration())
            ->mobileMessageList(['uid' => ['in',$uids]]);

        $res = $msg_and_redis
            ->save('race_advance_message', $list);

        $msg_and_redis->setMessageSum();

        if ($res) $this->success('发送成功');
        $this->error('发送失败');
    }

    public function index($area_id, $page = '')
    {
        $list = [];

        $data = Sys::get_container_redis()->hGetAll($area_id.'message');
        foreach ($data as $item){
            $list[] = json_decode($item, true);
        }
        $this->redisPaginate($list, $page);
        $this->success('','',$list);
    }

    /**
     * Notes: redis分页
     * Date: 2018/8/23 0023
     * Time: 下午 5:31
     * @throws
     */
    private function redisPaginate($list, $page = '')
    {
        $array = [];
        if (!empty($page)){
            $sum = count($list);
            $total = ceil($sum/20);
            $start = ($page - 1)*20;
            if ($total > 0){
                for ($i = $start;$i <= $start + 20;$i++){
                    if (!isset($list[$i])) break;
                    $array['data'][] = $list[$i];
                }
                if(isset($array['data']))
                    $array['total'] = $total;
            }
            $this->success('','',$array);
        }
    }
    /**
     * Notes:  生成uuid
     * Date: 2018/7/25 0025
     * Time: 下午 5:31
     */
    private function generateUniqueId()
    {
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $uuid = substr($charid, 0, 8)
                .substr($charid, 8, 4)
                .substr($charid,12, 4)
                .substr($charid,16, 4)
                .substr($charid,20,12);
            return $uuid;
        }
    }

    /**
     * Notes: 已通知
     * Date: 2018/8/22 0022
     * Time: 下午 5:15
     * @throws
     */
//    xxx.com/area_id/X/uid/X/
    public function notified($area_id = '', $uid = '')
    {
        $redis = Sys::get_container_redis();

        $res = $redis->hDel($area_id.'message', $uid);

        if ($res) $this->success('通知成功');
        $this->error('通知失败');
    }
}