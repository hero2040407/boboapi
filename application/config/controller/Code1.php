<?php
namespace app\config\controller;
//仅限自机（其实是200） 和 200
in_array(\BBExtend\Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

use BBExtend\Sys;

/**
 * 
 * 测试 sign方案的正确性
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/10/13
 */
class Code1
{
    // 测试 成就系统
    public function dengji()
    {
        $obj = new \BBExtend\user\achievement\Dengji(1);
        $obj->update(1);
    }
     
    // 测试 成就系统
    public function zhibo($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Zhibo( 1 );
        $obj->update($zhibo);
    }
    // 测试 成就系统
    public function pinglun($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Pinglun(1);
        $obj->update($zhibo);
    }
    // 测试 成就系统
    public function dianzan($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Dianzan(1);
        $obj->update($zhibo);
    }
    // 测试 成就系统
    public function zhubo($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Zhubo(1);
        $obj->update($zhibo);
    }
    // 测试 成就系统
    public function hongren($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Hongren(1);
        $obj->update($zhibo);
    }
    // 测试 成就系统
    public function huodong($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Huodong(1);
        $obj->update($zhibo);
    }
    // 测试 成就系统
    public function dasai($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Dasai(1);
        $obj->update($zhibo);
    }
    // 测试 成就系统
    public function neirong($zhibo)
    {
        $obj = new \BBExtend\user\achievement\Neirong(1);
        $obj->update($zhibo);
    }
    
    
    public function testlog()
    {
        \BBExtend\Sys::debug([3,44]);
        echo 44;
    }
    
    
}

