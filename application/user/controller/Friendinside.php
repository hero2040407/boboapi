<?php
/**
 * 交友模块
 */

namespace app\user\controller;

use BBExtend\Sys;
use BBExtend\common\Date;

class Friendinside
{
    
    public function comrade_list($uid, $startid=0,$length = 10)
    {
        $uid = intval( $uid );
        $length = intval( $length );
        $startid = intval( $startid );
        
        $db = Sys::get_container_dbreadonly( );
        
        $sql = "select hobby_id from bb_user_hobby
               where uid=? limit 1";
        $hobby_id = $db->fetchOne( $sql, $uid );
        if (! $hobby_id) {
            $hobby_id = 1;
        }
        
        $day30pre = Date::pre_day_start( 60 ); // 1个月内
        // 查大v
        $sql = "select uid from bb_users
 where  permissions=1 
  and exists (
       select 1 from bb_user_hobby
        where bb_user_hobby.uid = bb_users.uid
          and bb_user_hobby.hobby_id = {$hobby_id}
      )

  and exists (
                select 1 from bb_record
                where bb_record.uid = bb_users.uid
                and bb_record.time >{$day30pre}
      )
 and uid!= {$uid}
 and not exists(
   select 1 from bb_focus
    where bb_focus.uid = {$uid}
      and bb_focus.focus_uid = bb_users.uid
)
order by login_time desc
limit {$startid},{$length}

";
        $list = $db->fetchAll( $sql );
        $new=[];
        foreach ($list as $v) {
            $detail = \BBExtend\model\UserDetail::find( $v['uid'] );
            $new[]=   $detail->get_jiav();
        }
        $is_bottom = ( count( $new )== $length ) ? 0:1;
        return [
                'code' => 1,
                'data' => [
                        'list' => $new,
                        'is_bottom' =>$is_bottom,
                ]
        ];
        
    }
    

    /**
     * 交友首页
     */
    public function vip_index ( $uid,  $startid=0,$length = 10 )
    {
 //      Sys::display_all_error();
       
        $uid = intval( $uid );
        $length = intval( $length );
        $startid = intval( $startid );
        
        $db = Sys::get_container_dbreadonly( );
        
        $sql = "select uid from bb_users
 where  role=3
 and not_login=0 
 and not exists(
  select 1 from bb_focus
   where bb_focus.uid = ?
    and bb_focus.focus_uid = bb_users.uid
)
and uid != ?
order by login_time desc
limit {$startid},{$length}

";
        $result = $db->fetchAll($sql,[ $uid,$uid ]);
        
         $new =[];
        foreach ($result as $k => $v) {
            $detail = \BBExtend\model\UserDetail::find( $v['uid'] );
            $new[]=   $detail->get_jiav();
        }
        
        return [
                'code' => 1,
                'data' => [
                        'list' => $new,
                        'is_bottom' => ( count($new)==$length )? 0:1,
                ]
        ];
    }

   

}

