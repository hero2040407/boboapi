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
class IndexV2 extends Controller
{
    
    private  $is_bottom_zhibo;
    private  $is_bottom_ds;
    
    
    private function fetch_user_addi_info($uid)
    {
        $db = \BBExtend\Sys::get_container_db();
        $sql="
select * from ds_dangan_config_user_history
 where uid =? 
   and exists (
  select 1 from ds_public_config
   where ds_public_config.title = ds_dangan_config_user_history.title 
)

";
        $result = $db->fetchAll($sql,[ $uid ]);
        if ($result) {
            $new=[];
            foreach ( $result as $v ) {
                $new[$v['title']] = $v['content'];
            }
            return $new;
        }
        return [];
    }
    
    
    
    public function select_field_v5()
    {
        $ds_id = input('param.ds_id/d');
        $uid =  input('param.uid/d');
        $token = input('param.token');
        
        $user = \BBExtend\model\User::find( $uid );
        if (!$user) {
            return ['code'=>0];
        }
        if (!$user->check_token($token)) {
            return ['code'=>0,'message' =>'uid err' ];
        }
        
        
        //    Sys::display_all_error();
        $result = \BBExtend\video\RaceStatus::get_status_v5($uid, $ds_id);
        if ($result['code'] == 0 ) {
            return $result;
        }
       
        $race = \BBExtend\model\Race::find($ds_id);
        //
        
        // 谢烨，查 个人参赛信息。
        $db = Sys::get_container_dbreadonly();
        
        $sql="select * from ds_dangan_config where ds_id=?
                order by sort desc 
";
        $config = $db->fetchAll($sql,[ $ds_id ]);
        $new=[];
        foreach ( $config as $one ) {
            $temp = $one;
            $temp['options'] = explode(',', $temp['options']);
            $new[]= $temp;
        }
        $config = $new;
        
        //echo 43;exit;
        $sql="select * from ds_register_log
where uid= ? and phone !=''
order by id desc limit 1";
        $row = $db->fetchRow($sql,[$uid]);
        if ($row) {
            $info=[
                    'phone'=>$row['phone'],
                    'name'=>$row['name'],
                    'sex'=>$row['sex'],
                    'birthday'=>$row['birthday'],
                   
                    'pic'=>$row['pic'],
                    'addi_info' =>$this->fetch_user_addi_info($uid),
            ];
        }else {
            $info=null;
        }
        
        if ($result['data']['status']==2  ) {
            $db = Sys::get_container_dbreadonly();
            $sql="select id,address,title,status from ds_race_field where race_id = ? and is_valid=1 ";
            $result = $db->fetchAll($sql,[ $ds_id ]);
            
            // 谢烨，额外查一下
            $sql="select id                      from ds_race_field  where race_id = ? and is_valid=1 and
                    
                  exists(
                   select 1 from ds_register_log
                     where ds_register_log.uid=?
                       and has_pay=1 and has_dangan=1
                       and ds_register_log.ds_id = ds_race_field.id
)
                    
";
            $ids = $db->fetchCol($sql,[   $ds_id, $uid]);
            
            foreach ($result as $k => $v) {
                if (in_array( $v['id'], $ids  )) {
                    $result[$k]['status'] = 0;
                }
            }
            
            
            return ['code'=>1, 'data'=> ['list' => $result,'info'=>$info, 'config'=>$config , 
                    'upload_type'=> $race->upload_type,
                    'money' => $race->money,
                    'online_type'=> $race->online_type  ]  ];
        } else {
            return ['code'=>1, 'data'=> ['list' => [],'info'=>$info,'config'=>$config ,
                    'upload_type'=> $race->upload_type,
                    'money' => $race->money,
                    'online_type'=> $race->online_type  ]  ];
        }
        
        
    }
    
    
    /**
     * 提供某个大赛的赛区列表。
     * @param unknown $uid
     * @param unknown $ds_id
     * @return number[]|number[][]|string[][]|mixed[][]|number[]|array[][]|number[]|array[][]
     */
    public function select_field($v=1, $uid, $ds_id)
    {
        if ($v>=5) {
            return $this->select_field_v5();
        }
        
        
    //    Sys::display_all_error();
        $result = \BBExtend\video\RaceStatus::get_status($uid, $ds_id);
        $code = $result['code'];
        if ($code == 0 ) {
            return $result;
        }
        
        $race = \BBExtend\model\Race::find($ds_id);
        // 
        
        // 谢烨，查 个人参赛信息。
        $db = Sys::get_container_dbreadonly();
        $sql="select * from ds_register_log 
where uid= ? and phone !='' 
order by id desc limit 1";
        $row = $db->fetchRow($sql,[$uid]);
        if ($row) {
            $info=[
                    'phone'=>$row['phone'],
                    'name'=>$row['name'],
                    'sex'=>$row['sex'],
                    'birthday'=>$row['birthday'],
                    'area1_name'=>$row['area1_name'],
                    'area2_name'=>$row['area2_name'],
                    'height'=>$row['height'],
                    'weight'=>$row['weight'],
                    'pic'=>$row['pic'],
            ];
        }else {
            $info=null;
        }
        
        if ($result['data']['status']==2  ) {
            $db = Sys::get_container_dbreadonly();
            $sql="select id,address,title,status from ds_race_field where race_id = ? and is_valid=1 ";
            $result = $db->fetchAll($sql,[ $ds_id ]);
            
            // 谢烨，额外查一下
            $sql="select id                      from ds_race_field  where race_id = ? and is_valid=1 and
                    
                  exists(
                   select 1 from ds_register_log
                     where ds_register_log.uid=?
                       and has_pay=1 and has_dangan=1
                       and ds_register_log.ds_id = ds_race_field.id
)
                    
";
            $ids = $db->fetchCol($sql,[   $ds_id, $uid]);
            
            foreach ($result as $k => $v) {
                if (in_array( $v['id'], $ids  )) {
                    $result[$k]['status'] = 0;
                }
            }
            
            
            return ['code'=>1, 'data'=> ['list' => $result,'info'=>$info, 'online_type'=> $race->online_type  ]  ];
        } else {
            return ['code'=>1, 'data'=> ['list' => [],'info'=>$info,'online_type'=> $race->online_type  ]  ];
        }
        
        
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
        $sql = "select summary,title,pic,qrcode_pic,type,code,group_or_person from bb_group
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
                if ($group['type']==2) {// 这是qq
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
     * 大赛列表
     * @param number $startid
     * @param number $length
     * @param number $uid
     * @param number $range  默认1全部，2能参加，3已参加
     * @return number[]|unknown[][][]
     */
    public function ds_list_new($startid=0,$length=10,$uid=0,$range=0)
    {
        $time =time();
        $startid=intval($startid);
        $length=intval($length);
        
        $uid = intval($uid);
        $range = intval($range);
        
        
        
        $db = Sys::get_container_db();
        $sql ="
                select * from ds_race
                where is_active=1 and parent=0
                and id not between 198 and 203
                order by has_end asc, sort desc , start_time desc
                limit {$startid},{$length}
                ";
        if ($uid &&  \BBExtend\model\User::is_test( $uid ) ) {
            //\BBExtend\model\User::is_test( $uid );
            $sql ="
                select * from ds_race
                where is_active=1 and parent=0
                
                order by has_end asc, sort desc , start_time desc
                limit {$startid},{$length}
                ";
            
        }
        
        
        if ($range==3) { // 已参加,视频上传未审核和已通过审核
            $sql ="
            select * from ds_race
            where is_active=1 and level=1
and id not between 198 and 203

              and exists(select 1 from ds_register_log where
                 ds_register_log.uid = {$uid}
                 and ds_register_log.zong_ds_id =  ds_race.id
                 and ds_register_log.has_dangan=1
                 and ds_register_log.has_pay=1
              )
            order by has_end asc,sort desc , start_time desc
            limit {$startid},{$length}
            ";
        }
        if ($range==2) { // 能参加, 视频上传后审核失败和未上传过视频
            $sql ="
            select * from ds_race
            where is_active=1 and parent=0
and id not between 198 and 203

            and register_start_time < {$time}
            and register_end_time > {$time}
            and not exists(select 1 from ds_register_log where
                 ds_register_log.uid = {$uid}
                 and ds_register_log.zong_ds_id =  ds_race.id
                 and ds_register_log.has_dangan=1
                 and ds_register_log.has_pay=1
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
            select * from ds_race_field
            where is_valid=1 and race_id in (?)
            order by id desc 
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
            $t['banner'] =Image::geturl($v['banner_bignew']);
            $t['gray_banner'] =Image::get_grayurl( $v['banner_bignew']);
            
            $t['photo'] = BBUser::get_userpic($v['uid']);
            $t['master_nickname'] = BBUser::get_nickname($v['uid']);
            
            $user =  \BBExtend\model\User::find($v['uid']);
            
            // 防止测试服数据错误。
            if (!$user) {
                continue;
            }
            
            $t['role'] = $user->role;
            $t['frame'] = $user->get_frame();
            $t['badge'] = $user->get_badge();
            
            
            $t['master_uid'] = $v['uid'];
            
            $t['money'] = floatval( $v['money']);
            
            $t['end_time'] = $v['end_time'];
            $t['start_time'] = $v['start_time'];
            $t['register_end_time'] = $v['register_end_time'];
            $t['register_start_time'] = $v['register_start_time'];
            
            $status_arr = \BBExtend\video\RaceStatus::get_status($uid, $v['id']);
            $t['status'] = $status_arr['data']['status'];
            $t['describe'] = $status_arr['data']['describe'];
            
            
            
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
                if ($v2['race_id'] == $v['id'] ) {
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
                            'picpath' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v2['pic_bignew']),
                            'title' =>$v2['title'],
                            'linkurl' =>$v2['url'],
                    ];
                }
            }
            
            $sql = "select id from ds_race where parent = {$v['id']} and  is_app=1";
            $t['app_qudao_id'] =$v['id'];
            $t['app_qudao_id'] = intval($t['app_qudao_id']);
            
            //加入大赛群信息
            $groups = $this->get_ds_groups($v['id']);
            $t['wx_group'] = $groups['wx_group'];
            $t['qq_group'] = $groups['qq_group'];
            // 加入 是否有直播
            $ds  = \BBExtend\model\Race::find($v['id']);
            $t['has_live_video'] = $ds->has_live_video();
            
            $t['upload_type'] = $v['upload_type'];
            
            $temp[]= $t;
        }
        
        return [
                'code'=>1,
                'data' =>[
                        'is_bottom' => (count($temp )== $length) ? 0:1,
                        'list' => $temp,
                ],
                
//                 'data' => $temp,
        ];
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
        
        $db = Sys::get_container_db();
        
        if ( \BBExtend\model\User::is_test($uid) ) {
        
        $sql ="
                select * from ds_race 
                where is_active=1 and parent=0 
                order by has_end asc, sort desc , start_time desc
                limit {$startid},{$length}
                ";
        
        }else {
            $sql ="
                select * from ds_race
                where is_active=1 and parent=0
                and id !=123 and id != 167
                order by has_end asc, sort desc , start_time desc
                limit {$startid},{$length}
                ";
            
        }
        
        if ($range==3) { // 已参加,视频上传未审核和已通过审核
            $sql ="
            select * from ds_race
            where is_active=1 and parent=0
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


 and id !=123 and id != 167

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
            $t['banner'] =Image::geturl($v['banner_bignew']);
            $t['gray_banner'] =Image::get_grayurl( $v['banner_bignew']);
            
            $t['photo'] = BBUser::get_userpic($v['uid']);
            $t['master_nickname'] = BBUser::get_nickname($v['uid']);
            
            $user =  \BBExtend\model\User::find($v['uid']);
            
            // 防止测试服数据错误。
            if (!$user) {
                continue;
            }
            
            $t['role'] = $user->role;
            $t['frame'] = $user->get_frame();
            $t['badge'] = $user->get_badge();
            
            
            $t['master_uid'] = $v['uid'];
            
            $t['money'] = floatval( $v['money']);
            
            $t['end_time'] = $v['end_time'];
            $t['start_time'] = $v['start_time'];
            $t['register_end_time'] = $v['register_end_time'];
            $t['register_start_time'] = $v['register_start_time'];
            
            if ($time > $v['start_time'] && $time < $v['end_time'] ) {
                $t['status_word'] ='进行中';
                $t['status_word_color'] =0xff2400;
                if ($time > $v['register_start_time'] && $time < $v['register_end_time']) {
                    $t['status_word'] ='报名中';
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
                            'picpath' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v2['pic_bignew']),
                        'title' =>$v2['title'],
                        'linkurl' =>$v2['url'],
                    ];
                }
            }
            
