<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Controller;




class Authmanager  extends Common
{
    /**
     * 基础权限列表
     * 
     * @param string $module
     * @return number[]|string[]|number[]|array[][]
     */
    public function index ($role='')
    {
//         if (empty( $ )) {
//             return ['code'=>400,'message'=>'module不能为空'];
//         }
        
        
        $db = Sys::get_container_dbreadonly();
//         $sql="select * from backstage_auth ";
//         $result = $db->fetchAll($sql);
        //if ($module) {
            $sql="select roles,backstage_auth_list.module,backstage_auth_list.module_key,
backstage_auth_list.id as auth_id,
backstage_auth_list.name  
from backstage_auth 
left join backstage_auth_list
 on backstage_auth_list.id = backstage_auth.auth_id";
            $result = $db->fetchAll($sql);
            if ($role)  {
                
                $sql .= " where roles = ? ";
                $result = $db->fetchAll($sql,[ $role ]);
            }
            
// where roles=?";
         //   $result = $db->fetchAll($sql,[ $module ]);
        //}
        
       
        
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
    public function edit ($role, $auth_list)
    {
       
        if ( !in_array( $role,['admin','proxy','channel'] ) ) {
            return ['code'=>400,'message'=>'module错误'];
        }
        
       
        
        $db = Sys::get_container_db();
        
        $temp = explode(',', $auth_list);
        if ($temp) {
            foreach ($temp as $auth_id  ) {
                $sql="select * from backstage_auth_list where id=?";
                $temp2 = $db->fetchRow($sql,[ $auth_id ]);
                if (!$temp2) {
                    return ['code'=>400,'message'=>'权限id有错误，无法操作'];
                }
            }
            
            $sql="delete from backstage_auth where roles=?";
            $db->query($sql, [ $role ]);
            
            foreach ($temp as $auth_id  ) {
                $db->insert("backstage_auth",[
                        'auth_id' =>$auth_id,
                        'roles' =>$role,
                ]);
                
            }
        }
        
        return ['code'=>1,];
    }
    
    
    
    
    
}






