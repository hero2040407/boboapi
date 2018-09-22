<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 下午 3:04
 */
namespace app\backstage\controller;

use app\backstage\service\RaceRegister;
use app\backstage\service\SetRaceStatus;
use BBExtend\backmodel\Images;
use BBExtend\backmodel\RaceRegistration;

class Registration extends Common Implements CommonInterface
{
    /**
     * Notes:
     * Date: 2018/8/17 0017
     * Time: 下午 3:07
     * @throws
     */
    function index($ds_id = '',$age = '')
    {
        if (empty($ds_id))
            $this->error('ds_id必须');

        $map['ds_id'] = $ds_id;
        if (!empty($age))
            $map['age'] =['between',$age];

        $list = (new RaceRegistration())->where($map)->select();
        $this->success('','',$list);
    }

    /**
     * Notes:
     * Date: 2018/8/17 0017
     * Time: 下午 3:08
     * @param string $id
     * @throws
     */
    function read($id = '')
    {
        if (empty($id))
            $this->error('id必须');
        $list = RaceRegistration::get($id);
        if ($list) {
            $list->items = (new Images())->all($list->pic_id_list);
        }
        else $list = [];
        $this->success('','',$list);
    }

    /**
     * Notes: 修改为报名状态,也就是晋级
     * Date: 2018/9/7 0007
     * Time: 下午 3:07
     * @param string $area_id
     * @throws
     */
    function advance($id = '')
    {
        if (empty($id))
            $this->error('id');
        $ids = (array)$id;
        $race_status = new SetRaceStatus();
        $res = $race_status->signUp($ids);

        if ($res) $this->success('晋级成功');
        $this->error('晋级失败');
    }

    /**
     * Notes: 用户报名新增
     * Date: 2018/9/6 0006
     * Time: 下午 1:05
     * @throws
     */
    function create($ds_id = '',$zong_ds_id = '')
    {
        if (empty($ds_id) || empty($zong_ds_id))
            $this->error('ds_id和zong_ds_id必须');

        $register = new RaceRegistration();
        $data = getValidParam($this->param,
            'ds_id,zong_ds_id,phone,sex,birthday,name,area1_name,
            ,area2_name,height,weight,qudao_id,remark,pic');

        $uid = (new RaceRegister())->add($data['phone']);

        $res = $register->createRegister($ds_id, $zong_ds_id, $uid, $data);

        if ($res) $this->success('报名成功');
        $this->error('报名失败');
    }

    function delete()
    {

    }

    function update()
    {

    }

    /**
     * Notes:转变赛区
     * Date: 2018/9/10 0010
     * Time: 上午 9:54
     * @throws
     */
    public function changeArea($id = '', $area_id = '')
    {
        if (empty($id) || empty($area_id))
            $this->error('id和area_id必须');

        $ids = (array)$id;
        $res = (new RaceRegistration())->
            where('id','in',$ids)->update(['ds_id' => $area_id]);

        if ($res) $this->success('赛区转变成功');
        $this->error('赛区转变失败');
    }
}