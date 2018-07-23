<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 童星排行
 * 
 * @author xieye
 *
 */
class Vip
{
    public function sign_list($startid=0,$length=10,$uid=10000,$title='',$address='',
            $min_age=-1,$max_age=-1,
            $sex=-1,$hobby=''
            
            )
    {
        $startid=intval($startid);
        $length=intval($length);
        
        $page = $startid/$length;
        $page = intval($page)+1;
        
      //  $db = Sys::get_container_dbreadonly();
        
        $db = Sys::get_container_db_eloquent();
        
        $paginator = $db::table('bb_users_info')->select(['uid',]);
        $paginator =  $paginator->where( "has_sign", 1 );
        
        if ($title) {
            $paginator = $paginator->whereExists(function ($query) use ($title, $db) {
                $query->select($db::raw(1))
                ->from('bb_users')
                ->whereRaw('bb_users.uid = bb_users_info.uid')
                ->where('bb_users.nickname',$title)
                ->where('bb_users.role',3)
                ;
            });
        }
        
        if ($address) {
            $paginator = $paginator->whereExists(function ($query) use ($address, $db) {
                $query->select($db::raw(1))
                ->from('bb_users')
                ->whereRaw('bb_users.uid = bb_users_info.uid')
                ->where('bb_users.address', $address )
                ->where('bb_users.role', 3)
                ;
            });
        }
        if ($min_age!=-1) {
            $min_age = intval( $min_age );
            $year = date("Y") - $min_age;
            
            $paginator = $paginator->whereExists(function ($query) use ($year, $db) {
                $query->select($db::raw(1))
                ->from('bb_users')
                ->whereRaw('bb_users.uid = bb_users_info.uid')
                ->whereRaw('left(birthday,4) <=  '. $year )
                ->where('bb_users.role', 3)
                ;
            });
        }
        
        if ($max_age!=-1) {
            $max_age = intval( $max_age );
            $year = date("Y") - $max_age;
            
            $paginator = $paginator->whereExists(function ($query) use ($year, $db) {
                $query->select($db::raw(1))
                ->from('bb_users')
                ->whereRaw('bb_users.uid = bb_users_info.uid')
                ->whereRaw('left(birthday,4) >=  '. $year )
                ->where('bb_users.role', 3)
                ;
            });
        }
        
        if ($sex!=-1) {
            $sex = intval( $sex );
            $paginator = $paginator->whereExists(function ($query) use ($sex, $db) {
                $query->select($db::raw(1))
                ->from('bb_users')
                ->whereRaw('bb_users.uid = bb_users_info.uid')
                ->where('bb_users.sex', $sex )
                ->where('bb_users.role', 3)
                ;
            });
        }
        
        
        if ($hobby ) {
            
            $temp =  explode(',', $hobby) ;
            $i=0;
            $temp2 =[];
            foreach ($temp as $v) {
                $temp2[]= intval($v);
                $i++;
                if ($i ==3) {
                    break;
                }
            }
            $hobby = implode(',', $temp2);// 类似 3,5
          //  $speciality_list = json_encode($temp2);
            
            //$sex = intval( $sex );
            $paginator = $paginator->whereExists(function ($query) use ($hobby, $db) {
                $query->select($db::raw(1))
                ->from('bb_user_hobby')
                ->whereRaw('bb_user_hobby.uid = bb_users_info.uid')
                ->whereRaw('bb_user_hobby.hobby_id in ('. $hobby .')' )
                ;
            });
        }
        
        
        
        $paginator = $paginator->orderBy('sign_time', 'desc')->paginate($length, ['*'],'page',$page);
        $new=[];
        foreach ($paginator as $v) {
            $uid2 = $v->uid;
            $user = \BBExtend\model\UserDetail::find($uid2);
            $temp = $user->get_info_201807_focus($uid);
            $new[]= $temp;
        }
        
        
        return [
                'code'=>1,
                'data'=>[
                        'list' =>$new,
                        'is_bottom' =>(count($new) == $length)?0:1,
                ]
        ];
        
    }
    
    
    /**
     * 主打童星。
     * 
     */
    public function recommend_list($type = 1, $startid=0,$length=10,$uid=10000)
    {
        //Sys::display_all_error();
        
        $startid=intval($startid);
        $length=intval($length);
        $db = Sys::get_container_dbreadonly();
        if($type==1) {
           $sql = "select * from bb_users_recommend
order by create_time desc
limit {$startid},{$length}
";
        } else {
            $time = time() - 7 * 24 * 3600;
            $sql="
             select * from bb_users_info where vip_time > {$time} or sign_time >{$time}
order by id desc limit {$startid},{$length}

";
            
        }
        $result = $db->fetchAll($sql);
        $new =[];
        foreach ( $result as $v ) {
            $uid2 = $v['uid'];
            $user = \BBExtend\model\UserDetail::find($uid2);
            $temp = $user->get_info_201807_focus($uid);
           // $temp['is_upgrade'] = $v['is_upgrade'] ;
            $new[]= $temp;
        }
        return [
                'code'=>1,
                'data'=>[
                        'list' =>$new,
                        'is_bottom' =>(count($new) == $length)?0:1,
                ]
        ];
    }
    
    

