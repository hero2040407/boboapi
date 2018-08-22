<?php
namespace BBExtend\common;
/**
 * 图像处理类
 * 
 * xieye 2017 03 
 * 所有成员变量是 给upload方法用的。
 * 
 *
 * @author 谢烨
 */
 class Image
{
     
    public $msg='';
    public $fullname; // 硬盘根路径 /var/www/11.jpg
    public $filename; // 1.jpg
    public $webname;  // /1.jpg
    public $size;     // 文件大小，单位字节。
    
    public function __construct() {
        
    }
    
    
    /**
     * 返回一个阿里云图片 的宽度和高度。
     * @param unknown $url
     * @return unknown[]|mixed[]|number[]
     */
    public static function get_aliyun_pic_width_height($url){
        $url = $url . "?x-oss-process=image/info";
        $json = file_get_contents($url);
        $json = json_decode($json,true);
        if ($json && isset( $json['ImageHeight'] ) &&   isset ( $json['ImageWidth'] ) ) {
            return ['height' => $json['ImageHeight']['value'],'width' =>  $json['ImageWidth']['value'] ];
        }
        return ['width'=>0, 'height' =>0 ];
    }
    
    
    /**
     * 把一个图片生成一个灰色图片，且文件名自动加_gray，原文件不动。
     * 如果返回false，则表示处理失败，一般是文件不存在。
     * 正确的话，返回新的全文件名。
     */
    public static function gray($img)
    {
        //假如是外网文件，则不管
        if (preg_match('#^http#', $img)) {
            return false;
        }
        // 假如参数以/uploads开头，自动加/var/www/html/public前缀路径
        if (preg_match('#^/(uploads|public)#', $img)) {
            $img =  ROOT_PATH ."public" . $img;
        }
        $img = realpath($img);
        if (!$img) {
            return false; //如果文件不存在，返回false
        }
        
        //现在分离文件名，文件名前缀如123，文件名后缀如jpg
        $pre = preg_replace('#^(.+?)\.[^.]+$#', '$1', $img);
        $post = preg_replace('#^.+?\.([^.]+)$#', '$1', $img);
        $new_img = $pre ."_gray." . $post; // 该变量含路径，
        if (is_file($new_img)) { // 如果已经存在，则不处理
            return $new_img;
        }
        
        if (PHP_OS =="Linux") {
            $command ="convert {$img} -colorspace Gray {$new_img}";
            shell_exec ( $command );
        }else { // 如果是windows系统，直接拷贝，不处理。
            copy($img, $new_img);
        }
        return $new_img;
    }
    
    public static function geturl($img)
    {
        if (!$img) {
            $img = '/public/toppic/topdefault.png';
        }
        
        if (preg_match('#^http#', $img)) {
            return $img;
        }else {
            return \BBExtend\common\BBConfig::get_server_url() . $img;
        }
    }
    
    /**
     * 根据大图直接返回
     * @param unknown $img
     * @return string
     */
    public static function get_grayurl($img)
    {
        if (!$img) {
            $img = '/public/toppic/topdefault.png';
        }
    
        if (preg_match('#^http#', $img)) {
            
            $pre = preg_replace('#^(.+?)\.[^.]+$#', '$1', $img);
            $post = preg_replace('#^.+?\.([^.]+)$#', '$1', $img);
            $new_img = $pre ."_gray." . $post; // 该变量含路径，
            
            return $new_img;
        }else {
            $gray = self::gray($img);
            $gray = preg_replace('#^/var/www/html/public(.+)$#', '$1', $gray);
            // xieye 20171227 符号链接导致的bug
            if (preg_match('#/mnt#', $gray)) {
                $gray = preg_replace('#^/mnt(.+)$#', '$1', $gray);
            }
            
            return \BBExtend\common\BBConfig::get_server_url() . $gray;
        }
    }
    
    
    /**
     * 是否是png，gif，jpeg，svg中的一种。
     * 但只要是windows操作系统，认为一定是图片，忽略判断。
     * 
     * @param string $filename
     * @return boolean
     */
    public static function is_real_pic($filename)
    {
        if (PHP_OS != 'Linux') {
            return true;
        }
        $type = self::get_image_format($filename);
        if (in_array($type, array('png','gif', 'jpeg','svg'))) {
            return true;
        }
        return false;
    }
    
