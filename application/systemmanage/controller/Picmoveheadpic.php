<?php

/**
 *
 * 把图片从本机转移到oss。
 * 20180104
 *
 * @author 谢烨
 *
 */

namespace app\systemmanage\controller;

use OSS\OssClient;
use OSS\Core\OssException;
use BBExtend\common\Oss;

use BBExtend\Sys;
use BBExtend\common\Folder;

class Picmoveheadpic
{

    /**
     * 获得linux文件夹下的所有文件,通过参数返回结果
     *
     * @param string $dir1
     *            必须是绝对路径，且最后没有/，例如/home/dir2
     * @param array $arr
     *            一个空的数组传进去
     * @param string $regular
     *            一个正则表达式，对应文件名，例如'#\\.html$#'
     * @param string $content_regular
     *            一个正则表达式，对应文件内容，例如'#内容标题#'， 如果使用这个参数，文件编码要统一
     * @return 从参数arr中取结果
     */
    public static function get_file_by_folder ( $dir1, &$arr, $regular = '', $content_regular = '' )
    {
        // static $db = null;
        if (is_dir( $dir1 )) {
            $handle = dir( $dir1 );
            if ($dh = opendir( $dir1 )) {
                while ($entry = $handle->read( )) {
                    if (( $entry != "." ) && ( $entry != ".." ) && ( $entry != ".svn" )) {
                        // 文件全名
                        $new = $dir1 . "/" . $entry;
                        if (is_dir( $new )) {
                            // 比较
                            self::get_file_by_folder( $new, $arr, $regular, $content_regular );
                        } else { // 如果1是文件，
                            if ($regular && ( ! $content_regular )) {
                                if (preg_match( $regular, $entry )) {
                                    $arr[] = $new;
                                }
                            } elseif ($content_regular && ( ! $regular )) {
                                $content = file_get_contents( $new );
                                if (preg_match( $content_regular, $content )) {
                                    $arr[] = $new;
                                }
                            } elseif ($content_regular && $regular) {
                                $content = file_get_contents( $new );
                                if (preg_match( $regular, $entry ) &&
                                         preg_match( $content_regular, $content )) {
                                    $arr[] = $new;
                                }
                            } else {
                                $arr[] = $new;
                            }
                        }
                    }
                }
                closedir( $dh );
            }
        }
    
    }

    public function process ( )
    {
        
        exit();
        
        
        $dir = '/mnt/uploads/headpic';
        $arr = [];
        self::get_file_by_folder( $dir, $arr );
        // dump($arr);
        // [0] => string(46) "/mnt/uploads/headpic/7518367/5976f922c663a.png"
        // [1] => string(46) "/mnt/uploads/headpic/7819749/5964725f0d313.jpg"
        
//         $new = [];
//         foreach ($arr as $v) {
//             if (preg_match('#^/mnt/uploads/headpic/(\d+)/.+$#', $v)) {
//               $key = preg_replace( '#^/mnt/uploads/headpic/(\d+)/.+$#', '$1', $v );
//               $new[$key] = $v;
//             }
//         }
//         unset( $arr );
        
//         ksort( $new );
      //  dump($new);
        
       // return ;
        
        $i=0;
        foreach ($arr as $k => $v) {
            $i++;
            $full_path = $v;
            
            $oss_file_path = preg_replace( '#^.+?(uploads.+)$#', '$1', $full_path );
            
            echo   "{$i} : {$full_path}\n";
            $this->upload( $full_path, $oss_file_path );
            //echo intval() 
        }
        
        // dump($new);
    }

    /**
     * $full_path /mnt/uploads/1.jpg $oss_file_path 类似 uploads/1.jpg
     */
    // 1,上传文件到oss
    private function upload ( $full_path, $oss_file_path )
    {
        $bucket = Oss::getBucketName( );
        $ossClient = Oss::getOssClient( );
        if (is_null( $ossClient )) {
            return false;
        }
        // 上传本地文件
        $result = $ossClient->uploadFile( $bucket, $oss_file_path, $full_path );
        if ($result && isset( $result['x-oss-request-id'] )) {
            echo "upload {$oss_file_path} success !\n";
            return true;
        } else {
            return false;
        }
    
    }

}
      
    
   
