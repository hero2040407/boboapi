<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Controller;
use think\Request;


class Temp3 
{
    
    function index(){
        $db = Sys::get_container_db();
        $db2 = Sys::get_container_dbreadonly();
        $sql="select * from ds_register_log order by id asc";
        $query = $db2->query($sql);
        while($row= $query->fetch()) {
            if ( !$row['age'] ) {
                $sql="update ds_register_log set age=? where id=?";
                $db->query( $sql,[ date("Y"  )- substr( $row['birthday'] ,0,4),  $row['id'] ] );
                echo date("Y"  )- substr( $row['birthday'] ,0,4);
                echo "\n";
            }
//             echo $row['id'];
//             echo "\n";
            
        }
    }
    
}