            $sql = "select id from ds_race where parent = {$v['id']} and  is_app=1";
            $t['app_qudao_id'] =$v['id'];
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

    
    
    public function ds_one_new_v5($ds_id,$uid=10000){
        
        
        $ds_id = intval($ds_id);
        $uid=intval($uid);
        
        $time =time();
        
        $db = Sys::get_container_db();
        $sql ="
        select * from ds_race
        where  parent=0
        and id = {$ds_id}
        order by sort desc , start_time desc
        ";
        $v = $db->fetchRow($sql);
        if (!$v) {
            return ["code"=>0,"message"=>'参数错误'];
        }
        //         $sql ="
        //         select * from ds_race
        //         where is_active=1 and parent ={$ds_id}
        //         order by sort desc , start_time desc
        //         ";
        //         $child = $db->fetchAll($sql);
        
        $sql ="
            select * from ds_race_field
            where is_valid=1 and race_id =?
            order by id desc
            ";
        $child = $db->fetchAll($sql, $ds_id);
        
        
        
        $sql ="
        select * from ds_lunbo
        where ds_id  ={$ds_id}
        order by sort desc
        ";
        $lunbo = $db->fetchAll($sql);
        
        $t =[];
        $t['banner'] =Image::geturl($v['banner_bignew']);
        $t['gray_banner'] =Image::get_grayurl( $v['banner_bignew']);
        
        $t['photo'] = BBUser::get_userpic($v['uid']);
        
        
        $user_detail = \BBExtend\model\User::find( $v['uid'] );
        
        $t['role'] = $user_detail->role;
        $t['frame'] = $user_detail->get_frame();
        $t['badge'] = $user_detail->get_badge();
        
        
        
        $t['master_uid'] = $v['uid'];
        $t['master_nickname'] = \BBExtend\BBUser::get_nickname( $v['uid']);
        
        
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
        
        $status_arr = \BBExtend\video\RaceStatus::get_status($uid, $v['id']);
        $t['status'] = $status_arr['data']['status'];
        $t['describe'] = $status_arr['data']['describe'];
        
        
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
            if ($v2['race_id'] == $v['id'] ) {
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
                        'picpath' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v2['pic_bignew']),
                        'title' =>$v2['title'],
                        'linkurl' =>$v2['url'],
                ];
            }
        }
        
        $sql = "select id from ds_race where parent = {$v['id']} and  is_app=1";
        $t['app_qudao_id'] =$v['id'];
        $t['app_qudao_id'] = intval($t['app_qudao_id']);
        
        //群
        $groups = $this->get_ds_groups($ds_id);
        $t['wx_group'] = $groups['wx_group'];
        $t['qq_group'] = $groups['qq_group'];
        
        // 谢烨，这里，多了一个最新报名情况。
        $sql="select uid,pic from ds_register_log where zong_ds_id=? and has_pay=1 order by id desc limit 14";
        $result = $db->fetchAll($sql,[ $ds_id ]);
        
        $t['recent_list'] = $result;
        
        $t['upload_type'] = $v['upload_type'];
        
