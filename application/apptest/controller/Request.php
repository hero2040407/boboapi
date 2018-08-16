<?php

namespace app\apptest\controller;

use think\Db;
use BBExtend\Sys;

/**
 * 
 * @author Administrator
 *        
 */
class Request {
    // $temp='';
    public function index() {
//        $db = Sys::get_container_dbreadonly();
 Sys::display_all_error();
        $arr = range(1000000, 2060000, 10000);
        $db = Sys::get_container_db();
        foreach ( $arr as $startid ) {
               
               $sql="select * from bb_request order by id asc limit {$startid},10000";
               $query = $db->query($sql);
               
               while ( $v= $query->fetch() ){
                   if ( preg_match('#/user/info/get_public_addi_video#', $v['url']) ) {
                       $v2 = $v;
                       
                       
                       $command = $v['url'];
                       $command = preg_replace('#(\?|\=|\&)#', '/', $command);
                       $command = "/usr/bin/php /var/www/html/public/index.php {$command}";
                       
                       $out = shell_exec ( $command );
                       if ($out){
                          $v2['result'] =$out ;
                       
                          $db->insert( 'bb_request_analog',$v2 );
                       }
                      // echo $v['id']."\n";
                       
                   }
               }
               
               $size = $this->get_size();
               if ($size > 95) {
                   break;
               }
        }
    }
    
    private function get_size(){
        $command = "df -lh";
        $out = shell_exec ( $command );
        $arr = explode("\n", $out);
        $str = $arr[1];
        $str = preg_replace('#^.+ (\d+)\%.+$#', '$1', $str);
        return intval($str);
//         dump($arr);
//         echo "\n{$str}\n";
    }
    
    
    public function mysql()
    {
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $sql ="select * from bb_users limit 1";
        $result = \BBExtend\DbSelect::fetchAll($db, $sql);
        dump($result);
        
    }
    
}
