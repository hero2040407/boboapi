<?php
namespace app\command\controller;
use BBExtend\common\Str;
use BBExtend\Sys;
/**
 * 
 * 自动更新88，远程测试服，远程正式服的数据库。
 * 得当然先传文件过去
 * 
 * 
 * 在本机使用，使用方法
 * 
 * cd  d:\workspace_utf8\guai2
 * php .\public\index.php command/migrate/index
 * 
 * 
 * @author 谢烨
 *
 */

class Migrate
{
    public function index()
    {
        $temp = trim( file_get_contents( __DIR__. "/../../config/xieye_id.php"));
        if ($temp=='88') {
            file_put_contents( __DIR__. "/../../config/xieye_id.php", "1");
            $t = 1;
        }else {
            file_put_contents( __DIR__. "/../../config/xieye_id.php", "88");
            $t = 88;
        }
        
        $url = "http://www.test1.com/systemmanage/migrate/index";
        $result = file_get_contents($url);
        echo "\n\n---   ". $url."   ---\n" .  br2nl(  $result);
        echo "\n---------\n";
        
        if ($t ==88) {
            file_put_contents( __DIR__. "/../../config/xieye_id.php", "1");
        }else {
            file_put_contents( __DIR__. "/../../config/xieye_id.php", "88");
        }
        
        ob_flush();
        flush();
        
        
        $url = "http://bobot.yimwing.com/systemmanage/migrate/index";
        $result = file_get_contents($url);
        echo "\n\n---   ". $url."   ---\n" .  br2nl(  $result);
        echo "\n---------\n";
        ob_flush();
        flush();
       
        $url = "https://bobo.yimwing.com/systemmanage/migrate/index";
        $result = file_get_contents($url);
        echo "\n\n---   ". $url."   ---\n" .  br2nl(  $result);
        echo "\n---------\n";
        
        ob_flush();
        flush();
        
    }
    
    public function test()
    {
        $db = Sys::get_container_db();
        $sql ="select nickname from bb_users";
        echo $db->fetchOne($sql);
    }
    
    
}


function br2nl($text) {
    $text = Str::u2g($text);
    return   preg_replace('/<br\\s*?\/?>/i', "\n", $text);
}