//         // 谢烨，这里多了一个动态列表。
//         $t['dynamic_list'] =[];
//         $sql="select * from ds_user_log
// where ds_id=? and uid =?
// order by id desc ";
//         $result = $db->fetchAll($sql,[ $ds_id, $uid ]);
        
//         foreach ($result as $v) {
//             $t['dynamic_list'][]= [
//                     'title' => $v['title'],
//                     'create_time' => $v['create_time'],
//                     'content' => $v['content'],
//             ];
//         }
        
        return ['code'=>1,       'data' => $t,     ];
       
        
    }
    
    
    
    
    public function ds_one_new( $v=1, $ds_id,$uid=10000)
    {
        if ($v>=5) {
            return $this->ds_one_new_v5($ds_id,$uid);
        }
        
        $ds_id = intval($ds_id);
        $uid=intval($uid);
        
        $time =time();
        
        $db = Sys::get_container_db();
        $sql ="
        select * from ds_race
        where  parent=0
        and id = {$ds_id}
        order by sort desc , start_time desc
        ";
        $v = $db->fetchRow($sql);
        if (!$v) {
            return ["code"=>0,"message"=>'参数错误'];
        }
//         $sql ="
//         select * from ds_race
//         where is_active=1 and parent ={$ds_id}
//         order by sort desc , start_time desc
//         ";
//         $child = $db->fetchAll($sql);
        
        $sql ="
            select * from ds_race_field
            where is_valid=1 and race_id =?
            order by id desc
            ";
        $child = $db->fetchAll($sql, $ds_id);
        
        
        
        $sql ="
        select * from ds_lunbo
        where ds_id  ={$ds_id}
        order by sort desc
        ";
        $lunbo = $db->fetchAll($sql);
        
        $t =[];
        $t['banner'] =Image::geturl($v['banner_bignew']);
        $t['gray_banner'] =Image::get_grayurl( $v['banner_bignew']);
        
        $t['photo'] = BBUser::get_userpic($v['uid']);
        
        
        $user_detail = \BBExtend\model\User::find( $v['uid'] );
        
        $t['role'] = $user_detail->role;
        $t['frame'] = $user_detail->get_frame();
        $t['badge'] = $user_detail->get_badge();
        
        
        
        $t['master_uid'] = $v['uid'];
        $t['master_nickname'] = \BBExtend\BBUser::get_nickname( $v['uid']);
        
        
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
        
        $status_arr = \BBExtend\video\RaceStatus::get_status($uid, $v['id']);
        $t['status'] = $status_arr['data']['status'];
        $t['describe'] = $status_arr['data']['describe'];
        
        
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
            if ($v2['race_id'] == $v['id'] ) {
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
                        'picpath' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v2['pic_bignew']),
                        'title' =>$v2['title'],
                        'linkurl' =>$v2['url'],
                ];
            }
        }
        
        $sql = "select id from ds_race where parent = {$v['id']} and  is_app=1";
        $t['app_qudao_id'] =$v['id'];
        $t['app_qudao_id'] = intval($t['app_qudao_id']);
        
        //群
        $groups = $this->get_ds_groups($ds_id);
        $t['wx_group'] = $groups['wx_group'];
        $t['qq_group'] = $groups['qq_group'];
        
        // 谢烨，这里多了一个动态列表。
        $t['dynamic_list'] =[];
        $sql="select * from ds_user_log 
where ds_id=? and uid =?
order by id desc ";
        $result = $db->fetchAll($sql,[ $ds_id, $uid ]);
        
        foreach ($result as $v) {
            $t['dynamic_list'][]= [
                    'title' => $v['title'],
                    'create_time' => $v['create_time'],
                    'content' => $v['content'],
            ];
        }
        
        return ['code'=>1,       'data' => $t,     ];
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
        $t['banner'] =Image::geturl($v['banner_bignew']);
        $t['gray_banner'] =Image::get_grayurl( $v['banner_bignew']);
        
        $t['photo'] = BBUser::get_userpic($v['uid']);
        
        
        $user_detail = \BBExtend\model\User::find( $v['uid'] );
        
        $t['role'] = $user_detail->role;
        $t['frame'] = $user_detail->get_frame();
        $t['badge'] = $user_detail->get_badge();
        
        
        
        $t['master_uid'] = $v['uid'];
        $t['master_nickname'] = \BBExtend\BBUser::get_nickname( $v['uid']);
        
        
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
                        'picpath' => \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v2['pic_bignew']),
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
   
   
   
}
