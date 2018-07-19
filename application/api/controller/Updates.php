<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\UserUpdates;

/**
 * 童星排行
 * 
 * @author xieye
 *
 */
class Updates
{
    public function comment_list()
    {
        
    }
     
    
    
    
    public function add($word='',$pic_json='', $style=0, $uid, $token='',$baidu_citycode='')
    {
        $db = Sys::get_container_db();
        if ($style==2 ) {
            UserUpdates::insert_word($uid, $word,$baidu_citycode);
        }
        
        if ( $style == 3 || $style==5 ) {
          //  Sys::debugxieye($pic_json );
            $pic_arr = json_decode($pic_json,1  );
         //   Sys::debugxieye($pic_arr );
            if (isset ( $pic_arr[0]['pic_width']) && isset ( $pic_arr[0]['pic_height']) 
                    && isset ( $pic_arr[0]['url'])    ) {
                        UserUpdates::insert_pic($uid, $word, $pic_arr,$baidu_citycode);
            } else {
                return ['code'=>0,'message' =>'参数错误' ];
            }
        }
        
        return ['code'=>1];
    }
    
    
    public function detail($updates_id)
    {
        $updates = UserUpdates::find( $updates_id );
        $updates->incr_click_count();
        $temp = $updates->list_info();
        
        return ['code'=>1, 'data' => $temp ];
    }
    
    //动态,1发现，2星动态。3发现关注，4发现同城，5 品牌馆动态
    public function index($uid=10000,$startid=0, $length=10,$type=1,$baidu_citycode='', $keyword='')
    {
        $startid=intval($startid);
        $length=intval($length);
        $uid = intval($uid);
        

        $db = Sys::get_container_dbreadonly();
        
        if ($type==1) {
        $sql="select * from bb_users_updates 
   where status=1
   order by create_time desc limit ?,?";
        $result = $db->fetchAll($sql,[ $startid, $length ]);
        if ($keyword) {
           $keyword = \BBExtend\common\Str::like($keyword);
           if ($keyword) {
               $sql="select * from bb_users_updates
   where status=1
     and  exists(
    select 1 from bb_users_updates_media
     where bb_users_updates_media.bb_users_updates_id =bb_users_updates.id 
       and bb_users_updates_media.type=1
       and bb_users_updates_media.word like '%{$keyword}%'
  )
   order by create_time desc limit ?,?";
               $result = $db->fetchAll($sql,[ $startid, $length ]);
               
           }else {
               $result=[];
           }
        }
        
        }
        if ($type==2) {
            $sql="select * from bb_users_updates 
              where agent_uid >0 and  status=1
              order by create_time desc limit ?,?";
            $result = $db->fetchAll($sql,[ $startid, $length ]);
        }
        
        if ($type==3) {
            $sql="select * from bb_users_updates
              where exists (
                select 1 from bb_focus 
                 where bb_focus.uid = ?
                   and bb_focus.focus_uid = bb_users_updates.uid

              )
              and  status=1
              order by create_time desc limit ?,?";
            $result = $db->fetchAll($sql,[$uid, $startid, $length ]);
        }
        if ($type==4  ) {
            
            
            $sql="select * from bb_users_updates
              where baidu_citycode=?
              and  status=1
              order by create_time desc limit ?,?";
            $result = $db->fetchAll($sql,[ $baidu_citycode,  $startid,  $length ]);
        }
        if ($type==5  ) {
            
            
            $sql="select * from bb_users_updates
              where exists (
                select 1 from bb_users 
                 where bb_users.uid = bb_users_updates.uid
                   and bb_users.role = 4

              )
              and  status=1
              order by create_time desc limit ?,?";
            $result = $db->fetchAll($sql,[ $baidu_citycode,  $startid,  $length ]);
        }
       
        
        
        $new=[];
        foreach ($result as $v) {
            $id = $v['id'];
            $updates = UserUpdates::find( $id );
            $temp = $updates->list_info($uid);
            $new[]= $temp;
        }
        
      
        
        return [
                'code'=>1,
                'data'=>[
                        'list' =>$new,
                        'is_bottom' =>(count($new) == $length)?0:1,
                ]
        ];
        
        
    }
    

}


