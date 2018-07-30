<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/17
 * Time: 11:06
 */

namespace BBExtend;
use app\user\controller\User;
use think\Db;
use BBExtend\Sys;
use BBExtend\message\Message;
use BBExtend\BBUser;
use BBExtend\user\exp\Exp;
use think\Config;
use BBExtend\DbSelect;
use BBExtend\common\Date;
use BBExtend\fix\MessageType;

class BBRecord
{

    // 返回用户视频数量
    public static function get_movies_count ( $uid )
    {
        return Db::table( 'bb_record' )->where( [
                'uid' => $uid,
                'is_remove' => 0
        ] )->count( );
        // if ($MoviesDB)
        // {
        // return count($MoviesDB);
        // }
        // return 0;
    }

    /**
     * uid 是查询目标
     * 
     * @param unknown $uid
     * @param unknown $self_uid
     */
    public static function get_movies_count_2 ( $uid, $self_uid )
    {
        
        if ($uid == $self_uid) {
            return Db::table( 'bb_record' )->where( [
                    'uid' => $uid,
                    'is_remove' => 0
            ] )->count( );
        } else {
            return Db::table( 'bb_record' )->where( [
                    'uid' => $uid,
                    'is_remove' => 0
            ] )
                ->where( "type !=3" )
                ->count( );
        }
    }

    // 获得上传
    public static function get_record_by_type ( $type, $usersort )
    {
        Db::table( 'bb_record' )->where( [
                'type' => $type,
                'usersort' => $usersort,
                'audit' => 1
        ] )
            ->order( [
                'like' => 'desc',
                'look' => 'desc'
        ] )
            ->select( );
    }

    public static function update_record ( $recordDB )
    {
        $DB = Db::table( 'bb_record' )->where( 
                [
                        'video_path' => $recordDB['video_path'],
                        'uid' => $recordDB['uid']
                ] )->find( );
        $id = 0;
        if ($DB) {
            $id = $DB['id'];
            if (! $recordDB['room_id']) {
                $recordDB['room_id'] = $recordDB['uid'] . $DB['id'] . 'record_movies';
            }
            Db::table( 'bb_record' )->where( 
                    [
                            'video_path' => $recordDB['video_path'],
                            'uid' => $recordDB['uid']
                    ] )->update( $recordDB );
            BBRedis::getInstance( 'record' )->hMset( $recordDB['room_id'] . 'record', $recordDB );
        } else {
            // 审核-》默认为未审核状态 1为审核通过 2 未通过审核
            $recordDB['audit'] = 0;
            $recordDB['like'] = 0;
            // xieye 2017 03 look改随机数，已改0
            $recordDB['look'] = 0;
            $recordDB['time'] = time( );
            Db::table( 'bb_record' )->insert( $recordDB );
            $id = Db::table( 'bb_record' )->getLastInsID( );
            $recordDB['room_id'] = $recordDB['uid'] . $id . 'record_movies';
            Db::table( 'bb_record' )->where( 
                    [
                            'video_path' => $recordDB['video_path'],
                            'uid' => $recordDB['uid']
                    ] )->update( [
                    'room_id' => $recordDB['room_id']
            ] );
            BBRedis::getInstance( 'record' )->hMset( $recordDB['room_id'] . 'record', $recordDB );
        }
        return [
                'id' => $id,
                'room_id' => $recordDB['room_id']
        ];
    }

    public static function get_record ( $uid )
    {
        $recordDB = Db::table( 'bb_record' )->where( [
                'uid' => $uid
        ] )->select( );
        for ($i = 0; $i < count( $recordDB ); $i ++) {
            $recordDB[$i]['id'] = (int) $recordDB[$i]['id'];
            $recordDB[$i]['uid'] = (int) $recordDB[$i]['uid'];
            $recordDB[$i]['type'] = (int) $recordDB[$i]['type'];
            $recordDB[$i]['usersort'] = (int) $recordDB[$i]['usersort'];
            $recordDB[$i]['activity_id'] = (int) $recordDB[$i]['activity_id'];
            $recordDB[$i]['audit'] = (int) $recordDB[$i]['audit'];
            $recordDB[$i]['like'] = (int) $recordDB[$i]['like'];
            $recordDB[$i]['look'] = (int) $recordDB[$i]['look'];
        }
        return $recordDB;
    }

