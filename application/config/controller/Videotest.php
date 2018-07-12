<?php
namespace app\config\controller;
//仅限自机（其实是200） 和 200
in_array(\BBExtend\Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');

use think\Config;
/**
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Videotest extends \UnitTestCase
{
    
    public function index()
    {
    }
    
    public function test_details()
    {
    
        //验证第1名是1号
        $rank = \BBExtend\user\Ranking::getinstance(2)->get_caifu_ranking();
        $this->assertEqual($rank, 1);
        
        
        $rank = \BBExtend\user\Ranking::getinstance(1)->get_caifu_ranking();
        $this->assertEqual($rank, 1);
        $rank = \BBExtend\user\Ranking::getinstance(2)->get_caifu_ranking();
        $this->assertEqual($rank, 2);
        
        
        
        $param =['room_id'=> 111,'uid'=>1 ];
         $url ='http://127.0.0.1/shop/video/buy?'.http_build_query($param);
         $json = json_decode(file_get_contents($url),true);
         $this->assertEqual($json['code'], 1);
         
         //验证金钱减少
         $user = \app\shop\model\Users::get(1);
         $gold = $user->get_buy_info()['gold'];
         $this->assertEqual($gold, 10000-10);
         
         
         //验证第2名是1号
         $rank = \BBExtend\user\Ranking::getinstance(1)->get_caifu_ranking();
         $this->assertEqual($rank, 2);
         
         //验证重复购买失败
         $param =['room_id'=> 111,'uid'=>1 ];
         $url ='http://127.0.0.1/shop/video/buy?'.http_build_query($param);
         $json = json_decode(file_get_contents($url),true);
         $this->assertEqual($json['code'], 0);
          
          
    }
    
    
    
    function setUp() {
    
        $redis = $this->getredis();
        $redis->flushAll();
        $this->add_record();
    }
    function setDown() {
    }
    
    private function getredis()
    {
        $redis = new \Redis();
        $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
        $redis->auth(Config::get('REDIS_AUTH'));
        return $redis;
    }
    
    public function add_record()
    {
        $db = \BBExtend\Sys::get_container_db();
        $sql ="delete from bb_users";
        $db->query($sql);
        $sql ="delete from bb_record";
        $db->query($sql);
        $sql ="delete from bb_currency";
        $db->query($sql);
        $sql ="delete from bb_buy_video";
        $db->query($sql);
        
        
        $db->insert("bb_users", [
            'uid'=>1,
            'platform_id'=>'1',
        ]);
        $db->insert("bb_currency", [
            'uid'=>1,
            'gold'=>'10000',
        ]);
      
        
        $db->insert("bb_users", [
            'uid'=>2,
            'platform_id'=>'2',
        ]);
        $db->insert("bb_currency", [
            'uid'=>2,
            'gold'=>'10000',
        ]);
        
        $db->insert("bb_record", [
            'room_id'=>111,
            'price_type'=>'2',
            'price'=>10,
            'uid' => 2323,
        ]);
        
        
        
        
    }
    
}