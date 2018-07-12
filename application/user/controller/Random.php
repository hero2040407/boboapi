<?php
/**
 * 用户个人信息
 */

namespace app\user\controller;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\DbSelect;


class Random
{
    public function index ($uid,$key)
    {
        if (!in_array($key,['gexing','jingyan','huojiang','zhuanye'   ])){
            return ['code'=>0,'message' =>'key error'  ];
        }
        $db = Sys::get_container_db_eloquent();
        if (in_array( $key, ['gexing','jingyan' ])) {
            
            $sql="select uid from bb_users where role=3 and uid != ? 
    and exists(
        select 1 from bb_users_info
         where bb_users_info.uid = bb_users.uid
          and bb_users_info.gexing !=''
          and bb_users_info.jingyan !=''
)
  limit 10";
            
        }
        
        if (in_array( $key,[ 'huojiang','zhuanye'] )) {
          //  $db = Sys::get_container_db_eloquent();
            $sql="select uid from bb_users where role=2 and uid != ?
    and exists(
        select 1 from bb_users_starmaker
         where bb_users_starmaker.uid = bb_users.uid
          and bb_users_starmaker.huojiang !=''
          and bb_users_starmaker.zhuanye !=''
)
  limit 10";
            
        }
        $ids = DbSelect::fetchCol($db, $sql,[ $uid ]);
        if (!$ids) {
            return ['code'=>0,'message'=>'未查到信息'];
        }
        
        shuffle($ids);
        $id = array_pop($ids);
        $user = User::find($id);
        
        if ($key=='gexing') {
            $result = $user->get_gexing_arr();
        }
        if ($key=='jingyan') {
            $result = $user->get_jingyan_arr();
        }
        if ($key=='huojiang') {
            $result = $user->get_tutor_huojiang_arr();
        }
        if ($key=='zhuanye') {
            $result = $user->get_tutor_zhuanye_arr();
        }
        
        return ['code'=>1,'data'=>[
                'uid' => $id,
                'nickname' => $user->get_nickname(),
                'pic' => $user->get_userpic(),
                'result' => $result,
                
        ]];
        
        
    }
    
   
}

