<?php

namespace app\api\controller;
use think\Controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Config;

/**
 * 加用户
 * 
 * @author xieye
 *
 */
class User extends Controller
{
    /**
     * 谢烨注：这是安全代码，千万保留。
     */
    public function _initialize()
    {
        $ip = Config::get( "http_head_ip" );
        Sys::debugxieye($ip);
        
        if ( !in_array( $ip, [ '127.0.0.1','0.0.0.0','192.168.99.1' ] )  ) {
            exit('error');
        }
    }
    
    
    public function add( $phone  )
    {
        if (!$phone) {
            return ['code'=>0,'message' =>'phone必传' ];
        }
        $login_type=3;
        $platform_id = $phone;
        if (!preg_match('#^1\d{10}$#', $phone)) {
            return ['code'=>0,'message' =>'手机格式错误' ];
        }
        
        $db = Sys::get_container_db();
        
      
        $sql = "select * from bb_users_platform where platform_id=? and type =? ";
        $row = $db->fetchRow( $sql, [
                md5( $platform_id ),
                $login_type
        ] );
        
       
        
        if (! $row) { // 不存在平台表，说明需要注册
            $uid =  $this->otherlogin_register( $platform_id, '小朋友', '', $login_type );
            return ['code' =>1,'data' =>[ 'uid' =>$uid, 'has_created'=>1  ]  ];
        }
        else {
            
            return ['code' =>1,'data' =>[ 'uid' =>$row['uid'], 'has_created'=>0  ]  ];
        }
        
    }
    

    private function otherlogin_register($platform_id, $nickname, $device, $login_type){
        
        $UserDB =  \BBExtend\BBUser::registered( $nickname, $device, $login_type,'', '',
                $platform_id, '' );
        $uid = $UserDB['uid'];
        
        
        $UserDB['currency'] = \BBExtend\Currency::get_currency( $uid );
        
        $obj = \app\user\model\UserModel::getinstance( $uid );
        // xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
        \BBExtend\Level::get_user_exp( $uid );
        
        // 谢烨，新功能。新用户注册，自动关注10000号用户，只在正式服。
        if (Sys::is_product_server( )) {
            $help = \BBExtend\user\Focus::getinstance( $uid );
            $help->focus_guy( 10000 );
        }
        // 系统消息 ， 20161110，
        //$nickname = ( $nickname == '未知的火星人' ) ? "小朋友" : $nickname;
//         Message::get_instance( )->set_title( '系统消息' )
//         ->add_content( Message::simple( )->content( $nickname )       )
//         ->add_content(
//                 Message::simple( )->content(
//                         '欢迎您加入怪兽BOBO,在这里每个孩子' . '都是大明星，请共同维护怪兽岛绿色直播宣言——' ) )
//                         ->add_content( Message::simple( )->content( 'BOBO童心梦，传递正能量' ) )
//                         ->add_content( Message::simple( )->content( "。" ) )
//                         ->set_type( MessageType::register )
//                         ->set_uid( $uid )
//                         ->send( );
//                         $UserDB['unread_count'] = 1;
//                         $UserDB = self::conversion_for_login( $UserDB ); // 强制转换，和登录一样。
//                         $bonus = BBUser::regis_additional( $uid ); // 注册有一个额外流程，必须走。
//                         $return_platform_id='';
//                         if ($login_type==1) {// 微信登录返回openid
//                             $return_platform_id=$platform_id;
//                         }
//                         $secure_help = new \BBExtend\Secure();
//                         $secure_help->set_new_http_header_temptoken($uid);
                        
         return $uid;
    }
    
}


