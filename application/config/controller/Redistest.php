<?php
namespace app\config\controller;
use think\Config;
use BBExtend\Sys;
//仅限自机（其实是200） 和 200
in_array(Sys::get_machine_name(), ['200','245','xieye']) or exit('测试环境错误');

require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');


/**
 * 
 * @author Administrator
 *        
 */
class Redistest extends \UnitTestCase{
    
    public function index() {
        if (PHP_OS=='Linux'){
            exit();
        }
//         $address='%'."上海市"."%";
//         $startid=0;
//         $length=10;
//       $aa=  \think\Db::table('bb_activity')->where(['address'=>['like',$address],'is_remove'=>0])
//       ->limit($startid,$length)->order(['people'=>'desc'])->select();
        
    }
    
    
    public function test_z2()
    {
        $redis = $this->getredis();
        $aa='aa22';
        $redis->zAdd($aa, 12, '1');
        
        $redis->zAdd($aa, 120, '2');
        $redis->zAdd($aa, 1200, '33');
        $redis->zAdd($aa, -1100, '4');
        $redis->zAdd($aa, -1000, '5');
        
        $this->assertEqual(5, $redis->zCard($aa));
        
        //xieye，这里获得2的序号
        
        $arr = $redis->zReverseRange($aa,0,2);
       dump($arr);
        $temp = $redis->zRevRank($aa, '1');
         dump($temp);
         $this->assertEqual($redis->zScore($aa, 2) , 120);
         
//         $temp = $redis->zRevRank($aa, '2');
//         dump($temp);
//         $temp = $redis->zRevRank($aa, '3');
//         dump($temp);
//         $temp = $redis->zRevRank($aa, '4');
//         dump($temp);
//         $temp = $redis->zRevRank($aa, '5');
//         dump($temp);
        
//         $temp = $redis->zRevRank($aa, '56');
//         dump($temp);
        
    }
    
    public function test_normal()
    {
        $redis = $this->getredis();
        $this->assertTrue(1);
        $redis->set("aa",0);
        $this->assertTrue($redis->get('aaa')===false);
        $this->assertfalse($redis->get('aa')===false);
        
        $redis->incr("aa");
        $return = $redis->incr("aa");
        $return = $redis->incr("aa");
        $this->assertEqual($return, 3);
        
        $redis->delete("aa");
        $this->asserttrue($redis->get('aa')===false);
        
        
        //$redis->set("aa",0);
    }
    
    
    // xieye ,今天终于明白，当把集合的值全部清除后，自动删除键，为保。
    // 默认加一个uid为0的用户！！！
    public function test_lahei()
    {
        $redis = $this->getredis();
        $redis->sAdd('s0', '11');
        $redis->sAdd('s0', 2);
        $redis->sAdd('s0', 2);//要点：sadd，返回被新加入的元素数量
//         $redis->sAdd('s0', );
        $result = $redis->srem('s0',2);
        $this->assertTrue($redis->sismember('s0', 11) );//11 存在
        $this->assertfalse($redis->sismember('s0', 2) );
        
        
        $redis->sAdd('s1', null);
      //  dump($redis->sMembers('s1'));
//         dump($redis->exists('s234'));
//         dump($redis->exists('s0')); //查键是否存在。
        
        
       // $redis->sAdd('s1', null);
        $this->assertTrue(false=== $redis->get("s22") );
        $this->asserttrue(false=== $redis->get("s0") );
        
        //dump($redis->sMembers('s0')); // 要点，srem返回被移除的数量！！借此可判断是否真移除了！
//         dump($result); 
    }
    
