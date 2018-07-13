<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\Record;

/**
 * 童星排行
 * 
 * @author xieye
 *
 */
class Updates
{
    public function add($id,$word='',$pic_json='', $video_json='',$style=0)
    {
        $db = Sys::get_container_db();
        
        
    }
    
    //动态,1发现，2星动态。
    public function index($uid=10000,$startid=0, $length=10,$type=1)
    {
        $startid=intval($startid);
        $length=intval($length);
        
        $page = $startid/$length;
        $page = intval($page)+1;
        
        //  $db = Sys::get_container_dbreadonly();
        
        $db = Sys::get_container_db_eloquent();
        
        $paginator = $db::table('bb_record')->select(['id',]);
        //$paginator =  $paginator->where( "has_sign", 1 );
        $paginator =  $paginator->where( "audit", 1 );
        $paginator =  $paginator->where( "is_remove", 0);
        $paginator =  $paginator->where( "type", 1);
        
        
        
        
        
        $paginator = $paginator
         ->orderBy( "id","desc" )
         ->paginate($length, ['*'],'page',$page);
        
        $new=[];
        foreach ($paginator as $v) {
            $id = $v->id;
            $record = Record::find($id);
            
            $style=4;
            if ( $record->title ) {
                $style = 6;
            }
            
            $return = [
                   'uid'  => $record->uid, 
                   'style'=>$style,
                   'video'=>[
                           
                   ],
                   'pic'=>null,
                   'card'=>null,
                    
            ];
            
         //   $temp = $advise->get_index_info();
            $new[]= $return;
        }
        
        
        return [
                'code'=>1,
                'data'=>[
                        'list' =>$new,
                        'is_bottom' =>(count($new) == $length)?0:1,
                ]
        ];
        
        
    }
    

}


