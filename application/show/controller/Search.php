<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/6
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

class Search 
{
    public function movies()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        
        $length = input('?param.length')?(int)input('param.length'):20;
        $keyword = input('?param.keyword')? strval( input('param.keyword')):'';
//         if ($keyword) {
//             $keyword = $this->filter_str($keyword);
//         }
        
//         if (!$keyword) {
//             return ['message'=>'请填写有效的关键词','is_bottom'=>1,'code'=>0];
//         }
        
        //$obj = new BBShow();
        
        $ListDB = $this->get_show($uid,$start_id,$length,$keyword);
        if (count($ListDB) == $length)
        {
            return ['data'=>$ListDB,'is_bottom'=>0,'code'=>1];
        }
        else
        {
            return ['data'=>$ListDB,'is_bottom'=>1,'code'=>1];
        }
    }
    
    
    
    public function movies_v2()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        
        $length = input('?param.length')?(int)input('param.length'):20;
        $keyword = input('?param.keyword')? strval( input('param.keyword')):'';
        //         if ($keyword) {
        //             $keyword = $this->filter_str($keyword);
        //         }
        
        //         if (!$keyword) {
        //             return ['message'=>'请填写有效的关键词','is_bottom'=>1,'code'=>0];
        //         }
        
        //$obj = new BBShow();
        
        $ListDB = $this->get_show_v2($uid,$start_id,$length,$keyword);
        if (count($ListDB) == $length)
        {
            return ['data'=>['list' =>  $ListDB,'is_bottom'=>0 ]  ,'code'=>1];
        }
        else
        {
            return ['data'=>['list' =>  $ListDB,'is_bottom'=>1 ] ,'code'=>1];
        }
    }
    
    
    public function get_show_v2($uid,$limit_StartPos=0,$length = 20,$keyword)
    {
        $push_listDB =  self::get_show_list_v2($uid,$limit_StartPos,$length/2,$keyword);
        //    echo 1;
        $record_listDB = self::get_record_show_v2($uid,$limit_StartPos,$length/2,$keyword);
        
        $Data = array_merge($push_listDB,$record_listDB);
        $count_push = count($push_listDB);
        $count_record = count($record_listDB);
        if($count_push != $length/2)
        {
            $count = $length/2 - $count_push;
            $record_listDB = self::get_record_show_v2($uid, $limit_StartPos+$count_record,
                    $count,$keyword);
            $Data = array_merge($Data,$record_listDB);
        }
        if($count_record != $length/2)
        {
            $count = $length/2 - $count_record;
            $push_listDB =  self::get_show_list_v2($uid, $limit_StartPos+$count_push,$count,
                    $keyword);
            $Data = array_merge($Data,$push_listDB);
        }
        return $Data;
    }
    
    
    
    
    public function get_show($uid,$limit_StartPos=0,$length = 20,$keyword)
    {
        $push_listDB =  self::get_show_list($uid,$limit_StartPos,$length/2,$keyword);
    //    echo 1;
        $record_listDB = self::get_record_show($uid,$limit_StartPos,$length/2,$keyword);
        
        $Data = array_merge($push_listDB,$record_listDB);
        $count_push = count($push_listDB);
        $count_record = count($record_listDB);
        if($count_push != $length/2)
        {
            $count = $length/2 - $count_push;
            $record_listDB = self::get_record_show($uid, $limit_StartPos+$count_record,
                    $count,$keyword);
            $Data = array_merge($Data,$record_listDB);
        }
        if($count_record != $length/2)
        {
            $count = $length/2 - $count_record;
            $push_listDB =  self::get_show_list($uid, $limit_StartPos+$count_push,$count,
                    $keyword);
            $Data = array_merge($Data,$push_listDB);
        }
        return $Data;
    }
    
    
    public static function get_show_list($uid, $limit_StartPos=0,$length = 20,
            $keyword)
    {
        $DBList = array();
        
        $uid=intval($uid);
        $limit_StartPos=intval($limit_StartPos);
        $length=intval($length);
        
        
        $buy_help = new \BBExtend\user\Relation();
        $keyword_filter = self::filter_str($keyword);
        $db = Sys::get_container_db();
        if ($keyword_filter) {
        $sql="select * from bb_push where event='publish'
                 and (title like '%{$keyword_filter}%'
                 or
                 exists (select 1 from bb_users where bb_users.uid=bb_push.uid
                   and bb_users.nickname like '%{$keyword_filter}%'
                  )
                  or uid='{$keyword_filter}'
                 
                 )
                 and sort in (1,2,3)
                order by time desc
                limit {$limit_StartPos},{$length}
         
                ";
//                 $DBList = Db::table('bb_push')->where(['event'=>'publish'])
//                 ->order(['time'=>'desc'])->limit($limit_StartPos,$length)->select();
//                // break;
       // echo $sql; 
        $DBList = $db->fetchAll($sql);
        }
          
        if (!$DBList) {
            $sql="select * from bb_push where event='publish'
            and (
            exists (select 1 from bb_users where bb_users.uid=bb_push.uid
            and bb_users.nickname =?
            )
          
             
            )
            and sort in (1,2,3)
            order by time desc
            limit {$limit_StartPos},{$length}
             
            ";
            $DBList = $db->fetchAll($sql,$keyword);
        }
      //  }
        $Data = array();
        foreach ($DBList as $DB)
        {
            $DataDB['id'] = (int)$DB['id'] ;
            $DataDB['uid'] = (int)$DB['uid'] ;
    
            //谢烨20160922，加vip返回字段
            $DataDB['vip'] = \BBExtend\common\User::is_vip($DataDB['uid']) ;
    
            $DataDB['event'] = $DB['event'];
            $DataDB['pull_url'] = $DB['pull_url'];
            $DataDB['title'] = $DB['title'];
            $DataDB['label'] = (int)$DB['label'];
            $DataDB['login_address'] = $DB['address'];
            $DataDB['sex'] = User::get_usersex($DB['uid']);
            $DataDB['specialty'] = User::get_specialty($DB['uid']);
            $DataDB['time'] = $DB['time'];
            $DataDB['current_time'] = (string)time();
            $DataDB['is_focus'] = Focus::get_focus_state($uid,$DB['uid']);
            //显示在线观看人数以及点赞人数
            $RedisDB = BBRedis::getInstance('push')->hGetAll($DataDB['uid'].'push');
            if ($RedisDB)
            {
                    $DataDB['is_like'] = false;
    
                    $DataDB['like'] = (int)$RedisDB['like'];
                    $DataDB['people'] = (int)$RedisDB['people'] + 1 ;
            }
            $DataDB['nickname'] = User::get_nickname($DB['uid']);
            $Pic = $DB['bigpic'];
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            //如果没有http://
            if ($Pic)
            {
                $DataDB['bigpic'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                        $Pic );
               
            }else
            {
                $DataDB['bigpic'] = User::get_userpic($DB['uid']);
            }
            $DataDB['pic'] = User::get_userpic($DB['uid']);
            $DataDB['room_id'] = $DB['room_id'];
            $DataDB['age'] = User::get_userage($DB['uid']);
            $DataDB['type'] = 'push';
            $DataDB['push'] = true;
    
            //xieye 2016 10 购买课程
            $DataDB['price'] = (int)$DB['price'] ;
            $DataDB['price_type'] = (int)$DB['price_type'] ;
            $DataDB['has_buy'] = $buy_help->has_buy_video($uid, $DataDB['room_id']);
            $DataDB['is_lahei'] = $buy_help->has_lahei( $DataDB['uid'] , $uid );
            $DataDB['content_type'] = intval($DB['sort']);
            array_push($Data,$DataDB);
        }
        return $Data;
    }
    
   
    
    //获得短视频的视频列表
    public static function get_record_show($uid,$start_pos,$length = 20,$keyword)
    {
        $uid = intval($uid);
        $start_pos=intval($start_pos);
        $length=intval($length);
        
        $buy_help = new \BBExtend\user\Relation();
        $keyword_filter =  self::filter_str($keyword);
        $DBList = array();
        $db = Sys::get_container_db();
        if ($keyword_filter) {
        $sql="select * from bb_record
                where type in (1,2)
                and audit=1
                and is_remove=0
                and 
                (title like '%{$keyword_filter}%'
                 or
                 exists (select 1 from bb_users where bb_users.uid=bb_record.uid
                   and bb_users.nickname like '%{$keyword_filter}%'
                  )
                 or uid='{$keyword_filter}'
                 
                 )
                 and usersort in (1,2,3)
                order by time desc
                limit {$start_pos}, {$length}
                ";
        $DBList = $db->fetchAll($sql);
        }
        if (!$DBList) {
             $sql="select * from bb_record
                where type in (1,2)
                and audit=1
                and is_remove=0
                and 
                (
                 exists (select 1 from bb_users where bb_users.uid=bb_record.uid
                   and bb_users.nickname =?
                  )
                
                 
                 )
                 and usersort in (1,2,3)
                order by time desc
                limit {$start_pos}, {$length}
                ";
            $DBList = $db->fetchAll($sql,$keyword);
        }
        
        $Data = array();
        foreach ($DBList as $DB)
        { 
            $Data []= BBRecord::get_subject_detail_by_row($DB, $uid);
        }
        return $Data;
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public static function get_show_list_v2($uid, $limit_StartPos=0,$length = 20,
            $keyword)
    {
        $DBList = array();
        
        $uid=intval($uid);
        $limit_StartPos=intval($limit_StartPos);
        $length=intval($length);
        
        
        $buy_help = new \BBExtend\user\Relation();
        $keyword_filter = self::filter_str($keyword);
        $db = Sys::get_container_db();
        if ($keyword_filter) {
            $sql="select * from bb_push where event='publish'
                 and (title like '%{$keyword_filter}%'
                 or
                 exists (select 1 from bb_users where bb_users.uid=bb_push.uid
                   and bb_users.nickname like '%{$keyword_filter}%'
                  )
                  or uid='{$keyword_filter}'
                  
                 )
                 and sort in (1,2,3)
                order by time desc
                limit {$limit_StartPos},{$length}
                
                ";
            //                 $DBList = Db::table('bb_push')->where(['event'=>'publish'])
            //                 ->order(['time'=>'desc'])->limit($limit_StartPos,$length)->select();
            //                // break;
            // echo $sql;
            $DBList = $db->fetchAll($sql);
        }
        
        if (!$DBList) {
            $sql="select * from bb_push where event='publish'
            and (
            exists (select 1 from bb_users where bb_users.uid=bb_push.uid
            and bb_users.nickname =?
            )
            
            
            )
            and sort in (1,2,3)
            order by time desc
            limit {$limit_StartPos},{$length}
            
            ";
            $DBList = $db->fetchAll($sql,$keyword);
        }
        //  }
        $Data = array();
        foreach ($DBList as $DB)
        {
          //  $DataDB['id'] = (int)$DB['id'] ;
          //  $DataDB['uid'] = (int)$DB['uid'] ;
            
            $temp = \BBExtend\model\PushDetail::find( $DB['id'] );
            $temp->self_uid = $uid;
         //   $Data[]= $temp->get_all();
            
           
            array_push($Data,$temp->get_all()  );
        }
        return $Data;
    }
    
    
    
    //获得短视频的视频列表
    public static function get_record_show_v2($uid,$start_pos,$length = 20,$keyword)
    {
        $uid = intval($uid);
        $start_pos=intval($start_pos);
        $length=intval($length);
        
        $buy_help = new \BBExtend\user\Relation();
        $keyword_filter =  self::filter_str($keyword);
        $DBList = array();
        $db = Sys::get_container_db();
        if ($keyword_filter) {
            $sql="select * from bb_record
                where type in (1,2)
                and audit=1
                and is_remove=0
                and
                (title like '%{$keyword_filter}%'
                 or
                 exists (select 1 from bb_users where bb_users.uid=bb_record.uid
                   and bb_users.nickname like '%{$keyword_filter}%'
                  )
                 or uid='{$keyword_filter}'
                 
                 )
                 and usersort in (1,2,3)
                order by time desc
                limit {$start_pos}, {$length}
                ";
            $DBList = $db->fetchAll($sql);
        }
        if (!$DBList) {
            $sql="select * from bb_record
                where type in (1,2)
                and audit=1
                and is_remove=0
                and
                (
                 exists (select 1 from bb_users where bb_users.uid=bb_record.uid
                   and bb_users.nickname =?
                  )
                  
                  
                 )
                 and usersort in (1,2,3)
                order by time desc
                limit {$start_pos}, {$length}
                ";
            $DBList = $db->fetchAll($sql,$keyword);
        }
        
        $Data = array();
        foreach ($DBList as $DB)
        {
            $temp = \BBExtend\model\RecordDetail::find( $DB['id'] );
            $temp->self_uid = $uid;
            $Data[]=  $temp->get_all();
            
        }
        return $Data;
    }
    
    
    
    
    
    
    
    
    
    
    
    
}