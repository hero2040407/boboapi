<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/2
 * Time: 10:08
 */

namespace BBExtend;
use app\push\controller\Rewindmanager;
use think\Db;
use app\user\controller\User;
use BBExtend\Sys;

define('SHOW_TYPE_HEAD',100);//热门
define('SHOW_TYPE_REC',101);//推荐
define('SHOW_TYPE_NEW',102);//最新
define('SHOW_TYPE_FUJIN',103);//附近

class BBShow
{

    
    public function get_show($uid,$sort,$limit_StartPos=0,$length = 20,$activity = 0,
            $type = 0,$address='')
    {
        $push_listDB =  self::get_show_list($uid,$sort,$limit_StartPos,$length/2,$activity,$type,
                $address);
        $record_listDB = self::get_record_show($uid,$sort,$limit_StartPos,$length/2,$activity,
                $type,$address);
        $Data = array_merge($push_listDB,$record_listDB);
        $count_push = count($push_listDB);
        $count_record = count($record_listDB);
        if($count_push != $length/2)
        {
            $count = $length/2 - $count_push;
            $record_listDB = self::get_record_show($uid,$sort,$limit_StartPos+$count_record,
                    $count,$activity,$type,$address);
            $Data = array_merge($Data,$record_listDB);
        }
        if($count_record != $length/2)
        {
            $count = $length/2 - $count_record;
            $push_listDB =  self::get_show_list($uid,$sort,$limit_StartPos+$count_push,$count,
                    $activity,$type,$address);
            $Data = array_merge($Data,$push_listDB);
        }
        return $Data;
    }
    
    
    
