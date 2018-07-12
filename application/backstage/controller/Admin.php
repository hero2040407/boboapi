<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Controller;




class Admin  extends Common
{
   
    private function set_session($row){
        
        \BBExtend\Session::set_my_id($row['id']);
        
    }
    
    private function clean_up_session(){
        \BBExtend\Session::clean_up_my_id();
        
    }
    
    
    public function getid(){
        return ['code'=>1,'data'=> \BBExtend\Session::get_my_id() ];
    }
    
    
    public function logout(){
        $this->clean_up_session();
        return ['code'=>1, ];
    }
    
    /**
     * 辅助用，查某个账号是否有有效的大赛，或赛区，
     * 
     * @param unknown $admin_row
     * @return boolean
     */
    private function has_valid_field($row )
    {
        $db = Sys::get_container_db_eloquent();
        $id = $row['id'];
        if ($row['level']==1) {
            // 这使得代理。
            $sql="select count(*) from ds_race where is_active=1 and proxy_id=?";
            $count = DbSelect::fetchOne($db, $sql,[ $id ]);
            if ($count) {
                return true;
            }
            
        }
        if ($row['level']==2) {
            // 这使得渠道。
            $sql="select count(*) from ds_race_field where is_valid=1 and channel_id=?";
            $count = DbSelect::fetchOne($db, $sql,[ $id ]);
            if ($count) {
                return true;
            }
            
            
        }
        return false;
        
    }
    
    
    public function login($account, $pwd)
    {
        if (empty($account) || empty( $pwd )  ) {
            return ['code'=>400,'message'=>'账号和密码都不能为空'];
        }
        
        // 谢烨，查出对应的前端权限列表。
        
        if ($account=='admin' && $pwd=='7lxpkdd' ) {
            $this->set_session(['id' => -1 ]);
            return ['code'=>1,'data'=>['role'=> 'admin','auth_list'=> $this->get_auths('admin')  ] ];
            
        }
        
        $db = Sys::get_container_db_eloquent();
        $sql="select * from backstage_admin where is_valid=1 and account=? and pwd=?";
        $row = DbSelect::fetchRow($db, $sql,[ $account, md5( $pwd ) ]);
        if ($row) {
            if ($row['level']==1) {
                $auth = 'proxy';
            }else {
                $auth = 'channel';
            }
            
            // 谢烨，检查是否有有效的赛区。
            $temp = $this->has_valid_field($row);
            if (!$temp) {
                return ['code'=>400,'message'=>'该账户的对应大赛已经被禁止，您无法登陆。'];
            }
            
            
            $this->set_session($row );
            
            // 下面是查渠道的大赛id和赛区id。 
            $sql="select * from backstage_admin_race where account_id=?";
            $admin_race = DbSelect::fetchRow($db, $sql,[$row['id']]  );
            if ($admin_race) {
                $race_id  = $admin_race['race_id'];
                $field_id = $admin_race['field_id'];
            }else {
                $race_id= $field_id = 0;
                
            }
            
            if ($auth=='proxy') {
                $sql="select id from ds_race where proxy_id=?";
                $race_id_list = DbSelect::fetchCol($db, $sql,[ $row['id'] ]);
            }else {
                $race_id_list =[];
            }
            
            
            return ['code'=>1,'data'=>['role' => $auth,
                    'field_race_id' =>$race_id,
                    'field_id' => $field_id,
                    'race_id_list' => $race_id_list,// 大赛id数组
                    'auth_list' =>$this->get_auths($auth),  
                    
            ] ];
            
        }else {
            return ['code'=>400,'message'=>'没有找到匹配的账号，请检查账号和密码'];
        }
    }
    
    
    /**
     * 账号列表
     * 
     * 权限：
     * admin / index
index：代理通过parent字段。渠道通过field_id字段。
     * 
     * @return number[]|unknown[][][][]
     */
    public function index ($level=null, $is_valid=null,$per_page=10,$page=1,$parent=null,
            $id=null,$field_id=null)
    {
        
       // $db = Sys::get_container_dbreadonly();
         $db = Sys::get_container_db_eloquent();
         // 这里请自己先手动插入一条数据，表结构见前面的文章。
         $paginator = $db::table('backstage_admin')->select(['id',]);
         if ($level != null ) {
             $paginator =  $paginator->where( "level",$level );
         }
         if ($is_valid != null ) {
             $paginator =  $paginator->where( "is_valid",$is_valid );
         }
         if ($parent != null ) {
             $paginator =  $paginator->where( 
                     function ($query) use ($parent) {
                         $query->where('id', '=', $parent)
                         ->orwhere('parent', '=', $parent);
                         
                     }
                     
                     );
         }
         if ($id != null ) {
             $paginator =  $paginator->where( "id",$id );
         }
         
         if ($field_id != null ) {
             $paginator = $paginator->whereExists(function ($query) use ($db, $field_id) {
                 $query->select($db::raw(1))
                 ->from('ds_race_field')
                 ->whereRaw('ds_race_field.channel_id = backstage_admin.id')
                 ->whereRaw('ds_race_field.id = '.intval( $field_id ))
                 ;
             });
         }
         
         
         
         
         $paginator = $paginator->orderBy('id', 'asc')
           ->paginate($per_page, ['*'],'page',$page);
         $result=[];
         foreach ($paginator as $v) {
             
             $result[]= $v->id;
             
         }  
        
        $new=[];
        foreach ( $result as $v ){
            $temp = \BBExtend\model\BackstageAdmin::find( $v );
            $new[]= $temp->display() ;
        }
        return ['code'=>1,'data'=>[ 'list' => $new,
                'pageinfo' =>$this->get_pageinfo($paginator, $per_page) ]];
        
    }
   
    
    public function add ($account,$realname,$phone, $level,$parent,$field_id=null,$proxy_id=null)
    {
        
        if ( !$this->check_add_auth($id,$field_id,$proxy_id )) {
            return ['code'=>400,'message'=>'您无权操作'];
        }
        
        
        $parent = intval($parent);
        
        if (empty( $account ) || empty( $realname ) || empty( $phone ) || empty( $level )  
                   ) {
            return ['code'=>400,'message'=>'参数不能为空'];
        }
        if ( !in_array( $level,[1,2] ) ) {
            return ['code'=>400,'message'=>'level错误'];
        }
       
        // 账号检查，只允许 字母，数字，下划线。
        if (preg_match('/[^_0-9a-zA-Z]/', $account)      ) {
            return ['code'=>400,'message'=>'账号只能是数字字母下划线组成。'];
        }
        
        if (strlen( $account ) <6 || strlen( $account ) >20 ) {
            return ['code'=>400,'message'=>'账号长度必须6到20位'];
        }
        
        // 账号重复
        $db = Sys::get_container_db_eloquent();
        $sql="select count(*) from backstage_admin where account=?";
        $result = DbSelect::fetchOne($db, $sql,[ $account ]);
        if ($result) {
            return ['code'=>400,'message'=>'不可以重复已有账号'];
        }
        
//         $check_result = \BBExtend\common\Pwd::check_amdin($pwd);
     
//         if ( $check_result['code']==0 ) {
//             return ['code'=>400,'message'=>$check_result['message']];
//         }
        
        
        
        // realname 只能是汉字。
        if ( !\BBExtend\common\Str::is_all_chinese($realname)     ) {
            return ['code'=>400,'message'=>'真实姓名必须全部汉字'];
        }
        
        if ( !\BBExtend\common\Str::is_valid_phone($phone)  ) {
            return ['code'=>400,'message'=>'手机格式错误'];
        }
        
        if ( $level==1 && $parent>0 ) {
            return ['code'=>400,'message'=>'级别错误'];
        }
        
        if ($level==2 && $parent==0 ) {
            return ['code'=>400,'message'=>'级别错误'];
        }
        
        if ($parent) {
            $sql="select count(*) from backstage_admin where is_valid=1 and id=?";
            $result = DbSelect::fetchOne($db, $sql,[ $parent ]);
            if (!$result) {
                return ['code'=>400,'message'=>'新建渠道账号的代理商id设置错误'];
            }
            
        }
        
        $pwd = \BBExtend\common\Pwd::create_full_pass();
        $id =  $db::table('backstage_admin')->insertGetId([
             'account'=>$account,
                'pwd' =>md5( $pwd ),
                'realname' => $realname,
                'phone' =>$phone,
                'level' =>$level,
                'is_valid' =>1,
                'create_time' =>time(),
                'parent' => $parent,
                'pwd_original' => $pwd,
        ]);
        
        return ['code'=>1,'data'=>['insert_id'=>$id,'pwd'=>$pwd,  ]];
    }
    
