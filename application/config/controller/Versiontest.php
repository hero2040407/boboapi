<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;
//仅限自机（其实是200） 和 200
in_array(Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
class Versiontest extends \UnitTestCase
{
    public function index()
    {
        
    }
    
    public function test_android_new()
    {
        //$param =['v'=> 1, ];
        $url ='http://127.0.0.1/api/version/android_new';//.http_build_query($param);
        
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        $this->assertEqual($json['data']['versionName'], 30);
         $this->assertTrue($json['data']['versionCode'] === 20);
    }
     
    public function test_android_list()
    {
        $url ='http://127.0.0.1/api/version/android_list';//.http_build_query($param);
        
        $json = json_decode(file_get_contents($url),true);
        $this->assertEqual($json['code'], 1);
        $this->assertEqual(count($json['data']), 2);
    }
    
    /**
     * 返回最新的安卓版本
     */
    public function android_new()
    {
        $db = \BBExtend\Sys::get_container_db();
        $sql ='select version_name,
                 version_code, is_qiangzhi, url, update_content
                from  bb_version_android 
                order by create_time desc
                limit 1
                ';
        $row  = $db->fetchRow($sql);
        if (!$row) {
            return ['code'=>0, 'data' =>[], ];
        }
        return ['code'=>1, 'data'=>$this->format($row)];
    }
    
    /**
     * 返回最新的50个安卓版本，方便安卓开发人员通过浏览器查看，倒序排
     */
    public function android_list()
    {
        $db = \BBExtend\Sys::get_container_db();
        $sql ='select version_name,
                 version_code, is_qiangzhi, url, update_content
                from  bb_version_android
                order by create_time desc
                limit 50
                ';
        $row  = $db->fetchAll($sql);
        if (!$row) {
            return ['code'=>0, 'data' =>[], ];
        }
        $result=[];
        foreach ($row as $v) {
            $result []=  $this->format($v);
        }
        return ['code'=>1, 'data'=>$result];
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
        $sql='delete from bb_version_android';
        $db->query($sql);
        $db->insert('bb_version_android', [
            'version_name' => '3',
            'version_code' => '2',
            'url' => '4',
            'update_content' => '5',
            'is_qiangzhi' => '0',
        ]);
        $db->insert('bb_version_android', [
            'version_name' => '30',
            'version_code' => '20',
            'url' => '40',
            'update_content' => '50',
            'is_qiangzhi' => '1',
        ]);
        
    
    }
    
    
    
   
}
