<?php
/**
 * Created by PhpStorm.
 * User: xieye
 * Date: 2017/2/4
 * Time: 10:17
 */

namespace app\show\controller;


use BBExtend\BBShow;
use think\Db;
use BBExtend\Sys;
use app\user\controller\User;
use BBExtend\BBRedis;
use BBExtend\Focus;
use BBExtend\BBRecord;

class Vip
{
  
   
    
    //获得短视频的视频列表
    /**
     * 说明 type=1 今日打榜
     * type=2 大家都在看。
     * 
     * @param unknown $uid
     * @param unknown $startid
     * @param unknown $length
     */
    public  function index($uid,$sort_scheme=0)
    {
        $startid=0;
        $length=4;
        $uid=intval($uid);
        $sort_scheme=intval($sort_scheme);
        if ($sort_scheme==0) {
            $sort_scheme = intval( date("ndHi") );
        }
        $sort_scheme++;
        
        if (\app\user\model\Exists::userhExists($uid)!=1) {
            return ['code'=>0, 'message'=>'用户不存在' ];
        }
        
        $result = array();
        $db = Sys::get_container_db();
        
        // 谢烨，现在查才艺秀标签。
        $sql ="select * from bb_label where is_show=1 order by num desc";
        $label_list = $db->fetchAll($sql);
         
        
        
        
        // vip童星专区
        $sql=" select * from bb_record
                    where type in (1,2)
                      and audit=1
                      and is_remove=0
                      and exists (select 1 from bb_users 
                                       where bb_users.uid= bb_record.uid
                                         and bb_users.role=3
)
                      
                      
                    order by crc32(id+{$sort_scheme})
                    limit {$startid}, {$length}
                    ";
        $temp = $db->fetchAll($sql);
        if (count($temp) == $length ) {
            $result[]= [
                    'type'  => 10000,
                    'title' => "VIP童星专区",
                    'subtitle' => "更多VIP童星视频",
                    'list'  => $this->help_simple($temp, $uid),
                    ];
        }
        
        
        
        
        
        foreach ($label_list as $label_row) {
            $sql=" select * from bb_record
                    where type in (1,2)
                      and audit=1
                      and is_remove=0
                      and usersort =2
                      and label='{$label_row['id']}'
                      and heat >0
                    order by crc32(id+{$sort_scheme})
                    limit {$startid}, {$length}
                    ";
            $temp = $db->fetchAll($sql);
            if (count($temp) == $length ) {
                $result[]= [
                    'type'  => $label_row['id'],
                    'title' => $label_row['name']."专区",
                    'subtitle' => "更多{$label_row['name']}视频",
                    'list'  => $this->help_simple($temp, $uid),
                ];
            }
        }
        return ['code'=>1, 'data'=>$result,'sort_scheme' => $sort_scheme ];
    }
    
    private function help_simple($DBList, $uid)
    {
        $Data = array();
        foreach ($DBList as $DB)
        {
            $temp = \BBExtend\model\RecordDetail::find( $DB['id'] );
            
            $Data[]= $temp->get_simple();
        }
        return $Data;
    }
    
    private function help($DBList, $uid)
    {
        $Data = array();
        foreach ($DBList as $DB)
        {
            $temp = \BBExtend\model\RecordDetail::find( $DB['id'] );
            $temp->self_uid = $uid;
            $Data[]= $temp->get_all();
        }
        return $Data;
    }
    
    
    //获得短视频的视频列表
    /**
     * 说明 type=1 今日打榜
     * type=2 大家都在看。
     *
     * @param unknown $uid
     * @param unknown $startid
     * @param unknown $length
     */
    public  function movies($uid=0,$startid=0,$length = 4,$type=1)
    {
        if (\app\user\model\Exists::userhExists($uid)!=1) {
            return ['code'=>0, 'message'=>'用户不存在' ];
        }
        $startid=intval($startid);
        $length=intval($length);
        $type=intval($type);
        $uid = intval($uid);
        
        $DBList = array();
        $db = Sys::get_container_db();
         if ($type==10000) {
        $sql="select * from bb_record
        where type in (1,2)
        and audit=1
        and is_remove=0
    and exists(
select 1 from bb_users where bb_users.uid=bb_record.uid
   and bb_users.role=3
)
        and usersort in (1,2,3)
        order by time desc
        limit {$startid}, {$length}
        ";
         }
        elseif ($type==10001) {//1今日打榜，2大家看
            $sql="select * from bb_record
            where type in (1,2)
            and audit=1
            and is_remove=0
    
            and usersort in (1,2,3)
            order by heat desc
            limit {$startid}, {$length}
            ";
        }
        else {//1今日打榜，2大家看
    
            $sql="select * from bb_record
            where type in (1,2)
            and audit=1
            and is_remove=0
    
            and usersort =2
             and label='{$type}'
            order by `time` desc
            limit {$startid}, {$length}
            ";
        }
    
    
        $DBList = $db->fetchAll($sql);
        // }
         
        $Data = array();
        foreach ($DBList as $DB)
        {
            $temp = \BBExtend\model\RecordDetail::find( $DB['id'] );
            $temp->self_uid = $uid;
            $Data[]= $temp->get_all();
          //  $Data []= BBRecord::get_subject_detail_by_row($DB, $uid);
        }
    
        $is_bottom = (count($Data) == $length)?0:1;
    
        return ['code'=>1, 'data'=>['is_bottom' => $is_bottom,'list' => $Data ]];
    }
    
    
}