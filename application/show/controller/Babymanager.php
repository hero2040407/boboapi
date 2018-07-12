<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/2
 * Time: 10:06
 */

namespace app\show\controller;
use think\Db;
use BBExtend\BBShow;

class Babymanager 
{
    public function get_baby_show_list()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?input('param.startid'):0;
        $length = input('?param.length')?input('param.length'):20;
        $type = input('?param.type')?input('param.type'):100;
        $address = input('?param.address')?input('param.address'):'';
        
        $nickname=input('?param.nickname')?input('param.nickname'):'';
        $title=input('?param.titile')?input('param.title'):'';
        
        if ($type==102) {
            return $this->get_type_new($uid, $start_id, $length);
        }
        
        $obj = new BBShow();
//         $obj->nickname = $this->filter_str($nickname);
//         $obj->title =  $this->filter_str( $title  );
        
        $ListDB = $obj->get_show($uid,2,$start_id,$length,0,$type,$address);
        if (count($ListDB) == $length)
        {
            return ['data'=>$ListDB,'is_bottom'=>0,'code'=>1];
        }
        else
        {
            return ['data'=>$ListDB,'is_bottom'=>1,'code'=>1];
        }
    }
    
    // 2018 04 新接口
    public function get_baby_show_list_v2()
    {
        \BBExtend\Sys::display_all_error();
        
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?input('param.startid'):0;
        $length = input('?param.length')?input('param.length'):20;
        $type = input('?param.type')?input('param.type'):100;
        $address = input('?param.address')?input('param.address'):'';
        
        $nickname=input('?param.nickname')?input('param.nickname'):'';
        $title=input('?param.titile')?input('param.title'):'';
        
        if ($type==102) {
            return $this->get_type_new_v2($uid, $start_id, $length);
        }
        
        $obj = new \BBExtend\video\BBShow();
        $ListDB = $obj->get_show($uid,2,$start_id,$length,0,$type,$address);
        if (count($ListDB) == $length)
        {
            return ['data'=>['list' =>  $ListDB,'is_bottom'=>0 ]  ,'code'=>1];
        }
        else
        {
            return ['data'=>['list' =>  $ListDB,'is_bottom'=>1 ]  ,'code'=>1];
        }
    }
    
    
    
    private function get_type_new_v2($uid,$startid,$length)
    {
        $uid = intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        $db = \BBExtend\Sys::get_container_db();
        $sql="(select bb_push.id from bb_push
                        where  event='publish'
                          and exists (
                          select 1 from bb_users
                           where bb_users.uid = bb_push.uid
                             and bb_users.not_zhibo=0
                        )
                        and not exists(
                             select 1 from bb_users_test
                              where bb_users_test.uid = bb_push.uid
                          )
                         order by bb_push.time desc
                         limit 99999999
)
union all
(
select id+1000000 as id from bb_record
                where type =1
                  and usersort= 2
                  and audit=1
                  and is_remove=0
                 order by bb_record.time desc
                 limit 99999999
)
limit {$startid},{$length}
                ";
        $ids = $db->fetchCol($sql);
        $new=[];
        foreach ($ids as $id) {
            if ($id < 1000000) {
                // 这是直播
//                 $sql="select * from bb_push where id={$id}";
//                 $push_arr = $db->fetchRow($sql);
//                 $new[] = \BBExtend\BBPush::get_detail_by_row($push_arr, $uid);
                $temp = \BBExtend\model\PushDetail::find( $id );
                $temp->self_uid = $uid;
                $new[]= $temp->get_all();
                
            }else {
//                 $sql="select * from bb_record where id={$id}-1000000";
//                 $push_arr = $db->fetchRow($sql);
//                 $new[] = \BBExtend\BBRecord::get_detail_by_row($push_arr,$uid);
                
                $temp = \BBExtend\model\RecordDetail::find( $id-1000000 );
                $temp->self_uid = $uid;
                $new[]= $temp->get_all();
            }
            
        }
        
        $is_bottom = ( count( $new )== $length )? 0:1;
        
       return ['data'=>['list' =>  $new,'is_bottom'=>$is_bottom ]  ,'code'=>1];
//         return ['data'=>$new,'is_bottom'=>0,'code'=>1];
    }
    
    
    
    
    
    
    
    
    
    
    
    
    public function get_type_new($uid,$startid,$length)
    {
        $uid = intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        $db = \BBExtend\Sys::get_container_db();
        $sql="(select bb_push.id from bb_push
                        where  event='publish'
                          and exists (
                          select 1 from bb_users
                           where bb_users.uid = bb_push.uid
                             and bb_users.not_zhibo=0
                        )
                        and not exists(
                             select 1 from bb_users_test
                              where bb_users_test.uid = bb_push.uid
                          )
                         order by bb_push.time desc
                         limit 99999999
)
union all
(
select id+1000000 as id from bb_record
                where type =1
                  and usersort= 2
                  and audit=1
                  and is_remove=0
                 order by bb_record.time desc
                 limit 99999999
)
limit {$startid},{$length}
                ";
        $ids = $db->fetchCol($sql);
        $new=[];
        foreach ($ids as $id) {
            if ($id < 1000000) {
                // 这是直播
                $sql="select * from bb_push where id={$id}";
                $push_arr = $db->fetchRow($sql);
                $new[] = \BBExtend\BBPush::get_detail_by_row($push_arr, $uid);
            }else {
                $sql="select * from bb_record where id={$id}-1000000";
                $push_arr = $db->fetchRow($sql);
                $new[] = \BBExtend\BBRecord::get_detail_by_row($push_arr,$uid);
                
            }
            
        }
        
        return ['data'=>$new,'is_bottom'=>0,'code'=>1];
    }
    
    
    //谢烨20160926 ，过滤like
    private static   function filter_str($s)
    {
        //先把换行改成空格
        $pattern = '/(\r\n|\n)/';
        $s = preg_replace($pattern, '', $s);
        //20-7e 包括了0－9a-zA-Z空格，英文标点。是ascii表的主要一部分
        // 4e00- 9fa5 全部汉字，但不含中文标点
        $pattern = '/[^\x{4e00}-\x{9fa5}]/u';
        $s = preg_replace($pattern, '', $s);
        return $s;
    }
    
}