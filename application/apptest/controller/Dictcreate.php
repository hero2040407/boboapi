<?php
namespace app\apptest\controller;

// use BBExtend\BBRedis;
use  think\Db;
exit();
class Dictcreate
{
    /**
     * 创建 字典
     */
    public function index()
    {

        $arr = \BBExtend\common\MysqlTool::show_tables();
       // exit();
        foreach ($arr as $v) {
           //echo $v;
            $s = $this->get_table_DatabaseObject_info($v);
           // file_put_contents( $v. ".txt", $s);
            echo ($v);
            $filename = APP_PATH . "systemmanage/view/doc/{$v}.md";
            
         //   file_put_contents($filename, $s);
        }
        
       
        
    }
    
  
    
    function get_table_comment($name) {
        $db = \BBExtend\Sys::get_container_db();
        $sql ="select table_comment from information_schema.TABLES
        where table_schema='yxing' and table_name='{$name}'";
        return $db->fetchOne($sql);
    }
    
    function get_table_DatabaseObject_info($name) {
        $arr = $this->get_table($name);
        $s = "### {$name}表\n";
        $s .= "  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|\n";
        foreach ($arr as $v) {
            $temp='';
            if ($v['column_key']=='PRI') {
                $temp .='主键, ';
            }
            if ($v['extra']=='auto_increment') {
                $temp .='自增, ';
            }
            $feikong='';
            if ($v['is_nullable']=='YES'){
                $feikong ='是';
            }
          
            $s .= "|{$v['column_name']}| {$v['column_type']}|{$feikong} | ".
             " {$v['column_default']} |{$temp}| {$v['column_comment']} |\n";
        }
// | goods_title      | string |主键|商品标题  |
// | model            | string ||规格  |
   
// 注意：*注意*
//                 ";
        return $s;
    }
    
    function get_table($name)
    {
        $db = \BBExtend\Sys::get_container_db();
        $sql="
        select extra,table_name,column_name,is_nullable,column_default,
        column_type,column_key,column_comment 
        from information_schema.COLUMNS
        where table_schema='bobo'
        and table_name='{$name}'
        order by table_name,ORDINAL_POSITION
        ";
        return $db->fetchAll($sql);
    }
    
   
}
