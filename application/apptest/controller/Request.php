<?php

namespace app\apptest\controller;

use think\Db;


/**
 * 
 * @author Administrator
 *        
 */
class Request {
    // $temp='';
    public function index($a,$b=33) {
        echo "a={$a}\n<br>";
        echo "b={$b}\n<br>";
        
        //explod
        // 谢烨测试。
        // 你好
        
    }
    
    public function mysql()
    {
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $sql ="select * from bb_users limit 1";
        $result = \BBExtend\DbSelect::fetchAll($db, $sql);
        dump($result);
        
    }
    
}
