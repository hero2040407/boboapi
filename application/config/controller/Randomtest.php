<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;
//仅限自机（其实是200） 和 200
in_array(Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
class Randomtest extends \UnitTestCase
{
    public function index()
    {
        
    }
    
//     public function test4()
//     {
//         $s = '12a34';
//         for ($i=0;$i<strlen($s); $i++) {
//           echo $s[$i];
//         }
//     }
    
    public function test_3()
    {
        $a =123.134;
        $result = $this->part_of_decimal($a);
        $this->assertEqual($result[0], '1');
        $this->assertEqual($result[1], '3');
        
        $a =123.1;
        $result = $this->part_of_decimal($a);
        $this->assertEqual($result[0], '1');
        $this->assertEqual($result[1], '0');
        
        $a =123;
        $result = $this->part_of_decimal($a);
        $this->assertEqual($result[0], 0);
        $this->assertEqual($result[1], 0);
        
        
    }
    
    public function test_0dian()
    {
        $aa = strtotime(date('Y-m-d',time()));
        $bb = \BBExtend\common\Date::get_day_start();
        $this->assertEqual($aa, $bb);
        
        
    }
    
    public function test_1()
    {
        $boo = true;
        for ($i=0;$i<10000; $i++) {
            $r1 = rand() + rand(1,100)/100;
            $r2 = rand() + rand(1,100)/100;
            $min = min([$r1, $r2]);
            $max = max([$r1, $r2]);
            $result = $this->randomFloat($min, $max);
           // echo $min." ".$max . " ". $result."\n";
            if ($result >= $min && $result <= $max) {
                
            }else {
                $boo = false;
                echo "error".$min." ".$max . " ". $result."\n";
            }
            
        }
        $this->assertTrue($boo);
        //$param =['v'=> 1, ];
    }
     
    public function randomFloat($min, $max)
    {
        $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return sprintf("%.2f", $num);
    }
    
    
    public function part_of_decimal($a)
    {
        $a = strval($a);
        $arr=[0,0];
        if (preg_match('#\.[\d]+#', $a)) {
            $s = preg_replace('#^.*\.([\d]+)$#', '$1', $a);
            for ($i=0;$i<strlen($s) && $i < 2 ; $i++) {
                $arr[$i] = $s[$i];
            }
        }
        return $arr;
    }
    
    
    
    
   
}