    /**
     * 返回图片类型。
     * 可能是 png , gif, jpeg , svg, 或空字符串（未知）
     * 
     * @param string $filename 待检测文件名
     * @return string
     */
    public static function get_image_format($filename)
    {
        try{
            $image = new Imagick($filename);
            $type = $image->getimageformat();
            return strtolower( $type);
        }catch (Exception $e) {
            return ''; //返回空表示未知格式
        }
        return '';
    }
    
    /**
     * 调用系统的imagemaigic类库执行图像剪切
     * @param unknown $file
     * @param unknown $target
     * @param unknown $width
     * @param unknown $height
     * @return boolean
     */
    public static function cut($file, $target,$width, $height)
    {
        $file = realpath($file);
        $target_path = preg_replace('#^(.+)/[^/]+$#', '$1', $target);
       // Public_Folder::create_dir($target_path);
        if (PHP_OS == 'Linux') {
            if (!self::is_real_pic($file)) {
                return false;
            }
            $image = new Imagick($file);
            $image->cropThumbnailImage($width, $height);
            $image->writeimage($target);
        }else {
            //windows下，直接把文件拷贝过去。
            copy($file, $target);
        }
    }
    
    
    /**
     * 这是使用第3方的类库上传，
     *
     * form_name file表单名称
     * fold 放在upload的哪个文件夹下面。
     * limit_size限制字节，默认2M
     */
    public  function upload_codeguy($form_name,$fold='pic',$limit_size=2000000) 
    {
        require_once('Upload/Storage/FileSystem.php');
        require_once('Upload/File.php');
        require_once('Upload/Validation/Mimetype.php');
        require_once('Upload/Validation/Size.php');
        $new_path = ROOT_PATH . 'public'. DS . 'uploads'. DS . $fold . DS. date("Ymd") ;
        $storage = new \Upload\Storage\FileSystem($new_path);
        $file = new \Upload\File($form_name, $storage);
        
        // Optionally you can rename the file on upload
        $new_filename = uniqid();
        $file->setName($new_filename);
        
        // Validate file upload
        // MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
        $file->addValidations(array(
            // Ensure file is of type "image/png"
            new \Upload\Validation\Mimetype(['image/png', 'image/gif',  ]),
            // Ensure file is no larger than 5M (use "B", "K", M", or "G")
            new \Upload\Validation\Size('2M')
        ));
        
        // Access data about the file that has been uploaded
        $data = array(
            'name'       => $file->getNameWithExtension(),
            'extension'  => $file->getExtension(),
            'mime'       => $file->getMimetype(),
            'size'       => $file->getSize(),
        );
        
        // Try to upload file
        try {
            // Success!
            $file->upload();
            $this->fullname = $new_path .DS. $new_filename;
            $this->filename = $new_filename;
            $this->webname = preg_replace('#'. $_SERVER['DOCUMENT_ROOT'] .'#', '', $this->fullname);
            $this->size = $data['size'];
            $this->msg='';
            return true;
        } catch (\Exception $e) {
//             var_dump($file->getErrors());
            // Fail!
            $temp = $file->getErrors();
            if (is_array($temp)) {
                $this->msg = $temp[0];
            }else {
              $this->msg = $file->getErrors();
            }
            return false;
        }
       
    }
    
    
    /**
     * 在thinkphp5里面用工具上传图片
     * 
     * form_name file表单名称
     * fold 放在upload的哪个文件夹下面。
     * limit_size限制字节，默认2M
     */
    public  function upload($form_name,$fold='pic',$limit_size=2000000) {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($form_name);
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>$limit_size,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public'
                . DS . 'uploads'. DS . $fold);
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
         //   echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
          //  echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
     //       echo $info->getFilename();
        //  var_dump($info);
       // echo $info->getRealPath();
            
         // return realpath( $info->getPath()) . DS . $info->getFilename();
          $this->fullname = $info->getRealPath();
          $this->filename = $info->getFileName();
          $this->webname = preg_replace('#'. $_SERVER['DOCUMENT_ROOT'] .'#', '', $this->fullname);
          
          $this->msg='';
          return true; 
        }else{
            // 上传失败获取错误信息
            $this->msg = $file->getError();
            return false;
        }
    }
    
    
    
   
}//end class
