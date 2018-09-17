<?php
namespace app\api\controller;
use BBExtend\Sys;

/**
 * 礼物控制器
 * 
 * @author xieye
 *
 */
class Present
{
    public function index()
    {
        $db = Sys::get_container_db();
        $sql = "select * from bb_present order by gold asc";
        $result = $db->fetchAll($sql);
        foreach ($result as $k => $v) {
            
            
            
            $result[$k]['pic'] = 
            \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $result[$k]['pic'] );
            
            
        }
        
        return ['code'=>1,'data'=>$result];
        
    }
   
}
