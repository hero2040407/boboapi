<?php
namespace app\apptest\controller;

use BBExtend\Sys;

class Vip
{
    
    private function set_daoshi($row)
    {
        // dump($row);
        
        $db = Sys::get_container_db();
        //$uid = intval( $row['uid'] ) ;
        if ( isset( $row['uid'])  && is_numeric( $row['uid'])   ) {
            
            $uid =  $row['uid'];
            
            $sql="update bb_users set role=2 where uid=" .$uid ;
            //  echo $sql;
            $db->query($sql);
            
            dump($row);
        }
        
    }
    
    public function check_daoshi(){
        $file  = __DIR__ . "/daoshi.csv";
        //         $str = file_get_contents($file) ;
        //         $str = \BBExtend\common\Str::g2u($str);
        
        
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                // 这是每一行记录。
                if ($row > 1) {
                    $num = count($data);
                    //  echo "<p> $num fields in line $row: <br /></p>\n";
                    
                    $new=[];
                    for ($c=0; $c < $num; $c++) {
                        if ( $c==0 ) {
                            $new['uid'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==1 ) {
                            $new['name'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        
                    }
                    
                    //  dump($new);
                    $this->set_daoshi($new);
                }
                $row++;
            }
            fclose($handle);
        }
        
        
    }
    
    
    
    
    
    private function set_jigou($row)
    {
       // dump($row);
        
        $db = Sys::get_container_db();
        //$uid = intval( $row['uid'] ) ;
        if ( isset( $row['uid'])  && is_numeric( $row['uid'])   ) {
            
            $uid =  $row['uid'];
            
            $sql="update bb_users set role=4 where uid=" .$uid ;
            //  echo $sql;
            $db->query($sql);
            
            //dump($row);
        }
        
    }
    
    public function check_jigou(){
        $file  = __DIR__ . "/jigou.csv";
//         $str = file_get_contents($file) ;
//         $str = \BBExtend\common\Str::g2u($str);
        
        
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                // 这是每一行记录。
                if ($row > 1) {
                    $num = count($data);
                  //  echo "<p> $num fields in line $row: <br /></p>\n";
                    
                    $new=[];
                    for ($c=0; $c < $num; $c++) {
                        if ( $c==0 ) {
                            $new['uid'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==1 ) {
                            $new['name'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                       
                    }
                    
                    //  dump($new);
                    $this->set_jigou($new);
                }
                $row++;
            }
            fclose($handle);
        }
        
        
    }
    
    
    public function check()
    {
        $file  = __DIR__ . "/vip_info.csv";
        $str = file_get_contents($file) ;
        $str = \BBExtend\common\Str::g2u($str);
        
        
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                // 这是每一行记录。
                if ($row > 1) {
                    $num = count($data);
                    echo "<p> $num fields in line $row: <br /></p>\n";
                    
                    $new=[];
                    for ($c=0; $c < $num; $c++) {
                        if ( $c==0 ) {
                            $new['uid'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==1 ) {
                            $new['name'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==2 ) {
                            $new['birthday'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==3 ) {
                            $new['address'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==4 ) {
                            $new['sex'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==5 ) {
                            $new['spec'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==6 ) {
                            $new['height'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                        if ( $c==7 ) {
                            $new['weight'] = \BBExtend\common\Str::g2u(  $data[$c]);
                        }
                    }
                    
                  //  dump($new);
                    $this->set_vip($new);
                }
                $row++;
            }
            fclose($handle);
        }
        
        
   }
   
   private function set_vip($row)
   {
       $db = Sys::get_container_db();
       $uid = intval( $row['uid'] ) ;
       if ( isset( $row['height'])  && isset( $row['weight']) && $row['height'] && $row['weight']  ) {
           dump($row);
           
           $sql="update bb_users set role=3 where uid=" .$uid ;
         //  echo $sql;
           $db->query($sql);
           
           $info = \BBExtend\model\UserInfo::getinfo($uid) ;
           $info->height = $row['height'];
           $info->weight = $row['weight'];
           $info->update_time = time();
           $info->save();
           
           
       }else {
           $sql ="delete from bb_vip_application_log where uid = {$uid} ";
           $db->query($sql);
           $bind =[
                   'uid'=>$uid,
                   'create_time'=> time(),
                   'status' => 4,
                   
           ];
           $db->insert("bb_vip_application_log", $bind);
           
           
       }
       
       
   }
    
   
}
