<?php
namespace BBExtend\common;

/**
 * 抓取远程图片到本地，可以抓取不带有后缀的图片
 * @author YanYing <yanyinghq@163.com>
 * @link bidianer.com
 * 
 * 
 * demo 如下
 * 
    $object = new GrabImage();
    $img_url = "http://www.bidianer.com/img/icon_mugs.jpg"; // 需要抓取的远程图片
    $base_dir = "/var/www/html/runtime"; // 本地保存的路径
    $result=  $object->download($img_url , $base_dir);
    if ($result) {
      echo $result;
    }
    
 * 
 * 
 */
class GrabImage
{
    
    /**
     * @var string 需要抓取的远程图片的地址
     * 例如：http://www.bidianer.com/img/icon_mugs.jpg
     * 有一些远程文件路径可能不带拓展名
     * 形如：http://www.xxx.com/img/icon_mugs/q/0
     */
    private $img_url;
    
    /**
     * @var string 需要保存的文件名称
     * 抓取到本地的文件名会重新生成名称
     * 但是，不带拓展名
     * 例如：57feefd7e2a7aY5p7LsPqaI-lY1BF
     */
    private $file_name;
    
    /**
     * @var string 文件的拓展名
     * 这里直接使用远程图片拓展名
     * 对于没有拓展名的远程图片，会从文件流中获取
     * 例如：.jpg
     */
    private $extension;
    
    /**
     * @var string 文件保存在本地的目录
     * 这里的路径是PHP保存文件的路径
     * 一般相对于入口文件保存的路径
     * 比如：./uploads/image/201610/19/
     * 但是该路径一般不直接存储到数据库
     */
    private $file_dir;
    
    /**
     * @var string 数据库保存的文件目录
     * 这个路径是直接保存到数据库的图片路径
     * 一般直接保存日期 + 文件名，需要使用的时候拼上前面路径
     * 这样做的目的是为了迁移系统时候方便更换路径
     * 例如：201610/19/
     */
    private $save_dir;
    
    /**
     * @param string $img_url 需要抓取的图片地址
     * @param string $base_dir 本地保存的路径，比如：./uploads/image，最后不带斜杠"/"
     * @return bool|int
     */
    public function download($img_url , $base_dir)
    {
        $this->img_url = $img_url;
        $this->save_dir = ''; // 比如：201610/19/
        $this->file_dir = $base_dir.'/'; // 比如：./uploads/image/2016/10/19/
        return $this->start();
    }
    
    /**
     * 开始抓取图片
     */
    private function start()
    {
        if($this->setDir())
        {
            return $this->getRemoteImg();
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 检查图片需要保持的目录是否存在
     * 如果不存在，则立即创建一个目录
     * @return bool
     */
    private function setDir()
    {
        if(!file_exists($this->file_dir))
        {
            mkdir($this->file_dir,0777,TRUE);
        }
        
        $this->file_name = uniqid().rand(10000,99999);// 文件名，这里只是演示，实际项目中请使用自己的唯一文件名生成方法
        
        return true;
    }
    
    /**
     * 抓取远程图片核心方法，可以同时抓取有后缀名的图片和没有后缀名的图片
     *
     * @return bool|int
     */
    private function getRemoteImg()
    {
        $old_file_path = $this->file_dir.$this->file_name;
        // 获取数据并保存
        
        // 下载成功
        $contents=file_get_contents($this->img_url);
        
        // 写入本地硬盘成功
        if(file_put_contents($old_file_path , $contents))
        {
            // 这里返回出去的值是直接保存到数据库的路径 + 文件名，形如：201610/19/57feefd7e2a7aY5p7LsPqaI-lY1BF.jpg
            $result = getimagesize($old_file_path);
            
            // 分析成功
            if ($result) {
                $type = $result[2];
                if ( $type == 1 ){
                    $extension = 'gif';
                }elseif ( $type == 2 ){
                    $extension = 'jpg';
                }elseif ( $type == 3 ){
                    $extension = 'png';
                }elseif ( $type == 6 ){
                    $extension = 'bmp';
                }else {
                    //不是图片失败
                    
                    unlink( $old_file_path );
                    return false;
                }
                $new_file_path = $old_file_path . "." . $extension;
                rename( $old_file_path ,$new_file_path  );
                return $new_file_path;
            }
            
            return false;
            
        }
        return false;
       // }
    }
}