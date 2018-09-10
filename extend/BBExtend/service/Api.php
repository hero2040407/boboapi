<?php
namespace BBExtend\service;

/**
 * 
 * @author Administrator
 *
 */
class Api
{
    public static function get_all_method(){
    // 检索出所有的文件，和他的全部路径
        $temp=[];
        \BBExtend\common\Folder::get_file_by_folder('/var/www/html/application', $temp, '#\\.php$#' );
        //        dump($temp);
        
        $result = self::check1($temp);
       // dump($result);exit;
        // 类似 "app\systemmanage\controller\Vip"
        sort($result);
        
        $new2=[];
        foreach ( $result as $class_name ) {
            //  echo "<h5>{$class_name}</h5>";
            // $v 是 类名
            try{
                $temp =     self::check2($class_name);
                if ($temp){
                    foreach ($temp as $v) {
                        $new2[]= $v;
                    }
               }
                //dump( $temp );
                
            }catch( \Exception $e ) {
                
            }
            
        }
        sort($new2);
        return $new2;
    }
    
    
    
    private static function check1($arr)
    {
        // 过滤，确保只有正确的文件
        $new=[];
        foreach ($arr as $v) {
            if (preg_match( '#controller#',$v ) &&  !preg_match( '#apptest#i',$v )
                    &&  !preg_match( '#command#i',$v )
                    &&  !preg_match( '#live_device#i',$v )
                    &&  !preg_match( '#backstage#i',$v )
                    
                    ) {
                        $new[]= $v;
                    }
                    
                    
        }
        
        foreach ($new as &$v) {
            $v = self::my_getmethods($v);
        }
        
        return $new;
        
    }
    
    
    
    private static function my_getmethods( $file )
    {
        $name = preg_replace('#^/var/www/html/(.+)\.php$#', '$1', $file);
        $name = preg_replace('#/#', '\\', $name);
        $name = preg_replace('#application#', 'app', $name);
        
        
        return $name;
        
        
        // /var/www/html/application/config/controller/Jubaotest.php
        // 转换成 \app\config\
    }
    
    
    private static function check2($class_name){
        
        if ( preg_match('#(File|Worker22|Work23|Job23|init_demo|test3|Jobjuhe|think|test|Myjob|Redisent|Ios|Userfalse)#i', $class_name) ) {
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
            
            $temp = self::humpToLine( $arr[3]);
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
    
    
    private static function humpToLine($str){
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '_'.strtolower($matches[0]);
        },$str);
            
            if (preg_match( '#^_#',$str )) {
                $str = preg_replace('#^_(.+)$#', '$1', $str);
            }
            
            return $str;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
      
}