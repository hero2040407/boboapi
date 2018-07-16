<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\BBPush;

/**
 * 234
 * @author xieye
 *
 */
class Index
{
    

    /**
     * 新版首页
     * @param number $uid
     * @return 
     */
    public function index($uid=10000)
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select id,picpath,linkurl from bb_toppic where module_name='index_top' 
order by id asc";
        $pic_list = $db->fetchAll($sql);
        
        //正在直播
        $push_list = $this->zhibo_list($uid);
        
        return ['code'=>1,'data'=>[
                'pic_list' =>$pic_list,
                'push_list' => $push_list,
                'advise_list' =>$this->tonggao_list(),
                'vip_ranking' =>$this->rank_tongxing(),
                'recommend_user' => $this->rank_recommend(),// 主打童星
                
                
        ]];
    }
    
    /**
     * 选9个人。推荐的。
     */
    private function rank_recommend()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_users_recommend
order by create_time desc
limit 9
";
        $result = $db->fetchAll($sql);
        $new =[];
        foreach ( $result as $v ) {
            $uid = $v['uid'];
            $user = \BBExtend\model\UserDetail::find($uid);
            $temp = $user->get_info_201807_extend();
            $temp['is_upgrade'] = $v['is_upgrade'] ; 
            $new[]= $temp;
        }
        return $new;
    }
    
    /**
     * 童星排行。
     */
    private function rank_tongxing()
    {
        $key = "tongxing_index20180704:list";
        $redis = Sys::get_container_redis();
        
        $start = 0;
        $count = 3;
        $end = $start+ $count -1;
        
        
        $result = $redis->zrevrange($key,$start,$end);
        $new=[];
        foreach ($result as $uid) {
            $user = \BBExtend\model\UserDetail::find($uid);
            $new[]= $user->get_info_201807();
        }
        return $new;
    }
    
    /**
     * 首页通告列表
     * @param number $length
     */
    private function tonggao_list($length=5)
    {
        $length=intval($length);
        
        $time = time();
        
        $db = Sys::get_container_dbreadonly();
        $sql="select id from bb_advise 
where is_active=1
and end_time > ?
limit {$length}";
        $ids= $db->fetchCol($sql,[ $time ]);
        $new=[];
        foreach ($ids as $id) {
            $obj = \BBExtend\model\Advise::find( $id );
            $new[]= $obj->get_index_info();
        }
        return $new;
        
    }
    
    private function zhibo_list($uid,$startid=0,$length=1)
    {
        $db = Sys::get_container_dbreadonly();
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
       // $this->is_bottom = (count($result )== $length) ? 0:1;
        return $new;
    }
    

}





