<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\common\MysqlTool;

class Tablerow 
{ 
    
    /**
     * 查看表行数
     */
    public function mysqlrowcount()
    {
        // echo "windows 主机暂不处理";return;
        Sys::display_all_error();
        $db = Sys::get_container_db();
        // $arr = \BBExtend\common\MysqlTool::show_table_rows_html();

       //  dump($arr);
        $arr = MysqlTool::show_tables();
        $dbe = Sys::get_container_db_eloquent();
        $db2 = Sys::get_container_dbreadonly();

        $sql="show databases";
        $arr1 = DbSelect::fetchAll($dbe, $sql);
        $arr2 = $db2->fetchAll($sql);
        dump($arr1);
        dump($arr2);
        
        
        
        foreach ($arr as $v) {
          $sql="select count(*) from {$v}";
          
          $v1 = DbSelect::fetchOne($dbe, $sql);
          
          $v2 =  $db2->fetchOne($sql);
          
          
          echo $v ."__{$v1}__{$v2}<br>";
          if ($v1 != $v2) {
              echo "<font color=red>err</font>";
              
          }
        }
        
//         $sql="select * from bb_users where uid=? ";
//         $row = $db2->fetchRow($sql,[10000]);
//         dump($row);
        
        
        
        $sql="show databases";
        $arr1 = DbSelect::fetchAll($dbe, $sql);
        $arr2 = $db2->fetchAll($sql);
        dump($arr1);
        dump($arr2);
        
        
        
    }
    
   
    
}