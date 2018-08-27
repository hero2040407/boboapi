<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 上午 9:31
 */
namespace app\backstage\controller;

use BBExtend\backmodel\RaceFormPublicConfig;

class Publicformconfig extends Common
{

    /**
     * Notes: 查询所有大赛公共表单配置
     * Date: 2018/8/17 0017
     * Time: 下午 1:49
     * @throws
     */
    public function index()
    {
        $list = RaceFormPublicConfig::all();
        $this->success('','',$list);
    }

    /**
     * Notes: 新建公共表单配置
     * Date: 2018/8/23 0023
     * Time: 上午 11:31
     * @throws
     */
    public function create()
    {
        $data = getValidParam($this->param,'title,type,options,beizhu');
        $res = (new RaceFormPublicConfig())->save($data);
        $this->success($res);
    }

    /**
     * Notes:
     * Date: 2018/8/17 0017
     * Time: 下午 1:53
     * @param string $id
     * @throws
     */
    public function read($id = '')
    {
        if (empty($id))
            $this->error('id必须');
        $list = (new RaceFormPublicConfig())->where('id',$id)->select();
        $this->success('','',$list);
    }

    /**
     * Notes: 新增
     * Date: 2018/8/17 0017
     * Time: 下午 1:50
     * @param string $id
     * @throws
     */
    function update($id = '')
    {
        if (empty($id))
            $this->error('id必须');
        $data = getValidParam($this->param,'title');
        $res = (new RaceFormPublicConfig())->save($data, ['id' => $id]);
        $this->success($res);

    }

    /**
     * Notes:
     * Date: 2018/8/17 0017
     * Time: 下午 1:50
     * @param string $id
     * @throws
     */
    public function delete($id = '')
    {
        if (empty($id))
            $this->error('id必须');
        $res = RaceFormPublicConfig::destroy($id);
        $this->success($res);
    }
}