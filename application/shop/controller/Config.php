<?php
namespace app\shop\controller;

use  think\Db;
/**
 * 
 * 配置表
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/8/3
 * Time: 11:42
 */
class Config
{
    /**
     * ios更新用
     * 
     * 
     */
    public function get_iosupdate()
    {
        $key = "ios_update";
        return ["code"=>1 , "data"=>["value"=> $this->get_value($key) ]];
    }
    
    /**
     * 设置ios更新用
     *
     *
     */
    public function set_iosupdate($password='', $value=0 )
    {
        $key ="ios_update";
        $value = $value ? 1:0;
        if ($password =='855745') {
            $this->set_value($key, $value);
            return ["code"=>1 , "data"=>["value" => $value ]];
        }
        return ["code"=>0 , "message"=>"验证身份错误"];
    }
    
    /**
     * 设置的特点，先查有，就更新，没有，就添加。
     * @param unknown $key
     * @param unknown $value
     */
    private function set_value($key, $value) 
    {
        $id = Db::table('bb_shop_config')->where('bb_key',$key)->value("id");
        if ($id) {
            //更新
            Db::table("bb_shop_config")->where("id",$id)->update(
                ["bb_value" => $value,"update_time"=>time() ]);
            
        }else {
            //添加
            Db::table("bb_shop_config")->insert([
                "bb_key"=>$key,
                "bb_value"=>$value,
                "create_time" => time(),
            ]);
        }
    }
   
    /**
     * 到表里查这个键，没有则返回空字符串。
     * @param unknown $key
     */
    private function get_value($key) 
    {
        $value = Db::table('bb_shop_config')->where('bb_key',$key)->value("bb_value");
        if ($value!==false) {
            return $value;
        }else {
            return "";
        }
    }
    
}