<?php
namespace app\apptest\controller;
use think\Config;

// require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');


/**
 * 
 * @author Administrator
 *        
 */
class Redis {
    
    public function flushall() {
      
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