    /**
     * 短视频点赞
     *
     * @param unknown $uid
     * @param unknown $room_id
     * @param number $is_false
     * @return string[]|number[]
     */
    public static function record_like ( $uid, $room_id, $is_false = 0 )
    {
        $movieDB = self::get_movies_by_room_id( $room_id );
        if ($movieDB) {
            
            $db = Sys::get_container_db_eloquent( );
            $time1 = Date::pre_day_start( );
            $time2 = Date::pre_day_end( );
            
            $sql = "select * from bb_record_like
                     where uid =?
                       and room_id=?
                       
                    ";
            $LikeDB = DbSelect::fetchRow( $db, $sql, [
                    $uid,
                    $room_id
            ] );
            
            // 如果今天没有点过赞
            if (! $LikeDB) {
                if ($is_false == 0) {
                    \BBExtend\user\Tongji::getinstance( $uid )->zan( $movieDB['uid'] );
                }
                $Data = array();
                $Data['uid'] = $uid;
                $Data['room_id'] = $room_id;
                $Data['time'] = time( );
                $Data['count'] = 1;
                
                Db::table( 'bb_record_like' )->insert( $Data );
                
                $db2 = Sys::get_container_db( );
                $sql = "update bb_record set `like`=`like` +1,real_like=real_like+1 where room_id=? ";
                $db2->query( $sql, $room_id );
                
                // $movieDB['like'] = (int)$movieDB['like'] + 1;
                // self::update_record($movieDB);
                
                $user = BBUser::get_user( $uid );
                
                Exp::getinstance( $uid )->set_typeint( Exp::LEVEL_ACTIVITY_LIKE )->add_exp( );
                $ach = new \BBExtend\user\achievement\Dianzan( $uid );
                $ach->update( 1 );
                $ach = new \BBExtend\user\achievement\Zhubo( $movieDB['uid'] );
                $ach->update( 1 );
                // $db = Sys::get_container_db();
                // $sql='select ';
                $user22 = \app\user\model\UserModel::getinstance( $uid );
                $pic = $user22->get_user_pic_no_http( );
                Message::get_instance( )->set_title( '系统消息' )
                    ->set_img( $pic )
                    ->add_content( 
                        Message::simple( )->content( $user['nickname'] )
                            ->color( 0xf4a560 )
                            ->url( json_encode( [
                                'type' => 2,
                                'other_uid' => $uid
                        ] ) ) )
                    ->add_content( Message::simple( )->content( '赞了你的视频' ) )
                    ->add_content( Message::simple( )->content( $movieDB['title'] )
                    ->color( 0xf4a560 ) )
                    ->set_type( MessageType::shipin_beizan )
                    ->set_uid( $movieDB['uid'] )
                    ->set_other_uid( $uid )
                    ->set_other_record_id( $movieDB['id'] )
                    ->send( );
                
                return [
                        'message' => '点赞成功',
                        'code' => 1
                ];
            } else {
                // 如果今天已经点过赞。
                return [
                        'message' => '您今天已经点过赞了',
                        'code' => 0
                ];
            }
        }
        return [
                'message' => '没有这个视频信息请检查房间号是否正确',
                'code' => 0
        ];
    }

    public static function record_un_like ( $uid, $room_id )
    {
        $movieDB = self::get_movies_by_room_id( $room_id );
        if ($movieDB) {
            $LikeDB = Db::table( 'bb_record_like' )->where( [
                    'uid' => $uid,
                    'room_id' => $room_id
            ] )->find( );
            if ($LikeDB) {
                Db::table( 'bb_record_like' )->where( [
                        'uid' => $uid,
                        'room_id' => $room_id
                ] )->delete( );
                $db2 = Sys::get_container_db( );
                $sql = "update bb_record set `like`=`like` -1,real_like=real_like-1 where room_id=? ";
                $db2->query( $sql, $room_id );
                return [
                        'message' => '取消成功',
                        'code' => 1
                ];
            }
        }
        return [
                'message' => '你好像没有点过赞哦',
                'code' => 0
        ];
    }

    public static function get_is_like ( $uid, $room_id )
    {
        $LikeDB = Db::table( 'bb_record_like' )->where( [
                'uid' => $uid,
                'room_id' => $room_id
        ] )->find( );
        if ($LikeDB) {
            return true;
        }
        return false;
    }

    // 进入房间回调
    public static function notify_enterroom ( $uid, $room_id )
    {
        $MoviesDB = self::get_activity_movies_by_room_id( $room_id );
        if ($MoviesDB) {
            $cound = $MoviesDB['look'];
            $cound ++;
            BBRedis::getInstance( 'record' )->hSet( $room_id . 'record', 'look', $cound );
            Db::table( 'bb_record' )->where( 'room_id', $room_id )->update( [
                    'look' => $cound
            ] );
            if ($MoviesDB['usersort'] == 1 && $MoviesDB['type'] == 1) {
                Level::add_user_exp( $uid, LEVEL_SHOW_LIVE_COURSE );
            
            }
            
            // 谢烨，增加统计数据。
            \BBExtend\user\Tongji::getinstance( $uid )->view_record( );
            return $MoviesDB['like'];
        }
        return - 1;
    }

