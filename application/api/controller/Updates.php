<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\UserUpdates;

/**
 * 童星排行
 * 
 * @author xieye
 *
 */
class Updates
{
    public function comment_list()
    {
        
    }
     
    
    
    
    public function add($word='',$pic_json='', $style=0, $uid, $token='')
    {
        $db = Sys::get_container_db();
        if ($style==2 ) {
            UserUpdates::insert_word($uid, $word);
        }
        
        if ( $style == 3 || $style==5 ) {
            $pic_arr = json_decode($pic_json,1  );
            UserUpdates::insert_word($uid, $word, $pic_arr);
        }
        
        return ['code'=>1];
    }
    
    
    
    
    //动态,1发现，2星动态。
    public function index($uid=10000,$startid=0, $length=10,$type=1)
    {
        $startid=intval($startid);
        $length=intval($length);
        

        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_users_updates order by create_time desc limit ?,?";
        
        if ($type==2) {
            $sql="select * from bb_users_updates 
              where agent_uid >0
              order by create_time desc limit ?,?";
            
        }
        
        $result = $db->fetchAll($sql,[ $startid, $length ]);
        $new=[];
        foreach ($result as $v) {
            $id = $v['id'];
            $updates = UserUpdates::find( $id );
            $new[]= $updates;
        }
        
        //  $db = Sys::get_container_dbreadonly();
        
      
        
        return [
                'code'=>1,
                'data'=>[
                        'list' =>$new,
                        'is_bottom' =>(count($new) == $length)?0:1,
                ]
        ];
        
        
    }
    

}


