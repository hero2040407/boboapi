<?php
/**
 * 推荐栏目
 */

namespace app\show\controller;

use think\Db;
use BBExtend\Sys;
use BBExtend\BBRecord;
use BBExtend\BBPush;
use BBExtend\DbSelect;

class Recommend
{
    public $is_bottom;
    
    
   /**
    * 推荐，app首页。重要。
    * 直播部分已经移到 首页接口 /api/index/index
    * @param number $uid
    * @param number $startid
    * @param number $length
    */
    public function index($uid=0,$startid=0, $length=10) 
    {
        $uid=intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        $time1 = $this->microtime_float();
        
        // hanrea 20181017 直播已经放在 /api/index/index 接口 重新启动栏目功能
        // $zhibo_list = $this->zhibo_list($uid,$startid, $length);     // 谢烨，请单独写这句，勿删
        // $time2 = $this->microtime_float();
        // $cha = $time2 - $time1;
        // Sys::debug($uid." ==1== " . $cha);
        
        
        $subject_list = $this->subject_list($uid); // 谢烨，请单独写这句，勿删
        // $time3 = $this->microtime_float();
        // $cha2 = $time3 - $time2;
       //    Sys::debug($uid." ==1== " . $cha." ==2== " . $cha2);
        
        return [
            'code'=>1,
            'data' => [
                // 'zhibo_list' => $zhibo_list,
                // 'is_bottom'  => $this->is_bottom,
                'subject_list' => $subject_list,
             ]
        ];
    }
    
    
    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    
    
    
    public function zhibo($uid,$startid=0, $length=10)
    {
        $uid=intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        $zhibo_list = $this->zhibo_list($uid,$startid, $length);     // 谢烨，请单独写这句，勿删
        return [
            'code'=>1,
            'data' =>$zhibo_list,
            'is_bottom'=>$this->is_bottom,
        ];
    }
    
    
    public function zhibo_v2($uid,$startid=0, $length=10)
    {
        $uid=intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        $zhibo_list = $this->zhibo_list_v2($uid,$startid, $length);     // 谢烨，请单独写这句，勿删
        return [
                'code'=>1,
                
                'data' =>[ 'list'=>$zhibo_list, 'is_bottom'=>$this->is_bottom, ],
                
        ];
    }
    
    
    
