<?php
namespace app\config\controller;
// use BBExtend\BBShop;
use think\Controller;
require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');


/**
 * 
 * 商城单元测试类
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Apitest  extends \UnitTestCase
{
    public function index() 
    {
         $temp = get_cfg_var('guaishou.username');
    
         if ($temp && $temp=='200') {
    
         }else {
             exit();
         }
    }
    
    public function test_buy()
    {
        $this->assertTrue(1);
        
    }
    
    
}