    public function get_play_show($uid,$sort,$record_sort,$limit_StartPos=0,$length = 20,
            $activity = 0,$type = 0,$address='')
    {
        $push_listDB =  self::get_show_list($uid,$sort,$limit_StartPos,$length/2,$activity,
                $type,$address);
        $record_listDB = self::get_record_show($uid,$record_sort,$limit_StartPos,$length/2,
                $activity,$type,$address);
        $Data = array_merge($push_listDB,$record_listDB);
        $count_push = count($push_listDB);
        $count_record = count($record_listDB);
        if($count_push != $length/2)
        {
            $count = $length/2 - $count_push;
            $record_listDB = self::get_record_show($uid,$record_sort,
                    $limit_StartPos+$count_record,$count,$activity,$type,$address);
            $Data = array_merge($Data,$record_listDB);
        }
        if($count_record != $length/2)
        {
            $count = $length/2 - $count_record;
            $push_listDB =  self::get_show_list($uid,$sort,$limit_StartPos+$count_push,
                    $count,$activity,$type,$address);
            $Data = array_merge($Data,$push_listDB);
        }
        return $Data;
    }
//    protected static function get_play_show($uid,$sort,$limit_StartPos,$length,$activity)
//    {
//        $push_listDB =  self::get_show_list($uid,$sort,$limit_StartPos,$length,$activity,
//SHOW_TYPE_HEAD);
//        return $push_listDB;
//    }

    
    
    
    
    
    public static function get_show_list($uid,$sort,$limit_StartPos=0,$length = 20,
            $activity = 0,$type = 0,$address = '')
    {
        $DBList = array();
        $buy_help = new \BBExtend\user\Relation();
        switch ($type)
        {
            case SHOW_TYPE_HEAD:
                $DBList = Db::table('bb_push')->where(['sort'=>$sort,'event'=>'publish'])
                  ->order(['people'=>'desc'])->limit($limit_StartPos,$length)->select();
                break;
            case SHOW_TYPE_NEW:
                $db = Sys::get_container_db_eloquent();
                $sql="select bb_push.* from bb_push
                        where sort = ? and event='publish'
                          and exists (
                          select 1 from bb_users 
                           where bb_users.uid = bb_push.uid
                             and bb_users.not_zhibo=0
                        )
                         order by time desc 
                        limit ?,?
                        ";
                $DBList = \BBExtend\DbSelect::fetchAll($db, $sql,[$sort, $limit_StartPos,$length ]); 
                
//                 $DBList = Db::table('bb_push')->where(['sort'=>$sort,'event'=>'publish'])
//                   ->order(['time'=>'desc'])->limit($limit_StartPos,$length)->select();
                break;
            case SHOW_TYPE_REC:
                $DBList = Db::table('bb_push')->where(['sort'=>$sort,'event'=>'publish'])
                  ->order(['heat'=>'desc'])->limit($limit_StartPos,$length)->select();
                break;
            case SHOW_TYPE_FUJIN:
                
                $address = self::filter_str($address);
                if ($address) {
                    
                    $DBList = Db::table('bb_push')
                      ->where(['address'=>['like','%'.$address.'%'],'stealth'=>0,'sort'=>$sort,
                          'event'=>'publish'])->order(['heat'=>'desc'])
                      ->limit($limit_StartPos,$length)->select();
                    
                }else {
                    return [];
                }
                
                
                break;
            default:
                if ($type)
                {
                    $DBList = Db::table('bb_push')->where(['sort'=>$sort,'event'=>'publish',
                        'label'=>$type])->order(['time'=>'desc'])
                      ->limit($limit_StartPos,$length)->select();
                }else
                {
                    $DBList = Db::table('bb_push')->where(['sort'=>$sort,'event'=>'publish'])
                       ->order(['time'=>'desc'])->limit($limit_StartPos,$length)->select();
                }
                break;
        }
        $Data = array();
        foreach ($DBList as $DB)
        {
            $DataDB = BBPush::get_detail_by_row($DB, $uid);
            array_push($Data,$DataDB);
        }
        return $Data;
    }
    
    
    
    
//获得回播的视频列表
    public static function get_push_rewind_show($uid,$sort,$start_pos,$length,$activity_id)
    {
        $buy_help = new \BBExtend\user\Relation();
        $DBList = Db::table('bb_rewind')->where(['sort'=>$sort,'activity_id'=>$activity_id,
             'is_remove'=>0,'audit'=>1])->order(['people'=>'desc'])->limit($start_pos,$length)
           ->select();
        $Data = array();
        foreach ($DBList as $DB)
        {
            $DataDB['id'] = (int)$DB['id'] ;
            $DataDB['uid'] = (int)$DB['uid'] ;
            
            //谢烨20160922，加vip返回字段
            $DataDB['vip'] = \BBExtend\common\User::is_vip($DataDB['uid']) ;
            
            $DataDB['pull_url'] = $DB['rewind_url'];
            $DataDB['title'] = $DB['title'];
            $DataDB['label'] = (int)$DB['label'];
            $DataDB['specialty'] = User::get_specialty($DB['uid']);
            $DataDB['sex'] = User::get_usersex($DB['uid']);
            $DataDB['is_like'] = Rewindmanager::get_is_like($uid,$DB['room_id']);
            $DataDB['is_focus'] = Focus::get_focus_state($uid,$DB['uid']);
            $DataDB['like'] = (int)$DB['like'];
            $DataDB['people'] = (int)$DB['people'];
            $DataDB['nickname'] = User::get_nickname($DB['uid']);
            $Pic = $DB['bigpic'];
            $serverUrl = \BBExtend\common\BBConfig::get_server_url();
            if ($Pic)
            {
                if (!(strpos($Pic, 'http://') !== false))
                {
                    $DataDB['bigpic'] = $serverUrl.$Pic;
                }
                else
                {
                    $DataDB['bigpic'] = $Pic;
                }
            }else
            {
                $DataDB['bigpic'] = User::get_userpic($DB['uid']);
            }
            $DataDB['pic'] = User::get_userpic($DB['uid']);
            $DataDB['room_id'] = $DB['room_id'];
            $DataDB['age'] = User::get_userage($DB['uid']);
            $DataDB['type'] = 'rewind';
            $DataDB['push'] = false;
            
            //xieye 2016 10 购买课程
            $DataDB['price'] = (int)$DB['price'] ;
            $DataDB['price_type'] = (int)$DB['price_type'] ;
            $DataDB['has_buy'] = $buy_help->has_buy_video($uid, $DataDB['room_id']);
            $DataDB['is_lahei'] = $buy_help->has_lahei( $DataDB['uid'] , $uid );
            $DataDB['content_type'] = intval($sort);
            array_push($Data,$DataDB);
        }
        return $Data;
    }
    
    
    
    
    
