<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 上午 11:16
 */
namespace app\backstage\controller;

use BBExtend\backmodel\RaceLabel;
/**
 * 才艺
 * Class Talent
 * @package app\backstage\controller
 */
class Talent extends Common Implements CommonInterface
{
    /**
     * Notes: 获取大赛才艺信息
     * Date: 2018/8/17 0017
     * Time: 下午 2:59
     * @throws
     */
    function index()
    {
        $list = RaceLabel::all();
        $this->success('','',$list);
        // TODO: Implement index() method.
    }

    /**
     * Notes:
     * Date: 2018/8/17 0017
     * Time: 下午 2:59
     * @param string $id
     * @throws
     */
    function read($id = '')
    {
        if (empty($id))
            $this->error('id必须');
        $list = RaceLabel::get($id);
        $this->success('','',$list);
        // TODO: Implement read() method.
    }

    function update($id = '')
    {
        if (empty($id))
            $this->error('id必须');

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

}