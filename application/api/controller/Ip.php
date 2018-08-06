<?php
namespace app\api\controller;
use think\Db;
use BBExtend\Sys;

class Ip
{

    // 添加。
    public function deny($ip)
    {
        
        $redis = Sys::get_container_redis();
        $key_list = "limit:ip:week";
        $redis->sadd( $key_list, $ip );
        return ['code'=>1,'data' =>['list' => $redis->sMembers($key_list)  ] ];
    }
    
    
    
    public function allow($ip) {
        $redis = Sys::get_container_redis();
        $key_list = "limit:ip:week";
        $has_limit = $redis->sIsMember( $key_list, $ip );
        if ($has_limit === true) {
            $redis->sRemove( $key_list, $ip );
        }
        return ['code'=>1,'data' =>['list' => $redis->sMembers($key_list)  ] ];
        
        
        
    }
    
    public function allowall() {
        $redis = Sys::get_container_redis();
        $key_list = "limit:ip:week";
        $redis->del($key_list);
        
//         $has_limit = $redis->sIsMember( $key_list, $ip );
//         if ($has_limit === true) {
//             $redis->sRemove( $key_list, $ip );
//         }
        return ['code'=>1,'data' =>['list' => $redis->sMembers($key_list)  ] ];
        
        
        
    }
    
    
}
