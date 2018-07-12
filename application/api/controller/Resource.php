<?php
namespace app\api\controller;
use BBExtend\Sys;

/**
 * 动图资源控制器，按杨桦要求
 * @author xieye
 *
 */
class Resource
{
    const max_count=54;
    private function get_row($row)
    {
        $new = [];
        $new['id']=$row['id'];
        $new['title'] = $row['title'];
        
        $new['pic']=  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $row['pic'] );
        if ( isset( $row['url'] ) )
            $new['url']=  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(  $row['url'] );
        if ( isset( $row['position'] ) )
            $new['position']=  $row['position'];
                
            
        $new['pic_gray']=  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $row['pic_gray'] );
        if (!$new['pic_gray']) {
            $new['pic_gray']= $new['pic'];
        }
        
        return $new;
    }
    
    /**
     * 新版资源，只动图
     * @param number $uid
     * @return number[]|string[]|number[]|array[][][]
     */
    public function gif($uid=0)
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select count(*) from bb_users_test where uid=?";
        $count = $db->fetchOne( $sql,[ $uid ]);
        if (!$count) {
    //        return ['code'=>0,'message'=>'暂未开通' ]; // 这是防止正式用户看到的措施。可注释。
        }
        
        $redis = Sys::get_container_redis();
        
        
        $my = [];
        $my['id'] = -3;// 我的
        $my['pic'] = 'http://resource.guaishoubobo.com/public/face/img_my_click@3x.png';
        $my['pic_gray'] = 'http://resource.guaishoubobo.com/public/face/img_my@3x.png';
        $list = [];
        
        
        $my['list'] = $list;
        
        
        $hot_key ='gif_hot_key';
        $new_key = 'gif_new_key';
        
        $hot['id'] = -2;// 最热
        $hot['pic'] = 'http://resource.guaishoubobo.com/public/face/zuire.png';
        $hot['pic_gray'] = 'http://resource.guaishoubobo.com/public/face/zuire_gray.png';
        $result = $redis->hGetAll($hot_key)  ;
        // 对temp排序。
        arsort($result);
        $result = array_keys($result);
        
        $list=[];
        $count = self::max_count;
        $i=0;
        foreach ( $result as $v ) {
            $sql="select * from bb_resource where id=?";
            $temp = $db->fetchRow($sql, $v);
            if ($i++ > $count) {
                break;
            }
            if ($temp) {
                $temp = $this->get_row($temp);
                unset($temp['pic_gray'] );
                $list[]= $temp;
            }
        }
        $hot['list'] = $list;
        
        
        // 最新的逻辑
        $new2['id'] = -1;// 最新
        $new2['pic'] = 'http://resource.guaishoubobo.com/public/face/zuixin.png';
        $new2['pic_gray'] = 'http://resource.guaishoubobo.com/public/face/zuixin_gray.png';
       // $result = $redis->lRange($new_key, 0, -1);
        
        $sql = "
