<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/6
 * Time: 10:17
 */

namespace app\show\controller;


//use BBExtend\BBShow;
use BBExtend\Sys;
use BBExtend\BBPush;

class Togetherplay
{
    
    public function zhibo_list($uid,$startid,$length)
    {
        $db = Sys::get_container_db();
        $startid = intval($startid);
        $length = intval($length);
        $uid = intval($uid);
        $sql="select * from bb_push where event='publish'
        and exists (select 1 from bb_users where bb_users.uid = bb_push.uid
         and bb_users.permissions=4
        )
        and exists (
          select 1 from bb_users
            where bb_users.uid = bb_push.uid
              and bb_users.not_zhibo=0
        )
        order by bb_push.time desc limit
        {$startid},{$length}
        ";
        $result = $db->fetchAll($sql);
        $new =[];
        foreach ($result as $v) {
            $new[]= BBPush::get_detail_by_row($v, $uid);
        }
        $this->is_bottom = (count($result )== $length) ? 0:1;
        return ['code'=>1, 'data'=>$new,
            'is_bottom' => (count($new)== $length)? 0:1
            
        ];
    }
    
    
    public function zhibo_list_v2($uid,$startid,$length)
    {
        $db = Sys::get_container_db();
        $startid = intval($startid);
        $length = intval($length);
        $uid = intval($uid);
        $sql="select * from bb_push where event='publish'
        and exists (select 1 from bb_users where bb_users.uid = bb_push.uid
         and bb_users.permissions=4
        )
        and exists (
          select 1 from bb_users
            where bb_users.uid = bb_push.uid
              and bb_users.not_zhibo=0
        )
        order by bb_push.time desc limit
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
        $this->is_bottom = (count($result )== $length) ? 0:1;
        return ['code'=>1, 'data'=>$new,
                'is_bottom' => (count($new)== $length)? 0:1
                
        ];
    }
    
    public function index()
    {
        $self_uid     =  input('?param.self_uid')?(int)input('param.self_uid'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        // 这是排序方案
        $sort_scheme = input('?param.sort_scheme')?(int)input('param.sort_scheme'):0;
        
        
        $db = Sys::get_container_db();
        if ($sort_scheme==0) {
            $sort_scheme = intval( date("ndHi") );
        }
        if ($start_id==0) {
            $sort_scheme++;
        }
        
        
      //  $temp =  intval( date("YmdHi") ) + 1 + $sort_scheme ;
        
        $sql="
            select * from bb_users where permissions=4
                order by crc32(uid+{$sort_scheme}) asc
                limit {$start_id},{$length}
                
                ";
        $result = $db->fetchAll($sql);
        $new_all=[];
        foreach ($result as $v) {
            $uid = $v['uid'];
            $user = \app\user\model\UserModel::getinstance($uid);
            $new['uid'] =$uid;
            $new['pic'] = $user->get_userpic();
            $new['address'] = $user->get_user_address();
            $new['specialty' ] = $user->get_hobbys()  ;
            $new['nickname' ] = $user->get_nickname() ;
            $new_all[]= $new;
        }
        return ["code"=>1, "data"=>$new_all,
            'is_bottom' => (count($new_all)== $length)? 0:1,
            'sort_scheme' => $sort_scheme,
        ];
    }
    
}