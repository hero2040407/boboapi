<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 下午 3:04
 */
namespace app\backstage\controller;

use BBExtend\backmodel\Images;
use BBExtend\backmodel\RaceRegistration;
use think\cache\driver\File;

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
        $list->items = (new Images())->all($list->pic_id_list);
        $this->success('','',$list);
        // TODO: Implement read() method.
    }

    function update()
    {
        // TODO: Implement update() method.
    }

    function create()
    {
        $data = [
            '身高' => 133,
            '体重' => 65,
            '年龄' => 13
        ];
        $user_data = [
            'register_info' => $data
        ];
        (new RaceRegistration())->save($user_data);
        // TODO: Implement create() method.
    }

    function delete()
    {

        // TODO: Implement delete() method.
    }

    function categoryCreate($ds_id = '', $age = '')
    {
        if (empty($ds_id))
            $this->error('ds_id必须');
        if (empty($age))
            $this->error('age必须');

        $res = (new File())->set($ds_id, $age);
        if ($res) $this->success('设置成功');
        $this->error('设置失败');

    }

    function categoryIndex($ds_id = '')
    {
        if (empty($ds_id))
            $this->error('ds_id必须');

        $list = (new File())->get($ds_id);
        $this->success('','',$list);
    }
}