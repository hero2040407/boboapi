<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/16 0016
 * Time: 下午 4:40
 */
namespace app\backstage\controller;
use BBExtend\backmodel\RaceFormConfig;

/**
 * 生成表单
 * Class Generateform
 * @package app\backstage\controller
 */
class Generateform extends Common Implements CommonInterface
{
    /**
     * Notes:
     * Date: 2018/8/17 0017
     * Time: 下午 2:58
     * @param string $ds_id
     * @throws
     */
    function index($ds_id = '')
    {
        if (empty($ds_id))
            $this->error('ds_id必须');
        $list = (new RaceFormConfig())->where('ds_id',$ds_id)->get();
        $this->success('','',$list);
        // TODO: Implement index() method.
    }

    /**
     * Notes: 报名表单详情
     * Date: 2018/8/17 0017
     * Time: 下午 3:43
     * @param string $ds_id
     * @throws
     */
    function read($ds_id = '')
    {
        if (empty($ds_id))
            $this->error('ds_id必须');
        $list = (new RaceFormConfig())->where('ds_id',$ds_id)->select();
        $this->success('','',$list);
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

    /**
     * Notes: 生成表单
     * Date: 2018/8/17 0017
     * Time: 下午 2:02
     * @param string $ds_id
     * @throws
     */
    function save($ds_id = '', $items = [])
    {
        if (empty($ds_id))
            $this->error('ds_id必须');
        if (!$items)
            $this->error('items必须');
        $model = new RaceFormConfig();
        $ids = $model->where('ds_id',$ds_id)->column('id');

        $data_without_id = [];
        $exist_id = [];
        foreach ($items as $item){
            $item['ds_id'] = $ds_id;
            if (isset($item['id'])){
                $exist_id[] = $item['id'];
                $model->isUpdate()->save($item);
            }
            else $data_without_id[] = $item;
        }
        $diff_ids = array_diff($ids,$exist_id);

        if($diff_ids)
            (new RaceFormConfig())->where(['id' => ['in',$diff_ids]])->delete();

        $model->saveAll($data_without_id);

        $this->success('处理成功');
    }
}