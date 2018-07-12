<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;

use BBExtend\Sys;

class Tool { 
   public function display_table()
   {
       
           $db = Sys::get_container_db();
           $sql = "show tables";
           $result = $db->fetchAll($sql);
           if (!$result)
               return false;
           $temp_arr = array();
           foreach ($result as $value) {
               $temp =   array_values ( $value);
               $temp_arr[] = trim(  $temp[0]);
   
           }
           sort($temp_arr);
           $arr = $temp_arr;
           
           $s ="<table>\n";
           foreach($arr as $v) {
               $sql ="select count(*) from {$v}";
               $count = $db->fetchOne($sql);
                
                
               $s .= "<tr><td>" .$v .'</td>  <td>'. $count . ' '. "</td></tr>\n";
           }
           $s .= "</table>";
           //return $s;
           echo $s;
           //return $temp_arr;
       
   }
   
   public function display_table_twins()
   {
       
       $db = Sys::get_container_db();
       $db2 = Sys::get_container_dbreadonly();
       
       $sql = "show tables";
       $result = $db->fetchAll($sql);
       if (!$result)
         //  return false;
           $temp_arr = array();
           foreach ($result as $value) {
               $temp =   array_values ( $value);
               $temp_arr[] = trim(  $temp[0]);
               
           }
           sort($temp_arr);
           $arr = $temp_arr;
           
           $s ="<table>\n";
           foreach($arr as $v) {
               $sql ="select count(*) from {$v}";
               $count = $db->fetchOne($sql);
               $count2 = $db2->fetchOne($sql);
               
               
               $s .= "<tr><td>" .$v .'</td>  <td>'. $count . ' : '.$count2. "</td></tr>\n";
           }
           $s .= "</table>";
           //return $s;
           echo $s;
           //return $temp_arr;
           
   }
   
   
   private function get_css()
   {
       $str=<<<css
 <style>
body{
  margin:40px;
}

h2 {
   margin-top:60px;
}
table {
    width: 100%;
    margin-bottom: 20px;
    border-width: 1px 1px 1px medium;
    border-style: solid solid solid none;
    border-color: #AAA #AAA #AAA  -moz-use-text-color;
    -moz-border-top-colors: none;
    -moz-border-right-colors: none;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    border-image: none;
    border-collapse: collapse;
}
table {
    max-width: 80%;
    background-color: transparent;
    border-spacing: 0px;
}
table td ,table th{
    padding: 8px;
    border-left: 1px solid  #AAA;
    border-top: 1px solid  #AAA ;
    line-height: 20px;
    vertical-align: top;
   font-size: 16px;
   color: #2F2F2F;
              
}
            
 pre {
    display: block;
    padding: 9.5px;
    margin: 0px 0px 10px;
    font-size: 15px;
    font-family:'Times New Roman', Arial, 'Microsoft YaHei',SimHei; 
    line-height: 20px;
    word-break: break-all;
    word-wrap: break-word;
    white-space: pre-wrap;
    background-color: #F5F5F5;
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 4px;
}
a.a_return_index{
  display:block;
  float:right;
  clear:both;
}
a.a_return_index2{
   font-weight:bolder;
   font-size:150%;
   text-decoration:underline;
}
     
</style>
        
        <style >  
css;
       
       echo $str;
   }
   
   public function api()
   {
      // Sys::display_all_error();
       $name = Sys::get_machine_name();
       
       
       
       echo "
<link href='/public/js/yaml4/demos/css/flexible-grids.css' rel='stylesheet' type='text/css'/>
<script src='/public/js/jquery-1.9.1.min.js'></script>

<a class='a_return_index' href='/shop/doc/index'>返回怪兽BOBO接口文档首页</a>
<a class='a_return_index2'  href='/shop/doc/index2/name/服务器api修改日志'>查看 服务器 api 修改日志</a>
        <br>

<center><h2>怪兽bobo API 检索</h2>
<form method=get action='/systemmanage/tool/query'>
<input type=text style='width:480px;height:32px;padding-left:8px' placeholder='请输入完整的接口名称'  id=aa1 name='url' value='' />
<input type=submit class=' ym-button ym-primary '  style=' height:30px;padding-left:8px'  id=aa2 value='　查找一下' />
</form>

<script>

  $('#aa1').val('').focus();
</script>

</center>";
       if ($name=='200'){
           
       }else {
           exit();
       }
       
       // 检索出所有的文件，和他的全部路径
       $temp=[];
       self::get_file_by_folder('/var/www/html/application', $temp, '#\\.php$#' );
//        dump($temp);
       
       $result = $this->check1($temp);
       //dump($result);
       sort($result);
       
       $new2=[];
       foreach ( $result as $class_name ) {
         //  echo "<h5>{$class_name}</h5>";
           // $v 是 类名
           try{
             $temp =     $this->check2($class_name);
             
             foreach ($temp as $v) {
                 $new2[]= $v;
             }
             
             //dump( $temp );
             
           }catch( \Exception $e ) {
               
           }
//            $class = new \ReflectionClass('app\systemmanage\controller\Dict');
//            $methods = $class->getMethods(\ReflectionProperty::IS_PUBLIC);
//            dump($methods);
           
       }
       sort($new2);
       echo "<ol>";
       foreach ( $new2 as $k=>$v ) {
           echo "<li>" . $v . "  (<a target=_blank href='/systemmanage/tool/query?url=". urlencode($v) ."'>查询</a>)"  . "</li>" ;
           
       }
       echo "</ol>";
       $this->get_css();
       echo "
<script src='/public/js/yaml4/yaml/core/js/yaml-focusfix.js'></script>
";
       //dump($new2);
       
   }
   
