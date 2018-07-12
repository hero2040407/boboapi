<?php
namespace app\apptest\controller;
use think\Config;
use BBExtend\Sys;
// require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
use BBExtend\DbSelect;

/**
 * 
 * @author Administrator
 *        
 */
class Redis {
    
    
    public function mysqltest(){
        //$db = Sys::get_container_dbreadonly();
        $db = Sys::get_container_db_eloquent();
        
        
        $sql="select count(*) from bb_users";
        echo DbSelect::fetchOne($db, $sql);
        
    }
    
    public function flushall() {
      echo 11;
//         $redis = $this->getredis();
//         $redis->flushAll();
        
    }
    
    public function flush4() {
    
        $redis = $this->getredis();
        //$redis->flushAll();
        $redis->select(4);
      //  $redis->flushDB();
    echo "flush4 ok";
    }
    
    
    public function test() {
        $redis = $this->getredis();
        $redis->set("a",11);
        echo $redis->incr("a");
        echo "<br>------";
        echo "<br>如果看到横线上方有12，表示正确。";
    }
    
    private function getredis()
    {
        $redis = new \Redis();
        $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
        $redis->auth(Config::get('REDIS_AUTH'));
        return $redis;
    }
}
