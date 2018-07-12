<?php
/**
 * 视频点赞类
 * 
 * User: 谢烨
 */
namespace BBExtend\video;
use think\Db;
use BBExtend\BBRedis;
// use app\push\controller\Pushmanager;
// use app\record\controller\Recordmanager;
// use think\Request;

class Like 
{
    /**
     * ip点赞，可以录播，回播
     * @param string $type
     * @param string $room_id
     */
    public static function ip_like($ip, $type='', $room_id='')
    {
        
        //return 11;
        
//         $ip = $_SERVER['REMOTE_ADDR'];
        if(!filter_var($ip, FILTER_VALIDATE_IP)) {
            return ["code"=>0, 'message'=>'ip错误'];
        }
        switch ($type)
        {
//             case 'push':
//                 return Pushmanager::_like($ip,$room_id);
            case 'record':
                return self::ip_record_like($ip,$room_id);
            case 'rewind':
                return self::ip_rewind_like($ip,$room_id);
           default:
               return ['code'=>0, 'message'=>'type错误'];
        }
    }
    
    
    /**
     * ip 取消点赞
     * 
     * @param string $type
     * @param string $room_id
     * @return number[]|string[]|\BBExtend\video\string[]|\BBExtend\video\number[]|string[]|number[]
     */
    public static function ip_unlike($ip, $type='', $room_id='')
    {
    
//         $ip = $_SERVER['REMOTE_ADDR'];
        if(!filter_var($ip, FILTER_VALIDATE_IP)) {
            return ["code"=>0, 'message'=>'ip错误'];
        }
        switch ($type)
        {
            //             case 'push':
            //                 return Pushmanager::_like($ip,$room_id);
            case 'record':
                return self::ip_record_unlike($ip,$room_id);
            case 'rewind':
                return self::ip_rewind_unlike($ip,$room_id);
            default:
                return ['code'=>0, 'message'=>'type错误'];
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * 回播点赞。
     * 
     * @param unknown $ip
     * @param unknown $room_id
     */
    public static function ip_rewind_like($ip, $room_id)
    {
        $movieDB = Db::table('bb_rewind')->where(['room_id'=>$room_id])->find();
        if ($movieDB)
        {
            $LikeDB = Db::table('bb_rewind_like')->where(['ip'=>$ip, 'room_id'=>$room_id])->find();
            if (!$LikeDB)
            {
                $Data = array();
                $Data['uid'] = 0;
                $Data['ip'] = $ip;
                $Data['room_id'] = $room_id;
                $Data['time'] = time();
                Db::table('bb_rewind_like')->insert($Data);
                $like = $movieDB['like'] + 1;
                Db::table('bb_rewind')->where(['room_id'=>$room_id])->update(['like'=>$like]);
                return ['message'=>'点赞成功','code'=>1];
            }
            return ['message'=>'你已经点过赞了','code'=>0];
        }
        return  ['message'=>'没有这个视频信息请检查房间号是否正确','code'=>0];
    }
    
    /**
     * 回播取消点赞。
     *
     * @param unknown $ip
     * @param unknown $room_id
     */
    public static function ip_rewind_unlike($ip, $room_id)
    {
        $movieDB = Db::table('bb_rewind')->where(['room_id'=>$room_id])->find();
        if ($movieDB)
        {
            $LikeDB = Db::table('bb_rewind_like')->where(['ip'=>$ip, 'room_id'=>$room_id])->find();
            if ($LikeDB)
            {
                $db = \BBExtend\Sys::get_container_db();
                $sql = "delete from bb_rewind_like where id = ?";
                $db->query($sql, $LikeDB['id']);
                $sql ='update bb_rewind set `like` = `like` -1 where room_id = ?';
                $db->query($sql, $room_id);
                return ['message'=>'取消点赞成功','code'=>1];
            }
            return ['message'=>'您没有点过赞','code'=>0];
        }
        return  ['message'=>'没有这个视频信息请检查房间号是否正确','code'=>0];
    }
    
    
    /**
     * 用ip给录播点赞。html页面
     * 
     * @param unknown $ip
     * @param unknown $room_id
     * @return string[]|number[]
     */
    public static function ip_record_like($ip, $room_id)
    {
        
       // echo 23;return;
        $movieDB = \BBExtend\BBRecord::get_movies_by_room_id($room_id);
        if ($movieDB)
        {
            $LikeDB = Db::table('bb_record_like')->where(['ip'=>$ip,'room_id'=>$room_id])->find();
            if (!$LikeDB)
            {
                
                
                //日志记录
                $Data = array();
                $Data['uid'] = 0;
                $Data['ip'] = $ip;
                $Data['room_id'] = $room_id;
                $Data['time'] = time();
                Db::table('bb_record_like')->insert($Data);
                
               
                
                //保存record表，并设置redis
                $movieDB['like'] = (int)$movieDB['like'] + 1;
                $db = \BBExtend\Sys::get_container_db();
//                 echo $room_id;return;
                $sql ="update bb_record set `like` = `like` +1 where room_id = '{$room_id}'";
     //          \BBExtend\Sys::debugxieye($sql);
                $db->query($sql);
//                 echo 3334;return;
                
                BBRedis::getInstance('record')->hMset($room_id.'record',$movieDB);
                return ['message'=>'点赞成功','code'=>1];
            }
            return ['message'=>'你已经点过赞了','code'=>0];
        }
        return  ['message'=>'没有这个视频信息请检查房间号是否正确','code'=>0];
    }
    
    
    /**
     * 用ip给录播取消点赞。html页面
     *
     * @param unknown $ip
     * @param unknown $room_id
     * @return string[]|number[]
     */
    public static function ip_record_unlike($ip,$room_id)
    {
        //return;
        $movieDB = \BBExtend\BBRecord::get_movies_by_room_id($room_id);
        if ($movieDB)
        {
            $LikeDB = Db::table('bb_record_like')->where(['ip'=>$ip,'room_id'=>$room_id])->find();
            
            if ($LikeDB)
            {
                $db = \BBExtend\Sys::get_container_db();
                $sql ="delete from bb_record_like where id=?";
                $db->query($sql, $LikeDB['id']);
                $sql ="update bb_record set `like` = `like` -1 where room_id = ?";
                $db->query($sql, $room_id);
                
                $movieDB['like'] = (int)$movieDB['like'] - 1;
                BBRedis::getInstance('record')->hMset($room_id.'record',$movieDB);
                return ['message'=>'取消点赞成功','code'=>1];
            }
            return ['message'=>'你没有点过赞','code'=>0];
        }
        return  ['message'=>'没有这个视频信息请检查房间号是否正确','code'=>0];
    }
    
    

}