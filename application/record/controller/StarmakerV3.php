<?php
/**
 * 短视频星推官模块
 */

namespace app\record\controller;
use think\Config;
use BBExtend\model\User;
use BBExtend\model\Record;
use BBExtend\model\RecordInviteStarmaker;
use BBExtend\model\RecordInviteStarmakerFail;
use BBExtend\model\Starmaker as St;

use BBExtend\Currency;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\message\Message;
use BBExtend\fix\MessageType;
use BBExtend\fix\TableType;


use BBExtend\model\UserStarmaker;
use BBExtend\BBRecord;

class StarmakerV3
{
    /**
     * 新版导师首页。
     * 
     * 包含3部分，最新导师10个，最新视频10个，排行榜3个！
     * 
     * 
     */
    public function index($starmaker_count=10, $record_count=10){
        // 
        $db = Sys::get_container_dbreadonly();
        $sql="
 select bb_record.* from bb_record
where  type != 3
and audit =1
and is_remove=0
and  exists(
  select 1 from bb_record_invite_starmaker
    where bb_record_invite_starmaker.record_id = bb_record.id
     and bb_record_invite_starmaker.new_status = 4
)
  order by bb_record.id desc
  limit 0 ,?
";
        $result = $db->fetchAll( $sql,[ $record_count ]);
        $new=[];
        foreach ($result as $k => $v) {
            $temp =  \BBExtend\BBRecord::get_detail_by_row($v, 10000);
            //  $temp['audit'] = $v['audit'];
            
            $new[]=$temp;
        }
        $record_list = $new;
        
        
        // 下面是最新导师
        $sql="select * from  bb_users_starmaker
  where is_show=1
and exists (
                 select 1 from bb_users where bb_users.uid = bb_users_starmaker.uid
                    and  bb_users.role=2
               )
   order by id desc
   limit 0,?
";
        $result = $db->fetchAll( $sql,[ $starmaker_count ]);
        $new=[];
        foreach ($result as $v) {
            $temp=[];
            $user = \BBExtend\model\User::find($v['uid']);
            $temp['uid'] = $v['uid'];
            $temp['pic'] = $user->get_userpic() ;
            $temp['nickname'] = $user->get_nickname() ;
            $temp['sex'] = $user->get_usersex() ;
            $temp['level'] = $v['level'] ;
            $temp['info'] = $v['info'] ;
            
            $temp['fans_count'] = 0 ;
            $temp['income'] =  $v['income'] ;
            
            $temp['role'] = $user->role;
            $temp['frame'] = $user->get_frame();
            $temp['badge'] = $user->get_badge();
            
            
            
            $new[] = $temp;
        }
        $people_new = $new;
        
        // 下面是排行帮导师，固定3个
        $sql="select * from  bb_users_starmaker
  where is_show=1
and exists (
                 select 1 from bb_users where bb_users.uid = bb_users_starmaker.uid
                    and  bb_users.role=2
               )
   order by income desc
   limit 3
";
        $result = $db->fetchAll( $sql);
        $new=[];
        foreach ($result as $v) {
            $temp=[];
            $user = \BBExtend\model\User::find($v['uid']);
            $temp['uid'] = $v['uid'];
            $temp['pic'] = $user->get_userpic() ;
            $temp['nickname'] = $user->get_nickname() ;
            $temp['sex'] = $user->get_usersex() ;
            $temp['level'] = $v['level'] ;
            
            $temp['fans_count'] = 0 ;
            $temp['income'] =  $v['income'] ;
            
            
            $temp['role'] = $user->role;
            $temp['frame'] = $user->get_frame();
            $temp['badge'] = $user->get_badge();
            
            
            $new[] = $temp;
        }
        $ranking_new = $new;
        
        return [
                'code'=>1,
                'data'=>[
                        'record_list'=>$record_list,
                        'new_list' => $people_new,
                        'ranking_list' => $ranking_new,
                ]
                
        ];
        
    }
    
    
    
