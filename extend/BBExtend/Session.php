<?php

namespace BBExtend;

use BBExtend\Sys;
use think\Session as Se;
class Session
{
    // 取得当前id
    public static function get_my_id()
    {
//        if ( IS_WIN ) {
//            $file = "D:/workspace_utf8/guai2/runtime/session.txt";
//            $id = file_get_contents($file);
//            $id = intval($id);
//        //    Sys::debugxieye(11111);
//            if ($id && $id != 0 ) {
//          //      Sys::debugxieye("get ok:".$id);
//                return $id;
//            }else {
//                return false;
//            }
//        }
        
        $temp =  session("?backstage_islogin");
        if ($temp) {
            $temp2 = session("backstage_islogin");
            if ($temp2==1) {
                $id = session("backstage_id" );
                $redis = Sys::get_container_redis();
                
                $key = "backstage:id:".$id;
                $result = $redis->exists( $key );
                if ( $result ) {
                
                    return $id;
                }
            }
        }
        return false;
    }
    
    // 设置当前id,目前，只保存一天。一定会成功。
    public static function set_my_id($id)
    {
        $exists_id = self::get_my_id();
        if ($exists_id !==false   ) {
       //     return false; // 这里不能强行设置。
        }
        
//        if ( IS_WIN ) {
//            $file = "D:/workspace_utf8/guai2/runtime/session.txt";
//            file_put_contents($file, $id);
//
//        }
        
        
        session("backstage_islogin",1);
        session("backstage_id", $id );
        
        //session(null);
        $redis = Sys::get_container_redis();
        $key =  "backstage:id:".$id;
        $redis->set( $key,1 );
        $redis->setTimeout( $key, 24* 3600 );
    }
    
    
    
    // 清除当前id
    public static function clean_up_my_id(){
        
        $id = self::get_my_id();
        if ($id ===false ) {
            return false;
        }
        
//        if ( IS_WIN ) {
//            $file = "D:/workspace_utf8/guai2/runtime/session.txt";
//            file_put_contents($file, '0');
//
//        }
        
        Se::clear();
       // session(null);
        $redis = Sys::get_container_redis();
        $key =  "backstage:id:".$id;
        $redis->delete( $key );
        return true;
    }
    
    
    
    // 清除其他人id
    
    public static function clean_up_other($id)
    {
        
//        if ( IS_WIN ) {
//            $file = "D:/workspace_utf8/guai2/runtime/session.txt";
//            file_put_contents($file, '0');
//
//        }
        
        $redis = Sys::get_container_redis();
        $key =  "backstage:id:".$id;
        $redis->delete( $key );
        return true;
        
    }

}
