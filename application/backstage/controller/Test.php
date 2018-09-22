<?php
namespace app\backstage\controller;

use app\backstage\service\SetRaceStatus;
use BBExtend\Sys;
use BBExtend\DbSelect;

use think\Controller;
use think\Request;

class Test  
{
    
   
   
    
    /**
     * 未过期大赛
     * 
     * @return number[]|string[]|number[]
     */
    public function index($per_page=10,$aaa=1) 
    {
        $request = Request::instance( );
        $controller = $request->controller();
        
        $action = $request->action( );
        $route = $controller . '/' . $action;
        echo 'domain: ' . $request->domain() . '<br/>';
        echo $route;
    }

    /**
     * Notes: 添加用户
     * Date: 2018/8/20 0020
     * Time: 上午 11:22
     * @throws
     */
    public function updateUsers($race_id = '', $area_id = '')
    {
        $change = (new SetRaceStatus())->setAreaId($area_id);
        $change->setRaceId($race_id);
        $change->clearAllRedis();
//        $map['ds_id'] = 19;
//        $arr = (new RaceRegistration())->where($map)->select();
//
//        $arr = json_decode(json_encode($arr), true);
//
//        foreach ($arr as $item) {
//            $map1['id'] = $item['id'];
//            $age['age'] = rand(1, 16);
//            (new RaceRegistration())->save($age, $map1);
//        }
//        $this->success('修改用户成功');
    }
    
        
}




