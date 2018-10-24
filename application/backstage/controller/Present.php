<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/24 0024
 * Time: 上午 11:17
 */
namespace app\backstage\controller;

use think\Controller;

class Present extends Controller implements CommonInterface
{
    function index()
    {
        $data = file_get_contents(APP_PATH.'/json/spec.json');
        //json转换
        $data = json_decode($data,true);
        //返回
        $this->success('', '', $data);
    }

    function read()
    {
        // TODO: Implement read() method.
    }

    /**
     * Notes: 设置礼物参数
     * Date: 2018/10/24 0024
     * Time: 上午 10:43
     * @param string $json
     * @throws
     */
    function update($json = '')
    {
        if (empty($json))
            $this->error('json不得为空');

        $res = file_put_contents(APP_PATH.'/json/spec.json', $json);
        if ($res)
            $this->success('修改成功');
        else
            $this->error('修改失败');
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