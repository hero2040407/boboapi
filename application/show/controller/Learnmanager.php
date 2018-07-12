<?php
namespace app\show\controller;
use think\Db;
use BBExtend\BBShow;

/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/29
 * Time: 12:39
 */
class Learnmanager 
{

    //获得学啥直播以及录播列表
    public function get_learn_show_list()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?input('param.startid'):0;
        $length = input('?param.length')?input('param.length'):20;
        $type = input('?param.type')?input('param.type'):100;
        $address = input('?param.address')?input('param.address'):'';
        $obj = new BBShow();
        
        $ListDB = $obj->get_show($uid,1,$start_id,$length,0,$type,$address);
        
        if (count($ListDB) == $length)
        {
            return ['data'=>$ListDB,'is_bottom'=>0,'code'=>1];
        }
        else
        {
            return ['data'=>$ListDB,'is_bottom'=>1,'code'=>1];
        }
    }
    
}