<?php
namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\model\Achievement as Ach;

/**
 * 给vip加粉，必须每10分钟调用1次。
 * 
 * @author Administrator
 *
 */
class Robotuserfans
{
    
    public $count=0;
    
    /**
     * 机器人加粉
     * 
     * 每10分钟1次。
     * 
     * 
     * @return number[]|number[]
     */
    public function index()
    {
        $file = '/var/www/html/public/public/toppic/fans.json';
        if (is_file($file)) {
           $s =  file_get_contents($file);
           $arr = json_decode($s,true);
           $arr = $arr['arr'];
//            dump($arr);
        }else {
            return ['code' => 0,'message'=>'文件 /var/www/html/public/public/toppic/fans.json 不存在' ];
        }
        
        foreach ( $arr as $v ) {
            
            if ($this->check_user($v['uid'])) {
            
                $this->addfans_random($v['uid'], $v['fans_count'] );
            }
        }
        return ['code'=> 1,'data' =>['focus_count' => $this->count ] ];
    }
    
    
    private function check_user($uid){
        $user = \BBExtend\model\User::find($uid);
        if ($user) {
            return true;
        }
        return false;
    }
    
    
    private function addfans_random($target_uid,$fans_count)
    {
        
        //计算概率。
        $min = $fans_count / 2;
        $min = intval(ceil($min));
        $max = $fans_count+ $min;
        
        $count = mt_rand($min, $max);
        $random = mt_rand( 1,108 );
        
        if ($random < $count ) {
            
            // 首先，随机取一个粉丝。
            $dbzend = Sys::get_container_dbreadonly();
            $sql=" select uid from bb_users where permissions =98 order by rand() limit 1 ";
            $self_uid = $dbzend->fetchOne($sql);
            
            $this->addfans_go($self_uid, $target_uid);
        }
    }
    
    private function addfans_go($self_uid, $focus_uid) {
        
        $help = \BBExtend\user\Focus::getinstance($self_uid);
        if ( $help->focus_guy($focus_uid) ) {
            $this->count++;
          //  echo "{$self_uid} focus {$focus_uid}  success\n";
         
            $user = \BBExtend\model\User::find($focus_uid);
            
            $ach2 = new Ach();
            $ach = $ach2->create_default_by_user($user);
            
            $ach = new \BBExtend\user\achievement\Hongren($focus_uid);
            $ach->update(1);
            return ;
        } 
        
    }
    
    
    
}
