<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/19 0019
 * Time: 下午 2:02
 */

namespace app\backstage\controller;

use think\cache\driver\File;

class Group extends Common Implements CommonInterface
{
    function index($race_id = '')
    {
        if (empty($race_id))
            $this->error('ds_id必须');

        $list = (new File())->get($race_id.'age_group');

        $this->success('', '', $list);
    }

    function read()
    {
        // TODO: Implement read() method.
    }

    function update()
    {
        // TODO: Implement update() method.
    }

    function create()
    {
        // TODO: Implement create() method.
    }

    function delete()
    {
        // TODO: Implement delete() method.
    }

    function save($race_id = '', $items = '')
    {
        if (empty($race_id))
            $this->error('race_id必须');
        if (empty($items))
            $this->error('items必须');
        $array = ['A','B','C','D','E','F','G','H','I'];
        $rule = '^\d+,\d+$^';
        $i = 0;
        foreach ($items as &$item) {
            if (!preg_match($rule, $item['age'])) {
                $this->error('age格式不符合');
            }
            $item['key'] = $array[$i];
            $i++;
        }
        (new File())->set($race_id.'age_group', $items);
        $this->success('设置成功');
    }
}