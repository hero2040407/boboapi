<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 大赛分享页面。。
 * 
 * @author xieye
 *
 */
class Share
{
    public function race($uid,$race_id,$self_uid=0   )
    {
        $self_uid=intval($self_uid);
        $uid = intval($uid);
        $race_id = intval($race_id);
        
        $db = Sys::get_container_dbreadonly();
        $sql="select * from ds_register_log where uid=? and zong_ds_id=? and has_pay=1";
        $row = $db->fetchRow($sql,[ $uid,$race_id  ]);
        if (!$row) {
            return ['code'=>0, 'message' =>'未报名错误' ];
        }
        
        // 谢烨，现在获取此人的个人信息。
        $info2 = new \BBExtend\model\UserRace();
        
        
        
        return ['code'=>1, 'data' =>$info2->info( $row['id'], $self_uid )  ];
        
    }
    
    // type=1 普通，type=2 分享投票，type=3 波币购买投票。
    public function like($uid,$race_id ,$self_uid, $token,$type=1  )
    {
        $db = Sys::get_container_dbreadonly();
        $selfuser = \BBExtend\model\User::find($self_uid );
        if (!$selfuser) {
            return ['code'=>0,'message' =>'uid err'];
        }
        if (!$selfuser->check_token( $token )) {
            return ['code'=>0,'message' =>'uid err'];
        }
        
        $sql="select * from ds_register_log where uid=? and zong_ds_id=? and has_pay=1";
        $row = $db->fetchRow($sql,[ $uid,$race_id  ]);
        if (!$row) {
            return ['code'=>0, 'message' =>'参数错误' ];
        }
        
        
      
        // 谢烨，现在获取此人的个人信息。
        $info2 = new \BBExtend\model\UserRace();
        $result = $info2->like ( $self_uid, $row['id'], $type);
        if ($result) {
            return ['code'=>1,'data' =>['count' =>$info2->success_count ] ];
        }else {
            return ['code'=>0, 'message' =>$info2->err_msg  ];
        }
        
        
        
    }
   
    

}