    public function test_zset()
    {
        $redis = $this->getredis();
        $redis->zAdd('key', 1, 'val1');
        $redis->zAdd('key', 0, 'val0');
        $redis->zAdd('key', 5, 'val5');
        $redis->zRange('key', 0, -1); // array(val0, val1, val5)
        
        $redis->delete("key");
        $redis->zAdd('key', 0, 'val0');
        $redis->zAdd('key', 2, 'val2');
        $redis->zAdd('key', 10, 'val10');
        $this->assertEqual($redis->zSize("key") , 3);
        
        $redis->delete("key");
        $redis->zAdd('key', 2, 'val2');
        $redis->zAdd('key', 0, 'val0');
        $redis->zAdd('key', 10, 'val10');
        $redis->zRange('key', 0, -1); // ["val0", "val2","val10"]
        //0 start，3，end，
        //在分值范围内的个数
        $a = $redis->zCount('key', 1, 2); /* 2, corresponding to array('val0', 'val2') */
        $this->assertEqual($a , 1);
        
        $redis->delete('key');
        $redis->zIncrBy('key', 2.5, 'member1'); /* key or member1 didn't exist, so member1's score is to 0 before the increment */
        /* and now has the value 2.5  */
        $redis->zIncrBy('key', 1, 'member1'); /* 3.5 */
        
        //测并集
        $redis->delete('k1');
        $redis->delete('k2');
        $redis->delete('k3');
        $redis->delete('ko1');
        $redis->delete('ko2');
        $redis->delete('ko3');
        $redis->delete('ko4');
        
        $redis->zAdd('k1', 0, 'val0');
        $redis->zAdd('k1', 1, 'val1');
        $redis->zAdd('k1', 3, 'val3');
        
        $redis->zAdd('k2', 2, 'val1');
        $redis->zAdd('k2', 3, 'val3');
        //取并集，并自动保存。
        $redis->zInter('ko1', array('k1', 'k2')); /* 2, 'ko1' => array('val1', 'val3') */
      //  $redis->zInter('ko2', array('k1', 'k2'), array(1, 1));     /* 2, 'ko2' => array('val1', 'val3') */
        
        //
//        dump($redis->zRange("ko1", 0,-1));
        $redis->delete("key1");
        $redis->zAdd('key1', 0, 'val0');
        $redis->zAdd('key1', 2, 'val2');
        $redis->zAdd('key1', 10, 'val10');
        $redis->zRange('key1', 0, -1); /* array('val0', 'val2', 'val10') */
        
        // with scores
        $redis->zRange('key1', 0, -1, true); /* array('val0' => 0, 'val2' => 2, 'val10' => 10) */
        
        //分值
        $redis->zAdd('key', 0, 'val0');
        $redis->zAdd('key', 2, 'val2');
        $redis->zAdd('key', 10, 'val10');
        $redis->zRangeByScore('key', 0, 3); /* array('val0', 'val2') */
        $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE)); /* array('val0' => 0, 'val2' => 2) */
        $redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1))); /* array('val2') */
        $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE, 'limit' => array(1, 1))); 
        /* array('val2' => 2) */
        
    }
    
    public function test_set()
    {
        $redis = $this->getredis();
        $redis->sAdd('key1' , 'member1'); /* 1, 'key1' => {'member1'} */
        $redis->sAdd('key1' , 'member2', 'member3'); /* 2, 'key1' => {'member1', 'member2', 'member3'}*/
        $redis->sAdd('key1' , 'member2'); // 集合不允许重复值。
        
        $this->assertEqual($redis->sSize("key1") , 3);
        
        $redis->delete('s0', 's1', 's2');
        $redis->sAdd('s0', '1');
        $redis->sAdd('s0', '2');
        $redis->sAdd('s0', '3');
        $redis->sAdd('s0', '4');
        $redis->sAdd('s1', '1');
        $redis->sAdd('s2', '3');
        $arr = $redis->sDiff('s0', 's1', 's2');//返回所有在s0中，但不在s1，和s2中的，[4,2]
        $redis->sDiffStore( 'dst', 's0', 's1', 's2'); //顺便保存在dst中。
        $arr = $redis->sMembers("dst"); // ['4','2']
        
        $redis->delete("key1");
        $redis->sAdd('key1', 'val1');
        $redis->sAdd('key1', 'val2');
        $redis->sAdd('key1', 'val3');
        $redis->sAdd('key1', 'val4');
        $redis->sAdd('key2', 'val3');
        $redis->sAdd('key2', 'val4');
        $redis->sAdd('key3', 'val3');
        $redis->sAdd('key3', 'val4');
        $arr =($redis->sInter('key1', 'key2', 'key3'));//取交集 
        $this->assertTrue(in_array('val3', $arr) );
        $this->assertTrue(in_array('val4', $arr) );
        
        $redis->sInterStore('dst', 'key1', 'key2', 'key3');  //顺便保存
         $this->assertTrue( $redis->sIsMember('dst', 'val3') );
         //
         $redis->delete("key1");
         $redis->sAdd('key1' , 'member11');
         $redis->sAdd('key1' , 'member12');
         $redis->sAdd('key1' , 'member13'); /* 'key1' => {'member11', 'member12', 'member13'}*/
         $redis->sAdd('key2' , 'member21');
         $redis->sAdd('key2' , 'member22'); /* 'key2' => {'member21', 'member22'}*/
         $redis->sMove('key1', 'key2', 'member13'); /* 'key1' =>  {'member11', 'member12'} */
         /* 'key2' =>  {'member21', 'member22', 'member13'} */
         $this->assertTrue( !$redis->sIsMember('key1', 'member13') );
         $this->assertTrue( $redis->sIsMember('key2', 'member13') );
        
         $redis->delete("key1");
         $redis->sAdd('key1' , 'member1');
         $redis->sAdd('key1' , 'member2');
         $redis->sAdd('key1' , 'member3'); /* 'key1' => {'member1', 'member2', 'member3'}*/
         $redis->sRem('key1', 'member2', 'member3'); /*return 2. 'key1' => {'member1'} */
         $this->assertTrue( !$redis->sIsMember('key1', 'member3') );
         
         $redis->delete('s0', 's1', 's2');
         $redis->sAdd('s0', '1');
         $redis->sAdd('s0', '2');
         $redis->sAdd('s1', '3');
         $redis->sAdd('s1', '1');
         $redis->sAdd('s2', '3');
         $redis->sAdd('s2', '4');
         $arr = $redis->sUnion("s0", "s1","s2");
         $this->assertEqual(count($arr) , 4); //取并集
         //可以并集自动保存
         $redis->sUnionStore( "dst", "s0", "s1","s2");
         
    }
    
    public function test_list()
    {
        $redis = $this->getredis();
        $redis->rPush('key1', 'A');
        $redis->rPush('key1', 'B');
        $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
        $this->assertEqual($redis->lGet('key1', 0), "A");
        $this->assertEqual($redis->lGet('key1', -1), "C");
        $this->assertEqual($redis->lGet('key1', 10), false);
        
         $redis->delete('key1');
         $redis->lInsert('key1', \Redis::BEFORE, 'A', 'X'); /* 在a的后面插入x，但没有a，所以失败 */
         $this->assertEqual($redis->lLen('key1'), 0);
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'B');
        $redis->lPush('key1', 'C');
        $redis->lInsert('key1', \Redis::AFTER, 'C', 'X'); /* 4 */
        $arr = $redis->lRange('key1', 0, -1); /* array('C', 'X', 'B', 'A') */
        //dump($arr);
        $this->assertEqual($arr[1], "X");
        
        $arr = ["a"=>1,"b"=>'2'];
        $redis->set("aa", serialize($arr));
        $a =  unserialize( $redis->get("aa"));
        $this->assertTrue($a["a"]=== 1);
        $this->assertTrue($a["b"]=== '2');
        
        $redis->delete('key1');
        $redis->rPush('key1', 'A');
        $redis->rPush('key1', 'B');
        $redis->rPush('key1', 'C'); /* key1 => [ 'A', 'B', 'C' ] */
        $this->assertEqual($redis->lPop('key1'),  "A");
        $this->assertEqual($redis->lLen('key1'),2);
        //pushx函数不自动创建数组，所以失败
        $redis->delete('key1');
        $redis->lPushx('key1', 'A'); // returns 0
        $arr = $redis->lRange('key1', 0, -1);
        $this->assertEqual(count($arr), 0);
        $redis->lPush('key1', 'A'); // returns 1
        $arr = $redis->lRange('key1', 0, -1);
        $this->assertEqual(count($arr),1);
        
        $redis->delete('key1');
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'A');
        $redis->lPush('key1', 'B');
        $redis->lPush('key1', 'C');
        $redis->lPush('key1', 'X');
        $redis->lPush('key1', 'A');
        //$redis->lRange('key1', 0, -1); /* array('A', 'A', 'C', 'B', 'A') */
        $redis->lRem('key1', 'A', 44); /* 从左边移除该同样的元素n个 */
        $this->assertEqual($redis->lLen('key1'),  3);// X,C,B
        $redis->lSet('key1', 2, 'aa');
        $this->assertEqual($redis->lGet('key1',2),  'aa');
        
        $redis->delete('key1');
        $redis->rPush('key1', 'A');
        $redis->rPush('key1', 'B');
        $redis->rPush('key1', 'C');
        $redis->rPush('key1', 'D');
        $redis->rPush('key1', 'E');
        $redis->rPush('key1', 'F');
        $redis->lTrim('key1', 1, 3); //这个函数是修剪列表的意思。
        $a =$redis->lRange('key1', 0, -1); // ["B","C","D",]
        
        $redis->delete('x', 'y');
        $redis->lPush('x', 'abc');
        $redis->lPush('x', 'def');
        $redis->lPush('y', '123');
        $redis->lPush('y', '456');
        // move the last of x to the front of y.
        $this->assertEqual($redis->rPopLPush('x', 'y'),  "abc");