    public function zhibo_friend($uid,$startid=0, $length=10)
    {
        $uid=intval($uid);
        $startid=intval($startid);
        $length=intval($length);
        $zhibo_list = $this->zhibo_list_friend($uid,$startid, $length);     // 谢烨，请单独写这句，勿删
        return [
            'code'=>1,
            'data' =>$zhibo_list,
            'is_bottom'=>$this->is_bottom,
        ];
    }
    
    
    public function subject($uid,$subject_id,$startid=0, $length=10)
    {
        $db = Sys::get_container_db();
        $uid = intval($uid);
        $subject_id = intval($subject_id);
        $startid = intval($startid);
        $length=intval($length);
        
        $sql="
              select bb_record.*,bb_subject_movie.subject_id from bb_record 
                left join bb_subject_movie
                on bb_subject_movie.room_id = bb_record.room_id
                where bb_subject_movie.id >0
                 and  bb_record.audit=1
                 and bb_record.is_remove=0
                
                and bb_subject_movie.subject_id = {$subject_id}
                and bb_subject_movie.is_recommend = 1
                
                 and exists(
                   select 1 from bb_subject
                     where bb_subject.is_show=1
                       and bb_subject.id = bb_subject_movie.subject_id
                   )
               order by bb_subject_movie.sort desc
                limit {$startid},{$length}
                ";
        $records = $db->fetchAll($sql);
        $result =[];
       
        foreach ($records as $record) {
//             if ($record['subject_id'] == $v['id'] ) {
                $result []= BBRecord::get_subject_detail_by_row($record, $uid);
//             }
        }

        $subrow = $db->fetchOne("select title from bb_subject where id = {$subject_id}" );
        return ['code'=>1,
            'data' => $result,
            'title'=> $subrow,
            'is_bottom' => count($result)== $length ? 0 :1,
        ];
    }
    
    
    private function zhibo_list($uid,$startid,$length)
    {
        $db = Sys::get_container_db();
        $startid = intval($startid);
        $length = intval($length);
        $uid = intval($uid);
        
        $dbe = Sys::get_container_db_eloquent();
        $sql="select count(*) from bb_users_test where uid=?";
        $is_test_user = DbSelect::fetchOne($dbe, $sql,[ $uid ]);
        if ($is_test_user) {// 测试帐号不受限
            $sql="select bb_push.* from bb_push
            left join bb_users
            on bb_users.uid = bb_push.uid
            where bb_push.event='publish'
            and bb_users.not_zhibo=0
            
            order by
            bb_push.heat desc,
            bb_users.permissions asc,
            time desc limit
            {$startid},{$length}
            ";
        }else { // 普通用户，受限制
        
        $sql="select bb_push.* from bb_push 
left join bb_users
on bb_users.uid = bb_push.uid
where bb_push.event='publish'
  and bb_users.not_zhibo=0
  and not exists(
     select 1 from bb_users_test
      where bb_users_test.uid = bb_push.uid
  )
order by 
bb_push.heat desc,
bb_users.permissions asc,
time desc limit 
                 {$startid},{$length}
                ";
        }
        $result = $db->fetchAll($sql);
        $new =[];
        foreach ($result as $v) {
            $new[]= BBPush::get_detail_by_row($v, $uid);
        }
        $this->is_bottom = (count($result )== $length) ? 0:1;
        return $new;
    }
    
    
    
    
    private function zhibo_list_v2($uid,$startid,$length)
    {
        $db = Sys::get_container_db();
        $startid = intval($startid);
        $length = intval($length);
        $uid = intval($uid);
        
        $dbe = Sys::get_container_db_eloquent();
        $sql="select count(*) from bb_users_test where uid=?";
        $is_test_user = DbSelect::fetchOne($dbe, $sql,[ $uid ]);
        if ($is_test_user) {// 测试帐号不受限
            $sql="select bb_push.* from bb_push
            left join bb_users
            on bb_users.uid = bb_push.uid
            where bb_push.event='publish'
            and bb_users.not_zhibo=0
            
            order by
            bb_push.heat desc,
            bb_users.permissions asc,
            time desc limit
            {$startid},{$length}
            ";
        }else { // 普通用户，受限制
            
            $sql="select bb_push.* from bb_push
left join bb_users
on bb_users.uid = bb_push.uid
where bb_push.event='publish'
  and bb_users.not_zhibo=0
  and not exists(
     select 1 from bb_users_test
      where bb_users_test.uid = bb_push.uid
  )
order by
bb_push.heat desc,
bb_users.permissions asc,
time desc limit
                 {$startid},{$length}
                ";
        }
        $result = $db->fetchAll($sql);
        $new =[];
        foreach ($result as $v) {
            
            $temp = \BBExtend\model\PushDetail::find( $v['id'] );
            $temp->self_uid = $uid;
            $new[]= $temp->get_all();
            
          //  $new[]= BBPush::get_detail_by_row($v, $uid);
        }
        $this->is_bottom = (count($result )== $length) ? 0:1;
        return $new;
    }
    
    
    private function zhibo_list_friend($uid,$startid,$length)
    {
        $db = Sys::get_container_db();
        $startid = intval($startid);
        $length = intval($length);
        $uid = intval($uid);
        $sql="select * from bb_push where event='publish'
        and exists(select 1 from bb_focus
  where uid ={$uid} and bb_focus.focus_uid = bb_push.uid
)
and exists (
          select 1 from bb_users
            where bb_users.uid = bb_push.uid
              and bb_users.not_zhibo=0
        )

        order by time desc limit
        {$startid},{$length}
        ";
        $result = $db->fetchAll($sql);
        $new =[];
        foreach ($result as $v) {
            $new[]= BBPush::get_detail_by_row($v, $uid);
        }
        $this->is_bottom = (count($result )== $length) ? 0:1;
        return $new;
    }
        

    
    /**
     * 这是推荐首页，每个小栏目下面的视频，分组保存。
     * @param unknown $uid
     * @return unknown[]|number[][]|string[][]|boolean[][]|unknown[][]|NULL[][]
     */
    private function subject_list($uid)
    {
        $uid=intval($uid);
        $db = Sys::get_container_db();
        $sql ="select * from bb_subject 
                where is_show=1
                order by sort desc
                ";
        $subjects = $db->fetchAll($sql);

        $redis = Sys::getredis11();
        $key = "index:recommend:list";
        $records  = $redis->get($key);
        if (!$records) {
            
            $sql="
                  select bb_record.*,bb_subject_movie.subject_id from bb_record 
                    left join bb_subject_movie
                    on bb_subject_movie.room_id = bb_record.room_id
                    where bb_subject_movie.id >0
                     and  bb_record.audit=1
                     and bb_record.is_remove=0
                     and bb_subject_movie.is_recommend = 1
                     and exists(
                       select 1 from bb_subject
                         where bb_subject.is_show=1
                           and bb_subject.id = bb_subject_movie.subject_id
                       )
                    order by bb_subject_movie.sort desc
                    limit 800
                    ";
            $records = $db->fetchAll($sql); // 这是所有的推荐的短视频的集合
            $redis->set($key, serialize($records) );
            $redis->setTimeout($key,  60 );
        }else {
            $records = unserialize($records);
        }
        
        shuffle($records); // 2017 03 20
        $result =[];
        // 谢烨2017 03 运营要求，不能有重复，于是我加数组
        $unique_arr=[];
        
        foreach ($subjects as $v) {
            $subject=[
                'title' => $v['title'],
                'subtitle'=> $v['subtitle'],
                'subject_id' => $v['id'],
                'record_list' =>[],
            ];
            $i=0;
            foreach ($records as $record) {
                if ($record['subject_id'] == $v['id'] && (!in_array($record['id'], $unique_arr))  ) {
                    $i++;
                    if ($i ==3) {
                        break;
                    }
                    $subject['record_list'] []= BBRecord::get_subject_detail_by_row($record, $uid);
                    $unique_arr[]= $record['id'];
                   
                }
            }
           // Hanrea  结果每组有且只有两条数据
            if (count($subject['record_list'])==2) {
                $result[] = $subject;
            }  
        }

        return $result;
    }
    
   
    
    //谢烨20160926 ，过滤like
    private static   function filter_str($s)
    {
        //先把换行改成空格
        $pattern = '/(\r\n|\n)/';
        $s = preg_replace($pattern, '', $s);
        //20-7e 包括了0－9a-zA-Z空格，英文标点。是ascii表的主要一部分
        // 4e00- 9fa5 全部汉字，但不含中文标点
        $pattern = '/[^\x{4e00}-\x{9fa5}0-9a-zA-Z]/u';
        $s = preg_replace($pattern, '', $s);
        return $s;
    }
}