   public function query($url){
       $temp=[];
       self::get_file_by_folder('/var/www/html/application/shop/view', $temp, '#\\.md$#' );
      //  dump($temp);
       
       foreach ($temp as $filename) {
           
           if (preg_match('/api/', $filename)) {
               continue;
           }
           
           $content = file_get_contents($filename);
           
           
           //echo $
           if ( preg_match('#' . trim($url) . '\s+#si' , $content) ) {
           
              // $str = file_get_contents($filename);
               
               $new_file_url = "/shop/doc/";
               if ( preg_match('#doc2#', $filename) ){
                   $new_file_url .= "index2/name/";
               }else {
                   $new_file_url .= "index/name/";
               }
               $temp2 = preg_replace('#^.+/([^/]+).md$#', '$1', $filename);
               $new_file_url .= $temp2;
               header("location: {$new_file_url}");
               
           }
       }
       echo "没找到 {$url} 的文档";
       //header("location: {$new_file_url}");
//        echo $new_file_url;
       
   }
   
   
   
   private function check2($class_name){
       
       if ( preg_match('#(File|Worker22|Work23|Job23|init_demo|test3)#', $class_name) ) {
           return [];
       }
       if ( preg_match('#app\\\\config#', $class_name) ) {
           return [];
       }
       
       $class = new \ReflectionClass($class_name);
       $methods = $class->getMethods(\ReflectionProperty::IS_PUBLIC);
       
       $arr = explode('\\', $class_name);
       
       $new=[];
       foreach ($methods as $method) {
           
           $temp = $this->humpToLine( $arr[3]);
           $temp =  '/'. $arr[1].'/'.$temp."/".    $method->name."";
           
           if ( !preg_match('/_construct/', $temp) ){
//                echo $temp;
               $new[]= strtolower($temp);
           }
           
           
       }
       return $new;
//        sort( $new );
//        foreach ( $new as $v ) {
//            echo $v;
//        }
       
      // dump($methods);
   }
   
   
   private function check1($arr)
   {
       // 过滤，确保只有正确的文件
       $new=[];
       foreach ($arr as $v) {
           if (preg_match( '#controller#',$v ) &&  !preg_match( '#apptest#i',$v ) ) {
               $new[]= $v;
           }
           
           
       }
       
       foreach ($new as &$v) {
           $v = $this->my_getmethods($v);
       }
       
       return $new;
       
   }
   
   private function my_getmethods( $file )
   {
       $name = preg_replace('#^/var/www/html/(.+)\.php$#', '$1', $file);
       $name = preg_replace('#/#', '\\', $name);
       $name = preg_replace('#application#', 'app', $name);
       
       
       return $name;
       
       
      // /var/www/html/application/config/controller/Jubaotest.php
      // 转换成 \app\config\
   }
   
   /*
    * 驼峰转下划线
    */
   private function humpToLine($str){
       $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
           return '_'.strtolower($matches[0]);
       },$str);
       
           if (preg_match( '#^_#',$str )) {
               $str = preg_replace('#^_(.+)$#', '$1', $str);
           }
       
       return $str;
   }
   
  
       /**
        * 获得linux文件夹下的所有文件,通过参数返回结果
        *
        * @param string $dir1 必须是绝对路径，且最后没有/，例如/home/dir2
        * @param array  $arr  一个空的数组传进去
        * @param string $regular 一个正则表达式，对应文件名，例如'#\\.html$#'
        * @param string $content_regular 一个正则表达式，对应文件内容，例如'#内容标题#'，
        *     如果使用这个参数，文件编码要统一
        *
        * @return 从参数arr中取结果
        */
       public static function get_file_by_folder($dir1, &$arr,$regular='',$content_regular='')
       {
           //static $db = null;
           if (is_dir($dir1)) {
               $handle = dir($dir1);
               if ($dh = opendir($dir1)) {
                   while ($entry = $handle->read()) {
                       if (($entry != ".") && ($entry != "..")  && ($entry != ".svn")){
                           //文件全名
                           $new = $dir1."/".$entry;
                           if(is_dir($new)) {
                               //比较
                               self::get_file_by_folder($new,$arr,$regular,$content_regular) ;
                           } else { //如果1是文件，
                               if ($regular && (!$content_regular)){
                                   if (preg_match($regular,$entry)) {
                                       $arr[] = $new;
                                   }
                               }elseif($content_regular && (!$regular)){
                                   $content = file_get_contents($new);
                                   if (preg_match($content_regular,$content)) {
                                       $arr[] = $new;
                                   }
                               }elseif ($content_regular && $regular){
                                   $content = file_get_contents($new);
                                   if (preg_match($regular,$entry) &&
                                           preg_match($content_regular,$content)  ) {
                                               $arr[] = $new;
                                           }
                               }else{
                                   $arr[] = $new;
                               }
                           }
                       }
                   }
                   closedir($dh);
               }
           }
           
           
       }
       
       
   
   
   
   
    
}