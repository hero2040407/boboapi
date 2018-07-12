<?php
namespace app\systemmanage\controller;
/**
 * 
 * @author 谢烨
 */


use BBExtend\Sys;
use BBExtend\DbSelect;

class Usercreate
{ 
    
    /**
     * 
     * 
     */
    public function start()
    {
        $file1 = "/var/www/html/application/jiayonghu_1.txt";
        $file2 = "/var/www/html/application/jiayonghu_2.txt";
        
        $this->importfile($file1);
        $this->importfile($file2);
        
        
    }
    
    
    /**
     * 整体逻辑。
     * 
     * 对 file进行遍历，每个昵称得到一次机会。
     *     检查是否存在此账号，存在忽略。
     *     如果不存在，
     *        挑一个id最小的permissions=10 的账号。
     *        改掉它的permissions = 98
     *        把它的昵称替换掉。
     * 
     * @param unknown $file
     */
    private function importfile($file)
    {
        $i=0;
        $content_arr = file( $file );
        foreach ( $content_arr as $v ) {
            $v2 = $this->filter($v);
            echo  (($i++)." = ". $v2."\n");
            
//             if ($i>5000) {
//                 break;
//             }
            
            $this->replace($v2);
            
            
        }
    }
    
    
    private function replace($name)
    {
        $db = Sys::get_container_db();
        $sql="select uid from bb_users where nickname=?";
        $result = $db->fetchRow($sql,[ $name ]);
        if ($result) {
            return false;
        }
        
        $sql="select uid from bb_users where permissions=10 order by rand() LIMIT 1";
        $min_uid = $db->fetchOne($sql);
        
        $sql="update bb_users set nickname=? ,permissions=98 where uid=?";
        $db->query($sql,[ $name, $min_uid ]);
        echo "excute {$min_uid} : {$name} \n";
        
    }
    

    
    private function filter($v) 
    {
        $v2 = trim($v);
        $v2 = preg_replace('/#/', '', $v2);
        $v2 = preg_replace('#\$#', '', $v2);
        $v2 = preg_replace('#\"#', '', $v2);
        $v2 = preg_replace('#\&#', '', $v2);
        $v2 = preg_replace('#、#', '', $v2);
        $v2 = preg_replace('#。#', '', $v2);
        $v2 = preg_replace('#\?#', '', $v2);
        $v2 = preg_replace('#\@#', '', $v2);
        
//         $minganci_help = new \BBExtend\model\Minganci();
//         $v2 = $minganci_help->filter_by_asterisk($v2);
        
        $v2 = trim($v2);
        return $v2;
    }
    
    
    
    
}





