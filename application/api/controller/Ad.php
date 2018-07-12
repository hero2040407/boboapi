<?php
namespace app\api\controller;
use app\push\controller\Pushmanager;
use app\push\controller\Rewindmanager;
use app\record\controller\Recordmanager;
use BBExtend\BBRedis;
use think\Db;


class Ad
{
    /**
     * 返回最新的安卓版本
     */
    public function getone()
    {
        if (\BBExtend\Sys::is_product_server()) {
            return ['code'=>0,];
        }
        $db = \BBExtend\Sys::get_container_db();
        $sql ='select * from bb_ad limit 20';
        $result = $db->fetchAll($sql);
        if ($result) {
            shuffle($result);
            
            $row  = $result[0];
            $pic = \BBExtend\common\Image::geturl($row['pic']);
            $row['pic'] = $pic;
            
            return ['code'=>1, 'data'=>$row];
        } else {
            return ['code'=>0,];
            
        }
    }
    
   
   
}
