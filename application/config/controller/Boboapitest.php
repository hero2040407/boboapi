<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;

//仅限自机（其实是200） 和 200
in_array(Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');


require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
class Boboapitest extends \UnitTestCase
{
    
    const room_id_1 ='108661050record_movies';
    const room_id_2 ='108661050record_movies2';
    
    public function index() 
    {
    }
    
    /**
     * 测ip点赞 record
     */
    public function test_ip_record()
    {
        //谢烨，先测ip点赞，
        $ip = '10.0.0.11';
        $param =['ip'=> $ip, "type"=>'record', 'room_id'=>self::room_id_1];
        $url ='http://127.0.0.1/api/boboapi/ip_like?'.http_build_query($param);
        
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        $db = \BBExtend\Sys::get_container_db();
        //断言 新加入到一条数据
        $result = $db->fetchRow("select * from bb_record_like where ip='{$ip}'");
        $this->assertTrue($result);
        //断言点击量1
        $room_id = self::room_id_1;
        $result = $db->fetchOne("select `like` from bb_record where room_id='{$room_id}'");
        $this->assertEqual($result, 1);
        
        $result = \BBExtend\BBRedis::getInstance('record')->hGetAll( $room_id.'record');
        $this->assertEqual($result['like'], 1);
        //断言，继续就会失败
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 0);
        
        //下面取消点赞测试
        
        $param =['ip'=> $ip, "type"=>'record', 'room_id'=>self::room_id_1];
        $url ='http://127.0.0.1/api/boboapi/ip_unlike?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        //断言 没有数据
        $result = $db->fetchRow("select * from bb_record_like where ip='{$ip}'");
        $this->assertFalse($result);
        //断言点击量0
        $room_id = self::room_id_1;
        $result = $db->fetchOne("select `like` from bb_record where room_id='{$room_id}'");
        $this->assertEqual($result, 0);
        
        $result = \BBExtend\BBRedis::getInstance('record')->hGetAll( $room_id.'record');
        $this->assertEqual($result['like'], 0);
        //断言，继续就会失败
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 0);
    }

   
    /**
     * 测ip点赞 rewind
     */
    public function test_ip_rewind()
    {
        //谢烨，先测ip点赞，
        $ip = '10.0.0.11';
        $param =['ip'=> $ip, "type"=>'rewind', 'room_id'=>self::room_id_2];
        $url ='http://127.0.0.1/api/boboapi/ip_like?'.http_build_query($param);
    
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        $db = \BBExtend\Sys::get_container_db();
        //断言 新加入到一条数据
        $result = $db->fetchRow("select * from bb_rewind_like where ip='{$ip}'");
        $this->assertTrue($result);
        //断言点击量1
        $room_id = self::room_id_2;
        $result = $db->fetchOne("select `like` from bb_rewind where room_id='{$room_id}'");
        $this->assertEqual($result, 1);
    
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 0);
    
        // 下面测试取消点赞
        
        $param =['ip'=> $ip, "type"=>'rewind', 'room_id'=>self::room_id_2];
        $url ='http://127.0.0.1/api/boboapi/ip_unlike?'.http_build_query($param);
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        //断言 没有数据
        $result = $db->fetchRow("select * from bb_rewind_like where ip='{$ip}'");
        $this->assertFalse($result);
        //断言点击量0
        $room_id = self::room_id_2;
        $result = $db->fetchOne("select `like` from bb_rewind where room_id='{$room_id}'");
        $this->assertEqual($result, 0);
        
        //断言，继续就会失败
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 0);
    
    }
    
    
    
    function setUp() {
        
        $redis = $this->getredis();
        $redis->flushAll();
        $this->add_record();
    }
    function setDown() {
        $redis = $this->getredis();
        //$redis->flushAll();
      //  $this->add_record();
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
         $sql="delete from bb_record";
         $db->query($sql);
        $sql="delete from bb_record_like";
        $db->query($sql);
        $sql="delete from bb_rewind";
        $db->query($sql);
        $sql="delete from bb_rewind_like";
        $db->query($sql);
        
        
        $db->insert("bb_rewind", [
            'id' => 1,
            'uid'=>1,
            'room_id' => self::room_id_2,
        ]);
        
        $db->insert("bb_record", [
            'id' => 1,
            'uid' => 1,
            'type' => 1,
            'video_path'=>'http://record.yimwing.com/v/caaa8126-9ffe-4d55-b094-326a68c35829.mp4',
            'big_pic' => 'http://record.yimwing.com/v/caaa8126-9ffe-4d55-b094-326a68c35829.jpg',
            // 'like' => 10,
            'room_id' => self::room_id_1,
        ]);
    }
    
    
}