    /**
     * 查添加权限。
     * 
     * @param unknown $field_id
     * @param unknown $proxy_id
     * @return boolean
     */
    private function check_add_auth($field_id=null,$proxy_id=null){
        $role= $this->get_userinfo_role();
        if ($role=='admin') {
            return true;
        }
        
        if ($role=='proxy') {
            return true;
        }
        return false;
       // $admin = \BBExtend\model\BackstageAdmin::find( $id );
       // return $admin->check_edit_auth($field_id, $proxy_id);
    }
    
    /**
     * 查编辑权限。
     * @param unknown $id
     * @param unknown $field_id
     * @param unknown $proxy_id
     * @return boolean|unknown
     */
    private function check_edit_auth($id,$field_id=null,$proxy_id=null){
        $role= $this->get_userinfo_role();
        if ($role=='admin') {
            return true;
        }
        $admin = \BBExtend\model\BackstageAdmin::find( $id );
        return $admin->check_edit_auth($field_id, $proxy_id);  
    }
    
    
    public function edit ($id, $realname,$phone, $pwd='',$field_id=null,$proxy_id=null )
    {
        
        
        $admin = \BBExtend\model\BackstageAdmin::find( $id );
        if (!$admin) {
            return ['code'=>400,'message'=>'id错误'];
        }
        
        
        if ( !$this->check_edit_auth($id,$field_id,$proxy_id )) {
            return ['code'=>400,'message'=>'您无权操作'];
        }
        
        $admin->realname = $realname;
        $admin->phone = $phone;
        
        
        $result = $admin->edit($pwd);
        if ($result) {
        
           return ['code'=>1,];
        }else {
            return ['code'=>400, 'message' =>$admin->message ];
        }
    }
    
    
    /**
     * 单独设置某个账号有效无效的接口
     * 
     * @param number $id
     * @param unknown $is_valid
     * @param unknown $field_id
     * @param unknown $proxy_id
     * @return number[]|string[]|number[]
     */
    public function edit_valid($id=0, $is_valid,$field_id=null,$proxy_id=null) {
        
      
        
        $db = Sys::get_container_db_eloquent();
        $is_valid = $is_valid ? 1 : 0 ;
        $sql="select count(*) from backstage_admin where id = ? ";
        $result = DbSelect::fetchOne($db, $sql,[ $id ]);
        if (!$result) {
            return ['code'=>400,'message'=>'id错误'];
        }
        
        
        if ( !$this->check_edit_auth($id,$field_id,$proxy_id )) {
            return ['code'=>400,'message'=>'您无权操作'];
        }
        
        
        $db::table('backstage_admin')->where('id' , $id )->update(['is_valid'=>$is_valid]);
        return ['code'=>1, ];
    }
    
    
    
    
    
}






