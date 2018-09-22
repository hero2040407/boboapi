<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/23 0023
 * Time: 上午 10:08
 */
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\backmodel\RaceField;
use think\Controller;


class Export extends Controller
{
    /**
     * Notes: 收短信的人读取消息
     * Date: 2018/8/22 0022
     * Time: 下午 5:15
     * @throws
     */
//    xxx.com/area_id/X/uid/X/
    public function read($area_id = '', $id = '')
    {
        $redis = Sys::get_container_redis();
        $area = RaceField::find($area_id);
        $redis->hDel($area_id.'message', $id);

        if (!$area)
            $this->error('这个页面发生了错误');

        $race_id = $area->race_id;

        $this->redirect('https://bobot.yimwing.com/webapp/#/pull/ticket?dsid='.$race_id.'&uid='.$id);
    }

    /**
     * Notes:
     * Date: 2018/8/24 0024
     * Time: 上午 10:04
     * @throws
     */
    public function clear($area_id = '')
    {
        $redis = Sys::get_container_redis();
        do{
            $res = $redis->lPop('race_advance_message');
        }
        while($res);
        $redis->set($area_id.'sum', null);
        $res = $redis->del($area_id.'message');
        if ($res)
        $this->success('清理成功');
        $this->error('清理失败');
    }
}