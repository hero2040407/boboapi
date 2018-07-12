<?php
/**
 * 加水印函数
 * 
 * 使用示例
$file = "source.jpg";
Public_ShuiYin::add($file);

echo "<img src='source.jpg'>";

 * 
 * 
 * @author 谢烨
 */
class Public_ShuiYin
{
    public static function add($fullname)
    {
        if (PHP_OS == 'Linux') {
            try{
                //谢烨，我做备份
                $backup_pre = DATA_PATH . "/file/upbackup";
                $temp = realpath($fullname);
                $backup_full = $backup_pre . $temp;
                $target_path = preg_replace('#^(.+)/[^/]+$#', '$1', $backup_full);
                Public_Folder::create_dir($target_path);
                copy($temp, $backup_full);
                
                $a2 = '/data/www/yxing/code/web/js/sy2.png';
                $image = new Imagick($fullname);
                $src_width = $image->getImageWidth();
                $src_height = $image->getImageheight();
                 
                $im_name = md5(uniqid()).".png";
                $im_dst = '/data/www/yxing/data/temp/'. $im_name ;
                $command = "/usr/bin/convert -resize {$src_width} {$a2} {$im_dst}";
                system($command);
                 
                $im =  new  Imagick($im_dst);
                $im_height = $im->getimageheight();
                $cha = ($src_height - $im_height )/2;
                $cha= floor($cha);
                $image->compositeImage($im, Imagick::COMPOSITE_OVER, 0,$cha);
                $image->writeimage($fullname); //最后写入自己。
            }
            catch (Exception $e) {}
        }
        
    }
    
    
  
}//end class

