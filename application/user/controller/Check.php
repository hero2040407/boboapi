<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;

//use think\Request;
//use think\Db;



use BBExtend\Sys;
use BBExtend\DbSelect;


class Check
{
    /**
     * 查一个用户是否绑定手机号
     * 
     **/
    public function is_bind_phone($uid=0)
    {
        $uid = intval($uid);
        $user = \BBExtend\model\User::find($uid );
        if (!$user) {
            return ['code'=>0,'message' =>'用户不存在' ];
        }
        $success = $user->is_bind_phone();
        $success=intval($success);
        $db = Sys::get_container_dbreadonly();
        
        $gold = $bean = 0;
        $sql="select gold, gold_bean  from bb_currency where uid= ?";
        $row = $db->fetchRow($sql,[ $uid ]);
        if ($row) {
            $gold = $row['gold'];
            $bean = $row['gold_bean'];
        }
        
        return  ['code'=>1,'data'=>[ 'success' => $success,  
                'gold' => $gold, // 波币
                'bean' => $bean, // 波豆。
                'new_role_name' => $user->new_role_name(),
                // 是否认证 0 未认证, 1审核中, 2认证成功
                'authentication' => $user->attestation,

                'authentication_demo'=>[
                  'big_pic' =>'http://upload.guaishoubobo.com/10000/5hT5fkfHCK.png',
                  'video_path'=>'https://upload.guaishoubobo.com/10000/EHpYQTtrFf.mp4',
                ],

        ]  ];
        
    }
    
}
