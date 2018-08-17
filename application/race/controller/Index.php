<?php
namespace app\race\controller;

use think\Db;
use think\Config;
use think\Controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\BBUser;
use BBExtend\BBPush;
use BBExtend\BBRecord;
use BBExtend\common\User;
use BBExtend\common\Image;
use BBExtend\common\PicPrefixUrl;

/**
 * 大赛首页 app
 * 
 * @author xieye
 * 2017 03
 */
class Index extends Controller
{
    
    private  $is_bottom_zhibo;
    private  $is_bottom_ds;
    
    public function h5_detail($id,$type)
    {
        $db = Sys::get_container_dbreadonly();
        $id =  $ds_id = intval($id);
        
        $css = "/html5/css/style.css";
        //   $css = ($type=='android')? "/html5/css/style.css";// : "/html5/css/style_ios.css";
        // $css.= "?v=" . self::css_version;
        
      //  $css_content = file_get_contents(ROOT_PATH.'public' .   $css);
        
        
        
        if ($type=='race') {
        $sql="select * from ds_race where id={$ds_id}";
        $detail_arr = $db->fetchRow($sql);
        $detail=strval($detail_arr['detail']);
        $title = strval($detail_arr['title']);
        }else {
            $sql="select html_info from bb_task_activity where id={$ds_id}";
            $detail = $db->fetchOne($sql);
           // $detail=strval($detail_arr['detail']);
        }
        
        
        return [
                'code'=>1,
                'data' =>[
        //                'css_content' => $css_content,
                        'detail' =>$detail,
                ]
        ];
        
    }
    
    
    /**
     * 大赛的web详情页
     * @param number $ds_id
     */
    public function detail($ds_id=0)
    {
        $db = Sys::get_container_db();
        $type = Config::get("http_head_mobile_type"  );
        $css = ($type=='android')? "/html5/css/style.css" : "/html5/css/style_ios.css";
        $ds_id = intval($ds_id);
        $sql="select * from ds_race where id={$ds_id}";
        $detail_arr = $db->fetchRow($sql);
        $detail=strval($detail_arr['detail']);
        $title = strval($detail_arr['title']);
        $s=<<<html
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1.0"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name='apple-mobile-web-app-status-bar-style' content='black'>
    <meta name='format-detection' content='telephone=no'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$title}-详情</title>
    <link rel="stylesheet" type="text/css" href="{$css}">
    <script type="text/javascript" src="/share/js/jquery.min.js"></script>
    <script type="text/javascript" src="/share/js/Adaptive.js"></script>
</head>

<body>
<div class="main" id="main">
{$detail}
</div>
</body>
</html>

html;
        echo $s;
    }
    
    
    /**
     * 得到某个大赛的群信息
     * 
     * 查微信群
     * type=1 微信群  ，type=2 qq群
     * 
     * @param unknown $id
     */
    private function get_ds_groups($id)
    {
        $db = Sys::get_container_db_eloquent();
        $sql = "select summary,title,pic,qrcode_pic,type from bb_group
                 where bb_type= 2 and ds_id=?";
        $groups = DbSelect::fetchAll($db, $sql, [$id]);
        $wx_group = null;
        $qq_group = null;
        if ($groups) {
            foreach ($groups as $group) {
                if ($group['type']==1) {
                    $wx_group = $group;
                    unset($wx_group['type']);
                    $wx_group['pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['pic'], 1);
                    $wx_group['qrcode_pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['qrcode_pic'], 1);
                }
                if ($group['type']==2) {
                    $qq_group = $group;
                    unset($qq_group['type']);
                    $qq_group['pic'] = PicPrefixUrl::add_pic_prefix_https($qq_group['pic'], 1);
                    $qq_group['qrcode_pic'] = PicPrefixUrl::add_pic_prefix_https($qq_group['qrcode_pic'], 1);
                }
            }
            
        }
        return ['qq_group'=> $qq_group, 'wx_group'=>$wx_group];
    }
    
   
    /**
     * 单个大赛的信息
     * 
     * @param unknown $ds_id
     * @param unknown $uid
     */
    public function ds_one($ds_id) 
    {
        $ds_id = intval($ds_id);
        $time =time();
        
        $db = Sys::get_container_db();
        $sql ="
        select * from ds_race
        where is_active=1 and parent=0
        and id = {$ds_id}
        order by sort desc , start_time desc
        ";
        $v = $db->fetchRow($sql);
        if (!$v) {
            return ["code"=>0,"message"=>'参数错误'];
        }
        $sql ="
        select * from ds_race
        where is_active=1 and parent ={$ds_id}
        order by sort desc , start_time desc
        ";
        $child = $db->fetchAll($sql);
        
        $sql ="
        select * from ds_lunbo
        where ds_id  ={$ds_id}
        order by sort desc
        ";
        $lunbo = $db->fetchAll($sql);
        
        $t =[];
        $t['banner'] =Image::geturl($v['banner']);
        $t['gray_banner'] =Image::get_grayurl( $v['banner']);
    
        $t['photo'] = BBUser::get_userpic($v['uid']);
    
        $t['master_uid'] = $v['uid'];
    
        $t['money'] = floatval( $v['money']);
    
        $t['end_time'] = $v['end_time'];
        $t['start_time'] = $v['start_time'];
        $t['register_end_time'] = $v['register_end_time'];
        $t['register_start_time'] = $v['register_start_time'];
    
        if ($time > $v['start_time'] && $time < $v['end_time'] ) {
            $t['status_word'] ='比赛进行中';
            $t['status_word_color'] =0xff2400;
            if ($time > $v['register_start_time'] && $time < $v['register_end_time']) {
                $t['status_word'] ='报名进行中';
                $t['status_word_color'] =0x69ce6e;
            }
        }elseif ($time < $v['start_time']) {
            $t['status_word'] ='未开始';
            $t['status_word_color'] =0xff9000;
        }else {
            $t['status_word'] ='已结束';
            $t['status_word_color'] =0x575757;
        }
    
        $t['title'] =$v['title'];
        $sql ="select count(*) from ds_record where ds_id={$v['id']}";
        $t['count'] = $db->fetchOne($sql);
        $t['id'] = $v['id'];
        $t['current_time'] = $time;  // 当前时间，放到
    
        $t['detail_url'] = \BBExtend\common\BBConfig::get_server_url()."/race/index/detail/ds_id/{$v['id']}";
        $t['summary'] = $v['summary']; //简介
        // 现在显示分区信息。
        $t['child_race']=[];
        foreach ( $child as $v2 ) {
            if ($v2['parent'] == $v['id'] ) {
                $t['child_race'][]= [
                    'id' => $v2['id'],
                    'title' =>$v2['title'],
                ];
            }
        }
    
        // 轮播图
        $t['bigpic_list']=[];
        foreach ( $lunbo as $v2 ) {
            if ($v2['ds_id'] == $v['id'] ) {
                $t['bigpic_list'][]= [
                        'picpath' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v2['pic']),
                    'title' =>$v2['title'],
                    'linkurl' =>$v2['url'],
                ];
            }
        }
    
        $sql = "select id from ds_race where parent = {$v['id']} and  is_app=1";
        $t['app_qudao_id'] =$db->fetchOne($sql);
        $t['app_qudao_id'] = intval($t['app_qudao_id']);
        
        //群
        $groups = $this->get_ds_groups($ds_id);
        $t['wx_group'] = $groups['wx_group'];
        $t['qq_group'] = $groups['qq_group'];
        
        return ['code'=>1,       'data' => $t,     ];
    }
    
    
    /**
     * 大赛列表
     * @param number $startid
     * @param number $length
     * @param number $uid
     * @param number $range  默认1全部，2能参加，3已参加
     * @return number[]|unknown[][][]
     */
    public function ds_list($startid=0,$length=10,$uid=0,$range=0)
    {
        $time =time();
        $startid=intval($startid);
        $length=intval($length);
        
        $uid = intval($uid);
        $range = intval($range);
        
        $test_ds_id=14500;// 虎皮企鹅。
        
        $db = Sys::get_container_db();
        $sql ="
                select * from ds_race 
                where is_active=1 and parent=0 
                and id != {$test_ds_id}
                order by has_end asc, sort desc , start_time desc
                limit {$startid},{$length}
                ";
        if ($range==3) { // 已参加,视频上传未审核和已通过审核
            $sql ="
            select * from ds_race
            where is_active=1 and parent=0
                and id != {$test_ds_id}

              and exists(select 1 from ds_record where 
                 ds_record.uid = {$uid}
                 and ds_record.ds_id =  ds_race.id
                 and exists (select 1 from bb_record where bb_record.id = ds_record.record_id
                  and (bb_record.audit=1 or bb_record.audit=0)
                 )
              )
            order by has_end asc,sort desc , start_time desc
            limit {$startid},{$length}
            ";
        }
        if ($range==2) { // 能参加, 视频上传后审核失败和未上传过视频
            $sql ="
            select * from ds_race
            where is_active=1 and parent=0

                and id != {$test_ds_id}

            and register_start_time < {$time}
            and end_time > {$time}
            and
            ( 
               not exists(select 1 from ds_record where
                ds_record.uid = {$uid}
               and ds_record.ds_id =  ds_race.id
                )  
                or 
                (
                   exists(select 1 from ds_record where 
                 ds_record.uid = {$uid}
                 and ds_record.ds_id =  ds_race.id
                 and exists (select 1 from bb_record where bb_record.id = ds_record.record_id
                  and bb_record.audit=2
                            )
                          )
                )
            )
            order by has_end asc,sort desc , start_time desc
            limit {$startid},{$length}
            ";
        }
        
        $result = $db->fetchAll($sql);
        $ids = [];
        foreach ($result as $v) {
            $ids[]= $v["id"];
        }
        
        $child = $lunbo= [];
        
        if ($result) {
            $sql ="
            select * from ds_race
            where is_active=1 and parent in (?)
            order by sort desc , start_time desc
            ";
            $child = $db->fetchAll($db->quoteInto($sql, $ids));
            $sql ="
            select * from ds_lunbo
            where ds_id in (?)
            order by sort desc 
            ";
            $lunbo = $db->fetchAll($db->quoteInto($sql, $ids));
        }
        
        $temp = [];
        $time = time();
        foreach ($result as $v) {
            $t =[];
            $t['banner'] =Image::geturl($v['banner']);
            $t['gray_banner'] =Image::get_grayurl( $v['banner']);
            
            $t['photo'] = BBUser::get_userpic($v['uid']);
            
            $t['master_uid'] = $v['uid'];
            
            $t['money'] = floatval( $v['money']);
            
            $t['end_time'] = $v['end_time'];
            $t['start_time'] = $v['start_time'];
            $t['register_end_time'] = $v['register_end_time'];
            $t['register_start_time'] = $v['register_start_time'];
            
            if ($time > $v['start_time'] && $time < $v['end_time'] ) {
                $t['status_word'] ='比赛进行中';
                $t['status_word_color'] =0xff2400;
                if ($time > $v['register_start_time'] && $time < $v['register_end_time']) {
                    $t['status_word'] ='报名进行中';
                    $t['status_word_color'] =0x69ce6e;
                }
            }elseif ($time < $v['start_time']) {
                $t['status_word'] ='未开始';
                $t['status_word_color'] =0xff9000;
            }else {
                $t['status_word'] ='已结束';
                $t['status_word_color'] =0x575757;
            }
            
            $t['title'] =$v['title'];
            $sql ="select count(*) from ds_record where ds_id={$v['id']}";
            $t['count'] = $db->fetchOne($sql);
            $t['id'] = $v['id'];
            $t['current_time'] = $time;  // 当前时间，放到
            
            $t['detail_url'] = \BBExtend\common\BBConfig::get_server_url()."/race/index/detail/ds_id/{$v['id']}";
            $t['summary'] = $v['summary']; //简介
            // 现在显示分区信息。
            $t['child_race']=[];
            foreach ( $child as $v2 ) {
                if ($v2['parent'] == $v['id'] ) {
                    $t['child_race'][]= [
                        'id' => $v2['id'],
                        'title' =>$v2['title'],
                    ];
                }
            }
            
            // 轮播图
            $t['bigpic_list']=[];
            foreach ( $lunbo as $v2 ) {
                if ($v2['ds_id'] == $v['id'] ) {
                    $t['bigpic_list'][]= [
                            'picpath' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v2['pic']),
                        'title' =>$v2['title'],
                        'linkurl' =>$v2['url'],
                    ];
                }
            }
            
            $sql = "select id from ds_race where parent = {$v['id']} and  is_app=1";
            $t['app_qudao_id'] =$db->fetchOne($sql);
            $t['app_qudao_id'] = intval($t['app_qudao_id']);
            
            //加入大赛群信息
             $groups = $this->get_ds_groups($v['id']);
            $t['wx_group'] = $groups['wx_group'];
            $t['qq_group'] = $groups['qq_group'];
            // 加入 是否有直播
            $ds  = \BBExtend\model\Race::find($v['id']);
            $t['has_live_video'] = $ds->has_live_video();
            
            $temp[]= $t;
        }
        
        return [
            'code'=>1,
            'is_bottom' => (count($temp )== $length) ? 0:1,
            'data' => $temp,
        ];
    }

    
    /**
     * 大赛首页直播列表
     * 
     * 注意，是所有的大赛直播啊。
     * 
     */
    public function zhibo_list($uid,$startid=0,$length=10)
    {
        $db = Sys::get_container_db();
        $startid = intval($startid);
        $length = intval($length);
        $uid = intval($uid);
        $sql="select bb_push.* from bb_push where event='publish'
        and exists (
          select 1 from ds_show_video
            where ds_show_video.room_id = bb_push.room_id
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
        return [
            'code'=>1,
            'is_bottom' => (count($new )== $length) ? 0:1,
            'data' => $new,
        ];
    }

    
    /**
     * 大赛首页直播列表
     *
     * 注意，是所有的大赛直播啊。
     *
     */
    public function zhibo_list_v2($uid,$startid=0,$length=10)
    {
        $db = Sys::get_container_db();
        $startid = intval($startid);
        $length = intval($length);
        $uid = intval($uid);
        $sql="select bb_push.* from bb_push where event='publish'
        and exists (
          select 1 from ds_show_video
            where ds_show_video.room_id = bb_push.room_id
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
//             $new[]= BBPush::get_detail_by_row($v, $uid);
            
            
            $temp = \BBExtend\model\PushDetail::find( $v['id'] );
            $temp->self_uid = $uid;
            $new[]= $temp->get_all();
            
        }
        return [
                'code'=>1,
                'data'=>[ 'list' => $new, 'is_bottom' => (count($new )== $length) ? 0:1, ],
        ];
    }
    
    
    /**
     * 大赛演示视频列表
     * 
     * @author xieye 
     * 
     * 2017 1010 增加参数only_live，为1表示只想要直播视频列表。
     */
   public function publicize_list($ds_id=0,$startid=0,$length=10,$uid=0,$only_live=0)
   {
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
       $uid=intval($uid);
       $sql ="select ds_show_video.* from ds_show_video 
        where 
           ds_id ={$ds_id}
          and 
          ( exists    (select 1 from bb_push where bb_push.event='publish'
           and ds_show_video.type=1
           and ds_show_video.room_id = bb_push.room_id
                       )
             or exists (select 1 from bb_record 
               where ds_show_video.type=2
                 and ds_show_video.room_id = bb_record.room_id
                 and bb_record.audit=1
                 and bb_record.is_remove=0
                       )
          )
          order by ds_show_video.type asc 
          limit {$startid},{$length}
       ";
       if ($only_live) {
           $sql ="select ds_show_video.* from ds_show_video
           where
           ds_id ={$ds_id}
           and
            exists    (select 1 from bb_push where bb_push.event='publish'
           and ds_show_video.type=1
           and ds_show_video.room_id = bb_push.room_id
           )
           order by ds_show_video.type asc
           limit {$startid},{$length}
           ";
       }
       
       $temp  = $db->fetchAll($sql);
       $result=[];
       
       foreach ($temp as $v ) {
           if ($v['type']==1) {
               $sql="select * from bb_push where room_id=?";
               $row = $db->fetchRow($sql,$v['room_id'] );
               $sql ="select * from bb_users where uid={$row['uid']} and not_zhibo=0 ";
               // xieye 201708 ，防止禁止直播的人直播
               if ($db->fetchRow($sql)) {
               
                   $result []= BBPush::get_detail_by_row($row, $uid);
               }
           }
           if ($v['type']==2) {
               $sql="select * from bb_record where room_id=?";
               $row = $db->fetchRow($sql,$v['room_id'] );
               $result []= BBRecord::get_detail_by_row($row, $uid);
           }
       }
       
       return [
           'code'=>1,
           'is_bottom' => (count($result )== $length) ? 0:1,
           'data' => $result,
       ];
   }
   
   
   
   
   public function publicize_list_v2($ds_id=0,$startid=0,$length=10,$uid=0,$only_live=0)
   {
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
       $uid=intval($uid);
       $sql ="select ds_show_video.* from ds_show_video
        where
           ds_id ={$ds_id}
          and
          ( exists    (select 1 from bb_push where bb_push.event='publish'
           and ds_show_video.type=1
           and ds_show_video.room_id = bb_push.room_id
                       )
             or exists (select 1 from bb_record
               where ds_show_video.type=2
                 and ds_show_video.room_id = bb_record.room_id
                 and bb_record.audit=1
                 and bb_record.is_remove=0
                       )
          )
          order by ds_show_video.type asc
          limit {$startid},{$length}
       ";
       if ($only_live) {
           $sql ="select ds_show_video.* from ds_show_video
           where
           ds_id ={$ds_id}
           and
            exists    (select 1 from bb_push where bb_push.event='publish'
           and ds_show_video.type=1
           and ds_show_video.room_id = bb_push.room_id
           )
           order by ds_show_video.type asc
           limit {$startid},{$length}
           ";
       }
       
       $temp  = $db->fetchAll($sql);
       $result=[];
       
       foreach ($temp as $v ) {
           if ($v['type']==1) {
               $sql="select * from bb_push where room_id=?";
               $row = $db->fetchRow($sql,$v['room_id'] );
               $sql ="select * from bb_users where uid={$row['uid']} and not_zhibo=0 ";
               // xieye 201708 ，防止禁止直播的人直播
               if ($db->fetchRow($sql)) {
                   
                   $temp = \BBExtend\model\PushDetail::find( $row['id'] );
                   $temp->self_uid = $uid;
                   $result[]= $temp->get_all();
                   
                   
                 //  $result []= BBPush::get_detail_by_row($row, $uid);
               }
           }
           if ($v['type']==2) {
               $sql="select * from bb_record where room_id=?";
               $row = $db->fetchRow($sql,$v['room_id'] );
               
               
               $temp = \BBExtend\model\RecordDetail::find( $row['id'] );
               $temp->self_uid = $uid;
               $result[]= $temp->get_all();
               
               
//                $result []= BBRecord::get_detail_by_row($row, $uid);
           }
       }
       
       
//        return [
//                'code'=>1,
//                'is_bottom' => (count($result )== $length) ? 0:1,
//                'data' => $result,
//        ];
       
       return [
               'code'=>1,
               'data'=>[ 'list'=> $result, 'is_bottom' => (count($result )== $length) ? 0:1, ],
       ];
   }
   
   
   
   
   
   
   
   
   
   
   /**
    * 大赛热门列表，
    * 这是总赛区的！
    */   
   public function hot_list($ds_id,$startid=0,$length=10, $uid=0)
   {
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
       $uid=intval($uid);
       $redis  = Sys::getredis11();
       $key = "ds:hot_list:{$ds_id}";
       $endid = $startid+$length-1;
       
       $result = $redis->lRange($key,$startid,$endid);
       
       // ！！！不使用缓存了。
       $result =false;
       if (!$result) {
           $sql = "select ds_record.record_id from ds_record 
        where 
              ds_id ={$ds_id}
            
          
          and exists (select 1 from bb_record 
               where bb_record.id = ds_record.record_id
                 and bb_record.audit=1
                 and bb_record.is_remove=0
                     )          
          ";
           $ids = $db->fetchCol($sql);
           $ids = array_unique($ids);
           
           shuffle($ids);
         //  dump($ids);
           foreach ($ids as $id) {
               $redis->rPush($key, $id);
           }
           //$redis->setTimeout($key, 10* 60); // 分钟
           $redis->setTimeout($key, 1); 
       }
       $result = $redis->lRange($key,$startid,$endid);
       $temp=[];
       foreach ($result as $id) {
           $sql="select * from bb_record where id=?";
           $row = $db->fetchRow($sql,$id );
           $temp []= BBRecord::get_detail_by_row($row, $uid);
       }
       return [
           'code'=>1,
           'is_bottom' => (count($temp )== $length) ? 0:1,
           'data' => $temp,
       ];
       
   }

   
   public function hot_list201706($ds_id,$startid=0,$length=10, $uid=0,$ids='')
   {
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
       $uid=intval($uid);
       $redis  = Sys::getredis11();
       $key = "ds:hot_list:{$ds_id}";
       $endid = $startid+$length-1;
       
       if ($startid==0) {
           //谢烨，总共分5页。
           $all_length = 5 * $length;
          $sql =" select distinct ds_record.record_id from ds_record
           where
           ds_id ={$ds_id}
           and exists (select 1 from bb_record
                   where bb_record.id = ds_record.record_id
                   and bb_record.audit=1
                   and bb_record.is_remove=0
                   )
            limit {$all_length}       
           ";
           $all_ids = $db->fetchCol($sql);
           $all_ids_count = count($all_ids);
           shuffle($all_ids);
           
           $ids_arr = [];
           $ids_str ='';
           for ($i=0; $i < $length && $i < $all_ids_count;$i++) {
               $ids_arr[] = $all_ids[$i];
           }
           $ids_str = implode(',', $ids_arr);
       }else {
           $ids_str = $ids;
           $ids_arr =[];
           $temp = explode(',', $ids_str);
           foreach ($temp as $v) {
               $ids_arr[] = intval($v);
           }
           $ids_str = implode(',', $ids_arr);
       }
       
       $temp=[];
       foreach ($ids_arr as $id) {
           $sql="select * from bb_record where id=?";
           $row = $db->fetchRow($sql,$id );
           $temp []= BBRecord::get_detail_by_row($row, $uid);
       }
       
       $all_ids_str='';
       if ($startid==0) {
           $all_ids_str = implode(',', $all_ids);
       }
       
       return [
           'code'=>1,
           
           'data' => [
               'list' => $temp,
               'all_ids_str' => $all_ids_str,
           ],
       ];
        
   }
   
   
   
   public function hot_list201706_v2($ds_id,$startid=0,$length=10, $uid=0,$ids='')
   {
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
       $uid=intval($uid);
       $redis  = Sys::getredis11();
       $key = "ds:hot_list:{$ds_id}";
       $endid = $startid+$length-1;
       
       if ($startid==0) {
           //谢烨，总共分5页。
           $all_length = 5 * $length;
           $sql =" select distinct ds_record.record_id from ds_record
           where
           ds_id ={$ds_id}
           and exists (select 1 from bb_record
                   where bb_record.id = ds_record.record_id
                   and bb_record.audit=1
                   and bb_record.is_remove=0
                   )
            limit {$all_length}
           ";
           $all_ids = $db->fetchCol($sql);
           $all_ids_count = count($all_ids);
           shuffle($all_ids);
           
           $ids_arr = [];
           $ids_str ='';
           for ($i=0; $i < $length && $i < $all_ids_count;$i++) {
               $ids_arr[] = $all_ids[$i];
           }
           $ids_str = implode(',', $ids_arr);
       }else {
           $ids_str = $ids;
           $ids_arr =[];
           $temp = explode(',', $ids_str);
           foreach ($temp as $v) {
               $ids_arr[] = intval($v);
           }
           $ids_str = implode(',', $ids_arr);
       }
       
       $temp=[];
       foreach ($ids_arr as $id) {
           
           $temp2 = \BBExtend\model\RecordDetail::find( $id );
           $temp2->self_uid = $uid;
           $temp[]= $temp2->get_all();
           
//            $sql="select * from bb_record where id=?";
//            $row = $db->fetchRow($sql,$id );
//            $temp []= BBRecord::get_detail_by_row($row, $uid);
       }
       
       $all_ids_str='';
       if ($startid==0) {
           $all_ids_str = implode(',', $all_ids);
       }
       
       return [
               'code'=>1,
               
               'data' => [
                       'list' => $temp,
                       'all_ids_str' => $all_ids_str,
               ],
       ];
       
   }
   
   
   
   /**
    * 大赛排行榜
    * 这是总赛区的！
    */
   public function ranking_list($ds_id,$startid=0,$length=10, $uid=0)
   {
     //  Sys::display_all_error();
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
       $uid=intval($uid);
       $redis  = Sys::getredis11();
       $key = "ds:ranking_list:{$ds_id}";
       $endid = $startid+$length-1;
        
//        $result = $redis->lRange($key,$startid,$endid);
//        if (!$result) {
           $sql = "select ds_record.record_id,(
             select `like` from bb_record where bb_record.id = ds_record.record_id
           ) c_time  from ds_record
           where
             ds_id ={$ds_id}
           and exists (select 1 from bb_record
           where bb_record.id = ds_record.record_id
           and bb_record.audit=1
           and bb_record.is_remove=0
           )
           order by c_time desc
           limit {$startid},{$length}
           ";
           //echo $sql;
           $ids = $db->fetchCol($sql);
       //    $ids = array_unique($ids);
           foreach ($ids as $id) {
               $redis->rPush($key, $id);
           }
           $redis->setTimeout($key, 3*60); // 分钟
          // $result = $ids;
          // $redis->setTimeout($key, 1); // 分钟
//        }
       $result = $redis->lRange($key,$startid,$endid);
       $temp=[];
       $i=$startid+1;
       foreach ($ids as $id) {
           $sql="select * from bb_record where id=?";
           $row = $db->fetchRow($sql,$id );
           $t = BBRecord::get_detail_by_row($row, $uid);
           $t['paiming'] = $i;
           $i++;
           
           $temp []= $t;
       }
      
       return [
           'code'=>1,
           'is_bottom' => (count($temp )== $length) ? 0:1,
           'data' => $temp,
       ];
        
   }
   
   
   public function ranking_list_v2($ds_id,$startid=0,$length=10, $uid=0)
   {
       //  Sys::display_all_error();
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
       $uid=intval($uid);
       $redis  = Sys::getredis11();
       $key = "ds:ranking_list:{$ds_id}";
       $endid = $startid+$length-1;
       
       //        $result = $redis->lRange($key,$startid,$endid);
       //        if (!$result) {
       $sql = "select ds_record.record_id,(
             select `like` from bb_record where bb_record.id = ds_record.record_id
           ) c_time  from ds_record
           where
             ds_id ={$ds_id}
           and exists (select 1 from bb_record
           where bb_record.id = ds_record.record_id
           and bb_record.audit=1
           and bb_record.is_remove=0
           )
           order by c_time desc
           limit {$startid},{$length}
           ";
       //echo $sql;
       $ids = $db->fetchCol($sql);
       //    $ids = array_unique($ids);
       foreach ($ids as $id) {
           $redis->rPush($key, $id);
       }
       $redis->setTimeout($key, 3*60); // 分钟
       // $result = $ids;
       // $redis->setTimeout($key, 1); // 分钟
       //        }
       $result = $redis->lRange($key,$startid,$endid);
       $temp=[];
       $i=$startid+1;
       foreach ($ids as $id) {
           
           $temp2 = \BBExtend\model\RecordDetail::find( $id );
           $temp2->self_uid = $uid;
           $t =  $temp2->get_all();
           
//            $sql="select * from bb_record where id=?";
//            $row = $db->fetchRow($sql,$id );
//            $t = BBRecord::get_detail_by_row($row, $uid);
           
           
           
           $t['paiming'] = $i;
           $i++;
           
           $temp []= $t;
       }
       $is_bottom = (count($temp )== $length) ? 0:1;
       
       return [
               'code'=>1,
               'data' => ['list' =>$temp, 'is_bottom'=>$is_bottom ] ,
       ];
       
   }
       
   
   
   /**
    * 查用户对于大赛的状态
    * @param unknown $uid
    * @param unknown $ds_id
    */
   public function get_user_status($uid,$ds_id)
   {
       return \BBExtend\video\Race::get_user_race_status($uid, $ds_id);
   }
   
   public function get_user_status_v2($uid,$ds_id)
   {
       return \BBExtend\video\Race::get_user_race_status_v2($uid, $ds_id);
   }
   
   public function get_user_status_new($v=1, $uid, $ds_id)
   {
       if ($v >= 5) {
           return \BBExtend\video\RaceStatus::get_status_v5($uid, $ds_id);
       }
       
       return \BBExtend\video\RaceStatus::get_status($uid, $ds_id);
   }
   
   /**
    * 问答的列表
    * 
    * @param number $ds_id
    * @param number $startid
    * @param number $length
    * @return number[]|string[]|number[]|string[]
    */
   public function question_list($ds_id=0,$startid=0,$length=10, $uid=10000)
   {
       Sys::display_all_error();
       $db = Sys::get_container_db();
       $ds_id =intval($ds_id);
       $startid=intval($startid);
       $length=intval($length);
      
       $sql = "select * from ds_race where is_active=1 and level=1 and id = {$ds_id}";
       $row = $db->fetchRow($sql);
       if (!$row) {
           return ['code'=>0, 'message' => '大赛不存在'];
       }
       $master_uid = $row['uid'];
       $master = \app\user\model\UserModel::getinstance($master_uid);
       
       $master_nickname = $master->get_nickname();
       $master_pic = $master->get_userpic();
       
       $sql = "select ds_question.* from ds_question
       where ds_id ={$ds_id}
       and answer_time >0
       order by sort desc
       limit {$startid},{$length}
       ";
       $temp = $db->fetchAll($sql);
       foreach ($temp as $k => $v) {
           $temp[$k]['answer'] = "答：".$temp[$k]['answer']; 
           $temp[$k]['question'] = "问：".$temp[$k]['question'];
           
           $user = \app\user\model\UserModel::getinstance($v['question_uid']);
           
           $user2 = \BBExtend\model\User::find($v['question_uid']);
           
           $temp[$k]['user_role'] = $user2->role;
           $temp[$k]['user_badge'] = $user2->get_badge();
           $temp[$k]['user_frame'] = $user2->get_frame();
           
           
           $temp[$k]['user_uid'] = $v['question_uid'];
           $temp[$k]['user_nickname'] = $user->get_nickname();
           $temp[$k]['user_pic'] = $user->get_userpic();
           
           $temp[$k]['user_focus'] = \BBExtend\Focus::get_focus_state($uid, $v['question_uid']);
           
           
           $user = \app\user\model\UserModel::getinstance($v['answer_uid']);
           $temp[$k]['master_uid'] = $v['answer_uid'];
           
           $user3 = \BBExtend\model\User::find($v['answer_uid']);
           
           $temp[$k]['master_role'] = $user3->role;
           $temp[$k]['master_badge'] = $user3->get_badge();
           $temp[$k]['master_frame'] = $user3->get_frame();
           
           $temp[$k]['master_focus'] = \BBExtend\Focus::get_focus_state($uid, $v['answer_uid']);
           
           $temp[$k]['master_nickname'] = $user->get_nickname();
           $temp[$k]['master_pic'] = $user->get_userpic();
       }
           
       return [
           'code'=>1,
           'is_bottom' => (count($temp )== $length) ? 0:1,
           'data' => $temp,
       ];
       
   }
   
   
   
}
