<?php

namespace app\advise\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 234
 * @author xieye
 *
 */
class Index 
{
    // 1未绑定手机普通用户，2导师，3vip童星，4机构,5绑定手机普通用户，6一般童星，7签约童星
    public function index($uid=10000, $startid=0, $length=10)
    {
        $uid = intval($uid);
        $startid = intval($startid);
        $length = intval($length);
        
    }
    
    
    public function type_list(){
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_advise_type order by id";
        $result = $db->fetchAll($sql);
        return ['code'=>1,'data'=>[
                'list' => $result,
        ]];
        
    }
   
    
    
}





