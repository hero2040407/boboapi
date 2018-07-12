<?php
namespace app\backstage\controller;

use think\Controller;
use think\Request;

use BBExtend\Sys;
use BBExtend\DbSelect;


class Common extends Controller
{

    /**
     * 便利方法，获得当前用户详情。
     * 
     * @return NULL|unknown|array|NULL
     */
    protected function get_userinfo(){
        $id = \BBExtend\Session::get_my_id();
        $db = Sys::get_container_db_eloquent();
        if ($id) {
            $sql="select * from backstage_admin where id=?";
            return DbSelect::fetchRow($db, $sql,[ $id ]);
        }
        return null;
        
    }
    
    
    // 获取角色对应的权限
    protected function get_auths( $role ){
        $db = Sys::get_container_db_eloquent();
        
        $sql=" select backstage_auth_list.* from backstage_auth_list
          left join backstage_auth
          on backstage_auth.auth_id = backstage_auth_list.id
          where roles=?";
        $result = DbSelect::fetchAll($db, $sql, [ $role ]);
        
        return $result;
        
    }
    
    // 获取用户角色。
    protected function get_userinfo_role(){
        
        $id = \BBExtend\Session::get_my_id();
        if ($id==-1) {
            return 'admin';
        }
        
        $row = $this->get_userinfo();
        if ($row) {
            if ($row['level']==1) {
                $auth = 'proxy';
            }else {
                $auth = 'channel';
            }
            return $auth;
        }
        
        return null;
        
    }
    
    // 分页组件
    protected function get_pageinfo( $paginator,$per_page ){
        return [
                'per_page' =>$per_page,
                'page'=>$paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total'  => $paginator->total(),
                'current_page' => $paginator->currentPage(),
        ];
    }
    
    // 创建一个帐号。
    protected  function get_full_account($level)
    {
        $account = $this->create_account($level);
        $pwd_original = $this->create_pass();
        $pwd = md5( $pwd_original );
        return [
            'pwd' =>$pwd,
                'pwd_original' =>$pwd_original,
                'account' =>$account,
        ];
    }
    
    private  function create_account($level=1)
    {
        if ($level==1) {
            $account = 'p';
        }else {
            $account = 'c';
        }
        $temp = $account . mt_rand(100000,999999);
        $db = Sys::get_container_db_eloquent();
        $sql="select count(*) from backstage_admin where account=?";
        $result = DbSelect::fetchOne($db, $sql,[ $temp ]);
        if ($result) {
            return $this->create_account($level);
        }
        return $temp;
    }
    
    private  function create_pass()
    {
        return \BBExtend\common\Pwd::create_full_pass() ;
    }
    
    
    public function _initialize ( )
    {
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Credentials: true' );
        header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );
        header( 
                "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authKey, sessionId" );
        
        $request = Request::instance( );
        $controller = $request->controller();
        
        $action = $request->action( );
        $route = $controller . '/' . $action;
//         Sys::debugxieye(211);
        if (preg_match('/bobo.yimwing.com/', $request->domain())) {
            
//             echo "This is the interface used in the front end, prohibiting access";
//             die(); 
        }
        
//         Request::instance()->get(['aaa'=>120]);
        
        
        if ($action != 'login' && $action != 'logout' ) { // login是个例外
            
//             $login = session( "backstage_islogin" );
            $my_id = \BBExtend\Session::get_my_id();
//             Sys::debugxieye("my_id:{$my_id}");
            
            if ($my_id !== false) { // 已登录
//                 Sys::debugxieye(213);
                
                //第一步，路由校验。
                if ( $my_id== -1 ){
                    $role ='admin';
                } else {
                    $role = $this->get_userinfo_role();
                }
                
                
        //        Sys::debugxieye(33341);
                
                $result = \app\backstage\model\Auth::check_route($role,  strtolower( $controller), strtolower(  $action ) );
                if ($result ===false ) {
//                     Sys::debugxieye(214);
            //        Sys::debugxieye(33342);
                    header('Content-Type:application/json; charset=utf-8');
                    $arr = array('code'=>403,'message'=>'账号权限错误');
                    exit(json_encode($arr));
                }
           //     Sys::debugxieye(33343);
                
                // 第2步，请求参数注入。
                $result = \app\backstage\model\Auth::check_param($role, $my_id,  strtolower( $controller), strtolower(  $action ) );
                if ($result ===false ) {
                    //                     Sys::debugxieye(214);
             //       Sys::debugxieye(33344);
                    header('Content-Type:application/json; charset=utf-8');
                    $arr = array('code'=>404,'message'=>'账号缺少对应大赛或赛区，故禁止使用');
                    exit(json_encode($arr));
                }
                
                if ($route=='round/calling') {
//                     Sys::debugxieye(33346);
//                     Sys::debugxieye(input('param.field_id' ));
//                     Sys::debugxieye(input('param.uid' ));
//                     Sys::debugxieye(input('get.uid' ));
//                     Sys::debugxieye(input('post.uid' ));
                    
                    
                    
                }
                
                
            } else {                // 未登录
                
                header('Content-Type:application/json; charset=utf-8');
                $arr = array('code'=>402,'message'=>'未登录错误');
                exit(json_encode($arr));
            }
        
        }
    
    }

}

