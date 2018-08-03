<?php
//  1    2    3
namespace app\apptest\controller;
    

use  BBExtend\Sys;
//use app\shop\model\Area;
/**
 * 谢烨20160914，把杨桦的地区数据，导入到我方的数据库里bb_area
 * @author Administrator
 *
 */
class Ip
{
  //$temp='';
    public function index()
    {
        $redis = Sys::get_container_redis();
        $key =  "limit:ip:week";
        
        $result = $redis->sMembers();
        echo "所有被封禁的ip如下：";
        dump($result);
        
        
        
    }
   
}
