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
    
    
    
    
    
    
    
    
    
    
  
}//end class

