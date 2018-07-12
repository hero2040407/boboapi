<?php
/**
 * 通用文件夹函数
 * 
 * 
 * @author 谢烨
 */

namespace BBExtend\common;

class Folder
{
    
    public static function dir($path)
    {
        if (is_dir($path)) {
            $temp = scandir($path);
            $new=[];
            foreach ($temp as $v) {
                if (!in_array($v, ['.', '..',])) {
                    $new[] = $v;
                }
            }
            
            return $new;
        }else {
            return [];
        }
    }
    
    public static  function create_dir($dir){
    
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
           
        }
    }
    
    public static function realpath($file){
        if (PHP_OS=="Linux") {
            return realpath($file);
        }else {
            return preg_replace('#\\\\#', '/', realpath($file));
        }
        
        
    }
    
  
}//end class

