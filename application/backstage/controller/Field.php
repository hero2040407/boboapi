<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 赛区接口
 * 
 * @author Administrator
 *
 */
class Field  extends Common
{
    /**
     * 赛
     * 
     * @return number[]|string[]|number[]
     */
    public function index($status=null,$is_valid=null, $per_page=10,$page=1,$ds_id=null,$field_id=null,
            $proxy_id=null
            ) 
    {
        $db = Sys::get_container_db_eloquent();
        
    //    $proxy_id = input("get.proxy_id");
        
        
        $paginator = $db::table('ds_race_field')->select(['id',]);
        
        if ($status != null ) {
            $paginator =  $paginator->where( "status", $status );
            
        }
        if ($is_valid != null ) {
            $paginator =  $paginator->where( "is_valid", $is_valid );
            
        }
        if ($ds_id != null ) {
            $paginator =  $paginator->where( "race_id", $ds_id );
            
        }
        if ($field_id != null ) {
            $paginator =  $paginator->where( "id", $field_id );
            
        }
        
        if ($proxy_id) {
            $paginator = $paginator->whereExists(function ($query) use ($proxy_id,$db) {
//                 $db = Sys::get_container_db_eloquent();
                $query->select($db::raw(1))
                ->from('ds_race')
                ->whereRaw('ds_race.id = ds_race_field.race_id')
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
            $temp = \BBExtend\backmodel\RaceField::find( $v );
            $new[]= $temp->display();
        }
        return ['code'=>1, 'data'=>['list' => $new,
                'pageinfo' =>$this->get_pageinfo($paginator, $per_page) ] ];
    }
    
    
    private function create_channel_account($parent ){
        $obj = new \BBExtend\backmodel\Admin();
        $account_arr = $this->get_full_account( 2 );
        $db = Sys::get_container_db_eloquent();
     //   $db::table('')
    }
    
    
    // 添加赛区
    public function add($race_id,$address,$title,$realname,$phone  )
    {
        $obj = \BBExtend\backmodel\Race::find( $race_id );
        if (!$obj) {
            return ['code'=>400, 'message'=> 'race_id错误' ];
        }
        if ($obj->level != 1  ) {
            return ['code'=>400, 'message' => 'race_id权限错误' ];
        }
        if ($obj->proxy_id == 0 ) {
            return ['code'=>400, 'message' => '该大赛还没有代理人' ];
        }
        $proxy_id = $obj->proxy_id;
        
        if ( empty( $address )  ||  empty( $title) ||  empty( $realname) ||  empty( $phone) ) {
            return ['code'=>400, 'message' => '缺少参数' ];
        }
        
        
        $account_arr = $this->get_full_account(2);
        
        
        $account = new \BBExtend\backmodel\Admin();
        $account->realname = $realname;
        $account->phone = $phone;
        $account->level = 2;
        $account->parent = $proxy_id;
        $account->account = $account_arr['account'];
        $account->pwd = $account_arr['pwd'];
        $account->pwd_original = $account_arr['pwd_original'];
        $account->is_valid=1;
        $account->create_time = time();
        $account->save();
        
       
        
        
        
        $obj = new \BBExtend\backmodel\RaceField();
        $obj->address = $address;
        $obj->title = $title;
        $obj->channel_id = $account->id;
        $obj->status=0; //默认等待中。
        $obj->is_valid=1; // 有效
        $obj->create_time = time();
        $obj->race_id = $race_id;
        
        $obj->save();
        
        
        $admin_race = new \BBExtend\backmodel\AdminRace();
        $admin_race->account_id= $account->id ;
        $admin_race->race_id=$race_id;
        $admin_race->field_id= $obj->id ;
        $admin_race->save();
        
        $result =  ['code'=>1,'data' =>[
                'insert_id' =>$obj->id,
                'account' => $account->account,
                'pwd'     =>  $account->pwd_original,
                'channel_id' => $account->id,
                //''
        ] ];
        
    //    Sys::debugxieye( \BBExtend\common\Json::encode( $result ) );
        return $result;
    }
    
    
    /**
     * 修改赛区。
     * 
     * @param unknown $address
     * @param unknown $title
     * @param unknown $field_id
     * @param unknown $is_valid
     * @param unknown $status
     * @return number[]|string[]|number[]
     */
    public function edit($address,$title,$field_id,$is_valid, $status  )
    {
        $id = \BBExtend\Session::get_my_id();
        
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field_id) {
            return ['code'=>400, 'message' =>'赛区id错误。' ];
        }
        $field->address=$address;
        $field->title = $title;
        $field->is_valid = $is_valid;
        $field->status = $status;
        
        
        $field->save();
        return ['code' =>1 ];
        
    }
    
}






