<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Date;
use BBExtend\fix\MessageType;
use think\Db;




class Temp 
{
   
   public function area_list()
   {
       $db = Sys::get_container_db();
       $arr = ["福州","厦门","泉州","漳州","三明","莆田","南平","龙岩","宁德","平潭",];
       $arr = ["广州","深圳","珠海","汕头","佛山","韶关","湛江","肇庆","江门","茂名","惠州","梅州","汕尾","河源","阳江",
               "清远","东莞","中山","潮州","揭阳","云浮",];
       
       foreach ($arr as $area) {
           $sql="select count(*) from bb_users where address like '%{$area}%'";
           $count = $db->fetchOne($sql);
           echo $area. " : ". $count. "\n";
       }
       
   }
    
    
    
    public function index()
    {echo 222;
        $arr=[['a'=>2,'b'=>23]];
        $arr[]=['a'=>21,'b'=>213];
        $arr[]=['a'=>221,'b'=>213];
        $arr[]=['a'=>231,'b'=>213];
        $arr[]=['a'=>241,'b'=>213];
        
        $temp=['cccccc'=>34];
      //  dump($arr);
        
         array_splice($arr, mt_rand(1, count($arr)-1 ),0, [$temp] );
        dump($arr);
        
        
        
       // throw new \Exception('11');
//         $redis = Sys::get_container_redis();
//         $redis->zAdd('key', 1, 'val1');
//         $redis->zAdd('key', 0, 'val0');
//         $redis->zAdd('key', 5, 'val5');
//         $redis->zAdd('key', 6, 'val6');
//         $redis->zAdd('key', 7, 'val7');
//         $redis->zAdd('key', 8, 'val8');
//         $result=   $redis->zRange('key', 2, 3); // array(val0, val1, val5)
//         dump($result);
        
    }
    
    
    public function index222(){
      
        $s = "|大赛报名时间|报名否|视频上传状态|晋级状态|重复报名|最终结果|\n";
        $s.="| ---- | :---- | :---- | :---- | :---- | :---- |\n";
        
        $arr1 = ['大赛报名中','大赛报名结束后',];
        $arr2 = ['未报名','报名成功'];
        $arr3 = ['视频未上传','视频上传审核中','视频上传审核成功','视频上传审核失败',];
        $arr4 = ['未选拔','选拔晋级','选拔淘汰',];
        $arr5 = ['允许重复报名','不允许重复报名',];
        foreach ( $arr1 as $v1 ) {
            foreach ( $arr2 as $v2 ) {
                foreach ( $arr3 as $v3 ) {
                    foreach ( $arr4 as $v4 ) {
                        foreach ( $arr5 as $v5 ) {
                            
                            $length = \BBExtend\common\Str::strlen("{$v1}{$v2}{$v3}{$v4}{$v5}" );
                            
                            $s .= "|{$v1}|{$v2}|{$v3}|{$v4}|{$v5}";
                            $temp = 30- $length;
                            foreach (range(1,$temp) as $vvv) {
                                $s .= " ";
                            }
                            $s .= "|    |\n";
                        }
                    }
                }
            }
        }
        echo $s;
        file_put_contents('d:/1.md', $s);
    }
    
    
   
}