    //获得短视频的视频列表
    public static function get_record_show($uid,$sort,$start_pos,$length = 20,$activity_id = 0,
            $type = 0,$address = '')
    {
        
        $buy_help = new \BBExtend\user\Relation();
        
        $DBList = array();
        switch ($type)
        {
            case SHOW_TYPE_HEAD:
//                 if ($sort == 3)
//                 {
//                     $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
//                         'audit'=>1,'is_remove'=>0,'activity_id'=>$activity_id])
//                       ->order(['look'=>'desc'])->limit($start_pos,$length)->select();
//                 }else if($sort == 2)
//                 {
//                     $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
//                         'audit'=>1,'heat'=>1,'is_remove'=>0])->order(['time'=>'desc'])
//                       ->limit($start_pos,$length)->select();
//                 }else
//                 {
//                     $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
//                         'audit'=>1,'is_remove'=>0])->order(['look'=>'desc'])
//                       ->limit($start_pos,$length)->select();
//                 }
                
                if ($sort == 3)
                {
                    $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
                        'audit'=>1,'is_remove'=>0,'activity_id'=>$activity_id])
                        ->order(['look'=>'desc'])->limit($start_pos,$length)->select();
                }else if($sort == 2)
                {
                    $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
                        'audit'=>1,'is_remove'=>0])->order(['heat'=>'desc'])
                        ->limit($start_pos,$length)->select();
                }else
                {
                    $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
                        'audit'=>1,'is_remove'=>0])->order(['heat'=>'desc','look'=>'desc'])
                        ->limit($start_pos,$length)->select();
                }


                break;
            case SHOW_TYPE_NEW:
                $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,'audit'=>1,
                  'is_remove'=>0])->order(['time'=>'desc'])->limit($start_pos,$length)->select();
                break;
            case SHOW_TYPE_REC:
                if($sort == 2)
                {
                    $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
                        'audit'=>1,'is_remove'=>0])->order(['look'=>'desc'])
                      ->limit($start_pos,$length)->select();
                }else
                {
                    $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
                        'audit'=>1,'heat'=>1,'is_remove'=>0])->order(['time'=>'desc'])
                      ->limit($start_pos,$length)->select();
                }
                break;
            case SHOW_TYPE_FUJIN:
                
                //谢烨20160926，修改sql的like
                
                $address = self::filter_str($address);
                if ($address) {
                    $DBList = Db::table('bb_record')
                    ->where(['address'=>['like','%'.$address.'%'],'type'=>1,'usersort'=>$sort,
                        'audit'=>1,'is_remove'=>0])
                    ->order(['time'=>'desc'])->limit($start_pos,$length)->select();
                }else {
                    return [];
                }
                break;
            default:
                if ($type)
                {
                    $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
                        'audit'=>1,'label'=>$type,'is_remove'=>0])->order(['time'=>'desc'])
                      ->limit($start_pos,$length)->select();
                }else
                {
                    $DBList = Db::table('bb_record')->where(['type'=>1,'usersort'=>$sort,
                        'audit'=>1,'is_remove'=>0])->order(['time'=>'desc'])
                      ->limit($start_pos,$length)->select();
                }
                break;
        }
        $Data = array();
        foreach ($DBList as $DB)
        {
            $DataDB = BBRecord::get_detail_by_row($DB,$uid);
//             $DataDB['id'] = (int)$DB['id'] ;
//             $DataDB['uid'] = (int)$DB['uid'] ;
            
//             //谢烨20160922，加vip返回字段
//             $DataDB['vip'] = \BBExtend\common\User::is_vip($DataDB['uid']) ;
            
//             $DataDB['pull_url'] = $DB['video_path'];
//             $DataDB['title'] = $DB['title'];
//             $DataDB['label'] = (int)$DB['label'];
//             $DataDB['specialty'] = User::get_specialty($DB['uid']);
//             $DataDB['login_address'] = $DB['address'];
//             $DataDB['sex'] = User::get_usersex($DB['uid']);
//             $DataDB['is_like'] = BBRecord::get_is_like($uid,$DB['room_id']);
//             $DataDB['is_focus'] = Focus::get_focus_state($uid,$DB['uid']);
//             $DataDB['like'] = (int)$DB['like'];
//             $Look = BBRedis::getInstance('record')->hGet($DB['room_id'].'record','look');
//             if ($Look)
//             {
//                 $DataDB['people'] = (int)$Look;
//             }else
//             {
//                 $DataDB['people'] = (int)$DB['look'];
//             }
//             $DataDB['nickname'] = User::get_nickname($DB['uid']);
//             $Pic = $DB['big_pic'];
//             $serverUrl = \BBExtend\common\BBConfig::get_server_url();
//             if ($Pic)
//             {
//                 if (!(strpos($Pic, 'http://') !== false))
//                 {
//                     $DataDB['bigpic'] = $serverUrl.$Pic;
//                 }
//                 else
//                 {
//                     $DataDB['bigpic'] = $Pic;
//                 }
//             }else
//             {
//                 $DataDB['bigpic'] = $DataDB['thumbnailpath'];
//             }
//             $DataDB['pic'] = User::get_userpic($DB['uid']);
//             $DataDB['room_id'] = $DB['room_id'];
//             $DataDB['age'] = User::get_userage($DB['uid']);
//             $DataDB['type'] = 'record';
//             $DataDB['push'] = false;
//             //xieye 2016 10 购买课程
//             $DataDB['price'] = (int)$DB['price'] ;
//             $DataDB['price_type'] = (int)$DB['price_type'] ;
//             $DataDB['has_buy'] = $buy_help->has_buy_video($uid, $DataDB['room_id']);
//             $DataDB['is_lahei'] = $buy_help->has_lahei( $DataDB['uid'] , $uid );
//             $DataDB['content_type'] = intval($sort);
            
            array_push($Data,$DataDB);
        }
        return $Data;
    }
    
    //谢烨20160926 ，过滤like
    private static   function filter_str($s)
    {
        //先把换行改成空格
        $pattern = '/(\r\n|\n)/';
        $s = preg_replace($pattern, '', $s);
        //20-7e 包括了0－9a-zA-Z空格，英文标点。是ascii表的主要一部分
        // 4e00- 9fa5 全部汉字，但不含中文标点
        $pattern = '/[^\x{4e00}-\x{9fa5}]/u';
        $s = preg_replace($pattern, '', $s);
        return $s;
    }
    
    
}