    /**
     * 新版首页，童星排行 之 
     * 
     * type=1，魅力排行。
     * type =2 活跃人气
     * 
     * 魅力值排行积分值=粉丝数+int(点赞数/50)

                         活跃排行积分值=视频数+大赛次数*10+通告次数*15

     * 
     * @param number $uid
     * @return 
     */
    public function index($uid=10000,$startid=0, $length=10, $type=1)
    {
        $key = "tongxing_index20180704:list";
        if ($type!=1) {
            $key = "tongxing_index20180704:list:renqi";
        }
        
        
        $redis = Sys::get_container_redis();
        
        $startid=intval($startid);
        $length=intval($length);
        
//         $start = 0;
//         $length = 3;
//         $end    = 2;
        $end = $startid+ $length -1;
        
        
        
        $result = $redis->zrevrange($key,$startid,$end);
        $new=[];
        foreach ($result as $uid2) {
            $user = \BBExtend\model\UserDetail::find($uid2);
            $new[]= $user->get_info_201807_extend();
        }
        
        $is_bottom = ( count($new)==$length )? 0:1;
        
        //现在检查 uid 是否在缓存中。
        $rank =  $redis->zRevRank($key, $uid);
        if ( is_numeric( $rank) ) {
            $rank++;
        }else {
            $rank = null;
        }
        
        return ['code'=>1, 'data'=>[
                'list' => $new,
                'is_bottom' =>$is_bottom,
                'rank' =>$rank,
        ]];
        
        
    }
    
    public function help(){
        $detail=<<<html
怪兽bobo招募小童星覆盖全国各地区范围，目标是打造全国最漂亮、最有才艺的500个孩子，现第一阶段已经签约了二十余位小艺人，有着例如金牌小艺人嘟嘟这类的超高颜值的萌娃，也有着类似于李思闵一样的才艺童星。所有签约艺人由经纪人直接对接做出发展规划，并进行线上线下推广与拍摄，招募则是通过试镜卡参与试镜录制与经纪人面试，试镜通过并符合标准的孩子将有机会与怪兽bobo进行童星签约，有童星经纪人专门推广运营，也会参与我们的拍摄通告哦！
参与试镜卡申请的孩子需要将资料（模卡形象照片、自我介绍视频、演出经历才艺特长）发送至后台邮箱 3446711614@qq.com，由工作人员审核回复。
html;
        $standard=<<<html
自信并有梦想的4-15周岁孩子，颜值在线或有着过人的才艺，有着基础的沟通交流能力。生活中喜欢在镜头下展示自我，附有一定的表演天赋与综艺感。
html;
        
        return [
                "detail"=>$detail,
                "standard"=>$standard,
                "user" =>[
                   "pic" => "http://bobo-upload.oss-cn-beijing.aliyuncs.com/public/help/logo.png",     
                        "nickname"=>"怪兽客服",
                        "phone" => "400-880-2610",
                ],
        ];
        
    }
    
    public function audition_help()
    {
        return [
                'code'=>1,
                'data'=>\BBExtend\video\AuditionHelp::index(),
                
        ];
    }
    

}


