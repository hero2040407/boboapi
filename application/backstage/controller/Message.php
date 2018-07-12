<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\fix\MessageType;


class Message  extends Common
{
    
    
    /**
     * 审核 消息
     * 
     * @param unknown $id
     * @param unknown $result
     * @return number[]|string[]
     */
    public function check($id, $result)
    {
        $db = Sys::get_container_db();
        $message = \BBExtend\backmodel\RaceMessage::find( $id );
        if (!$message) {
            return ['code'=>0,'message'=>'id err'];
        }
        if ( in_array( $message->is_valid,[1,2])) {
            return ['code'=>0,'message'=>'已经审核过'];
        }
        
        
        if (!in_array( $result,[1,2] )) {
            return ['code'=>0,'message'=>'result err'];
        }
        $message->is_valid = $result;
        $message->check_time = time() ;
        $message->save();
        
        if ($result==1) {
            $select = $db->select();
            $select->from('ds_register_log','uid');
            $select->where('zong_ds_id = ?', $message->ds_id );
            if ($message->field_id) {
                $select->where('ds_id = ?', $message->field_id );
            }
            // 
            if ($message->target_type==1) {
                // 只查成功者
                $select->where('race_status = 12' );
            }
            if ($message->target_type==2) {
                // 只查失败
                $select->where('race_status = 13' );
            }
            
            $result = $db->fetchCol($select);
            $client = new \BBExtend\service\pheanstalk\Client();
            foreach ($result as $uid) {
                
                $client->add(
                        new \BBExtend\service\pheanstalk\Data($uid ,
                                MessageType::dasai_message  ,
                                [       'content' => $message->content,
                                        'small_type' => 'hand',
                                ],
                                time()  )
                        );  
            }
            
        }
        
        return ['code'=>1];
    }
    
    
    public function index($is_valid=null,$per_page=10,$page=1,$ds_id=null)
    {
        $db = Sys::get_container_db_eloquent();
        // 这里请自己先手动插入一条数据，表结构见前面的文章。
        $paginator = $db::table('ds_race_message')->select( ['id',] );
        if ($is_valid != null ) {
            $paginator =  $paginator->where( "is_valid",$is_valid );
        }
        if ($ds_id != null ) {
            $paginator =  $paginator->where( "ds_id",$ds_id );
        }
        
        
        
        $paginator = $paginator->orderBy('id', 'desc')
            ->paginate($per_page, ['*'],'page',$page);
        $result=[];
        foreach ($paginator as $v) {
            
            $result[]= $v->id;
            
        }
        
        $new=[];
        foreach ( $result as $v ){
            $temp = \BBExtend\backmodel\RaceMessage::find( $v );
            $new[]= $temp->display() ;
        }
       
        return ['code'=>1,'data'=>[ 'list' => $new,
                'pageinfo' =>$this->get_pageinfo($paginator, $per_page) ]];
    }
    
    
    // 添加消息。
    /**
     * 所有人，
     * 晋级者，
     * 失败者。
     * 
     * @param unknown $type
     * @param number $ds_id
     * @param number $field_id
     * @param unknown $content
     */
    public function add( $type=0,$ds_id=0,$field_id=0,$content  )
    {
        $db = Sys::get_container_db_eloquent();
        if (empty( $content )) {
            return ['code'=>400, 'message' => '信息不全' ];
        }
        
        $race  = \BBExtend\backmodel\Race::find( $ds_id );
        if (!$race ) {
            return ['code'=>400, 'message' => '大赛id错误' ];
        }
        if ( $field_id ) {
            $field = \BBExtend\backmodel\RaceField::find( $field_id );
            if (!$field) {
                return ['code'=>400, 'message' => '赛区id错误' ];
            }
        }
        if ( !in_array($type, [0,1,2] ) ) {
            return ['code'=>400, 'message' => 'type错误' ];
        }
        $db = Sys::get_container_db_eloquent();
        $db::table('ds_race_message')->insert([
                'ds_id' => $ds_id,
                'field_id' => $field_id,
                'is_valid' =>0,
                'create_time' =>time(),
                'target_type' => $type,
                'admin_id'  => \BBExtend\Session::get_my_id(),
                'content' =>$content,
                
        ]);
        
        return ['code'=>1,];
        
    }
    
    
    /**
     * 自动公告读取。
     * 
     * @param string $type
     * @return number[]|string[]|number[]|mixed[][]|number[]|string[][]
     */
    public function read($type='race_msg_register')
    {
        if ( !in_array( $type,['race_msg_register','race_msg_promote'  ] ) ) {
            return ['code'=>400,'message' =>'type error' ];
        }
        $db = Sys::get_container_db();
        // 13 是配置表的消息设置。
        $sql="select * from bb_config_str where type=13 and config=?";
        
        
        $result = $db->fetchRow($sql,[ $type ]);
        if ($result  ) {
           
            return ['code'=>1,'data'=>['message' => $result['val'] ] ];
            
        }else {
            
            return ['code'=>1, 'data'=>['message' => '' ] ];
        }
    }
    
    
    /**
     * 自动公告修改。
     * 
     * @return number[]|string[]|number[]
     */
    public function edit($type='race_msg_register', $content) 
    {
        if (empty( $content )) {
            return ['code'=>400,'message' =>'内容不能空' ];
        }
        if ( !in_array( $type,['race_msg_register','race_msg_promote'  ] ) ) {
            return ['code'=>400,'message' =>'type error' ];
        }
        
        
        
        $db = Sys::get_container_db();
        // 13 是配置表的消息设置。
        $sql="select * from bb_config_str where type=13 and config=?";
        
        
        $result = $db->fetchRow($sql,[ $type ]);
        if ($result  ) {
            $sql="update bb_config_str set val=? where id=?";
            $db->query( $sql,[ $content, $result['id'] ] );
            
            
        }else {
            $db->insert("bb_config_str", [
                    'config' => $type,
                    'val' =>$content,
                    'type'=>13,
            ]);
            
        }
        
        return ['code'=>1 ];
    }
    
  
    
        
}




