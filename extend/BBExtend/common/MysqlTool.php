<?php
namespace BBExtend\common;
use think\Db;
use BBExtend\Sys;

/**
 * 公
 *
 * @author 谢烨
 * 
 * xieye
 */
class MysqlTool
{
    
    /**
     * 根据 yaml内容清理数据库
     */
    public static function populate_by_yaml($yaml)
    {
        if (Sys::is_product_server()) {
            exit;
        }
        
        //         $s =<<<html
        // bb_act:
        //   -
        //     id: 1
        //     title: "现在He'llo buddy!"
        //   -
        //     id: 2
        //     title: "I like it!"
        // html;
        $data =   \Symfony\Component\Yaml\Yaml::parse($yaml);
        $db = Sys::get_container_db();
        foreach ($data as $table_name => $table_all_data) {
            // 无论如何，强制删除整表
            $sql="truncate table {$table_name}";
            $db->query($sql);
            //如果有每个表数据，每张表只插入一次，速度快！
            if ($table_all_data && is_array($table_all_data) && count($table_all_data)>0 ) {
    
                $key_arr=[];
                $val_arr=[];
                $all_wenhao_arr=[];
                // 先收集每个表所有的键
                foreach ($table_all_data as $v2) {
                    foreach ($v2 as $k3=> $v3) {
                        $key_arr[] = $k3;
                    }
                    break;
                }
                //定义每行的问号sql
                $temp_arr=[];
                $count = count($key_arr);
                foreach (range(1, $count) as $countv ) {
                    $temp_arr[] = "?";
                }
                $wenhao_str = "(". implode(',', $temp_arr). ")";
    
                foreach ($table_all_data as $v2) {
                    foreach ($v2 as $vvv) {
                        $val_arr[]= $vvv;// 收集所有的数据
                    }
                    $all_wenhao_arr[] =$wenhao_str; // 每行就是一个问号字符串
                }
    
                // 现在，key_arr已经收集成功。
                // 拼接sql
                $sql="insert into {$table_name} (". implode(',', $key_arr) .") values ".
                        implode(",", $all_wenhao_arr);
                        // var_dump($sql);
                        $db->query($sql,$val_arr);
            }
        }
    }
    
    
    
    
    
    /**
     * 清空一个表，并且置auto-increment=1;
     */
    public static function clear_table($table)
    {
       // if ($db==null)
       
        $db = Sys::get_container_db();
        $sql = "delete from {$table}";
        $db->query($sql);
        $sql = "alter table {$table} auto_increment = 1 ";
        $db->query($sql);
    }
    
    /**
     * 返回一个数组，是库里的所有表
     * 
     * @param string $database 库名
     * @param resource $db db对象
     */
    public static function show_tables( $pre='')
    {
       
//         if ($db==null)
//             $db = Sys::get_container_db();
        $sql = "show tables";
        $result = Db::query($sql);
        if (!$result)
          return false;
        $temp_arr = array();
        foreach ($result as $value) {
            $temp =   array_values ( $value);
            $temp_arr[] = trim(  $temp[0]);
    
        }
        sort($temp_arr);
        
        if ($pre) {
            $temp =[];
            foreach ($temp_arr as $v) {
                if (preg_match("#^{$pre}#", $v)) {
                    $temp[]= $v;
                }
            }
            return $temp;
        }
        
        return $temp_arr;    
                
            
    }
    
    /**
     * 查出一个表里的所有字段
     */
    public static function show_columns($table_name, $db=null)
    {
        if ($db==null)
            $db = Sys::get_container_db();
        $sql = "desc {$table_name}";
        $result = $db->fetchAll($sql);
        
        $new = array();
        foreach ($result as $value)
            $new[] = $value['Field'];
        
        return $new;    
    }
    
   
    public static function hastable($table_name) {
       $arr = self::show_table();
       if (in_array( $table_name, $arr )) {
           return true;
       }
       return false; 
    }
    
    public static function show_table_rows_html($db=null){
       $s ="
               <title>怪兽bobo表行数统计</title>
               <h1>怪兽bobo表行数统计</h1>
               
               <table>\n";
       $arr = self::show_tables();
       
          $db = Sys::get_container_db();
       foreach($arr as $v) {
         $sql ="select count(*) from {$v}";
         $count = $db->fetchOne($sql);
         if ($count > 1000000) {
             $count ="<font style='color:red;font-weight:bolder;'>{$count}</font>";
         }
       
         $s .= "<tr><td>" .$v .'</td>  <td>'. $count . ' '. "</td></tr>\n";
       }
       $s .= "</table>";
       return $s;
    }
    
    

}


