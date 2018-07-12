<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/6
 * Time: 10:17
 */

namespace app\show\controller;


use BBExtend\BBShow;

class Playmanager 
{
    public function get_play_show_list()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?input('param.startid'):0;
        $activity_id = input('?param.activity_id')?input('param.activity_id'):0;
        $length = input('?param.length')?input('param.length'):20;
        $type = input('?param.type')?input('param.type'):100;
        $address = input('?param.address')?input('param.address'):'';
        
        $obj = new BBShow();
        
        $ListDB = $obj->get_show($uid,3,$start_id,$length,$activity_id,$type,$address);
        if (count($ListDB) == $length)
        {
            return ['data'=>$ListDB,'is_bottom'=>0,'code'=>1];
        }
        else
        {
            return ['data'=>$ListDB,'is_bottom'=>1,'code'=>1];
        }
    }
    
    
    public function get_play_push_rewind_show_list()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?input('param.startid'):0;
        $activity_id = input('?param.activity_id')?input('param.activity_id'):0;
        $length = input('?param.length')?input('param.length'):20;
        $ListDB = BBShow::get_push_rewind_show($uid,3,$start_id,$length,$activity_id);
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