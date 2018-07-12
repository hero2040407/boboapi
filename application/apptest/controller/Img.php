<?php
namespace app\apptest\controller;

// use BBExtend\BBRedis;
use  think\Db;
use BBExtend\common\MysqlTool;
use BBExtend\Sys;

use BBExtend\common\Image;

class Img
{
    public $arr=[];
    
    public function getsize($size, $format) {
        $p = 0;
        if ($format == 'kb') {
            $p = 1;
        } elseif ($format == 'mb') {
            $p = 2;
        } elseif ($format == 'gb') {
            $p = 3;
        }
        $size /= pow(1024, $p);
        return number_format($size, 3);
    }
    
//     $filename = '/data/webroot/usercode/code/resource/test.txt';
//     $size = filesize($filename);
    
//     $size = getsize($size, 'kb'); //进行单位转换
    
//     CREATE TABLE bb_test_big_pic (
//             id int(11) NOT NULL AUTO_INCREMENT,
//             record_id int(11) NOT NULL DEFAULT '0' COMMENT '',
//             http_pic varchar(255) NOT NULL DEFAULT '' COMMENT '图片全路径',
//             filename varchar(255) NOT NULL DEFAULT '' COMMENT '硬盘全路径',
//             size float not null default 0 comment '大小，单位mb',
//             PRIMARY KEY (id)
//             ) ENGINE=MyISAM
    
    private function process($img,$id) {
        if (!preg_match('#^http#', $img)) {
            return;
        }
        if (preg_match('#\\?#', $img)) {
            $img = preg_replace('#^(.+?)\\?.+$#', '$1', $img);
        }
        if (!preg_match('#(jpg|png|gif|jpeg)$#i', $img)) {
            return;
        }
        
        
       // $count_all++;
         
        // $img = $img_arr[0];
        echo " : " . $img . "\n";
        $filename = preg_replace('#^.+/([^/]+)$#', '$1', $img);
        
        //   echo '  '. $filename;
        
        $str = file_get_contents($img);
        $new_all_filename ='/var/www/html/runtime/temp2/'. $filename;
        file_put_contents('/var/www/html/runtime/temp2/'. $filename , $str);
        
        $t = $this->getsize( filesize($new_all_filename), 'mb' );
        if ($t > 1) {
            
            echo    $img.' = ' . $t. "\n";
        }
        $db = Sys::get_container_db();
        $db->update("bb_test_big_pic", [
            'size' => $t,
            'filename' => $new_all_filename,
            
        ],"id = {$id}");
        return;
    }
    
    public function bigpic()
    {
        $db = Sys::get_container_db();
        Sys::display_all_error();
        $sql="select * from bb_record order by id asc";
        $query = $db->query($sql);
        $temp=[];
        
        while ($row = $query->fetch()){
            if (in_array($row['big_pic'], $temp)) {
                continue;
            }
            $temp[]= $row['big_pic'];
            $db->insert("bb_test_big_pic", [
                'record_id' =>$row['id'],
                'http_pic' =>$row['big_pic'],
                'filename' =>'',
                'size' =>0,
            ]);
            $this->process($row['big_pic'],$db->lastInsertId() );
            
            if (in_array($row['thumbnailpath'], $temp)) {
                continue;
            }
            $temp[]= $row['thumbnailpath'];
            $db->insert("bb_test_big_pic", [
                'record_id' =>$row['id'],
                'http_pic' =>$row['thumbnailpath'],
                'filename' =>'',
                'size' =>0,
            ]);
            $this->process($row['thumbnailpath'],$db->lastInsertId());
        }
        echo "all ok\n";
    }
    
    public function index()
    {
        $db = Sys::get_container_db();
        $sql ="select subject_pic from bb_record where subject_pic !='' order by id desc limit 1000";
        $img_arr = $db->fetchCol($sql);
     //   dump($img_arr);
        
        $count=0;
        foreach ($img_arr as $img) {
          $count++;
        // $img = $img_arr[0];
       //   echo $img;
          $filename = preg_replace('#^.+/([^/]+)$#', '$1', $img);
          
       //   echo '  '. $filename;
          
          $str = file_get_contents($img);
          $new_all_filename ='/var/www/html/runtime/temp2/'. $filename;
          file_put_contents('/var/www/html/runtime/temp2/'. $filename , $str);
          
          $t = $this->getsize( filesize($new_all_filename), 'mb' );
          if ($t > 1) {
              echo $img.' = ' . $t. "\n";
          }
          
        //  echo ' = '. $this->getsize( filesize($new_all_filename), 'mb' );
          
        //  echo "<br>\n";
        }
        
        
        echo "ok";
        
    }
   
    
   
}
