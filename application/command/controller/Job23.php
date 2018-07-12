<?php
namespace app\command\controller;
use BBExtend\Sys;



/**
 * 获取微信统一id并保存到数据库
 * @author xieye
 *
 */

class Job23  
{
    public function perform()
    {
        ini_set ( 'error_reporting', 6143 );
        ini_set('display_errors', 1);
        $db = Sys::get_container_db();
        $db->closeConnection();
        $db = Sys::get_container_db();
        $type = $this->args['type'];
        if ($type==10000) {
            $this->test();
            return ;
        }
        $this->worker($this->args, $type);
    }
    
    /**
     * 获取微信统一id并保存到数据库
     * @param unknown $args
     */
    public function worker($args,$type) 
    {
        $db = Sys::get_container_db();
        
    }
    
    
    public function test()
    {
        echo time() .":  test Job3 ok!\n";
        $db = Sys::get_container_db();
        $db->insert("bb_alitemp", [
            'url' => "test Job3 ok!",
            'create_time' => date("Y-m-d H:i:s"),
        ]);
    }
    
    
}