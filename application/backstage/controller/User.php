<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;



class User   extends Common
{
    
    public function export_list($ds_id = null, $field_id = null, $proxy_id = null,
                                $match_status = '', $sex = '')
    {
        $per_page=10000;
        $page=1;

        $map = ['has_dangan' => 1, 'has_pay' => 1];
        if ($sex !== '') $map['sex'] = $sex;
        if ($match_status) $map['race_status'] = $match_status;

        $db = Sys::get_container_db_eloquent();
        $paginator = $db::table('ds_register_log')
        ->where($map)
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
            $new[]= $temp->get_export();
        }
        
        $new2=[];
        $title=["报名id","BOBO号",'姓名','性别','手机','生日','身高','体重','照片','大赛id','大赛名称','赛区id','赛区名称','在线缴纳费用(元)','报名时间'];
        foreach ($new as $v) {
            $new2[]= [ $v['id'], $v['uid'] , $v['name'],$v['sex'], $v['phone'] ,$v['birthday'],
                    $v['height'].' cm',$v['weight'].' kg',$v['pic'],
                    $v['race_id'],$v['race_title'],$v['field_id'],
                    $v['field_title'],  $v['money'], $v['create_time']  ];
        }
        $this->put_csv( $new2 ,  $title);
        
    }
    
    
    
    private function put_csv($list,$title)
    {
        $file_name = "dasai".date("Ymd_Hi").".csv";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$file_name );
        header('Cache-Control: max-age=0');
        $file = fopen('php://output',"a");
        $limit = 1000;
        $calc = 0;
        foreach ($title as $v){
//             $tit[] = iconv('UTF-8', 'GB2312//IGNORE',$v);
            $tit[] = \BBExtend\common\Str::u2g($v);
            
            
        }
        fputcsv($file,$tit);
        foreach ($list as $v){
            $calc++;
            if($limit == $calc){
                ob_flush();
                flush();
                $calc = 0;
            }
            foreach($v as $t){
//                 $tarr[] = iconv('UTF-8', 'GB2312//IGNORE',$t);
                $tarr[] = \BBExtend\common\Str::u2g($t);
            }
            fputcsv($file,$tarr);
            unset($tarr);
        }
        unset($list);
        fclose($file);
        exit();
    }
    
    
    
   
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
    public function index($per_page=10,$page=1,$ds_id=null, $field_id=null,$proxy_id=null,
                            $uid = '', $phone = '', $name = '', $match_status = '')
    {
        $map = ['has_dangan' => 1, 'has_pay' => 1];

        if (!empty($uid)) $map['uid'] = $uid;
        if (!empty($phone)) $map['phone'] = $phone;
        if (!empty($name)) $map['name'] = $name;
//        不传为全部 11为签到 12为晋级 13为淘汰
        if ($match_status) $map['race_status'] = $match_status;

        $db = Sys::get_container_db_eloquent();
        $paginator = $db::table('ds_register_log')
        ->where($map)
        ->whereExists(function ($query) {
            $db = Sys::get_container_db_eloquent();
            $query->select($db::raw(1))
            ->from('ds_race')
            ->whereRaw('ds_race.level=1')
            ->whereRaw('ds_race.id = ds_register_log.zong_ds_id')
            ->whereRaw('ds_race.online_type=2');
        })->select(['id',]);
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




