<?php
namespace BBExtend\common;
use OSS\OssClient;
use OSS\Core\OssException;
/**
 * 谢烨，阿里云sdk的客户端对象。
 *  
 * @author 谢烨
 */
class Oss
{
    const endpoint = 'http://oss-cn-beijing.aliyuncs.com';
    const accessKeyId = 'LTAIdnZssaoNUoGc';
    const accessKeySecret = 'QSvRUGKeEOgEPDCfcK7VnQmVuA6bYD';
    const bucket = 'bobo-upload';
    
    /**
     * 根据Config配置，得到一个OssClient实例
     *
     * @return OssClient 一个OssClient实例
     */
    public static function getOssClient()
    {
        try {
            $ossClient = new OssClient(self::accessKeyId, self::accessKeySecret, self::endpoint, false);
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }
    
    /**
     * 下图是默认头像。
     * http://resource.guaishoubobo.com/uploads/headpic/default.png
     * 
     * demo
     *  $help = new \BBExtend\common\Oss();
       $file = '/var/www/html/public/robots.txt';
       $remote = 'uploads/headpic_date/'.date("Ymd")."/".basename($file);
       $result= $help->upload_local_file($file, $remote);
       dump($result);
     * 
     * @param unknown $full_path
     * @param unknown $oss_file_path
     * @return boolean|string
     */
    public function upload_local_file($full_path, $oss_file_path)
    {
        
       // $oss_file_path = preg_replace('#^.+?(uploads.+)$#','$1', $full_path);
        
        $bucket = self::getBucketName( );
        $ossClient = self::getOssClient( );
        if (is_null( $ossClient )){
            return false;
        }
        // 上传本地文件
        $result = $ossClient->uploadFile( $bucket, $oss_file_path, $full_path );
        if ($result && isset( $result['x-oss-request-id'] )) {
            // echo "upload {$oss_file_path} success !\n";
            unlink($full_path);
            return 'http://resource.guaishoubobo.com/'.$oss_file_path;
        } else {
            return false;
        }
        
    }
    
    /**
     * 抓取远程的文件，判断是图片，然后上传到阿里云。
     * 
     * demo
     * 
     *  $help = new \BBExtend\common\Oss();
        $img_url="http://bobo-upload.oss-cn-beijing.aliyuncs.com/public/temp/5abb126903008";
        $oss_file_path_no_filename = 'public/';
        $result= $help->upload_remote_pic($img_url, $oss_file_path_no_filename)  ;
        dump($result);
     * 
     * @param unknown $full_path
     * @param unknown $oss_file_path
     * @return boolean|string
     */
    public function upload_remote_pic($remote_path, $oss_file_path_no_filename)
    {
        $bucket = self::getBucketName( );
        $ossClient = self::getOssClient( );
        if (is_null( $ossClient )){
            return false;
        }
        // 先下载到本地
        $full_path = $this->download_pic($remote_path);
        if (!$full_path) {
            return false;
        }
        
        // 因为这是我下载的，所以文件名可靠。只需把新文件名附加到oss路径上即可。
        $filename = preg_replace('#^.+/([^/]+)$#', '$1', $full_path);
        $oss_file_path = $oss_file_path_no_filename . $filename;
        
        // 上传本地文件
        $result = $ossClient->uploadFile( $bucket, $oss_file_path, $full_path );
        if ($result && isset( $result['x-oss-request-id'] )) {
            // echo "upload {$oss_file_path} success !\n";
            unlink($full_path);
            return 'http://resource.guaishoubobo.com/'.$oss_file_path;
        } else {
            return false;
        }
        
    }
    
    
    public function download_pic($img_url)
    {
        $help = new \BBExtend\common\GrabImage();
        $base_dir='/var/www/html/runtime/temp';
        $result =  $help->download($img_url, $base_dir);
        return $result;
    }
    
    
    public static function getBucketName()
    {
        return self::bucket;
    }
    
    /**
     * 工具方法，创建一个存储空间，如果发生异常直接exit
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {
            
            $message = $e->getMessage();
            if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
                echo "Please Check your AccessKeyId and AccessKeySecret" . "\n";
                exit(0);
            } elseif (strpos($message, "BucketAlreadyExists") !== false) {
                echo "Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. " . "\n";
                exit(0);
            }
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }
    
    public static function println($message)
    {
        if (!empty($message)) {
            echo strval($message) . "<br>\n";
        }
    }
  
}//end class