    // 获得秀场的视频列表
    public static function get_record_show ( $uid, $sort, $start_pos, $length = 20, $activity_id = 0 )
    {
        $DBList = Db::table( 'bb_record' )->where( 
                [
                        'usersort' => $sort,
                        'activity_id' => $activity_id,
                        'audit' => 1
                ] )
            ->order( [
                'like' => 'desc',
                'heat' => 'desc'
        ] )
            ->limit( $start_pos, $length )
            ->select( );
        $Data = array();
        foreach ($DBList as $DB) {
            $DataDB['uid'] = $DB['uid'];
            $DataDB['pull_url'] = $DB['video_path'];
            $DataDB['title'] = $DB['title'];
            $DataDB['label'] = (int) $DB['label'];
            $DataDB['specialty'] = User::get_specialty( $uid );
            $DataDB['login_address'] = $DB['address'];
            $DataDB['like'] = (int) $DB['like'];
            $DataDB['is_like'] = self::get_is_like( $uid, $DB['room_id'] );
            $Look = BBRedis::getInstance( 'record' )->hGet( $DB['room_id'] . 'record', 'look' );
            if ($Look) {
                $DataDB['people'] = (int) $Look;
            } else {
                $DataDB['people'] = (int) $DB['look'];
            }
            $DataDB['nickname'] = User::get_nickname( $DB['uid'] );
            $Pic = $DB['big_pic'];
            $serverUrl = \BBExtend\common\BBConfig::get_server_url( );
            if ($Pic) {
                $DataDB['bigpic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default( 
                        $Pic );
            
            } else {
                $DataDB['bigpic'] = User::get_userpic( $DB['uid'] );
            }
            $DataDB['pic'] = User::get_userpic( $DB['uid'] );
            $DataDB['room_id'] = $DB['room_id'];
            $DataDB['age'] = User::get_userage( $DB['uid'] );
            $DataDB['push'] = false;
            array_push( $Data, $DataDB );
        }
        return $Data;
    }

    
    
