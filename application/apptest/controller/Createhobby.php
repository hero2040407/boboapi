<?php
namespace app\apptest\controller;

// use BBExtend\BBRedis;
use  think\Db;
use BBExtend\common\MysqlTool;
use BBExtend\Sys;
class Createhobby
{
    
    public function index ()
    {
        $str='[';
        $arr = json_decode($str,true);
        var_dump($arr);
        
    }
    
    public function index2()
    {exit;
       // $redis = Sys::getredis_paihangbang();
        // 1财富，2粉丝，3，等级经验，4，怪兽数量
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql ="select uid,specialty from bb_users order by uid asc";
        $query = $db2->query($sql);
        
       
        $i=0;
        while ($row = $query->fetch()) {
            $i++;
            if ($i%100==0) {
                echo "$i ... ... \n";
            }
            $uid = $row['uid'];
            $str = $row['specialty'];
            $arr = json_decode($str,true);
            if ($arr) {
                foreach ($arr as $v) {
                    $db->insert("bb_user_hobby", [
                        'uid' => $uid,
                        'hobby_id' => intval($v),
                        
                    ]);
                }
            }
            
//             $help = new \BBExtend\user\Ranking($uid);
//             $help->set_caifu_ranking()->set_dengji_ranking()
//                  ->set_fensi_ranking()->set_guaishou_ranking();
// //                  ->set_dashang_ranking();
        }
        echo "all ok \n";
        
    }
    
   
    
   
}
