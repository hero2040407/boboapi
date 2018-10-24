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
    public function like($uid,$race_id ,$self_uid, $token,$type=1,$spec_id=''  )
    {
        if ( !in_array($type, [1,2, 3]) ) {
            return ['code'=>0,'message' => 'type error' ];
        }
        $vnum = 10;

        if ($spec_id){
            //读取json文件
            $data = file_get_contents(APP_PATH.'/json/spec.json');
            //json转换
            $data = json_decode($data,true);
            //类型
            $spec = $data['price_vnum'];
            //删除
            unset($data);
            //判断是否存在
            if(!isset($spec[$spec_id])) return  ['code' =>0,'message' => '非法访问' ] ;
            $vnum = $spec[$spec_id]['vnum'];
        }

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
        $result = $info2->like ($self_uid, $row['id'], $type, $vnum);
        if ($result) {
            return ['code'=>1,'data' =>['count' =>$info2->success_count, 'gold' => $info2->gold ] ];
        }else {
            return ['code'=> $info2->error_code, 'message' =>$info2->err_msg  ];
        }
    }
   
    

}