//         var_dump($redis->lRange('x', 0, -1));
//         var_dump($redis->lRange('y', 0, -1));
    }
   
    
    //谢烨，现在知道hash的错误了。
    //怪兽系统，应该使用同一个库。
    
    // 要点，这里只能保存二维数组。
    //
    public function test_hash()
    {
        $redis = $this->getredis();
        $redis->delete('h');
        $redis->hSet('h', 'key1', 'hello'); /* 1, 'key1' => 'hello' in the hash at "h" */
        $this->assertEqual($redis->hGet('h', 'key1'), "hello");
        
        $redis->hSet('h', 'key1', 'plop'); /* 0, value was replaced. */
        $this->assertEqual($redis->hGet('h', 'key1'), "plop");
        $redis->set("h",22);
        $this->assertEqual($redis->get('h'), "22");
       // $this->assertEqual($redis->hGet('h', 'key1'), "plop");
       
        $redis->delete('h');
        $redis->hSetNx('h', 'key1', 'hello'); 
        $redis->hSetNx('h', 'key1', 'world'); //这个函数，键已存在，则赋值失败，
        
        $this->assertEqual($redis->hGet('h', 'key1'), "hello");
        //无论哈希不存在，还是键不存在，都返回假。
        $this->assertTrue(false === $redis->hget('hh','hh') );
        $this->assertTrue(false === $redis->hget('h','hh') );
        //
        $redis->delete('h');
        $redis->hSet('h', 'key1', 'hello');
        $redis->hSet('h', 'key2', 'plop');
        $this->assertEqual($redis->hLen('h'), 2);
        
        $redis->hDel('h', 'key2');
        $this->assertEqual($redis->hLen('h'), 1);
        
        $redis->delete('h');
        $redis->hSet('h', 'a', 'x');
        $redis->hSet('h', 'b', 'y');
        $redis->hSet('h', 'c', 'z');
        $redis->hSet('h', 'd', 't');
        $arr = $redis->hKeys("h");
        $this->assertEqual($arr[0], 'a');
        $this->assertEqual($arr[3], 'd');
        $values = $redis->hVals("h");
        $this->assertEqual($values[0], 'x');
        $this->assertEqual($values[3], 't');
        $arr = $redis->hGetAll("h");
        $this->assertEqual($arr['a'], 'x');
        $this->assertEqual($arr['d'], 't');
        //查键是否存在
        $this->assertTrue($redis->hExists("h","a"));
        $this->assertTrue(!$redis->hExists("h","aaaa"));
        $redis->delete('h');
        $redis->hIncrBy('h', 'x', 2); //注意直接加，不设初始！！
        $redis->hIncrBy('h', 'x', 1); 
        $this->assertEqual($redis->hGet("h","x"), 3);
        //
        $redis->delete('user:1');
        $redis->hMSet('user:1', array('name' => 'Joe', 'score' => 22,"s3"=>33));
        $arr = $redis->hMGet('user:1',["score","s3"]);
        $this->assertEqual($arr["s3"], 33);
        
    }
    
    public function test_mset(){
        $redis = $this->getredis();
        $redis->mSet(array('key0' => 'value0', 'key1' => 'value1'));
        $this->assertTrue('value0' === $redis->get('key0') );
        $this->assertTrue('value1' === $redis->get('key1') );
    }
    
    public function test_range()
    {
        $redis = $this->getredis();
        $redis->set('key', 'string value');
        $this->assertTrue('string' === $redis->getRange('key', 0, 5) );
        $this->assertTrue('value' === $redis->getRange('key', -5, -1) );
        
        $redis->set('key', 'Hello world11');
        $redis->setRange('key', 6, "redi"); 
        $this->assertTrue("Hello redid11" === $redis->get('key') );
        
        $this->assertTrue(13 === $redis->strLen('key') );
        
        $redis->delete('s');
        $redis->sAdd('s', 5);
        $redis->sAdd('s', 4);
        $redis->sAdd('s', 2);
        $redis->sAdd('s', 1);
        $redis->sAdd('s', 3);
        $arr = $redis->sort('s');
      //  dump($redis->sort('s'));
        $this->assertTrue(1 == $arr[0] );
    }
    
    //返回所有键，判断值类型
    public function test_key()
    {
        $redis = $this->getredis();
        $redis->set("a2",100);
        $redis->set("aa",10);
        $arr = $redis->keys("a*");
        $this->assertTrue("a2" === $arr[0] );
        $this->assertTrue("aa" === $arr[1] );
        $this->assertTrue(\Redis::REDIS_STRING === $redis->type('aa') );
       //dump($redis->type('aa'));
        
        $redis->append("aa", 33);
        $this->assertTrue("1033" === $redis->get("aa") );
        
        //dump($redis->keys("a*"));
    }
    
    /**
     * 可以一次删多个.
     * 如果没有才设置
     * 自增，得到返回，且自增可以加没有键的值。
     * // 获取随机数
     */
    public function test_setnx()
    {
        $redis = $this->getredis();
        $redis->set("a2",100);
        $redis->set("aa",10);
        $redis->setNx("aa",20);
        $this->assertTrue("10" === $redis->get("aa") );
        $redis->del( array('aa',"a2"));
        $this->assertTrue(false === $redis->get("aa") );
        
       // $redis->set("a2",100);
        $result = $redis->incr("a22",2);
        $this->assertTrue(2 == $result );
        $result = $redis->incr("a22",20);
        $this->assertTrue(22 == $result );
        // 
        $result = $redis->incrByFloat("a23",2.2);
        $this->assertTrue(2.2 == $result );
        $result = $redis->incrByFloat("a23",20.3);
        $this->assertTrue(22.5 == $result );
        
        $arr = $redis->mGet(["a22","a23"]);
        $this->assertTrue(22.5 == $arr[1] );
        $this->assertTrue(22 == $arr[0] );
        
        $redis->set('x', '42'); 
        $exValue = $redis->getSet('x', 'lol');    // return '42',
        $this->assertTrue(42 == $exValue );
        //replaces x by 'lol' 
        $newValue = $redis->get('x');     // return 'lol'
        $this->assertTrue('lol' == $newValue );
        
        $key = $redis->randomKey();
       // dump($key);
        $this->assertTrue( $key );
    }
    
    /**
     * get 函数，如果库中都不存在，则返回false，所以用全等判断！！
     * set 不能直接存数组！！文档中说得很清楚！
     * redis 会把所有都当成字符型，哪怕你传给它整型！！
     */
    public function t1est_get()
    {
        $redis = $this->getredis();
        $redis->set("aa",10);
        $this->assertTrue(false=== $redis->get("a2") );
        $this->assertTrue(is_string($redis->get("aa")));
        $this->assertFalse(is_int($redis->get("aa")));
        
        $redis->set("ab",["aa"=>1,'b'=>["abc"=>100]]);
        $temp = $redis->get("ab");
        $this->assertTrue("Array" === $temp );
         //延时设置
        $redis->setEx("a2",1, 20);
        $this->assertTrue("20" === $redis->get("a2") );
        
        sleep(2);
        $this->assertTrue(false === $redis->get("a2") );
        $this->assertTrue(false === $redis->exists("a2") );
    }
    
    
    //测试键的个数
    public function test_count()
    {
        $redis = $this->getredis();
        $redis->set("aa",10);
        $redis->set("a2",10);
        
        $redis->select(15);
        $redis->set("a1",20);
        $redis->set("a2",20);
        $redis->set("a3",20);
        
        $redis->select(0);
        $this->assertEqual( 2 , $redis->dbSize() );
        $redis->select(15);
        $this->assertEqual( 3 , $redis->dbSize() );
    }
    
    /**
     * $redis->connect('127.0.0.1', 6379, 2.5);//仅保持2.5秒
     * select 为当前的连接选择数据库
     * 
     * bgSave();异步保存到硬盘。
     * config，设置或得到单个配置,因为各机器的配置不同，所以不好断言。
     * lastSave()，返回最后保存到硬盘的时间。
     * time(),返回服务器时间
     */
    public function test_connect(){
        $redis = $this->getredis();
        $s = $redis->info();
//         dump($redis->info("CPU"));
//         dump($redis->time() );
//         dump($redis->slowLog("get", 2) );
        
        $this->assertTrue( array_key_exists('redis_version', $s) );
       // $this->assertEqual( $s['redis_version'] ,'2.6.12' );
       // $this->assertPattern('#2.6#', $s['redis_version']); 
        //以下这句勿删。
     //    $this->assertEqual( $redis->config("GET", 'dir') ,['dir' => 'C:\redis' ] );
     
        
      //  dump( $redis->config("GET",'dir'));
        
        
    }
    
    public function test_ping(){
        $redis = $this->getredis();
        $this->assertEqual( $redis->ping() ,'+PONG' );
      //  echo $redis->ping();
    }
    
    /**
     * 已核实，最大的select是15！，即总共16个。
     * 同时测试 了默认的0库。
     */
    public function test_select_default(){
        $redis = $this->getredis();
        $redis->set("aa",10);
        $this->assertEqual( 10 , $redis->get("aa") );
    
        $redis->select(15);
        $redis->set("aa",20);
        $this->assertEqual( 20 , $redis->get("aa") );
    
        $redis->select(0);
        $this->assertEqual( 10 , $redis->get("aa") );
        
        $redis->select(15);
        $this->assertEqual( 20 , $redis->get("aa") );
    }
    
    /**
     * 测试不同的选择。
     */
    public function test_select(){
        $redis = $this->getredis();
     //   dump($redis->getDbNum());
        $redis->select(1);
     //   dump($redis->getDbNum());
        $redis->set("aa",10);
        $this->assertEqual( 10 , $redis->get("aa") );
        
        $redis->select(2);
        $redis->set("aa",20);
        $this->assertEqual( 20 , $redis->get("aa") );
        
        $redis->select(1);
        $this->assertEqual( 10 , $redis->get("aa") );
        
    }
    
    function setUp() {
        
        $redis = $this->getredis();
        $redis->flushAll();
        \BBExtend\Sys::display_all_error();
        
        
        $db = \BBExtend\Sys::get_container_db();
        $db->insert('bb_alitemp', ['url'=>'test']);
        
    }
    function setDown() {
        $redis = $this->getredis();
        $redis->flushAll();
    }
    
    private function getredis()
    {
        $redis = new \Redis();
        $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
        $redis->auth(Config::get('REDIS_AUTH'));
        return $redis;
    }
    
}
