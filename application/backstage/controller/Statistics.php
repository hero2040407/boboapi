<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Controller;

/**
 * 大赛统计
 * 
 * @author xieye
 *
 */
class Statistics  extends Common
{
    
    public function index( $ds_id )
    {
//         Sys::debugxieye(11);
        $race = \BBExtend\backmodel\Race::find( $ds_id );
        if (!$race) {
            return ['code'=>0];
        }
//         Sys::debugxieye(22);
        $db = Sys::get_container_dbreadonly();
        $sql1="
SELECT FROM_UNIXTIME( create_time, '%Y-%m-%d' ) as c ,count(*) as count1
        FROM ds_register_log
        where zong_ds_id = ?
        GROUP BY c
";
        $sql2="
        SELECT FROM_UNIXTIME( finish_time, '%Y-%m-%d' ) as c ,count(*) as count2
        FROM ds_register_log
        where zong_ds_id = ?
        and is_finish=1
        GROUP BY c

";
        $result1 = $db->fetchAll($sql1, [ $ds_id ]);
        $result2 = $db->fetchAll($sql2, [ $ds_id ]);
     //   Sys::debugxieye($result1);
        
        $date=[];
        $new=[];
        foreach ( $result1 as $v ) {
            $date[]= $v['c'];
        }
        foreach ( $result2 as $v ) {
            $date[]= $v['c'];
        }
        if (empty($date)) {
            return ['code'=>1,'data'=>['list' =>[] ]  ];
        }
       // Sys::debugxieye($date);
        // 去除重复。
        $date = array_unique($date);
        // 排序
        sort($date);
        foreach ($date as $v) {
            $temp=[
                 'date' =>$v,
                 'count1' =>0,
                 'count2' =>0,   
            ];
            
            foreach ( $result1 as $v2 ) {
                if ($v2['c'] == $v ) {
                    $temp['count1'] = $v2['count1'];
                    break;
                }
            }
            foreach ( $result2 as $v2 ) {
                if ($v2['c'] == $v ) {
                    $temp['count2'] = $v2['count2'];
                    break;
                }
            }
            $new[]= $temp;
        }
        
        // 谢烨，返回所有。
        $i=0;
        $sum1= $sum2= 0;
        foreach ( $new as  $k=> $v ) {
            $sum1+= $v['count1'];
            $sum2 += $v['count2'];
            
            $new[$k]['sum1'] = $sum1;
            $new[$k]['sum2'] = $sum2;
        }
        
        return ['code'=>1, 'data'=>['list' =>$new ]  ];
        
        
    }
    
    
        
    
    
}






