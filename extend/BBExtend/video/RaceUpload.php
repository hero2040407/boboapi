<?php
namespace BBExtend\video;



use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 大赛视频上传，帮助类
 * 
 * 
 * @author xieye
 *
 */
class RaceUpload
{
   
    public function upload_record($record_id, $ds_id,$uid)
    {
        
        $db = Sys::get_container_db();
        $record = \BBExtend\model\Record::find( $record_id );
        $video_path = $record->video_path;
        
        // 先查uid 与 ds_id 什么关系。
        $owners = \BBExtend\video\Race::get_owner($ds_id);
       
        // 如果是运营方，加入ds_show_record表，
        // 如果是普通人，则加入ds_record表，注意，需事先删除（仅从大赛表里啊，不是从bb_record）
        if ( in_array($uid, $owners) ) {
            $db->insert("ds_show_video", [
                    'ds_id' =>$ds_id,
                    'room_id' => $record->room_id,
                    'video_id' => $record_id,
                    'uid' => $uid,
                    'type' =>2,
                    'create_time'=>time(),
            ]);
            
        }else {
            $sql ="delete from ds_record where uid=? and ds_id=?";
            $db->query($sql,[ $uid, $ds_id ]);
            $db->insert("ds_record", [
                    'ds_id' =>$ds_id,
                    'uid' => $uid,
                    'create_time'=>time(),
                    'record_id' => $record_id,
            ]);
        }
        
        // 谢烨，为了对付自动转码较早的情况，额外查一下mov和qt
        if ( preg_match('#(mov|qt|quicktime)$#i', $video_path) ) {
            $sql="select * from bb_aliyun_record where video_path=?";
            $row = $db->fetchRow($sql,  [$video_path ]);
            if ($row) {
                $sql="update bb_record set video_path=?,transcoding_complete=1  where id=?";
                $db->query( $sql,[ $row['target_path'],  $record_id] );
            }
        }
        
        // 上传完短视频，得记录一下，因为是大赛啊。
        \BBExtend\backmodel\RaceLog::upload($ds_id, $uid);
        
        //  $uid=0,$id=0,$time=0,$type=1
//         $data= new \BBExtend\service\pheanstalk\DataDasai( $uid,$record_id, time(),2);
        
//         // 查询渠道id
//         $sql="select qudao_id from ds_register_log where uid=? and ds_id=?";
//         $qudao_row = $db->fetchRow($sql,[ $uid, $ds_id ]);
//         if ( $qudao_row  && $qudao_row['qudao_id'] == DASAI_PUSH_QUDAO_ID ) {
//             //  $uid=0,$id=0,$time=0,$type=1
//             $data= new \BBExtend\service\pheanstalk\DataDasai( $uid, $qudao_row['id'] ,time(),2);
//             //  $service = new
//             $client = new \BBExtend\service\pheanstalk\Client();
//             $client->add_dasai($data);
//         }
        
        
//         //  $service = new
//         $client = new \BBExtend\service\pheanstalk\Client();
//         $client->add_dasai($data);
        
        
    }
  
  
   
    
    
    

}