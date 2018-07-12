<?php
namespace BBExtend\backmodel;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 大赛用户日志
 * 
 * type1报名日志，2视频上传日志，3视频审核日志，4晋级日志。
 * 
 */
class RaceLog
{
    
    /**
     * 报名日志，type=1
     * 
     * 问题是，什么时候调用这里。
     * 假设不需缴费，则报名成功直接插入，
     * 否则缴费后插入，
     * 为简单起见，我现在就插入。
     * 
     * 
     */
    public static function register($ds_id, $uid)
    {
        $db = Sys::get_container_db_eloquent();
        $db::table('ds_user_log')->insert([
                'ds_id' =>$ds_id,
                'create_time' =>time(),
                'uid' =>$uid,
                'type' =>1,
                'title' =>'报名成功',
                'content' =>'报名成功，请上传参赛视频',
        ]);
    }
    
    /**
     * 视频上传日志，type=2
     */
    public static function upload($ds_id, $uid)
    {
        $db = Sys::get_container_db_eloquent();
        $db::table('ds_user_log')->insert([
                'ds_id' =>$ds_id,
                'create_time' =>time(),
                'uid' =>$uid,
                'type' =>2,
                'title' =>'审核中',
                'content' =>'视频已上传正在审核',
        ]);
    }
    
    
    /**
     * 视频审核日志，type=3,
     * audit=1 成功，audit=2 ，失败。
     */
    public static function check($ds_id, $uid,$audit)
    {
        $db = Sys::get_container_db_eloquent();
        Sys::debugxieye("racelog:{$ds_id}||{$uid}||{$audit}");
        if ($audit==1) {
        
            $ds = Race::find( $ds_id );
      //      $rank = $ds->rank( $uid );
            
            $db::table('ds_user_log')->insert([
                    'ds_id' =>$ds_id,
                    'create_time' =>time(),
                    'uid' =>$uid,
                    'type' =>3,
                    'title' =>'视频审核通过',
        //            'content' =>'视频审核已通过，'. "当前网络排名 {$rank}，" .'分享视频提高排名',
		            'content' =>'视频审核已通过，分享视频提高排名',
            ]);
        }else {
            $db::table('ds_user_log')->insert([
                    'ds_id' =>$ds_id,
                    'create_time' =>time(),
                    'uid' =>$uid,
                    'type' =>3,
                    'title' =>'视频审核未通过',
                    'content' =>'视频审核未通过，请重新上传',
            ]);
        }
    }
    
    /**
     * 晋级日志，type=4
     * 
     * success =1 成功，success=2 失败。
     */
    public static function upgrade($ds_id, $uid, $success)
    {
        $db = Sys::get_container_db_eloquent();
        if ($success==1) {
            
            $ds = Race::find( $ds_id );
        //    $rank = $ds->rank( $uid );
			
			$rank=0;
			
            $content = '恭喜您通过线下选拔，成功晋级';
            if ( $rank>0 ) {
                $content.="，网络排名：{$rank}";
            }
            
            $db::table('ds_user_log')->insert([
                'ds_id' =>$ds_id,
                'create_time' =>time(),
                'uid' =>$uid,
                'type' =>4,
                'title' =>'成功晋级',
                'content' =>$content,
            ]);
        }else {
            
            $db::table('ds_user_log')->insert([
                    'ds_id' =>$ds_id,
                    'create_time' =>time(),
                    'uid' =>$uid,
                    'type' =>2,
                    'title' =>'晋级失败',
                    'content' =>'晋级失败，可尝试报名其他赛区',
            ]);
            
        }
    }
    
    
    
}