select id from bb_resource where type=3
and exists(
 select 1 from bb_resource_group
  where bb_resource_group.id = bb_resource.group_id
)
order by create_time desc limit  " . self::max_count;
        
        $result = $db->fetchCol($sql);
        
        $i=0;
        $list=[];
        foreach ( $result as $v ) {
            $sql="select * from bb_resource where id=?";
            $temp = $db->fetchRow($sql, $v);
            if ($i++ > $count) {
                break;
            }
            if ($temp) {
                $temp = $this->get_row($temp);
                unset($temp['pic_gray'] );
                $list[]= $temp;
            }
        }
        $new2['list'] = $list;
        
        
        
        
        $sql ="select * from bb_resource_group
                where type=3
                order by sort desc
                ";
        $result = $db->fetchAll($sql);
        $new=[];
        foreach ($result as $v){
                $temp= $this->get_row( $v );
                $temp['list']=[];
                $new[]= $temp;
        }
        
        
        $sql ="select * from bb_resource
                where type=3
                order by sort desc
                ";
        $result2 = $db->fetchAll($sql);
        
        foreach ($new as $k=>$v) {
            foreach ($result2 as $v2){
                if ( $v2['group_id']== $v['id'] ) {
                    $temp = $this->get_row($v2);
                    unset($temp['pic_gray'] );
                    
                    $new[$k]['list'][]= $temp;
                    
                }
            }
        }
        $arr=[];
        $arr[]= $my;
        $arr[]= $hot;
        $arr[]= $new2;
        foreach ($new as $v) {
            $arr[] = $v;
        }
        return ['code'=>1,'data'=>[ 'list'=> $arr ],      ];
        
    }
    
    
    
    public function mp3($keyword='')
    {
        
        $db = Sys::get_container_dbreadonly();
        
        $sql ="select id,title,pic,url,group_id,author,display_duration from bb_resource
                where type=2
                order by sort asc
                ";
        if ( $keyword ) {
            $keyword = \BBExtend\common\Str::like($keyword);
            if ($keyword) {
                $sql ="select id,title,pic,url,group_id,author,display_duration from bb_resource
                where type=2
                 and title like '%{$keyword}%'
                order by sort asc
                ";
            }else {
                $sql ="select id,title,pic,url,group_id,author,display_duration from bb_resource
                where type=10000000
                order by sort asc
                ";
            }
            
            
        }
        
        $resource_arr = $db->fetchAll($sql);
        foreach ($resource_arr as $k=>$v) {
            if (!preg_match('/^http/', $v['pic'])) {
                $resource_arr[$k]['pic'] = \BBExtend\common\BBConfig::get_server_url() . $resource_arr[$k]['pic'];
            }
            if (!preg_match('/^http/', $v['url'])) {
                $resource_arr[$k]['url'] = \BBExtend\common\BBConfig::get_server_url() . $resource_arr[$k]['url'];
            }
        }
        return ['code'=>1, 'data'=> [ 'list' =>$resource_arr  ] ];
        
    }
    
    public function index()
    {
        $db = Sys::get_container_db();
//         $sql="select id,title,sort,type from bb_resource_group order by sort asc";
//         $resource_group_arr = $db->fetchAll($sql);
        
        $sql="select * from bb_resource_group
                where type in (1,2)
                order by type asc, sort asc
                ";
        $resource_group = $db->fetchAll($sql);
        
//         $sql="select bb_resource.id,
//                   bb_resource.group_id,
//                   bb_resource.sort,
//                 bb_resource.type,
//                 bb_resource.pic,
//                 bb_resource.url,
//                 bb_resource.title,
//                 bb_resource_group.title group_name
                                
//                  from bb_resource 
//                 left join bb_resource_group
//                  on bb_resource_group.id = bb_resource.group_id
//                 order by bb_resource_group.type asc,
//                   bb_resource_group.sort asc,
//                   bb_resource.sort asc
//                 ";
        $sql ="select id,title,pic,url,group_id,type from bb_resource
                where type in (1,2)
                order by sort asc
                ";
        $resource_arr = $db->fetchAll($sql);
        foreach ($resource_arr as $k=>$v) {
            if (!preg_match('/^http/', $v['pic'])) {
                $resource_arr[$k]['pic'] = \BBExtend\common\BBConfig::get_server_url() . $resource_arr[$k]['pic'];
            }
            if (!preg_match('/^http/', $v['url'])) {
                $resource_arr[$k]['url'] = \BBExtend\common\BBConfig::get_server_url() . $resource_arr[$k]['url'];
            }
        }
        
        $result=[];
        $type_arr =[1=>'动图',2=>"音乐"];
        foreach (range(1,2) as $type) {
            $type_name = $type_arr[$type];
            $result[$type] =[];
            foreach ($resource_group as $group) {
                if ($group['type'] == $type ) {
                    $group['resource_list'] = [];
                    foreach ($resource_arr as $resource) {
                        if ($resource['group_id'] == $group['id'] ) {
                            $group['resource_list'][] = $resource;
                        }
                    }
                    $result[$type][] = $group;
                }
            }
        }
        
        return ['code'=>1,'data'=>$result,      ];
        
    }
}
