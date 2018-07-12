<?php
/**
 * Created by PhpStorm.
 * User: 谢烨
 */

namespace app\user\controller;
use think\Db;
use BBExtend\common\Date;
use BBExtend\Sys;
use BBExtend\user\Common;
use BBExtend\BBRecord;

// use BBExtend\user\Relation as Re;
class Focuson
{
    
    
    /**
     * 2017 04 新版 我可能感兴趣的人。
     */
    public function new_index($startid=0,$length=10, $self_uid=0 )
    {
        
        $db = Sys::get_container_db();
        $uid = $self_uid = intval($self_uid);
        
        
        $sql="select hobby_id from bb_user_hobby
               where uid=? limit 1";
        $hobby_id = $db->fetchOne($sql,$uid);
        if (!$hobby_id) {
            $hobby_id=1;
        }
        
        $startid = intval($startid);
        $length = intval($length);
        $day30pre = Date::pre_day_start(60);//1个月内
    
        $redis = Sys::getredis11();
        $key = "focuson:new_index:{$uid}";
        $key2 = "{$startid}_{$length}";
        $result  = $redis->hGet($key,$key2);
        
        $result=false;
        // 谢烨注意201708 ，原因是bb_record忘了给uid字段加缓存，加了就好了。
        if ( $result === false ) {
        
            $sql="
            select uid from bb_user_hobby
            where hobby_id ={$hobby_id}
            and exists (
                select 1 from bb_record
                where bb_record.uid = bb_user_hobby.uid
                
                and bb_record.time >{$day30pre}
            )
        
            and uid != {$uid}
            
            and not exists (select 1 from bb_focus 
               where bb_focus.uid = {$uid}
                  and bb_user_hobby.uid = bb_focus.focus_uid
                )
             
            limit {$startid}, {$length}
            ";
            $result = $db->fetchCol($sql);
            $redis->hSet($key, $key2, serialize( $result)); // 先保存一个小时。
            $redis->setTimeout($key,3600 * 24 );
        }else {
            $result = unserialize($result);
        }
        $new =[];
        foreach ($result as $id) {
           // $arr = [];
            $var= Common::get_xijie_hobby($id, $self_uid);
//             $arr['record_list'] = [];
//             // 谢烨，我得挑选最新的4部record
//             $sql="select * from bb_record where  type in (1,2)
//             and audit=1
//             and is_remove=0
//             and usersort in (1,2,3)
//             and uid = {$id}
//             order by time desc
//             limit 4
//             ";
//             $temp2 = $db->fetchAll($sql);
//             foreach ($temp2 as $v) {
//                 $arr['record_list'][]= BBRecord::get_subject_detail_by_row($v, $self_uid);
//             }
            $new[]= $var;
        }
        $is_bottom = (count($new)== $length) ? 0:1;
        return ['code'=>1, 'data'=>$new, 'is_bottom' => $is_bottom  ];
    }
    
    
    
    
    /**
     * 课程列表
     * @param number $startid
     * @param number $length
     * @param number $self_uid
     */
    public function movie_list($startid=0,$length=10, $self_uid=0 )
    {
         Sys::display_all_error();
        $uid = $self_uid = intval($self_uid);
        $startid=intval($startid);
        $length = intval($length);
        //首先，从历史表里查出你最喜欢什么类型的课程
        $db = Sys::get_container_db();
      
        $sql ="select (select name from bb_speciality 
         where id=hobby_id) name from bb_user_hobby where uid={$uid}";
        $names = $db->fetchCol($sql);
        $new_ids = [];
        $new_ids_0 ='';
        foreach ($names as $name) {
            $sql ="select id from bb_label where name=? ";
            $id = $db->fetchOne($sql,$name);
            if ($id) {
                $new_ids[]= strval($id);
                $new_ids_0 = strval($id);
                break;
            }
        }
     //   return 1;
        if ($new_ids) {
        
           $sql ="select * from bb_record where 
                type=1 and usersort=2
                and label = '{$new_ids_0}' 
                 and audit=1
                 and is_remove=0
                 order by time desc
                   limit {$startid},{$length}
                ";
    //      $sql= $db->quoteInto($sql, $new_ids);
         //  return 0;
           $DBList = $db->fetchAll($sql);
           
        }else {
            $sql ="select * from bb_record where
            type=1 and usersort=2
            and audit=1
            and is_remove=0
            order by time desc
            limit {$startid},{$length}
            ";
            
            $DBList = $db->fetchAll($sql);
        }
        $DBList = $db->fetchAll($sql);
    
       // $buy_help = new \BBExtend\user\Relation();
        //type=1 是所有秀场，usersort=1是学啥，audit=1审核过，
//         $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>1,
//             'label' => $label,
//             'audit'=>1,'is_remove'=>0])->order(['look'=>'desc'])
//             ->limit($startid,$length)->select();
    
