<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;

/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class MsgConfig extends Model 
{
    protected $table = 'bb_msg_user_config_v3';
    public $timestamps = false;
    
    private function get_param()
    {
        return [
                [
                        "bigtitle"=>"互动通知",
                        "config_list" =>
                        [
                        
                            [
                                    'type' => 122,
                                    'title' =>"关注提醒",
                            ],
                            [
                                    'type' => 119,
                                    'title' =>"点赞提醒",
                            ],
                            [
                                    'type' => 120,
                                    'title' =>"评论提醒",
                            ],
                                
                        ],
               ],
                [
                        "bigtitle"=>"好友动态",
                        "config_list" =>
                        [
                                
                                [
                                        'type' => 123,
                                        'title' =>"好友新视频动态",
                                ],
                                [
                                        'type' => 124,
                                        'title' =>"好友直播动态",
                                ],
                        ],
                ],
        ];
    }
    
    private function get_all_type()
    {
        $param = $this->get_param();
        $type_arr=[];
        foreach ( $param as $v ) {
            foreach ($v['config_list'] as $v2) {
                $type_arr[]= $v2['type'];
            }
        }
        return $type_arr;
    }
    
//     private function get_name_from_type($type)    
//     {
//         $param = $this->get_param();
//         $type_arr=[];
//         foreach ( $param as $v ) {
//             foreach ($v['config_list'] as $v2) {
//                 $type_arr[]= $v2['type'];
//             }
//         }
//         return $type_arr;
//     }
    
    
    
    
    public function set_config($uid,$type,$value)
    {
        $uid = intval($uid);
        
      
        $type_arr = $this->get_all_type();
        if (!in_array($type,  $type_arr  )) {
            return ['code'=>0,'message' =>'type error' ];
        }
        if (!in_array($value,  [0,1]  )) {
            return ['code'=>0,'message' =>'value error' ];
        }
        
        $db = Sys::get_container_db();
        //         $uid = $this->uid;
        $sql ="update bb_msg_user_config_v3
                set value=?
             where uid=? and type=? ";
        $db->query($sql,[ $value, $uid, $type ]);
        return true;
    }
    
    private function get_value_from_arr($type, $result)
    {
        foreach ($result as $v) {
            if ($v['type'] == $type ) {
                return $v['value']; 
            }
        }
        return 1;
    }
    
    /**
     * 得到所有配置
     */
    public function get_all_config($uid)
    {
        $uid = intval($uid);
        $db = Sys::get_container_db();
//         $uid = $this->uid;
        $sql ="select uid,type,value from bb_msg_user_config_v3 
             where uid={$uid} ";
        $result = $db->fetchAll($sql);
        if (!$result) {
            $this->init2($uid);
            $result = $db->fetchAll($sql);
        }
        
        $param = $this->get_param();
        foreach ($param as $k=> $v) {
            foreach ( $v['config_list'] as $k2=> $v2 ) {
                 $param[$k]['config_list'][$k2]['value'] = $this->get_value_from_arr($v2['type'], $result);
            
            }
        }
        return $param;
    }
    
    
    
    private function init2($uid)
    {
        $db = Sys::get_container_db();
        $sql="delete from bb_msg_user_config_v3 where uid = {$uid}";
        $db->query($sql);
//         互动通知
//         122 关注提醒
//         119 点赞提醒
//         120 评论提醒
        
//         好友动态
//         123 好友新视频动态
//         124 好友直播动态
        $type_arr = [122,119,120,123,124,];
        foreach ($type_arr as $v) {
            $db->insert('bb_msg_user_config_v3', [
                    'uid' =>$uid,
                    'value'=>1,
                    'type' =>$v,
            ]);
        }
        
    }
    
    
    
}
