<?php
namespace app\backstage\controller;

use BBExtend\backmodel\AdminActionLog;
use think\Controller;
use think\Request;

use BBExtend\Sys;
use BBExtend\DbSelect;


class Common extends Controller
{
    protected $param;
    protected $userInfo;
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
            switch($row['level'])
            {
                case '0':
                    $auth = 'admin';
                    break;
                case '1':
                    $auth = 'proxy';
                    break;
                case '2':
                    $auth = 'channel';
                    break;
                default :
                    break;
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
        header('Access-Control-Allow-Origin: *' );
        header('Access-Control-Allow-Credentials: true' );
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authKey, sessionId" );

        $request = Request::instance();
        $controller = $request->controller();
        $this->param = $request->param();

        $action = $request->action( );
        $route = $controller . '/' . $action;

        if ($action != 'login' && $action != 'logout') { // login是个例外

            $my_id = \BBExtend\Session::get_my_id();
//            $my_id = -1;
            if ($my_id !== false) { // 已登录
//                 Sys::debugxieye(213);

                //第一步，路由校验。
                if ( $my_id== -1 ){
                    $role ='admin';
                } else {
                    $role = $this->get_userinfo_role();
                }


        //        Sys::debugxieye(33341);

                $result = \app\backstage\model\Auth::check_route($role, strtolower( $controller), strtolower(  $action ) );
                if ($result ===false ) {
//                     Sys::debugxieye(214);
            //        Sys::debugxieye(33342);
                    header('Content-Type:application/json; charset=utf-8');
                    $arr = array('code'=>403,'message'=>'账号权限错误');
                    exit(json_encode($arr));
                }
           //     Sys::debugxieye(33343);

                // 第2步，请求参数注入。
                $result = \app\backstage\model\Auth::check_param($role, $my_id, strtolower( $controller), strtolower(  $action ) );
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
            $this->userInfo = $this->get_userinfo();
        }
    }

    /**
     * Notes:
     * Date: 2018/9/5 0005
     * Time: 上午 9:35
     * @param $admin_name /管理员名字
     * @param $action /管理员行为
     * @throws
     */
    protected function adminActionLog($action)
    {
        $admin_log = new AdminActionLog();
        $user_info = $this->userInfo;
        $admin_log->action_ip = getClientIp(1);
        $admin_log->remark = $user_info['realname'].$action;
        $admin_log->user_id = $user_info['id'];
        $admin_log->create_time = time();
        $admin_log->save();
    }
    
    public function _empty()
    {
        $this->error('此路由不存在');
    }

}

