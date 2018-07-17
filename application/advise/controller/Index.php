<?php

namespace app\advise\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\Advise;
/**
 * 通告列表
 * @author xieye
 *
 */
class Index 
{
    public function index($startid=0,$length=10,$uid=10000,$auth=-1,$address='',
            $min_age=-1,$max_age=-1,
            $sex=-1,$card_type=-1,$type=-1,$title=''
            
            )
    {
        $startid=intval($startid);
        $length=intval($length);
        
        $page = $startid/$length;
        $page = intval($page)+1;
        
        //  $db = Sys::get_container_dbreadonly();
        
        $db = Sys::get_container_db_eloquent();
        
        $paginator = $db::table('bb_advise')->select(['id',]);
        //$paginator =  $paginator->where( "has_sign", 1 );
        
        
        if ($address) {
            $paginator =  $paginator->where( "address", $address );
        }
        // 将来修改，应该合并条件。
        if ($min_age!=-1 && $max_age!=-1) {
            $min_age = intval( $min_age );
            $max_age = intval( $max_age );
            
            //$year = date("Y") - $min_age;
            
            $paginator = $paginator->whereExists(function ($query) use ($min_age,$max_age, $db) {
                $query->select($db::raw(1))
                ->from('bb_advise_role')
                ->whereRaw('bb_advise_role.advise_id = bb_advise.id')
                ->whereRaw("bb_advise_role.min_age >= {$min_age}" )
                ->whereRaw("bb_advise_role.max_age <= {$max_age}" )
                ;
            });
        }
        
        if ($auth!=-1) {
            $auth=intval($auth);
            $paginator =  $paginator->where( "auth", $auth );
        }
        
        
        if ($sex!=-1) {
            $sex = intval( $sex );
            $paginator = $paginator->whereExists(function ($query) use ($sex, $db) {
                $query->select($db::raw(1))
                ->from('bb_advise_role')
                ->whereRaw('bb_advise_role.advise_id = bb_advise.id')
                ->whereRaw("bb_advise_role.sex = {$sex}" )
                ;
            });
        }
        
        // 0免费，1影视，2娱乐，3特殊试镜卡
        if ($card_type!=-1) {
            $card_type =intval($card_type);
            if ($card_type==3) {
                $paginator =  $paginator->whereRaw( "audition_card_type>2" );
            }else {
                $paginator =  $paginator->where( "audition_card_type", $card_type );
            }
        }
        
        if ($type!=-1 && $type) {
//             $type =intval($type);
            
            $temp  = array();
            $temp2 = explode(',', $type);
            foreach ($temp2 as $v) {
                $temp [] = intval($v);
            }
        //    $temp = implode(',',$temp);
            
            $paginator =  $paginator->whereIn( "type", $temp );
        }
        
        if ($title) {
            $title = \BBExtend\common\Str::like($title);
            if ($title) {
                $paginator =  $paginator->whereRaw( "title like '%{$title}%'"  );
            }
        }
        
        
        
        $paginator = $paginator
        ->orderBy( "is_recommend","desc" )
        ->orderBy('id', 'desc')->paginate($length, ['*'],'page',$page);
     //   dump($paginator);
        
        $new=[];
        foreach ($paginator as $v) {
            $id = $v->id;
            $advise = Advise::find($id);
            $temp = $advise->get_index_info();
            $new[]= $temp;
        }
        
        
        return [
                'code'=>1,
                'data'=>[
                        'list' =>$new,
                        'is_bottom' =>(count($new) == $length)?0:1,
                ]
        ];
        
    }
    
    
    public function type_list(){
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_advise_type order by id";
        $result = $db->fetchAll($sql);
        return ['code'=>1,'data'=>[
                'list' => $result,
        ]];
        
    }
   
    /**
     * 通告详情。
     */
    public function detail($id)
    {
    
        $advise = Advise::find($id);
        //$temp = $advise->get_index_info();
        return ['code'=>1, 'data' =>$advise->detail_info()   ];
    }
    
}





