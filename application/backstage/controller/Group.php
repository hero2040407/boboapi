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

        $list = (new File())->get($race_id);

        $this->success('','',$list);
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
        $rule = '^\d+,\d+$^';
        foreach($items as $item){
            if (!preg_match($rule,$item['age'])){
                $this->error('age格式不符合');
            }
        }
        (new File())->set($race_id,$items);
        $this->success('设置成功');
    }
}