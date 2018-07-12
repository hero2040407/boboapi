<?php
namespace BBExtend\user;

use BBExtend\common\Client;

/**
 * 查询 手机是否绑定的帮助类
 */
class BindPhone
{

    public $message = '';
    public $result;
    public $result_arr;

    public $uid = 0;

    public function __construct ( $uid )
    {
        $this->uid = intval( $uid );
    }

    // 查是否绑定
    // Bobo-Version: web/3.5.1   可能值 android  ios  weixin web
    public function check ( )
    {
        if (     Client::is_web() ||
                ( Client::is_android( ) && Client::big_than_version( '4.0.0' ) ) ||
                 ( Client::is_ios( ) && Client::big_than_version( '3.5.0' ) )
                
                
                ) {
            
            $user = \BBExtend\model\User::find( $this->uid );
            if (! $user) {
                $this->message = '用户不存在';
                $this->result = false;
                return false;
            }
            
            $success = $user->is_bind_phone( );
            if (! $success) {
                $this->message = '请绑定手机';
                $this->result = false;
                return false;
            }
            $this->result=true;
            return true;
        }
        $this->result = true;
        return true;
    }
    

    public function get_message ( )
    {
        return $this->message;
    }
    
    // 返回标准格式
    public function get_result_arr(){
        if ($this->result) {
            return ['code'=>1]; // 这句话实际不会用到
        }
        return ['code' =>\BBExtend\fix\Err::code_not_bind , 'message' =>$this->message ];
    }
    

}