<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;

use BBExtend\fix\LevelFix;
use BBExtend\user\exp\Exp;

//仅限自机（其实是200） 和 200
in_array(Sys::get_machine_name(), ['200','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
class Exptest extends \UnitTestCase
{
    public function index()
    {
        
    }
    
    
    public function test_exp_class()
    {
        $db = Sys::get_container_db();
        $fix_arr = \BBExtend\fix\LevelFix::get_all();
        $fix_every = \BBExtend\fix\LevelFix::get_every();
        
        $sql="select count(*) from bb_users_exp_log where uid=1";
        $count=$db->fetchOne($sql);
        Exp::getinstance(1)
            ->set_is_test(1)
            ->set_typeint(Exp::LEVEL_INVITATION_REGISTER) 
            ->add_exp();
        
        // 邀请注册加5点，无限。
        $sql="select * from bb_users_exp where uid=1";
        $row=$db->fetchRow($sql);
        $this->assertEqual($row['level'], 1);
        $this->assertEqual($row['exp'], 5);
        $this->assertEqual($row['next_exp'], $fix_arr[2]);
        
        $sql="select count(*) from bb_users_exp_log where uid=1";
        $count2=$db->fetchOne($sql);
        $this->assertEqual($count+1, $count2);
        
        Exp::getinstance(2)
          ->set_is_test(1)
          ->set_typeint(Exp::LEVEL_INVITATION_REGISTER)
          ->add_exp();
        
        // 邀请注册加5点，无限。
        $sql="select * from bb_users_exp where uid=2";
        $row=$db->fetchRow($sql);
        $this->assertEqual($row['level'], 3);
        $this->assertEqual($row['exp'], 4);
        $this->assertEqual($row['next_exp'], $fix_every[4]);
        
    }
    
    public function reckon_exp($exp_sum)
    {
       $fix_arr = LevelFix::get_all();
        $fix_every = LevelFix::get_every();
        if ($exp_sum >= $fix_arr[100]) {
            return [100, 0, 0];
        }
        
        $level = 1;
        $exp = $next_exp = 0;
        for ($i = 100; $i > 0; $i--) {
            if ($exp_sum >= $fix_arr[$i-1] && $exp_sum < $fix_arr[$i] ) {
                $level = $i-1;
                break;
            }
        }
        if ($level < 100) {
            $exp = $exp_sum - $fix_arr[$level];
            $next_exp = $fix_every[$level+1] ;
        }
        return [$level, $exp, $next_exp];
    }
   
    
    public function test_1()
    {
        $fix_all = LevelFix::get_all();
        $fix_every = LevelFix::get_every();
       // dump($fix_all);
        $exp_sum = 234616;// 先测超过最大值。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 100);
        $this->assertEqual($exp, 0);
        $this->assertEqual($next_exp, 0);
       
        $exp_sum = 234615;// =100。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 100);
        $this->assertEqual($exp, 0);
        $this->assertEqual($next_exp, 0);
       
        $exp_sum = 234614;// < 100。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 99);
        $this->assertEqual($exp, 6329);
        $this->assertEqual($next_exp, $fix_every[100]);
        
        $exp_sum = $fix_all[99];// =99。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 99);
        $this->assertEqual($exp, 0);
        $this->assertEqual($next_exp, $fix_every[100]);
       
        $exp_sum = 222071;// <99。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 98);
        $this->assertEqual($exp, 1);
        $this->assertEqual($next_exp, $fix_every[99]);
         
        $exp_sum = $fix_all[98];// =98。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 98);
        $this->assertEqual($exp, 0);
        $this->assertEqual($next_exp, $fix_every[99]);
        
        $exp_sum = 0;// =1。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 1);
        $this->assertEqual($exp, 0);
        $this->assertEqual($next_exp, $fix_every[2]);
        
        $exp_sum = 1;// >1。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 1);
        $this->assertEqual($exp, 1);
        $this->assertEqual($next_exp, $fix_every[2]);
        
        $exp_sum = $fix_all[2];// =2。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 2);
        $this->assertEqual($exp, 0);
        $this->assertEqual($next_exp, $fix_every[3]);
        
        
        $exp_sum = $fix_all[2]+3;// >2。
        list($level, $exp, $next_exp) = $this->reckon_exp($exp_sum);
        $this->assertEqual($level, 2);
        $this->assertEqual($exp, 3);
        $this->assertEqual($next_exp, $fix_every[3]);
        
        
        
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
    
        $db->query("delete from bb_users_exp");
        $db->query("delete from bb_users");
        $db->query("delete from bb_users_exp");
        $db->query("delete from bb_users_exp");
        
        
        
        $db->insert("bb_users_exp",[
            'uid' => 1,
            'level' =>1,
            'next_exp'=>0,
            'exp' =>0,
        ]);
        $db->insert("bb_users_exp",[
            'uid' => 2,
            'level' =>2,
            'next_exp'=>1,
            'exp' =>94,
        ]);
    
    }
    
   
}
