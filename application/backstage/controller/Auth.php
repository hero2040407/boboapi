<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Controller;




class Auth  extends Common
{
    /**
     * 基础权限列表
     * 
     * @param string $module
     * @return number[]|string[]|number[]|array[][]
     */
    public function index ($module='')
    {
        if (empty( $module )) {
            return ['code'=>400,'message'=>'module不能为空'];
        }
        
        
        $db = Sys::get_container_dbreadonly();
        $sql="select * from backstage_auth_list ";
        $result = $db->fetchAll($sql);
        if ($module) {
            $sql="select * from backstage_auth_list where module=?";
            $result = $db->fetchAll($sql,[ $module ]);
        }
        
       
        
        return ['code'=>1,'data'=>[ 'list' => $result ]];
    }
    
   
   
    
    
    /**
     * 权限编辑
     *
     * @param string $module
     * @param string $module_key
     * @param string $name
     * @param string $roles
     * @return number[]|string[]|number[]|number[][]
     */
    public function edit ($id,$module='',$module_key='',$name='')
    {
        if (empty( $module ) || empty( $module_key ) || empty( $name ) ) {
            return ['code'=>400,'message'=>'参数不能为空'];
        }
        if ( !in_array( $module,['front','api','button'] ) ) {
            return ['code'=>400,'message'=>'module错误'];
        }
        
        $auth = \BBExtend\backmodel\Authlist::find( $id );
        if (!$auth) {
            return ['code'=>400,'message'=>'id错误'];
        }
        
        // 查询，键和module是否重复
        
        $db = Sys::get_container_db_eloquent();
        $sql = "select count(*) from backstage_auth_list where module=? and module_key=? and id != ? ";
        $result = DbSelect::fetchOne($db, $sql,[ $module, $module_key,$id ]);
        if ($result) {
            return ['code'=>400, 'message' => 'module和key已有重复数据'];
        }
        
        
//         // 查询角色 是否正确。
//         $temp = explode(',', $roles);
//         foreach ( $temp as $v ) {
//             if ( !in_array( $v,['admin','proxy','channel'] ) ) {
//                 return ['code'=>400,'message'=>'roles错误'];
//             }
//         }
        
        $auth->module = $module;
        $auth->module_key = $module_key;
        $auth->name = $name;
    //    $auth->roles = $roles;
        $auth->save();
        
//         $id =  $db::table('backstage_auth')->insertGetId([
//                 'module'=>$module,
//                 'module_key' =>$module_key,
//                 'name' => $name,
//                 'roles' =>$roles,
//         ]);
        
        return ['code'=>1,];
    }
    
    
    /**
     * 权限增加
     * 
     * @param string $module
     * @param string $module_key
     * @param string $name
     * @param string $roles
     * @return number[]|string[]|number[]|number[][]
     */
    public function add ($module='',$module_key='',$name='')
    {
        if (empty( $module ) || empty( $module_key ) || empty( $name )   ) {
            return ['code'=>400,'message'=>'参数不能为空'];
        }
        if ( !in_array( $module,['front','api','button'] ) ) {
            return ['code'=>400,'message'=>'module错误'];
        }
       
        // 查询，键和module是否重复
        
        $db = Sys::get_container_db_eloquent();
        $sql = "select count(*) from backstage_auth_list where module=? and module_key=?";
        $result = DbSelect::fetchOne($db, $sql,[ $module, $module_key ]);
        if ($result) {
            return ['code'=>400, 'message' => 'module和key已有重复数据'];
        }
        
//         $temp = explode(',', $roles);
//         foreach ( $temp as $v ) {
//             if ( !in_array( $v,['admin','proxy','channel'] ) ) {
//                 return ['code'=>400,'message'=>'roles错误'];
//             }
//         }
        
        $id =  $db::table('backstage_auth_list')->insertGetId([
             'module'=>$module,
                'module_key' =>$module_key,
                'name' => $name,
               // 'roles' =>$roles, 
        ]);
        
        return ['code'=>1,'data'=>['insert_id'=>$id]];
    }
    
    
    /**
     * 权限删除
     * 
     * @param unknown $id
     * @return number[]|string[]|number[]
     */
    public function remove($id) {
        $db = Sys::get_container_db_eloquent();
        $sql="select count(*) from backstage_auth_list where id = ?";
        $result = DbSelect::fetchOne($db, $sql,[ $id ]);
        if (!$result) {
            return ['code'=>400,'message'=>'id错误'];
        }
        
        $sql="select count(*) from backstage_auth where auth_id = ?";
        $result = DbSelect::fetchOne($db, $sql,[ $id ]);
        if ($result) {
            return ['code'=>400,'message'=>'该权限已被使用，不能删除。'];
        }
        
        
        
        $db::table('backstage_auth_list')->where('id' , $id )->delete();
        return ['code'=>1, ];
    }
    
    
    
    
    
}






