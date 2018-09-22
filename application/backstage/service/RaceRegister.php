<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10 0010
 * Time: 下午 12:01
 */
namespace app\backstage\service;

use BBExtend\Sys;

class RaceRegister
{
    public function add($phone)
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
        ]);

        if (!$row) { // 不存在平台表，说明需要注册
            $uid =  $this->register( $platform_id, '小朋友', '', $login_type );
        }
        else $uid = $row['uid'];
        return $uid;
    }

    private function register($platform_id, $nickname, $device, $login_type){

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
        return $uid;
    }
}