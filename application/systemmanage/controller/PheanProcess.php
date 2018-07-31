<?php

/**
 * 
 * 队列执行，点评。
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;

use BBExtend\service\pheanstalk\Datadp;
use BBExtend\service\pheanstalk\Data;
use BBExtend\service\pheanstalk\DataDasai;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\service\pheanstalk\DataWeixin;

class PheanProcess
{

    /**
     * 测试用
     */
    public function dptest ( )
    {
        $data = $_SERVER['argv'][2];
        $data = unserialize( urldecode( $data ) );
        $this->dptest_precess( $data );
    }

    private function dptest_precess ( Datadp $data )
    {
        $channel = $data->get_channel( );
        echo "呵呵";
    }

    
    /**
     * 这是处理大赛。
     */
    public function dasai ( )
    {
        $data = $_SERVER['argv'][2];
        $data = unserialize( urldecode( $data ) );
        $this->dasai_precess( $data );
    }
    
    
    /**
     * 这是处理。
     */
    public function weixin ( )
    {
        $data = $_SERVER['argv'][2];
        $data = unserialize( urldecode( $data ) );
        $this->weixin_precess( $data );
    }
    
    
    private function weixin_precess ( DataWeixin $data )
    {
        $uid =  $data->uid;
        $code = $data->code;
        $token = $data->token;
        
        
        $db = \BBExtend\Sys::get_container_db();
        $db->insert( 'bb_alitemp',['uid' => 321 ] );
    }
    
    
    
    
    private function dasai_precess ( DataDasai $data )
    {
        $uid =  $data->get_uid();
        $id = $data->get_id();
        $time = $data->get_time();
        
        $type = $data->get_type();
        
        if ($uid < 0) {
            return $this->errorlog($uid,$type);
        }
        
        if ( $type==1 ) {
            $this->excute_dasai1($uid, $id);
        }
        if ( $type==2 ) {
            $this->excute_dasai2($uid, $id);
        }
        
        
    }
    
    
    
    /**
     *
     * @param unknown $uid
     * @param unknown $id
     */
    private function excute_dasai1($uid, $id)
    {
        $db = \BBExtend\Sys::get_container_db();
        
        $sql="select * from ds_register_log where id=?";
        $row= $db->fetchRow($sql,[ $id ]);
        $uid = $row['uid'];
        
        $sql="select original from  bb_users_platform where type=3 and uid = ?";
        $phone = $db->fetchOne($sql,[ $uid ]);
        if (!$phone) {
            $phone = $row['phone'];
        }
        $name = $row['name'];
        $boboid = $uid;
        $birthday = $row['birthday'];
        $sex = $row['sex'];
        
        $data = [
                'phone'=>$phone,
                'name'=>$name,
                'boboid'=>$boboid,
                'birthday'=>$birthday,
                'sex'=>$sex,
        ];
        $url = "http://47.104.197.175/api/v1/auth/bobo";
        $response = \Requests::post( $url ,[ ], $data);
        
    }
    
    
    private function excute_dasai2($uid, $id)
    {
        $db = \BBExtend\Sys::get_container_db();
        
        $sql="select * from bb_record where id=?";
        $row= $db->fetchRow($sql,[ $id ]);
        $uid = $row['uid'];
        
        $pic = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                $row['big_pic'] , $row['thumbnailpath'] );
        
        $data = [
                'path'=>$row['video_path'],
                'cover'=>$pic,
                'boboid'=>$uid,
                
        ];
        $url = "http://47.104.197.175/api/v1/video/bobo";
        $response = \Requests::post( $url ,[ ], $data);
        
    }
    
    
    
    
    
    
    
    
    /**
     * 这是处理index的。
     */
    public function index ( )
    {
        $data = $_SERVER['argv'][2];
        $data = unserialize( urldecode( $data ) );
        $this->index_precess( $data );
    }

    private function index_precess ( Data $data )
    {
        
        $obj = WorkerJobPush::factory( $data );
        $obj->excute( );
    
    }

    /**
     * 这是处理点评。
     */
    public function dp ( )
    {
        $data = $_SERVER['argv'][2];
        $data = unserialize( urldecode( $data ) );
        $this->dp_precess( $data );
    }

    private function dp_precess ( Datadp $data )
    {
        
        // 谢烨，这分好几种情况，如果名字已经存在，且此单未点评，则必定运行。
        // 如果名字不存在，则为抢单，此时，先检查其余短视频，已邀请当前星推官，且未完成的情况，如有，则返回错误
        $uid = $data->get_uid( );
        $record_id = $data->get_record_id( );
        $time = $data->get_time( );
        $channel = $data->get_channel( );
        
        if ($uid < 0) {
            return $this->errorlog( $uid, $channel );
        }
        
        $db = \BBExtend\Sys::get_container_db( );
        
        $invite = \BBExtend\model\RecordInviteStarmaker::where( 'record_id', $record_id )->first( );
        if (! $invite) {
            return $this->node( $uid, [
                    'code' => 0,
                    'message' => 'invite 不存在'
            ], $channel );
        }
        
        if ($invite->starmaker_uid) {
            // 这是指定的情况，此时，如单已完成，返回错误，如单未完成，返回正确。
            if ($invite->starmaker_uid == $uid) {
                
                if ($invite->status == 1) {
                    return $this->node( $uid, [
                            'code' => 1,
                            'message' => ''
                    ], $channel );
                } else {
                    return $this->node( $uid, [
                            'code' => 0,
                            'message' => '此单已点评过，不可重复点评'
                    ], $channel );
                }
            } else {
                
                return $this->node( $uid, [
                        'code' => 0,
                        'message' => '此单已被他人抢单成功。'
                ], $channel );
            }
        
        } else {
            // 这是抢单的情况，
            // 首先，本人是星推官吗,外面已经检查过，
            // 然后，查当前用户，是否有 其他的status=1 的单子。
            
            $sql = "select count(*)
                                   from bb_record_invite_starmaker
                                  where starmaker_uid = {$uid}
                                    and status=1
                                    and record_id != {$record_id}
                                    and exists (
                                             select 1 from bb_record
                                      where bb_record.id = bb_record_invite_starmaker.record_id
                                       and bb_record.is_remove=0
                                       and bb_record.audit=1
                                  )
            ";
            $count = $db->fetchOne( $sql );
            if ($count) {
                return $this->node( $uid, [
                        'code' => 0,
                        'message' => '您有其他邀请未点评，不能抢单，请查看您的专属邀请'
                ], $channel );
            } else {
                // 抢单成功。
                $invite->starmaker_uid = $uid;
                $invite->new_status = 2;
                $invite->save( );
                
                $id = intval( $invite->id );
                $sql = "select * from bb_record_invite_starmaker
                           where id = {$id}
                         ";
                $db = \BBExtend\Sys::get_container_db_eloquent( );
                $result = \BBExtend\DbSelect::fetchRow( $db, $sql );
                $db::table( 'bb_record_invite_starmaker_log' )->insert( $result );
                
                return $this->node( $uid, [
                        'code' => 1,
                        'message' => '抢单成功。'
                ], $channel );
            }
        }
    
    }

    
    private function errorlog($uid,$channel)
    {
        $db = \BBExtend\Sys::get_container_db();
        $db->insert("bb_alitemp", [
                'create_time' => date("Y-m-d H:i:s"),
                'uid' => $uid,
                'content'=> "当前时间".date("Y-m-d H:i:s")." 这是一个phean的<b>点评队列</b>的测试。传入的随机数是" .$channel ,
        ]);
    }
    
    private function node ( $uid, $result, $channel )
    {
        $db = \BBExtend\Sys::get_container_db( );
        $db->insert( "bb_alitemp",
                [
                        'create_time' => date( "Y-m-d H:i:s" ),
                        'content' => 'message:' . $result['message']
                ] );
        $redis = \BBExtend\Sys::get_container_redis( );
        $redis->lPush( $channel, serialize( $result ) );
        
        $redis->setTimeout( $channel, 60 * 60 );
      //  unset( $redis );
    }

}