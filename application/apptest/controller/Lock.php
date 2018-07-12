<?php
namespace app\apptest\controller;

//use BBExtend\BBRedis;
use  think\Db;
use BBExtend\Sys;


class Lock
{
    public function index()
    {
        echo 1;exit;
        $num = 8000000;
        $num++;
        $count = 2000000;
        $db = Sys::get_container_db();
        for ($i=$num; $i< $num+$count; $i=$i+100) {
            $aa = range($i, $i+99);
            $sql="insert into bb_user_suiji (id) values ";
            foreach ($aa as $v) {
                $sql .="({$v}),";
            }
            $sql = trim($sql,',' );
            echo $sql."\n\n";
            $db->query($sql);
//             $db->insert('bb_user_suiji', ['id' => $i ]);
            //echo $i."\n";
        }
        
    }
    
   
    
}
