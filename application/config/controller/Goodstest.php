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

class Goodstest extends \UnitTestCase
{
    
    public function index()
    {
    }
    
    public function test_details()
    {
    
        $param =['goods_id'=> 1, ];
         $url ='http://127.0.0.1/shop/goods/details?'.http_build_query($param);
         $json = json_decode(file_get_contents($url),true);
      //   dump($json);
         $this->assertEqual($json['code'], 1);
         //1的数据是有pic_list,没有show，则应返回pic_list的数据
         $pic_list = $json['data']['pic_list'];
         $server = \BBExtend\common\BBConfig::get_server_url();
         $this->assertEqual($pic_list[0]['picpath'], $server. "/public/1.png");
         $this->assertEqual($json['data']['pic'], $server. "/public/1.png");
         
         
         //2的数据是1的数据是没有pic_list,也没有show_pic_list。
         $param =['goods_id'=> 2, ];
         $url ='http://127.0.0.1/shop/goods/details?'.http_build_query($param);
         $json = json_decode(file_get_contents($url),true);
         $this->assertEqual($json['code'], 1);
         
         $pic_list = $json['data']['pic_list'];
         $this->assertEqual($pic_list, "");
         $this->assertEqual($json['data']['pic'], $server. "/public/1.png");
          
         //3的数据是什么都没有。应返回缺省值
         $param =['goods_id'=> 3, ];
         $url ='http://127.0.0.1/shop/goods/details?'.http_build_query($param);
         $json = json_decode(file_get_contents($url),true);
         $this->assertEqual($json['code'], 1);
         $pic_list = $json['data']['pic_list'];
         $this->assertEqual($pic_list, "");
         $this->assertEqual($json['data']['pic'], $server . '/public/shop_goods/default.png');
         
         //4的数据是有show_pic_list，有pic_list。应返回show的数据
         $param =['goods_id'=> 4, ];
         $url ='http://127.0.0.1/shop/goods/details?'.http_build_query($param);
         $json = json_decode(file_get_contents($url),true);
         $this->assertEqual($json['data']['pic'], $server . '/public/3.png');
       //  dump($json);
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
        $sql ="delete from bb_shop_goods";
        $db->query($sql);
        
        
        // 1号数据
        $pic ='';
        $pic_list='
[
    {
        "picpath": "/public/1.png",
        "style": "中号",
        "linkurl": ""
    },
    {
        "picpath": "/public/2.png",
        "style": "小号",
        "linkurl": ""
    }
]
                ';
        $style_list ='中号,小号';
        $db->insert("bb_shop_goods", [
                'id'=>1,
                'exchange_level'=>1,
                'currency'=> 1,
                'money' => 1,
                'discount' => 10,
                'title' => '鞋子',
                'info' => '鞋子info',
                'inventory' => mt_rand(0, 3),
                'sell_num' => 2,
                'pic_list' => $pic_list,
                'pic' => $pic,
                'model_list' => '',
                'style_list' => $style_list,
                'heat' => mt_rand(100, 999),
                'is_rmd' => 0,
        ]);
        
        // 2号数据
        $pic ='/public/1.png';
        $pic_list='';
        $style_list ='';
        $db->insert("bb_shop_goods", [
            'id'=>2,
            'exchange_level'=>1,
            'currency'=> 1,
            'money' => 1,
            'discount' => 10,
            'title' => '鞋子',
            'info' => '鞋子info',
            'inventory' => mt_rand(0, 3),
            'sell_num' => 2,
            'pic_list' => $pic_list,
            'pic' => $pic,
            'model_list' => '',
            'style_list' => $style_list,
            'heat' => mt_rand(100, 999),
            'is_rmd' => 0,
        ]);
        
        // 3号数据
        $pic ='';
        $pic_list='';
        $style_list ='';
        $db->insert("bb_shop_goods", [
            'id'=>3,
            'pic_list' => $pic_list,
            'pic' => $pic,
            'model_list' => '',
            'title' => '鞋子',
            'info' => '鞋子info',
            'style_list' => $style_list,
            'heat' => mt_rand(100, 999),
            'is_rmd' => 0,
        ]);
        
        // 4号数据
        $pic ='';
 
        $show_pic_list='
[
    {
        "picpath": "/public/3.png",
        "title": "中号"
    },
    {
        "picpath": "/public/4.png",
        "title": "小号"
    }
]
                ';
        
        
        $pic_list='
[
    {
        "picpath": "/public/1.png",
        "style": "中号",
        "linkurl": ""
    },
    {
        "picpath": "/public/2.png",
        "style": "小号",
        "linkurl": ""
    }
]
                ';
        $style_list ='中号,小号';
        $db->insert("bb_shop_goods", [
            'id'=>4,
            'exchange_level'=>1,
            'currency'=> 1,
            'money' => 1,
            'discount' => 10,
            'title' => '鞋子',
            'info' => '鞋子info',
            'inventory' => mt_rand(0, 3),
            'sell_num' => 2,
            'pic_list' => $pic_list,
            'pic' => $pic,
            'model_list' => '',
            'style_list' => $style_list,
            'heat' => mt_rand(100, 999),
            'is_rmd' => 0,
            'show_pic_list'=> $show_pic_list,
        ]);
        
    }
    
}