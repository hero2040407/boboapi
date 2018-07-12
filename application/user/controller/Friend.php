<?php
/**
 * 交友模块
 */

namespace app\user\controller;

use BBExtend\Sys;
use BBExtend\common\Date;

class Friend
{
    public $is_bottom;
    public $vedio_is_bottom;

    /**
     * 交友首页
     */
    public function index ( $uid, $sort_scheme = 0, $length = 10, $length2 = 10 )
    {
        $sort_scheme = intval( $sort_scheme );
        if ($sort_scheme == 0) {
            $sort_scheme = intval( date( "His" ) );
        }
        $uid = intval( $uid );
        $length = intval( $length );
        $length2 = intval( $length2 );
        
        $list = $this->get_xingqu_list( $uid, $sort_scheme, 0, $length );
        // xieye,显示6个我关注的人。
        $help = \BBExtend\user\Focus::getinstance($uid);
        $list22 =  $help->get_guanzhu_list();
        $new2=[];
        $i=0;
        foreach ( $list22  as $uid22 ) {
            if ($uid22) {
                $i++;
                $user = \BBExtend\model\User::find( $uid22 );
                $temp=[];
                $temp['pic'] = $user->get_userpic();
                $temp['role'] = $user->role;
                $temp['badge'] = $user->get_badge();
                $temp['uid'] = $user->uid;
                $temp['new_movie_count'] = $this->query_not_read($uid, $uid22);
                $new2[]= $temp;
                if ($i >= 7) {
                    break;
                }
            }
        }
        
        
        return [
                'code' => 1,
                'data' => [
                        'sort_scheme' => $sort_scheme,
                        'list' => $list,
                        'list_guanzhu' => $new2,
                        'list_video' => $this->get_video_list( $uid, $sort_scheme, 0, $length2 )
                ]
        ];
    }
    
    // 查未读短视频数量，我自己加一个月限制。
    private function query_not_read($uid, $target_uid)
    {
        $db = Sys::get_container_dbreadonly();
        $uid = intval($uid);
        $target_uid = intval( $target_uid );
        $day30pre = Date::pre_day_start( 30 );
        $sql="
select count(*) from bb_record
            where uid={$target_uid}
            and  type !=3
            and audit=1
            and is_remove=0
            and time > {$day30pre}
            and not exists (select 1 from  bb_moive_view_unique_log
            where bb_moive_view_unique_log.target_uid = {$target_uid}
            and bb_moive_view_unique_log.uid = {$uid}
            and bb_moive_view_unique_log.movie_id = bb_record.id
            and bb_moive_view_unique_log.create_time > {$day30pre}
)
";
        $count = $db->fetchOne($sql);
        return intval($count  );
        
    }

    // 我感兴趣的人的列表
    public function comrade_list ( $uid, $sort_scheme, $startid = 0, $length = 10 )
    {
        $list = $this->get_xingqu_list( $uid, $sort_scheme, $startid, $length );
        return [
                'code' => 1,
                'data' => [
                        'list' => $list,
                        'is_bottom' =>$this->is_bottom,
                ],
        ];
    }

    /**
     * 你可能感兴趣的人，包括所有大v， 和我有同样兴趣的人。
     */
    private function get_xingqu_list ( $uid, $sort_scheme, $startid = 0, $length = 10 )
    {
        $uid = intval( $uid );
        $sort_scheme = intval( $sort_scheme );
        $startid = intval( $startid );
        $length = intval( $length );
        
        $startid=0;
        $length= 20;
        
        $db = Sys::get_container_dbreadonly( );
        
        $sql = "select hobby_id from bb_user_hobby
               where uid=? limit 1";
        $hobby_id = $db->fetchOne( $sql, $uid );
        if (! $hobby_id) {
            $hobby_id = 1;
        }
        $day30pre = Date::pre_day_start( 60 ); // 1个月内
                                             // 查大v
        $sql = "select uid,pic,nickname,role from bb_users
 where permissions=1 and
exists (
  select 1 from bb_user_hobby
   where bb_user_hobby.uid = bb_users.uid
    and bb_user_hobby.hobby_id = {$hobby_id}
)
and role !=3
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
order by crc32(uid + {$sort_scheme})
limit {$startid},{$length}

";
        // echo $sql;
        $list = $db->fetchAll( $sql );
        
        
        $sql = "select uid,pic,nickname,role from bb_users
 where  role=3 
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
order by crc32(uid + {$sort_scheme})
limit {$startid},{$length}

";
        // echo $sql;
        $list2 = $db->fetchAll( $sql );
        $list3=[];
        while (true) {
            //$hasa = $hasb =0;
            $a = array_shift($list2  );
            if ($a) {
               $list3[]= $a;
            }
            $b = array_shift($list  );
            if ($b) {
                $list3[]= $b;
            }
            if ( !$a && !$b ) {
                break;
            }
        }
        
        
        
        $new = [];
        foreach ($list3 as $k => $v) {
            if ($v['role'] == 3) {
                $v['info'] = '加V用户推荐';
            } else {
                $v['info'] = '志同道合的人';
            }
            $v['pic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v['pic'] );
            
            // 加入badge，
            if ($v['role'] == 3) {
                $v['badge'] = \BBExtend\fix\Pic::VIP;
            }elseif ( $v['role'] == 2 ) {
                $temp = \BBExtend\model\User::find( $v['uid'] );
                $v['badge'] =$temp->get_badge();
            }else {
                $v['badge'] ='';
            }
            
            $new[] = $v;
        }
        
        $this->is_bottom=1;
        if (count($new) == $length ) {
            $this->is_bottom=0;
        }
        
        return $new;
    
    }

    // 视频列表
    public function video_list ( $uid, $sort_scheme=0, $startid = 0, $length = 10 )
    {
        return [
                'code' => 1,
                'data' => [
                        'list' => $this->get_video_list( $uid, $sort_scheme, $startid, $length ),
                        'is_bottom' =>$this->vedio_is_bottom,
                ]
        ];
    }

    private function get_video_list ( $uid, $sort_scheme, $startid = 0, $length = 10 )
    {
        
        $uid = intval( $uid );
        $sort_scheme = intval( $sort_scheme );
        $startid = intval( $startid );
        $length = intval( $length );
        
        $db = Sys::get_container_dbreadonly();
//         $sql = "select id from bb_record where 
//          exists(
//            select 1 from bb_users 
//             where bb_users.uid = bb_record.uid
//               and bb_users.role=3
              
// )
//          and is_remove=0 and audit=1
//          and bb_record.uid !=?
//          order by crc32(id + {$sort_scheme})
//           limit {$startid},{$length}
// ";

        $sql = "select id from bb_record where
         exists(
           select 1 from bb_focus
            where bb_focus.uid = ?
              and bb_focus.focus_uid = bb_record.uid
)
         and is_remove=0 and audit=1
         and type in (1,2,4)
         order by time desc
          limit {$startid},{$length}
";
        
        $new = [];
        $ids = $db->fetchCol( $sql, [
                $uid,
        ] );
        foreach ($ids as $id) {
            
            // 短视频
            $temp = \BBExtend\model\RecordDetail::find( $id );
            $temp->self_uid = $uid;
            $new[] = $temp->get_all( );
        }
        
        $this->vedio_is_bottom = 1;
        if (count($new) == $length ) {
            $this->vedio_is_bottom = 0;
        }
        
        return $new;
    }

}