    public function index_v2($starmaker_count=10, $record_count=10){
        //
        $db = Sys::get_container_dbreadonly();
        $sql="
 select bb_record.* from bb_record
where  type != 3
and audit =1
and is_remove=0
and  exists(
  select 1 from bb_record_invite_starmaker
    where bb_record_invite_starmaker.record_id = bb_record.id
     and bb_record_invite_starmaker.new_status = 4
)
  order by bb_record.id desc
  limit 0 ,?
";
        $result = $db->fetchAll( $sql,[ $record_count ]);
        $new=[];
        foreach ($result as $k => $v) {
//             $temp =  \BBExtend\BBRecord::get_detail_by_row($v, $uid);
//             //  $temp['audit'] = $v['audit'];
            
//             $new[]=$temp;
            
            $temp = \BBExtend\model\RecordDetail::find( $v['id'] );
            $temp->self_uid = 10000;
            $new[]= $temp->get_all();
            
            
        }
        $record_list = $new;
        
        
        // 下面是最新导师
        $sql="select * from  bb_users_starmaker
  where is_show=1
   order by id desc
   limit 0,?
";
        $result = $db->fetchAll( $sql,[ $starmaker_count ]);
        $new=[];
        foreach ($result as $v) {
            $temp=[];
            $user = \BBExtend\model\User::find($v['uid']);
            $temp['uid'] = $v['uid'];
            $temp['pic'] = $user->get_userpic() ;
            $temp['nickname'] = $user->get_nickname() ;
            $temp['sex'] = $user->get_usersex() ;
            $temp['level'] = $v['level'] ;
            $temp['info'] = $v['info'] ;
            
            $temp['fans_count'] = 0 ;
            $temp['income'] =  $v['income'] ;
            
            $temp['role'] = $user->role;
            $temp['frame'] = $user->get_frame();
            $temp['badge'] = $user->get_badge();
            
            
            
            $new[] = $temp;
        }
        $people_new = $new;
        
        // 下面是排行帮导师，固定3个
        $sql="select * from  bb_users_starmaker
  where is_show=1
   order by income desc
   limit 3
";
        $result = $db->fetchAll( $sql);
        $new=[];
        foreach ($result as $v) {
            $temp=[];
            $user = \BBExtend\model\User::find($v['uid']);
            $temp['uid'] = $v['uid'];
            $temp['pic'] = $user->get_userpic() ;
            $temp['nickname'] = $user->get_nickname() ;
            $temp['sex'] = $user->get_usersex() ;
            $temp['level'] = $v['level'] ;
            
            $temp['fans_count'] = 0 ;
            $temp['income'] =  $v['income'] ;
            
            
            $temp['role'] = $user->role;
            $temp['frame'] = $user->get_frame();
            $temp['badge'] = $user->get_badge();
            
            
            $new[] = $temp;
        }
        $ranking_new = $new;
        
        return [
                'code'=>1,
                'data'=>[
                        'record_list'=>$record_list,
                        'new_list' => $people_new,
                        'ranking_list' => $ranking_new,
                ]
                
        ];
        
    }
    
    
    
    
    
    
    /**
     * 这是新的导师首页，精彩视频
     * @param number $startid
     * @param number $length
     * @return number[]|number[][]|number[][][][]|string[][][][]|boolean[][][][]|NULL[][][][]|unknown[][][][]|mixed[][][][]|unknown[][][][][]|string[][][][][]
     */
    public function record_list($startid=0, $length=10 )
    {
        $db = Sys::get_container_dbreadonly();
        $sql="
 select bb_record.* from bb_record
where  type != 3
and audit =1
and is_remove=0
and  exists(
  select 1 from bb_record_invite_starmaker
    where bb_record_invite_starmaker.record_id = bb_record.id
     and bb_record_invite_starmaker.new_status = 4
)
  order by bb_record.id desc
  limit ? ,?
";
        $result = $db->fetchAll( $sql,[ $startid, $length ]);
        $new=[];
        foreach ($result as $k => $v) {
            $temp =  \BBExtend\BBRecord::get_detail_by_row($v, $uid);
          //  $temp['audit'] = $v['audit'];
            
            $new[]=$temp;
        }
        return [
                'code'=>1,
                'data' =>[
                        'is_bottom' =>( count($new) == $length )?0:1,
                        'list' =>$new,
                ]
        ];
        
        
        
        
    }
    
    
    
    
    
    public function record_list_v2($startid=0, $length=10 )
    {
        $db = Sys::get_container_dbreadonly();
        $sql="
 select bb_record.* from bb_record
where  type != 3
and audit =1
and is_remove=0
and  exists(
  select 1 from bb_record_invite_starmaker
    where bb_record_invite_starmaker.record_id = bb_record.id
     and bb_record_invite_starmaker.new_status = 4
)
  order by bb_record.id desc
  limit ? ,?
";
        $result = $db->fetchAll( $sql,[ $startid, $length ]);
        $new=[];
        foreach ($result as $k => $v) {
            //$temp =  \BBExtend\BBRecord::get_detail_by_row($v, $uid);
            //  $temp['audit'] = $v['audit'];
            
            $temp = \BBExtend\model\RecordDetail::find( $v['id'] );
            $temp->self_uid = 10000;
            $new[]= $temp->get_all();
            
            
            
            //$new[]=$temp;
        }
        return [
                'code'=>1,
                'data' =>[
                        'is_bottom' =>( count($new) == $length )?0:1,
                        'list' =>$new,
                ]
        ];
        
        
        
        
    }
    
    
    
    
    
    
    
}






