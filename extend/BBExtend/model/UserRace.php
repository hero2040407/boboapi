<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class UserRace extends User 
{
    public $err_msg='';
    
    public function like($self_uid, $log_id,$type){
        $db = Sys::get_container_db();
        $sql="select * from ds_register_log where id=?";
        $row = $db->fetchRow($sql,[ $log_id ]);
        $uid = $row['uid'];
        $race_id = $row['zong_ds_id'];
        
//         $race = Race::find( $row['zong_ds_id'] );
//         $user = User::find( $uid );
        $datestr = date("Ymd");
        $sql="select * ds_like where self_uid=? and type=1 and register_log_id=? and date=?";
        $row = $db->fetchRow($sql,[ $self_uid, $log_id, $datestr ]);
        if ($row) {
            $this->err_msg='您今日已投过票，请明天再来';
            return false;
        }
        $bind=[
                'register_log_id' =>$log_id,
                'create_time' =>time(),
                'self_uid' =>$self_uid,
                'target_uid' =>$uid,
                'race_id' => $race_id,
                'count' =>1,
                'datestr' =>$datestr,
                'type' =>1,
                
        ];
        $db->insert("ds_like",$bind);
        $sql="update ds_register_log where ticket_count = ticket_count+1 where id=?";
        $db->query($sql,[ $log_id ]);
        
        
        return true;
        
    }
    
    
    
    
    public function info($log_id)
    {
        $db = Sys::get_container_db();
        $sql="select * from ds_register_log where id=?";
        $row = $db->fetchRow($sql,[ $log_id ]);
        $uid = $row['uid'];
        $race_id = $row['zong_ds_id'];
        
        $race = Race::find( $row['zong_ds_id'] );
        $user = User::find( $uid );
        
        $result=[];
        $race_info=[];
        $race_info['pic'] = $row['pic'];
        $race_info['name'] = $row['name'];
        $race_info['field_name'] = $this->get_field_name($row['ds_id']  );
        $race_info['age'] = $row['age'];
        $race_info['race_name'] = $race->title;
        $race_info['ticket_count'] = $row['ticket_count'];
        
        
        $result['race_info'] =$race_info ;
        
        
        $upload=null;
        
        // 下面 是上传内容。
        if ( $race->upload_type == 1 || $race->upload_type == 3) {
            // 视频可能
            if ( $row['record_url'] ) {
                
                $upload['type']='video';
                $upload['record_url']=$row['record_url'] ;
                $upload['record_cover']=$row['record_cover'] ;
                
            }
            
        }else {
            // 图片可能
            if ( $row['pic_id_list'] ) {
                
                
                $sql="select url from bb_pic where type=1 and uid=?  and act_id=?";
                $url_arr = $db->fetchCol($sql,[ $uid, $race_id ]);
                
                if ( $url_arr  ) {
                    $upload['type']='photo';
                    $upload['photo_url_list']=$url_arr ;
                }
                
                
            }
        }
        $result[ 'upload' ] = $upload;
        
        
        // 下面个性，才艺。
        
        $userinfo=[];
        if ( $user->role==3 ) {
            $userinfo['gexing'] = $user->get_gexing_arr();
            $userinfo['jingyan'] = $user->get_jingyan_arr();
            
        }
        $result['vip_info'] = $userinfo;
        
        
        $updates_list = [];
        // 下面 是动态。
        $sql="select id from bb_users_updates
   where status=1
     and is_remove=0
     and style in (3,5)
     and  uid = ?
   order by create_time desc limit 0,5";
        $updates_id_arr = $db->fetchCol($sql,[$uid]);
        foreach ($updates_id_arr as $id ) {
            $updates = \BBExtend\model\UserUpdates::find( $id );
            
            $updates_list[]= $updates->list_info(10000);
            
        }
        $result['updates_list'] = $updates_list;
        return $result;
        
    }
    
    
    private function get_field_name($field_id)
    {
        $db = Sys::get_container_db();
        $sql="select * from ds_race_field where id=?";
        $row = $db->fetchRow($sql,[ $field_id ]);
        return $row['title'];
    }
    
      
    
}