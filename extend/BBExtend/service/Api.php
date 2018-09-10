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
    }
      
}