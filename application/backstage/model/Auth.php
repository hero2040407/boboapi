<?php
namespace app\backstage\model;

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Request;



/**
 * 权限类
 * 
 * @author xieye
 *
 */
class Auth 
{
    
    
    private static function setparam($key,$value){
     //   Request::instance()->get([$key => $value ]);
    //    Request::instance()->post([$key => $value ]);
        Request::instance()->param([$key => $value ]);
        
    }
    
    /**
     * 参数过滤，权限保证！！
     * 如果有错误，返回false，返回404
     * 
     * @param unknown $role
     * @param unknown $controller
     * @param unknown $action
     */
    public static function check_param($role,$uid,  $controller, $action)
    {
        $db = Sys::get_container_db_eloquent();
        $route = $controller . '/' . $action;
        
        
        if( $role=='admin' ) {
            return true;
        }
        
        $temp = Request::instance()->param();
        if ($temp) {
            foreach ($temp as $k=>$v) {
              //  echo "111--{$k}--{$v}<br>";
                Request::instance()->param([$k => $v ]);
            }
        }
        
        
        // 渠道注入参数。
        if ( $role == 'channel' ) {
            $sql="select * from backstage_admin where id=?";
            $admin = DbSelect::fetchRow($db, $sql,[ $uid ]);
            
            // 
            $sql="select * from backstage_admin_race where account_id=? ";
            $admin_race = DbSelect::fetchRow($db, $sql,[ $uid ]);
            if (!$admin_race) {
                return false;
            }
            
            
            $field_id = $admin_race['field_id'];
            $ds_id = $admin_race['race_id'];
            
         
            
            // 谢烨，这是通用设置。
            //Request::instance()->get(['field_id' => $field_id ]);
            self::setparam('field_id' , $field_id);
            
            
            if ($route=='message/index') {
                self::setparam('ds_id' , $ds_id);
//                 Request::instance()->get(['ds_id' => $ds_id ]);
            }
            if ($route=='race/detail') {
                self::setparam('ds_id' , $ds_id);
//                 Request::instance()->get(['ds_id' => $ds_id ]);
            }
            
            // 渠道也能看大赛列表
            if ( $route=='race/index' ) {
                self::setparam('ds_id' , $ds_id);
//                 Request::instance()->get(['ds_id' => $ds_id ]);
            }
            
            
            if ($route=='admin/edit') {
                self::setparam('id' , $uid);
//                 Request::instance()->get(['id' => $uid ]);
            }
            return true;
        }
        
        
        
        
        
        // 代理注入参数。
        if ( $role == 'proxy' ) {
            $sql="select * from backstage_admin where id=?";
            $admin = DbSelect::fetchRow($db, $sql,[ $uid ]);
            
            self::setparam('proxy_id' , $uid);
            // 
            $sql="select id from  ds_race where proxy_id=? ";
            $ds_id_arr = DbSelect::fetchCol($db, $sql,[ $uid ]  );
            
            if (empty( $ds_id_arr )) {
                return false;
            }
            // 主要权限检查，是查这个race_id
            if ( input('?param.ds_id')   ) {
                $ds_id = input('param.ds_id');
            } elseif (  input('?param.race_id')  ){
                $ds_id = input('param.race_id');
            } else {
                $ds_id = 0;
            }
            $result = true;
            
            // 赛区编辑，检查 field_id
            if ( $route =='field/edit' ){
                $field_id = input('param.field_id');
                
                $sql="select count(*) from ds_race_field
where id = ?
and exists(
  select 1 from ds_race
   where ds_race_field.race_id = ds_race.id
     and ds_race.proxy_id = ?
)
";
                $count = DbSelect::fetchOne($db, $sql,[ $field_id, $uid ]);
                if (!$count) {
                    return false;
                }
            }
            
            
            // 账号列表
            if ($route=='admin/index') {
                self::setparam('parent' , $uid);
//                 Request::instance()->get(['parent' => $uid ]);
            }
            
            if ($route=='user/index') {
             //   self::setparam('ds_id' , $uid);
                //                 Request::instance()->get(['parent' => $uid ]);
            }
            
            return true;
        }
        return false;
    }
    
    
    private static function check_ds_id($ds_id, $arr)
    {
        if ($ds_id==0) {
            return false;
        }
        if ( in_array( $ds_id,  $arr ) ) {
            return true;
        }
        return false;
    }
    
    
    /**
     * 路由权限校验
     * 
     * @param unknown $role
     * @param unknown $controller
     * @param unknown $action
     * @return boolean
     */
    public static function check_route( $role, $controller, $action )
    {
        
     //   return true;
        
        
        if ($role=='admin') {
            return true;
        }
        
        $route = $controller . '/' . $action;
        
        
        // 代理账号
        if ( $role=='proxy' ) {
            if (in_array($route, [
                    'statistics/index',
                    'message/add',
                    'message/index',
                    'user/index',
                    'user/detail',
                    'user/export_list',
                    
                    'race/detail',
                    'field/edit',
                    'field/add',
                    'field/index',
                    'race/index',
                    'admin/edit',
                    'admin/logout',
                    'admin/login',
                    'admin/index',
                    'admin/getid',
                    
            ] )) {
                return true;
            }
            return false;
            
            
        }
        
        // 渠道账号权限。
        if ( $role=='channel' ) {
            
            if (in_array($route, [
                    'message/add',
                    'message/index',
                    'user/index',
                    'user/detail',
                    'user/export_list',
                    
                    'field/edit',
                    'field/index',
                    'race/detail',
                    'race/index',
                    'admin/edit',
                    'admin/logout',
                    'admin/login',
                    'admin/getid',
            ] )) {
                return true;
            }
            
            // 叫号系统全部支持
            if ( $controller == 'round' ) {
                return true;
            }
            
            
            return false;
            
            
        }
        return false;
    }
    
    
//     protected $table = 'bb_record';
    
}