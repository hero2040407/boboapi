<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;



class User  extends Common
{
    
   
    public function detail($register_id, $field_id=null,$proxy_id=null )
    {
        $db = Sys::get_container_db_eloquent();
        
        $sql="select id from ds_register_log where id=?";
        $id = DbSelect::fetchOne($db, $sql,[ $register_id ]);
        if (!$id){
            return ['code'=>400];
        }
        
        if ($field_id) {
            $sql="select id from ds_register_log where id=? and ds_id =?";
            $id = DbSelect::fetchOne($db, $sql,[ $register_id, $field_id ] );
            
        }
        if ($proxy_id) {
            $sql="select id from ds_register_log where id=? 
and
exists(

 select 1 from ds_race 
  where ds_race.proxy_id= ?
   and ds_race.id = ds_register_log.zong_ds_id
)
";
            $id = DbSelect::fetchOne($db, $sql,[ $register_id, $proxy_id ] );
            
        }
        
        
        $temp = \BBExtend\backmodel\OfflineRegisterLog::find( $id );
        $result = $temp->display_detail();
        
        
        return ['code'=>1, 'data' =>$result ];
        
    }
    
    /**
     * 未过期大赛
     * 
     * @return number[]|string[]|number[]
     */
    public function index($per_page=10,$page=1,$ds_id=null, $field_id=null,$proxy_id=null) 
    {
        $time = time();
        
        $db = Sys::get_container_db_eloquent();
        $paginator = $db::table('ds_register_log')
        ->whereExists(function ($query) {
            $db = Sys::get_container_db_eloquent();
            $query->select($db::raw(1))
            ->from('ds_race')
            ->whereRaw('ds_race.level=1')
            ->whereRaw('ds_race.id = ds_register_log.zong_ds_id')
            ->whereRaw('ds_race.online_type=2')
            ;
        })
        ->select(['id',]);
//         $paginator =  $paginator->where( "parent",0 );// 确保只查大赛
        
        if ($ds_id != null ) {
            $paginator =  $paginator->where( "zong_ds_id", $ds_id );
//             $paginator =  $paginator->where( "end_time",">", time() );
        }
       
        
        if ($field_id != null ) {
            $paginator =  $paginator->where( "ds_id", $field_id );
        }
        if ($proxy_id != null ) {
            
            $paginator = $paginator->whereExists(function ($query) use ($proxy_id,$db) {
                //                 $db = Sys::get_container_db_eloquent();
                $query->select($db::raw(1))
                ->from('ds_race')
                ->whereRaw('ds_race.id = ds_register_log.zong_ds_id')
                ->whereRaw('ds_race.proxy_id='.intval( $proxy_id ))
                ;
            });
            
        }
        
        
        $paginator = $paginator->orderBy('id', 'asc')->paginate($per_page, ['*'],'page',$page);
        $result=[];
        foreach ($paginator as $v) {
            $result[]= $v->id;
        }  
        
       
        $new=[];
        foreach ($result as $v) {
            $temp = \BBExtend\backmodel\OfflineRegisterLog::find( $v );
            $new[]= $temp->display();
        }
        return ['code'=>1, 'data'=>['list' => $new, 
                'pageinfo' =>$this->get_pageinfo($paginator, $per_page) ] ];
    }
    
  
    
        
}




