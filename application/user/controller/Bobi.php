<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;

//use think\Request;
//use think\Db;



use BBExtend\Sys;

class Bobi 
{
    /**
     * 波币使用日志
     **/
    public function index($startid=0,$length=20,$uid)
    {
        $uid = intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        
        if (\app\user\model\Exists::userhExists($uid) !=1) {
            return ['code'=>0,'message'=>'用户不存在'];
        }
        
        $db = Sys::get_container_db();
        
        $sql="select count,time,way from bb_currency_log where type=1 and uid={$uid} order by id desc
         limit {$startid},{$length}
        ";
        $result = $db->fetchAll($sql);
        
        
//         $is_bottom = (count($result)== $length) ? 0:1;d
        return ['code'=>1,'data'=>[
           'is_bottom' =>  (count($result)== $length) ? 0:1,
            'result' => $result,
            
        ]];
        
    }
}