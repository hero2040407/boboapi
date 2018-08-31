<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\video\RaceStatus;

/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class UserRace extends User 
{
    public $err_msg='';
    public $success_count =0;
    
    public function like($self_uid, $log_id,$type)
    {
        $db = Sys::get_container_db();
        $sql="select * from ds_register_log where id=?";
        $row = $db->fetchRow($sql,[ $log_id ]);
        $uid = $row['uid'];
        $race_id = $row['zong_ds_id'];
        $datestr = date("Ymd");
        $race  = \BBExtend\model\Race::find( $race_id );
        
        if ( time() > $race->end_time  ) {
            $this->success_count=0;
            return true;
        }
        
        
//         $race = Race::find( $row['zong_ds_id'] );
//         $user = User::find( $uid );

        if ($type==1) {
        
            
            $sql="select * from  ds_like where self_uid=? and type=1 and register_log_id=? and datestr=?";
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
            $this->success_count=1;
            $sql="update ds_register_log set ticket_count = ticket_count+1 where id=?";
            $db->query($sql,[ $log_id ]);
            return true;
        }
        if ($type==2) {
            $sql="select *  from ds_like where self_uid=? and type=2 and register_log_id=? ";
            $row = $db->fetchRow($sql,[ $self_uid, $log_id,  ]);
            if ($row) {
//                 $this->err_msg='您今日已投过票，请明天再来';
//                 return false;
                $this->success_count=0;
                return true;
            }
            $bind=[
                    'register_log_id' =>$log_id,
                    'create_time' =>time(),
                    'self_uid' =>$self_uid,
                    'target_uid' =>$uid,
                    'race_id' => $race_id,
                    'count' =>1,
                    'datestr' =>$datestr,
                    'type' =>2,
                    
            ];
            $db->insert("ds_like",$bind);
            $this->success_count=1;
            $sql="update ds_register_log set ticket_count = ticket_count+1 where id=?";
            $db->query($sql,[ $log_id ]);
            return true;
            
            
        }
        
        if ($type==3) {
            $result = \BBExtend\Currency::add_bobi($self_uid,
                    -100, '声援大赛好友');
            if ($result!==false ) {
                $bind=[
                        'register_log_id' =>$log_id,
                        'create_time' =>time(),
                        'self_uid' =>$self_uid,
                        'target_uid' =>$uid,
                        'race_id' => $race_id,
                        'count' =>1,
                        'datestr' =>$datestr,
                        'type' =>2,
                        
                ];
                $db->insert("ds_like",$bind);
                $this->success_count=1;
                $sql="update ds_register_log set ticket_count = ticket_count+1 where id=?";
                $db->query($sql,[ $log_id ]);
                return true;
                
            }else {
                $this->err_msg='您的BO币余额不足，不可以投票，请充值';
                return false;
                
            }
        }
        
        
        
        
        if ($type==4) {
            
                $bind=[
                        'register_log_id' =>$log_id,
                        'create_time' =>time(),
                        'self_uid' =>$self_uid,
                        'target_uid' =>$uid,
                        'race_id' => $race_id,
                        'count' =>1,
                        'datestr' =>$datestr,
                        'type' =>4,
                        
                ];
                $db->insert("ds_like",$bind);
                $this->success_count=1;
                $sql="update ds_register_log set ticket_count = ticket_count+1 where id=?";
                $db->query($sql,[ $log_id ]);
                return true;
                
           
        }
        
        
        
        
        
        return false;
        
    }
    
    
    
    /**
     * 分享页面
     * @param unknown $log_id
     * @param unknown $self_uid
     * @return NULL[][]|unknown[][]|mixed[][]|NULL[]|mixed[]|unknown[][][]
     */
    public function info($log_id,$self_uid)
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
        $race_info['upload_type'] = $race->upload_type;
        $race_info['badge'] = $user->get_badge();
      //  $race_info['upload_checked'] = $row['upload_checked'];
        
        $arr= RaceStatus::get_status_v5($uid, $race_id);
        
        $race_info['status']=  $arr['data']['status'];
        
        $result['race_info'] =$race_info ;
        
        
        $upload=[];
        
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
                
                
                $sql="select * from bb_pic where type=1 and uid=?  and act_id=?";
                $url_arr = $db->fetchAll($sql,[ $uid, $race_id ]);
                
                if ( $url_arr  ) {
                    $upload['type']='photo';
                    
                    $new=[];
                    foreach ( $url_arr as $v ) {
                        $new[]= [
                                'url' =>$v['url'],
                                'pic_width' =>$v['width'],
                                'pic_height' =>$v['height'],
                                
                        ];
                    }
                    
                    $upload['photo_url_list']=$new ;
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
        $count=0;
        if ( $self_uid) {
            $sql="select count(*) from ds_like where self_uid=? and race_id=? and target_uid=? 
                 and datestr=?";
            $count = $db->fetchOne($sql,[ $self_uid, $race_id, $uid, date("Ymd") ]);
            
        }
        $result['my_ticket_count_today'] = $count;
        
        // 当前用户参加大赛的状态。
        if ($self_uid) {
          $join_arr = \BBExtend\video\RaceStatus::get_status_v5($self_uid, $race_id);
          $result['self_join_status'] = $join_arr['data']['status'] ;
          $result['self_role'] = 1;
        }else {
            $result['self_join_status'] = 0 ;
            $self_user = \BBExtend\model\User::find( $self_uid );
            $result['self_role'] = $self_user->role;
        }
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