    private static function get_activity_movies_sort( $uid, $activity_id, $start_id, $length, $type = 0,
            $sort = 0 )
    {
        $activity_id = intval( $activity_id );
        $uid = intval( $uid );
        $start_id = intval( $start_id );
        $length = intval( $length );
        $sort = intval($sort);
        
      //  if (! $type) // 排行榜
       // {
            $db2 = Sys::get_container_db( );
            $sql = "select has_paiming from bb_task_activity where id = {$activity_id}";
            $is_reward = $db2->fetchOne( $sql );
            $db_uid = null;
            $paiming = 0;
            if ($type) { // 按最新
                
                $DB = Db::table( 'bb_record' )->where(
                        [
                                'type' => 2,
                                'activity_id' => $activity_id,
                                'audit' => 1,
                                'usersort' => $sort,
                                'is_remove' => 0
                        ] )
                        ->order( [
                                'time' => 'desc'
                        ] )
                        ->limit( $start_id, $length )
                        ->select( );
                
                
                
            }else { // 排行榜
            
                    if ($is_reward == 0) { //未固化
                        // $DB =
                        // Db::table('bb_record')->where(['type'=>2,'activity_id'=>$activity_id,
                        // 'audit'=>1,'is_remove'=>0])->order(['like'=>'desc'])
                        // ->limit($start_id,$length)->select();
                     //   echo 1113;
                        $DB = Db::table( 'bb_record' )->where(
                                [
                                        'type' => 2,
                                        'usersort' => $sort,
                                        'activity_id' => $activity_id,
                                        'audit' => 1,
                                        'is_remove' => 0
                                ] )
                                ->order( [
                                        'like' => 'desc'
                                ] )
                                ->limit( $start_id, $length )
                                ->select( );
                                
                    } else { // 固化排名
                  //      echo 1114;
                       // if ($sort > 0) {
                            $sql = "
                                           select bb_record.*,bb_user_activity.paiming_new as paiming  
                             from bb_user_activity
                            left join bb_record
                            on bb_record.id = bb_user_activity.record_id
                            where bb_user_activity.activity_id={$activity_id}
                             and bb_record.usersort = {$sort}
                            order by bb_user_activity.paiming_new asc
                            limit {$start_id},{$length}
                                                    ";
                        //}
                        
                        $DB = $db2->fetchAll( $sql );
                        // 谢烨，201803 ，为失败方补救一下呗@
                        if (!$DB) {
                            $DB = Db::table( 'bb_record' )->where(
                                    [
                                            'type' => 2,
                                            'usersort' => $sort,
                                            'activity_id' => $activity_id,
                                            'audit' => 1,
                                            'is_remove' => 0
                                    ] )
                                    ->order( [
                                            'like' => 'desc'
                                    ] )
                                    ->limit( $start_id, $length )
                                    ->select( );
                        }
                    }
            }
        
        $Data = array();
        $i=0;
        foreach ($DB as $movesDB) {
            $i++;
            $movesDB['paiming'] = $i;
            $movesDB['comments_num'] = self::get_comments_count( $movesDB['room_id'] );
            $movesDB['comments_score'] = self::get_score_avg( $movesDB['room_id'] );
            $movesDB['activity_id'] = (int) $movesDB['activity_id'];
            $movesDB['like'] = (int) $movesDB['like'];
            $movesDB['is_like'] = self::get_is_like( $uid, $movesDB['room_id'] );
            $movesDB['id'] = (int) $movesDB['id'];
            $movesDB['audit'] = (int) $movesDB['audit'];
            
            $movesDB['content_type'] = (int) $movesDB['usersort'];
            if ($movesDB['content_type'] == 0) {
                $movesDB['content_type'] == 2;
            }
            array_push( $Data, $movesDB );
        }
        return $Data;
        
        
    }
    
    
    /**
     * 获得活动所有视频并且从大到小排序
     *
     * @param int $uid
     *            当前用户id
     * @param int $activity_id
     *            活动id
     * @param int $start_id
     *            起始序号
     * @param int $length
     *            长度
     * @param int $type
     *            1按时间排序，0排行榜
     * @param int $sort
     *            11红方，12蓝方，0随意
     */
    public static function get_activity_movies ( $uid, $activity_id, $start_id, $length, $type = 0,
            $sort = 0 )
    {
        $activity_id = intval( $activity_id );
        $uid = intval( $uid );
        $start_id = intval( $start_id );
        $length = intval( $length );
        $sort = intval($sort);
        
        if ($sort) {
            return self::get_activity_movies_sort($uid, $activity_id, $start_id, $length,$type,$sort);
        }
        
        if (! $type) // 排行榜0
        {
            $db2 = Sys::get_container_db( );
            $sql = "select is_send_reward from bb_task_activity where id = {$activity_id}";
            $is_reward = $db2->fetchOne( $sql );
            $db_uid = null;
            $paiming = 0;
            if ($is_reward == 0) {
                // $DB =
                // Db::table('bb_record')->where(['type'=>2,'activity_id'=>$activity_id,
                // 'audit'=>1,'is_remove'=>0])->order(['like'=>'desc'])
                // ->limit($start_id,$length)->select();
                
                // 查有没有
                $db_uid = Db::table( 'bb_record' )->where( 
                        [
                                'type' => 2,
                                'activity_id' => $activity_id,
                                'audit' => 1,
                                'is_remove' => 0
                        ] )
                    ->where( "uid = {$uid}" )
                    ->find( );
                if ($db_uid) { // 找排名
                    $sql = "
                           select id,uid from bb_record where type=2 and activity_id = {$activity_id}
                         and audit=1 and is_remove=0 order by `like` desc
                           ";
                    $temp = $db2->fetchAll( $sql );
                    $i = 1;
                    foreach ($temp as $v) {
                        if ($v['uid'] == $uid) {
                            $paiming = $i;
                            $db_uid['paiming'] = $paiming;
                            break;
                        }
                        $i ++;
                    }
                }
            
            } else {
                // $sql ="
                // select bb_record.* from bb_user_activity_reward
                // left join bb_record
                // on bb_record.id = bb_user_activity_reward.record_id
                // where bb_user_activity_reward.activity_id={$activity_id}
                // order by bb_user_activity_reward.paiming asc
                // limit {$start_id},{$length}
                // ";
                // $DB = $db2->fetchAll($sql);
                
                // 先查有没有
                $sql = "
                select bb_record.*, bb_user_activity_reward.paiming from bb_user_activity_reward
                left join bb_record
                on bb_record.id = bb_user_activity_reward.record_id
                where bb_user_activity_reward.activity_id={$activity_id}
                and bb_record.uid = {$uid}
                
                ";
                $db_uid = $db2->fetchRow( $sql );
            
            }
            if ($db_uid) { // 在其中
                           
                // 现在，要找出db_uid的排名。
                
                if ($start_id == 0) {
                    if ($is_reward == 0) {
                        $DB = Db::table( 'bb_record' )->where( 
                                [
                                        'type' => 2,
                                        'activity_id' => $activity_id,
                                        'audit' => 1,
                                        'is_remove' => 0
                                ] )
                            ->order( [
                                'like' => 'desc'
                        ] )
                            ->limit( $start_id, $length )
                            ->select( );
                            
                    } else {
                        $sql = "
                                   select bb_record.* from bb_user_activity_reward
                    left join bb_record
                    on bb_record.id = bb_user_activity_reward.record_id
                    where bb_user_activity_reward.activity_id={$activity_id}
                    order by bb_user_activity_reward.paiming asc
                    limit {$start_id},{$length}
                                            ";
                        $DB = $db2->fetchAll( $sql );
                    }
                    
                    $paiming = $start_id + 1;
                    foreach ($DB as $k => $v) {
                        $DB[$k]['paiming'] = $paiming;
                        $paiming ++;
                    
                    }
                    
                    array_unshift( $DB, $db_uid );
                    if (count( $DB ) > $length) {
                        array_pop( $DB );
                    }
                } else { // 不是首页
                    if ($is_reward == 0) {
                        $DB = Db::table( 'bb_record' )->where( 
                                [
                                        'type' => 2,
                                        'activity_id' => $activity_id,
                                        'audit' => 1,
                                        'is_remove' => 0
                                ] )
                            ->order( [
                                'like' => 'desc'
                        ] )
                            ->limit( $start_id - 1, $length )
                            ->select( );
                    
                    } else {
                        $start_id --;
                        $sql = "
                        select bb_record.* from bb_user_activity_reward
                        left join bb_record
                        on bb_record.id = bb_user_activity_reward.record_id
                        where bb_user_activity_reward.activity_id={$activity_id}
                        order by bb_user_activity_reward.paiming asc
                        limit {$start_id},{$length}
                        ";
                        
                        $DB = $db2->fetchAll( $sql );
                    }
                    $paiming = $start_id;
                    foreach ($DB as $k => $v) {
                        $DB[$k]['paiming'] = $paiming;
                        $paiming ++;
                    }
                
                }
            } else { // 我不在其中
                if ($is_reward == 0) {
                    $DB = Db::table( 'bb_record' )->where( 
                            [
                                    'type' => 2,
                                    'activity_id' => $activity_id,
                                    'audit' => 1,
                                    'is_remove' => 0
                            ] )
                        ->order( [
                            'like' => 'desc'
                    ] )
                        ->limit( $start_id, $length )
                        ->select( );
                } else {
                    $sql = "
                                   select bb_record.* from bb_user_activity_reward
                    left join bb_record
                    on bb_record.id = bb_user_activity_reward.record_id
                    where bb_user_activity_reward.activity_id={$activity_id}
                    order by bb_user_activity_reward.paiming asc
                    limit {$start_id},{$length}
                                            ";
                    
                    $DB = $db2->fetchAll( $sql );
                }
                $paiming = $start_id + 1;
                foreach ($DB as $k => $v) {
                    $DB[$k]['paiming'] = $paiming;
                    $paiming ++;
                
                }
            }
        
        } else // 最新1
        {
            $DB = Db::table( 'bb_record' )->where( 
                    [
                            'type' => 2,
                            'activity_id' => $activity_id,
                            'audit' => 1,
                            'is_remove' => 0
                    ] )
                ->order( [
                    'time' => 'desc'
            ] )
                ->limit( $start_id, $length )
                ->select( );
        }
        
        $Data = array();
        foreach ($DB as $movesDB) {
            $movesDB['comments_num'] = self::get_comments_count( $movesDB['room_id'] );
            $movesDB['comments_score'] = self::get_score_avg( $movesDB['room_id'] );
            $movesDB['activity_id'] = (int) $movesDB['activity_id'];
            $movesDB['like'] = (int) $movesDB['like'];
            $movesDB['is_like'] = self::get_is_like( $uid, $movesDB['room_id'] );
            $movesDB['id'] = (int) $movesDB['id'];
            $movesDB['audit'] = (int) $movesDB['audit'];
            
            $movesDB['content_type'] = (int) $movesDB['usersort'];
            if ($movesDB['content_type'] == 0) {
                $movesDB['content_type'] == 2;
            }
            array_push( $Data, $movesDB );
        }
        return $Data;
    }

    
    
    
    
    
    
    
    
    
    public static function get_activity_movies_v2( $uid, $activity_id, $start_id, $length, $type = 0,
            $sort = 0 )
    {
        $activity_id = intval( $activity_id );
        $uid = intval( $uid );
        $start_id = intval( $start_id );
        $length = intval( $length );
        $sort = intval($sort);
        
        if ($sort) {
            return self::get_activity_movies_sort($uid, $activity_id, $start_id, $length,$type,$sort);
        }
        
        $help = new \BBExtend\video\ActList($uid, $activity_id, $start_id, $length,$type);
        $DB = $help->list_arr();
        
        $Data = array();
        foreach ($DB as $movesDB) {
            $movesDB['comments_num'] = self::get_comments_count( $movesDB['room_id'] );
            $movesDB['comments_score'] = self::get_score_avg( $movesDB['room_id'] );
            $movesDB['activity_id'] = (int) $movesDB['activity_id'];
            $movesDB['like'] = (int) $movesDB['like'];
            $movesDB['is_like'] = self::get_is_like( $uid, $movesDB['room_id'] );
            $movesDB['id'] = (int) $movesDB['id'];
            $movesDB['audit'] = (int) $movesDB['audit'];
            
            $movesDB['content_type'] = (int) $movesDB['usersort'];
            if ($movesDB['content_type'] == 0) {
                $movesDB['content_type'] == 2;
            }
            array_push( $Data, $movesDB );
        }
        return $Data;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // 获得录播活动中第一名
    public static function get_activity_number_one ( $activity_id )
    {
        return Db::table( 'bb_record' )->where( [
                'type' => 2,
                'activity_id' => $activity_id
        ] )
            ->order( [
                'like' => 'desc',
                'look' => 'desc'
        ] )
            ->find( );
    }

    // 根据房间号得到一个视频数据
    public static function get_movies_by_room_id ( $room_id )
    {
        if (! $room_id) {
            return null;
        }
        $DB = null;
        // $DB = BBRedis::getInstance('record')->hGetAll($room_id.'record');
        
        if (! $DB) {
            $DB = Db::table( 'bb_record' )->where( [
                    'room_id' => $room_id
            ] )->find( );
            if ($DB) {
                BBRedis::getInstance( 'record' )->hMset( $room_id . 'record', $DB );
                return $DB;
            }
        }
        return $DB;
    }

    public static function get_activity_movies_by_room_id ( $room_id )
    {
        if (! $room_id) {
            return null;
        }
        $DB = null;
        // $DB = BBRedis::getInstance('record')->hGetAll($room_id.'record');
        if (! $DB) {
            $DB = Db::table( 'bb_record' )->where( [
                    'room_id' => $room_id
            ] )->find( );
            if ($DB) {
                BBRedis::getInstance( 'record' )->hMset( $room_id . 'record', $DB );
                $DB['comments_num'] = self::get_comments_count( $room_id );
                $DB['comments_score'] = self::get_score_avg( $room_id );
                $DB['activity_id'] = (int) $DB['activity_id'];
                $DB['like'] = (int) $DB['like'];
                $DB['id'] = (int) $DB['id'];
                return $DB;
            }
        }
        return $DB;
    }

    public static function get_movieds_by_room_id ( $room_id )
    {
        if ($room_id == '' || $room_id == 0) {
            return null;
        }
        // $DB = BBRedis::getInstance('record')->hGetAll($room_id.'record');
        $DB = null;
        if (! $DB) {
            $DB = Db::table( 'bb_record' )->where( 'room_id', $room_id )->find( );
            if ($DB) {
                BBRedis::getInstance( 'record' )->hMset( $room_id . 'record', $DB );
                $DB['comments_num'] = self::get_comments_count( $room_id );
                $DB['comments_score'] = self::get_score_avg( $room_id );
                $DB['activity_id'] = (int) $DB['activity_id'];
                $DB['like'] = (int) $DB['like'];
                $DB['id'] = (int) $DB['id'];
            }
        }
        return $DB;
    }

    // 根据ID得到一个视频数据
    public static function get_activity_movies_by_id ( $id )
    {
        $DB = Db::table( 'bb_record' )->where( [
                'id' => $id
        ] )->find( );
        return $DB;
    }

    // 活动评论平均分数
    public static function get_score_avg ( $room_id )
    {
        $CommentsDB_avg = Db::table( 'bb_record_comments' )->where( 
                [
                        'activity_id' => $room_id,
                        'audit' => 1,
                        'is_remove' => 0
                ] )
            ->field( 'score' )
            ->find( );
        if ($CommentsDB_avg) {
            return (int) $CommentsDB_avg['score'];
        }
        return 0;
    }

    // 获得评论数量
    public static function get_comments_count ( $room_id )
    {
        $CommentsDB_avg = Db::table( 'bb_record_comments' )->where( 
                [
                        'activity_id' => $room_id,
                        'audit' => 1,
                        'is_remove' => 0
                ] )->select( );
        return count( $CommentsDB_avg );
    }

    /**
     * 谢烨，规则如下: push : room_id = uid + 'push' rewind: room_id = stream_name +
     * end_time ，且一定是 mlandclub- 开头,且event = rewind record: room_id = uid + id +
     * 'record_movies'
     */
    public static function get_table_name ( $room_id )
    {
        if (! $room_id) {
            return false;
        }
        if (preg_match( '#record_movies$#', $room_id )) {
            return 'bb_record';
        }
        if (preg_match( '#^mlandclub#', $room_id ) || preg_match( '#^[0-9A-Z]{8}-#', $room_id )) {
            return 'bb_rewind';
        } else {
            return 'bb_push';
        }
    }

    public static function get_all_movie ( $room_id )
    {
        $table = self::get_table_name( $room_id );
        if ($table) {
            $db = Sys::get_container_db( );
            $sql = "select * from {$table} where room_id=?";
            return $db->fetchRow( $sql, $room_id );
        }
        return false;
    }

    public static function get_all_movie_model ( $room_id )
    {
        $table = self::get_table_name( $room_id );
        if ($table == 'bb_record') {
            return \app\shop\model\Record::get( [
                    'room_id' => $room_id
            ] );
        }
        if ($table == 'bb_rewind') {
            return \app\shop\model\Rewind::get( [
                    'room_id' => $room_id
            ] );
        }
        if ($table == 'bb_push') {
            return \app\shop\model\Push::get( [
                    'room_id' => $room_id
            ] );
        }
        return false;
    }

    /**
     * 便利性函数， 方便返回给客户端，输入是一个表里查到行，并确保存在。 输入uid，是当前用户。
     * 
     * @param unknown $DB
     */
    public static function get_detail_by_row ( $DB, $uid = 0 )
    {
        $DataDB = [];
        $user = \app\user\model\UserModel::getinstance( $DB['uid'] );
        
        
        $user_detail = \BBExtend\model\User::find( $DB['uid'] );
        
        $DataDB['role'] = $user_detail->role;
        $DataDB['frame'] = $user_detail->get_frame();
        $DataDB['badge'] = $user_detail->get_badge();
        
        
        $DataDB['id'] = (int) $DB['id'];
        $DataDB['uid'] = (int) $DB['uid'];
        $DataDB['ach_count'] = $user->get_ach_count( );
        $DataDB['vip'] = $user->get_user_vip( );
        $DataDB['pull_url'] = $DB['video_path'];
        $DataDB['title'] = $DB['title'];
        $DataDB['label'] = (int) $DB['label'];
        $DataDB['specialty'] = $user->get_specialty( );
        $DataDB['login_address'] = $DB['address'];
        $DataDB['sex'] = $user->get_usersex( );
        $DataDB['is_like'] = self::get_is_like( $uid, $DB['room_id'] );
        $DataDB['is_focus'] = Focus::get_focus_state( $uid, $DB['uid'] );
        $DataDB['like'] = (int) $DB['like'];
        $Look = false;
        if ($Look !== false) {
            $DataDB['people'] = (int) $Look;
        } else {
            $DataDB['people'] = (int) $DB['look'];
        }
        
        // xieye 20180107
        $record_model = new \BBExtend\model\Record( );
        $DataDB['people'] = $record_model->get_views( $DataDB['id'] );
        
        $DataDB['nickname'] = $user->get_nickname( );
        $Pic = $DB['big_pic'];
        $serverUrl = \BBExtend\common\BBConfig::get_server_url( );
        $DataDB['bigpic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default( $Pic,
                $DB['thumbnailpath'] );
        
        $DataDB['pic'] = $user->get_userpic( );
        $DataDB['room_id'] = $DB['room_id'];
        $DataDB['age'] = $user->get_userage( );
        // 2017 04 加等级
        $DataDB['level'] = $user->get_user_level( );
        
        $DataDB['type'] = 'record';
        $DataDB['push'] = false;
        // xieye 2016 10 购买课程
        $buy_help = new \BBExtend\user\Relation( );
        $DataDB['price'] = (int) $DB['price'];
        $DataDB['price_type'] = (int) $DB['price_type'];
        $DataDB['has_buy'] = $buy_help->has_buy_video( $uid, $DataDB['room_id'] );
        $DataDB['is_lahei'] = $buy_help->has_lahei( $DataDB['uid'], $uid );
        $DataDB['content_type'] = self::get_content_type( $DataDB['room_id'], $DB['usersort'],
                $DB['type'] );
        $DataDB['publish_time'] = strval( $DB['time'] ); // 2016 12 新增 发布时间。
                                                          // 还要评论条数
                                                          // $DataDB['comment_count']
                                                          // =
                                                          // Db::table('bb_record_comments')
                                                          // ->where("uid >0")
                                                          // ->where([
                                                          // 'activity_id'=>$DataDB['id']
                                                          // ,'audit'=>1,'is_remove'=>0])->count();
                                                          
        // 2017 04
        if (self::display_comment_count_by_version( )) {
            $DataDB['comment_count'] = \BBExtend\BBComments::Get_comments_count( 
                    "bb_record_comments", $DataDB['id'] );
        }
        // 2017 04
        $db = Sys::get_container_db( );
        $sql = "select ds_id,rank from ds_record where record_id={$DataDB['id']}";
        $ds_record = $db->fetchRow( $sql );
        // $ds_id = $db->fetchOne($sql);
        if ($ds_record) {
            $ds_id = $ds_record['ds_id'];
            $sql = "select title from ds_race where id={$ds_id}";
            $title = $db->fetchOne( $sql );
            $DataDB['ds'] = [
                    'id' => $ds_id,
                    'title' => $title,
                    'rank' => $ds_record['rank']
            ];
        } else {
            $DataDB['ds'] = null;
        }
        
        // xieye 20180107,掩盖bug
        if ($DataDB['people'] < $DataDB['like']) {
            $DataDB['people'] = intval( $DataDB['like'] * 1.05 );
        }
        
        return $DataDB;
    }

    /**
     * 201708 专门给首页用的。
     * 
     * @param unknown $DB
     * @param number $uid
     */
    public static function get_detail_by_row_redisss ( $DB, $uid = 0 )
    {
        $key = "record:detail2:" . $DB['id'];
        $redis = Sys::getredis11( );
        $result = $redis->get( $key );
        if (! $result) {
            
            $DataDB = [];
            $user = \app\user\model\UserModel::getinstance( $DB['uid'] );
            
            $user_detail = \BBExtend\model\User::find( $DB['uid'] );
            
            $DataDB['role'] = $user_detail->role;
            $DataDB['frame'] = $user_detail->get_frame();
            $DataDB['badge'] = $user_detail->get_badge();
            
            
            
            $DataDB['id'] = (int) $DB['id'];
            $DataDB['uid'] = (int) $DB['uid'];
            $DataDB['ach_count'] = $user->get_ach_count( );
            $DataDB['vip'] = $user->get_user_vip( );
            $DataDB['pull_url'] = $DB['video_path'];
            $DataDB['title'] = $DB['title'];
            $DataDB['label'] = (int) $DB['label'];
            $DataDB['specialty'] = $user->get_specialty( );
            $DataDB['login_address'] = $DB['address'];
            $DataDB['sex'] = $user->get_usersex( );
            
            $DataDB['like'] = (int) $DB['like'];
            $Look = BBRedis::getInstance( 'record' )->hGet( $DB['room_id'] . 'record', 'look' );
            if ($Look !== false) {
                $DataDB['people'] = (int) $Look;
            } else {
                $DataDB['people'] = (int) $DB['look'];
            }
            
            $DataDB['people'] = (int) $DB['look'];
            
            $DataDB['nickname'] = $user->get_nickname( );
            $DataDB['bigpic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default( 
                    $DB['big_pic'], $DB['thumbnailpath'] );
            
            // $Pic = $DB['big_pic'];
            // $serverUrl = \BBExtend\common\BBConfig::get_server_url();
            // if ($Pic) {
            // if (!(strpos($Pic, 'http://') !== false)) {
            // $DataDB['bigpic'] = $serverUrl.$Pic;
            // } else {
            // $DataDB['bigpic'] = $Pic;
            // }
            // } else {
            // $DataDB['bigpic'] = $DataDB['thumbnailpath'];
            // }
            $DataDB['pic'] = $user->get_userpic( );
            $DataDB['room_id'] = $DB['room_id'];
            $DataDB['age'] = $user->get_userage( );
            // 2017 04 加等级
            $DataDB['level'] = $user->get_user_level( );
            
            $DataDB['type'] = 'record';
            $DataDB['push'] = false;
            // xieye 2016 10 购买课程
            
            $DataDB['is_lahei'] = 0;
            $DataDB['content_type'] = self::get_content_type( $DataDB['room_id'], $DB['usersort'],
                    $DB['type'] );
            $DataDB['publish_time'] = strval( $DB['time'] ); // 2016 12 新增
                                                               // 发布时间。
            
            $DataDB['comment_count'] = \BBExtend\BBComments::Get_comments_count( 
                    "bb_record_comments", $DataDB['id'] );
            
            // 2017 04
            $db = Sys::get_container_db( );
            
            $sql = "select ds_id,rank from ds_record where record_id={$DataDB['id']}";
            $ds_record = $db->fetchRow( $sql );
            // $ds_id = $db->fetchOne($sql);
            if ($ds_record) {
                $ds_id = $ds_record['ds_id'];
                $sql = "select title from ds_race where id={$ds_id}";
                $title = $db->fetchOne( $sql );
                $DataDB['ds'] = [
                        'id' => $ds_id,
                        'title' => $title,
                        'rank' => $ds_record['rank']
                ];
            } else {
                $DataDB['ds'] = null;
            }
            
            // $sql ="select ds_id from ds_record where
            // record_id={$DataDB['id']}";
            // $ds_id = $db->fetchOne($sql);
            // if ($ds_id) {
            // $sql ="select title from ds_race where id={$ds_id}";
            // $title=$db->fetchOne($sql);
            // $DataDB['ds'] = ['id' => $ds_id, 'title' => $title ];
            // } else {
            // $DataDB['ds'] = null;
            // }
            $redis->set( $key, serialize( $DataDB ) );
            $redis->setTimeout( $key, 3600 );
        } else {
            $DataDB = unserialize( $result );
            $DataDB['like'] = (int) $DB['like'];
        }
        $DataDB['is_like'] = self::get_is_like( $uid, $DataDB['room_id'] );
        $DataDB['is_focus'] = Focus::get_focus_state( $uid, $DataDB['uid'] );
        
        // xieye 20180107
        $record_model = new \BBExtend\model\Record( );
        $DataDB['people'] = $record_model->get_views( $DataDB['id'] );
        // ,掩盖bug
        if ($DataDB['people'] < $DataDB['like']) {
            $DataDB['people'] = intval( $DataDB['like'] * 1.05 );
        }
        
        //
        
        return $DataDB;
    }

    /**
     * 是否显示评论数量，在视频详情中
     */
    public static function display_comment_count_by_version ( )
    {
        return true;
        $version = Config::get( "http_head_version" );
        $type = Config::get( "http_head_mobile_type" );
        if ($type == 'android') {
            if (version_compare( $version, '2.0.5', '>=' )) {
                return true;
            }
        }
        if ($type == 'ios') {
            if (version_compare( $version, '3.0.0', '>=' )) {
                return true;
            }
        }
        return false;
    }

    /**
     * 便利性函数， 方便返回给客户端，输入是一个表里查到行，并确保存在。 输入uid，是当前用户。
     * 2017 08 调用首页专用的视频详情函数
     *
     * @param unknown $DB
     */
    public static function get_subject_detail_by_row ( $DB, $uid = 0 )
    {
        // 2017 08 调用首页专用的视频详情函数
        $DataDB = self::get_detail_by_row_redisss( $DB, $uid );
        $DataDB['title'] = $DB['subject_title'];
        if (! $DataDB['title']) {
            $DataDB['title'] = $DB['title'];
        }
        
        // 重要，取的字段不同。但是返回给客户端仍然是bigpic，切记！
        $Pic = $DB['subject_pic'];
        if (! $Pic) {
            $Pic = $DB['big_pic'];
        }
        $DataDB['bigpic'] = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default( $Pic,
                $DB['thumbnailpath'] );
        
        // $serverUrl = \BBExtend\common\BBConfig::get_server_url();
        // if ($Pic) {
        // if (!(strpos($Pic, 'http://') !== false)) {
        // $DataDB['bigpic'] = $serverUrl.$Pic;
        // } else {
        // $DataDB['bigpic'] = $Pic;
        // }
        // }else {
        // $DataDB['bigpic'] = $DataDB['thumbnailpath'];
        // }
        
        return $DataDB;
    }

    public static function get_content_type ( $room_id, $sort = 0, $type = 0 )
    {
        // 10 直播
        // 20 回播
        
        // 30 个人认证
        // 31 活动。
        // 32 课程秀
        // 33 才艺秀
        // 34 玩啥（vip专区）
        $table = self::get_table_name( $room_id );
        if ($table == 'bb_push') {
            return 10;
        }
        if ($table == 'bb_rewind') {
            return 20;
        }
        if ($table == 'bb_record') {
            // return 20;该视频类型 1：秀场 2：邀约 3：个人验证 , 
            if ($type == 3) {
                return 30;
            }
            if ($type == 2) {
                return 31;
            }
            if ($type == 1) {
                return 33; 
                
                
//                 if ($sort == 0 || $sort == 2) {
//                     return 33;
//                 }
//                 if ($sort == 1) {
//                     return 32;
//                 }
//                 if ($sort == 3) {
//                     return 34;
//                 }
            }
            return 33;
        }
        return 0;
    }

}