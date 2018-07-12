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

class Picmove
{

    public function test ( )
    {
        $bucket = Oss::getBucketName( );
        $ossClient = Oss::getOssClient( );
        if (is_null( $ossClient ))
            exit( 1 );
        // *******************************简单使用***************************************************************
        // echo "ok";
        // 1,上传文件到oss
        // 2， 复制文件到备份目录
        // 3. 修改原数据库记录
        // 4， 记录数据库日志表
        // 5， 删除原文件
        
        $bendi_file = '/var/www/html/public/public/monster/author/wengtianyu.png';
        // 上传本地文件
        $result = $ossClient->uploadFile( $bucket, "uploads/monster/author/wengtianyu.png", $bendi_file );
        if ($result && isset( $result['x-oss-request-id'] )) {
            echo "upload success";
        } else {
            echo "upload fail";
        }
        
        // Oss::println('id:'. $result['x-oss-request-id']);
        // Oss::println('etag:'.$result['etag']);
        // Oss::println('content:'.$result['content-md5']);
        // Oss::println('body:'.$result['body']);
    }
    
    
    /**
     * $full_path /mnt/uploads/1.jpg
     * $oss_file_path 类似 uploads/1.jpg
     * 
     */
    // 1,上传文件到oss
    private function upload($full_path, $oss_file_path )
    {
        $bucket = Oss::getBucketName( );
        $ossClient = Oss::getOssClient( );
        if (is_null( $ossClient )){
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
    
    
    // 2， 复制到备份目录
    private function copy( $full_path )
    {
        // 首先，我把原路径替换
        // /mnt/uploads/shop_goods/piclist/1/57c01e9126930.jpg
        // /mnt/backup/uploads/shop_goods/piclist/1/57c01e9126930.jpg
        $new_full_path = preg_replace('#/uploads#','/backup/uploads', $full_path);
        // 得到路径
        $new_dir = preg_replace('#^(.+)/[^/]+$#','$1', $new_full_path);
        Folder::create_dir($new_dir);
        copy($full_path, $new_full_path);
        return $new_full_path;
        //$oss_file_path = preg_replace('#^.+?(uploads.+)$#','$1', $full_path);
    }
    
    // 3. 修改原数据库记录
    private function update_database($full_path, $table_name,$column_name, $id)
    {
        $db = Sys::get_container_db_eloquent();
        
        $pre = 'http://resource.guaishoubobo.com';
        $row = $db::table( $table_name )->where( 'id', $id )->first() ;
        
        $old_value = $row->$column_name;
        
        $sql="update {$table_name} set {$column_name}= 
             concat ( '{$pre}',{$column_name} )  where id=".intval( $id );
        
        $db::update( $sql );
        return [$old_value, $pre. $old_value  ];
    }
    
    // 4， 记录数据库日志表
    private function log($old_table, $old_column, $old_value, $new_value, $new_backup, $id){
        $db = Sys::get_container_db_eloquent();
        $db::table('bb_oss')->insert([
            'type' =>1,
            'old_table' =>$old_table,
            'old_column' =>$old_column,
            'old_value' =>$old_value,
            'new_value' =>$new_value,
            'new_backup_file_path' =>$new_backup,
            'create_time' =>time(),
            'old_id' =>$id,
                
        ]);
    }
    
    // 5， 删除原文件
    private function delete($full_path)
    {
        unlink( $full_path );
    }
    
    // *******************************简单使用***************************************************************
    // 1,上传文件到oss
    // 2， 复制文件到备份目录
    // 3. 修改原数据库记录
    // 4， 记录数据库日志表
    // 5， 删除原文件
    private function process($column, $table_name,$column_name, $id)
    {
        $pic_root = "/mnt";
        if ($column && (!preg_match( '#^http#', $column ))
                && ( preg_match( '#^/uploads#', $column ) )
                ) {
                    
          //  echo $row['big_pic']."\n";
            $full_path = $pic_root.$column;
            $file_name='';
            $oss_file_path='';
            if (is_file($full_path)) {
                $file_name = basename( $full_path );
                $oss_file_path = preg_replace('#^.+?(uploads.+)$#','$1', $full_path);
                
            }
            
            if ($file_name) {
                $upload_result = $this->upload($full_path, $oss_file_path);
                if ($upload_result) {
                    $new_backup = $this->copy($full_path);
                    $u_arr= $this->update_database($full_path, $table_name,$column_name, $id);
                    $this->log($table_name, $column_name, $u_arr[0], $u_arr[1], $new_backup, $id);
                    $this->delete($full_path);
                    
                    return true;
//                     echo "is_file\n";
                } else {
                    return false;
                }
            } else {
                return false;
                //echo "not_is_file\n";
            }
            
        }
        return false;
    }
    
    
    
    public function bb_record()
    {
        $db = Sys::get_container_db_eloquent();
        $dbzend = Sys::get_container_db();
        $sql="select id,big_pic,thumbnailpath,subject_pic 
           from bb_record order by id asc";
        $query = $dbzend->query($sql );
        $i=0;
        
        while ( $row = $query->fetch() ) {
            
            if ($i>1000) {
                break;
            }
            $result1= $this->process($row['big_pic'], 'bb_record', 'big_pic', $row['id']  );
            $result2= $this->process($row['thumbnailpath'], 'bb_record', 'thumbnailpath', $row['id']  );
            $result3= $this->process($row['subject_pic'], 'bb_record', 'subject_pic', $row['id']  );
            if ($result1 || $result2 || $result3 ) { 
                $i++; 
            }
            echo $row['id']."\n";
        }
        
    }
    
    

}
      
    
   