            $Data = array();
            foreach ($DBList as $DB)
            {
                $temp = BBRecord::get_detail_by_row($DB,$self_uid);
//                 $temp['comment_count']=\BBExtend\BBComments::Get_comments_count(
//                           "bb_record_comments", $temp['id'] );  
                
                $Data[]= $temp;
            }
            $is_bottom = (count($Data)== $length) ? 0:1;
            return ['code'=>1, 'data' =>$Data,'is_bottom' => $is_bottom  ];
    }
    
    
    /**
     * 课程列表
     * @param number $startid
     * @param number $length
     * @param number $self_uid
     */
    public function course_list($startid=0,$length=10, $self_uid=0 )
    {
     
        $uid = $self_uid = intval($self_uid);
        //首先，从历史表里查出你最喜欢什么类型的课程
        $db = Sys::get_container_db();
        $sql ="select label from bb_moive_view_stats 
                where  uid = {$uid}
                  and  type=1
                  and usersort=1
                 order by view_count desc
                 limit 1
                ";
        $label = $db->fetchOne($sql);
        if ($label<=0) {
            $label = 10; //这是远程服务器最多的课程类短视频。
        }
        
        $buy_help = new \BBExtend\user\Relation();
        //type=1 是所有秀场，usersort=1是学啥，audit=1审核过，
        $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>1,
                        'label' => $label,
                        'audit'=>1,'is_remove'=>0])->order(['look'=>'desc'])
                        ->limit($startid,$length)->select();
            
        $Data = array();
        foreach ($DBList as $DB)
        {
           $Data[]= BBRecord::get_detail_by_row($DB,$self_uid);
        }
        $is_bottom = (count($Data)== $length) ? 0:1;
        return ['code'=>1, 'data' =>$Data,'is_bottom' => $is_bottom  ];
    }
    
    
    // 推荐小主播列表
    /**
     * 查和我有共同兴趣的人，按短视频更新时间排序。
     */
    public function anchor_list($startid=0,$length=10, $self_uid=0 )
    {
        $db = Sys::get_container_db();
        $uid = $self_uid = intval($self_uid);
        $startid = intval($startid);
        $length = intval($length);
        $day30pre = Date::pre_day_start(30);//1个月内
        
        $sql="
        select uid from bb_user_hobby
         where hobby_id in (select hobby_id from bb_user_hobby b
                             where b.uid={$uid}
                           ) 
          and exists (
           select 1 from bb_record 
            where bb_record.uid = bb_user_hobby.uid
              and bb_record.audit=1
              and bb_record.is_remove=0
              and bb_record.type in (1,2)
              and bb_record.time >{$day30pre}
             )
          
          and uid != {$uid}
             
        limit {$startid}, {$length}
        ";
        $result = $db->fetchCol($sql);
        $new =[];
        foreach ($result as $id) {
            $arr = [];
            $arr['anchor'] = Common::get_xijie_hobby($id, $self_uid);
            $arr['record_list'] = [];
            // 谢烨，我得挑选最新的4部record
            $sql="select * from bb_record where  type in (1,2)
                  and audit=1
                  and is_remove=0
                  and usersort in (1,2,3)
                  and uid = {$id} 
                  order by time desc
                  limit 4
                    ";
            $temp2 = $db->fetchAll($sql);
            foreach ($temp2 as $v) {
                $arr['record_list'][]= BBRecord::get_subject_detail_by_row($v, $self_uid);
            }
            $new[]= $arr;
        }
        $is_bottom = (count($new)== $length) ? 0:1;
        return ['code'=>1, 'data'=>$new, 'is_bottom' => $is_bottom  ];
    }
    
    
//    public function lahei($uid=0,$target_uid=0)
//    {
//        $help = new Re();
//        $result = $help->lahei($uid, $target_uid);
//        if ($result) {
//            return ['code'=>1];
//        }
//        return ['code'=>0, 'message'=>'您已拉黑过对方'];
//    }
   
   public function getone($self_uid=0)
   {
       $uid = $self_uid;
       $uid = intval($uid);
       if (\app\user\model\Exists::userhExists($uid)!=1) {
           return ['code'=>0, 'message'=>'用户不存在' ];
       }
       //查找我可能感兴趣的人，
       //要求1：有1个月内的短视频。
       //2，必须有一个兴趣与我相同。
       //3，必须不是我关注的人。
//        假设我的id10000，兴趣是2，6
//        查找100个和我兴趣相同的人。

       $day30pre = Date::pre_day_start(30);//1个月内
       
       //取50个，随机挑选一个。
       $sql="
               select uid from bb_user_hobby
where hobby_id in (select hobby_id from bb_user_hobby b
  where b.uid={$uid}
) and exists (
  select 1 from bb_record where 
   bb_record.uid = bb_user_hobby.uid
   and bb_record.audit=1
   and bb_record.is_remove=0
   and bb_record.type in (1,2)
   and bb_record.time >{$day30pre}
)and not exists (select 1 from bb_focus where 
    bb_focus.focus_uid = bb_user_hobby.uid
    and bb_focus.uid = {$uid}
 )
 and uid != {$uid}
limit 50
               ";
       $db = Sys::get_container_db();
       $uid_list = $db->fetchCol($sql);
       if ($uid_list) {
//            $key = array_rand($uid_list);
           $focuson_uid = $uid_list[array_rand($uid_list)];
           return ['code'=>1,
               'data' => Common::get_xijie($focuson_uid, $uid),
               
           ];
       }else {
           $focuson_uid=0;
           return ['code'=>0];
       }
       
       
       
   }
      
}
