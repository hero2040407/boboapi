<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 轮播图。
 * 
 * @author xieye
 *
 */
class Toppic
{
    public function index($sort_id=20   )
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select id,picpath,linkurl from bb_toppic where sort_id=?
order by id asc";
        $pic_list = $db->fetchAll($sql,[ $sort_id ]);
        return ['code'=>1, 'data' =>['list' => $pic_list ] ];
    }
    
    
    

}


