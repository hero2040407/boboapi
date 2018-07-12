<?php
namespace app\apptest\controller;
use app\user\controller\User;


use think\Request;
use  think\Db;
use BBExtend\BBRedis;
use think\Session;
use think\Config;
use BBExtend;
use BBExtend\Sys;

// use BBExtend\message\Simple;
use BBExtend\message\Message;


use BBExtend\user\check\Record as RecordCheck;
use BBExtend\BBRecord;


class Index extends \think\Controller
{
    public $aaa =1;
    
    public $dat = ['aa'=>1,'bb'=>2];   
    
    public function getip(){
        echo $_SERVER['REMOTE_ADDR'];
    }
    
    //本函数别改。
    public function userinfo($uid=0)
    {
        
        // 现在查名次
        $caifu = \BBExtend\user\Ranking::getinstance($uid)->get_caifu_ranking();
        $fensi = \BBExtend\user\Ranking::getinstance($uid)->get_fensi_ranking();
        $level = \BBExtend\user\Ranking::getinstance($uid)->get_dengji_ranking();
        dump(["财富排行"=> $caifu,
            "粉丝排行" => $fensi,
            "等级排行" => $level,
        ]);
        
        $temp =  \BBExtend\BBUser::get_user($uid);
       //  dump($temp);
         $db = Sys::get_container_db();
         $sql="select * from bb_users where uid=".intval($uid);
         dump($db->fetchRow($sql));
         
         $sql="select * from bb_currency where uid=".intval($uid);
         dump($db->fetchRow($sql));
         
        
         
    }
    
    public function pdo2()
    {
        echo 1;
        \BBExtend\Sys::display_all_error();
        $dbms   = 'mysql';
        $host   = '10.0.0.88';
        //数据库主机名
        $dbName = 'bobo';
        //使用的数据库
        $user   = 'root';
        //数据库连接用户名
        $pass   = 'xf1980';
        //对应的密码
        $dsn    = "mysql:host=$host;dbname=$dbName";
        echo 3;
        $db = new \PDO($dsn, $user, $pass);
        echo 2;
        
        $sql="set names utf8";
        $db->exec($sql);
        
        $sql= 'select * from bb_users limit 1';
        $query = $db->query($sql);
        echo 5;
        $row = $query->fetch();
        echo 6;
        var_dump($row);
//         dump($row);
    }
    
    public function message()
    {
        $time  = date("Y-m-d H:i:s");
        $time1  = preg_replace('#\d\d$#', '00', $time);
        $time1 = strtotime($time1);
        $time2 = $time1 + 59;
        
        
//         Message::get_instance()
//         ->set_title('系统消息')
//         ->add_content(Message::simple()->content('哈哈成为了您的新粉丝。'))
//         ->set_type(113)
//         ->set_uid(12431)
//         ->send();
//         echo "ok";
        
    }
    
    public function node()
    {
        
//         uid int not null default 0,
//         uname varchar(255) not null default '',
//         content varchar(255) not null default '',
//         created_at int  not null default 0,
//         to_uid int not null default 0 ,
//         to_uname varchar(255) not null default '',
        
        
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $content = input('?param.content')?input('param.content'):'';
        $to_uid = input('?param.to_uid')?(int)input('param.to_uid'):0;
        $uname = input('?param.uname')?input('param.uname'):'';
        $to_uname = input('?param.to_uname')?input('param.to_uname'):'';
        $created_at = time();
        
        
         $params = array(
                'host'     => 'localhost',
                'username' => 'root',
                'password' => 'root',
                'dbname'   => 'test',
                'charset'  => 'utf8', //其会自动执行 set names utf8

                'port'     => '3306',
                'driver_options'=> [
                    \PDO::ATTR_STRINGIFY_FETCHES => false,
                    \PDO::ATTR_EMULATE_PREPARES  => false,
                ],
            );
            
            
            $db = \Zend_Db::factory('pdo_mysql', $params);
        $db->insert("liangpiao_type", [
            'uid' =>$uid,
            'content' =>$content,
            'to_uid' =>$to_uid,
            'uname' =>$uname,
            'to_uname' =>$to_uname,
            'created_at' =>time(),
            
        ]);
        
    }
    
    public function test_redis()
    {
        $redis2 = Sys::getredis11();
        $key="name1";
        $redis2->set($key,date("H:i:s"));
        echo "当前时间是".$redis2->get($key);
        echo "<br>有时间显示说明redis功能正常。";
        
        $key="name2";
        $redis = new \Redis();
        $redis->connect('127.0.0.1','6380');
        //$redis->select(1);
        $redis->set($key,date("H:i:s"));
        echo "<br>当前时间是".$redis->get($key);
        echo "<br>有时间显示说明redis6380功能正常。";
        
    }
    
    
    public function index23()
    {
        $img='/uploads/race/20170329/7151b334a9a324ffcf77fb51c3a5beb3.jpg';
        //假如是外网文件，则不管
        if (preg_match('#^http#', $img)) {
            return false;
        }
        
        // 假如参数以/uploads开头，自动加/var/www/html/public前缀路径
        if (preg_match('#^/uploads#', $img)) {
            $img =  ROOT_PATH ."public" . $img;
        }
        $img = realpath($img);
        if (!$img) {
            return false; //如果文件不存在，返回false
        }
        
        //现在分离文件名，文件名前缀如123，文件名后缀如jpg
        $pre = preg_replace('#^(.+?)\.[^.]+$#', '$1', $img);
        $post = preg_replace('#^.+?\.([^.]+)$#', '$1', $img);
        $new_img = $pre ."_gray." . $post; // 该变量含路径，
        
        $command ="convert {$img} -colorspace Gray {$new_img}";
        shell_exec ( $command );
        return $new_img;
      
    }
    
    
    public function index22()
    {
        $db = Sys::get_container_db();
        $act_id_arr =[83,84,90];
        $zong_ds_map = [
            83=>17,
            84=> 9,
            90 => 59,
        ];
        $qudao_map = [
            83=>18,
            84=> 11,
            90 => 60,
        ];
        
        foreach ($act_id_arr as $act_id) {
            $zong_ds_id = $zong_ds_map[$act_id];
            $qudao_id = $qudao_map[$act_id];
            //先查bb_record
            $sql ="select * from bb_record where type=2 and 
                      is_remove=0 and audit=1 and activity_id={$act_id}";
            $record_arr =$db->fetchAll($sql);
            // 谢烨，先确保给register_ar注册
            // 先查有没有
            foreach ($record_arr as $record) {
                $uid = $record['uid'];
                $record_id = $record['id'];
                $sql="select * from ds_register_log where zong_ds_id={$zong_ds_id}
                   and uid = {$uid}
                ";
                $has_join = $db->fetchRow($sql);
                if (!$has_join) {
              //      Sys::debugxieye("给uid:{$uid}自动报名，大赛id:{$zong_ds_id}\n");
                    /////..自动报名
                }
                $sql="select * from ds_record where ds_id={$zong_ds_id}
                and uid = {$uid}
                ";
                $has_join = $db->fetchRow($sql);
                if (!$has_join) {
            //        Sys::debugxieye("给uid:{$uid}上传视频，视频id:{$record_id}，大赛id:{$zong_ds_id}\n");
                    /////..自动上传视频
                    /// 改视频id，
                    // 去除此人在此活动的记录select count(*) from bb_user_activity where uid={$uid}
       //and activity_id = {$act_id}
                    /// 加入到alitemp表           
                    
                }
                
                
            }
            
            
        }
        
        
        
//        $arr=[
//          "ar1"=>[
//              ["ar2"=>1,"ar23"=>1,],
//              ["ar2"=>1,"ar23"=>1,],
//          ]  ,
//            "ar12"=>[
//                ["ar2"=>1,"ar23"=>1,],
//                ["ar2"=>1,"ar23"=>1,],
//            ]  ,
//        ];
//        dump($arr);
    }
    
    
    public function index26(){
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql="select * from bb_users order by uid asc";
        $query = $db->query($sql);
        $i=0;
        while ($row=$query->fetch()) {
            $i++;
            $speciality_list = $row['specialty'];
            $uid = $row["uid"];
         //   echo $row["uid"]."\n";
             $temp = json_decode($speciality_list,true);
            $temp2 =[];
            foreach ($temp as $v) {
                $temp2[]= intval($v);
            }
            
            sort($temp2);
            
            $new_speciality_list = json_encode($temp2);
            $count1 = count($temp2);
            
            $sql="select hobby_id from bb_user_hobby where uid = {$uid} order by hobby_id";
            $hos = $db2->fetchCol($sql);
            $hos = (array)$hos;
            $new_speciality_list2 = json_encode($hos);
            
            if ($new_speciality_list != $new_speciality_list2) {
                echo "{$uid} :" .$new_speciality_list."....".$new_speciality_list2."\n";
            }
           // echo $new_speciality_list."....".$new_speciality_list2."\n";
            
            if ($i%1000==0) {
                echo "...\n";
            }
        }
        echo "all ok\n";
    }
    
    
    public function index244()
    {
        $dbms   = 'mysql';
        $host   = '127.0.0.1';
        //数据库主机名
        $dbName = 'bobo';
        //使用的数据库
        $user   = 'root';
        //数据库连接用户名
        $pass   = 'ChenyueAbc.123';
        //对应的密码
        $dsn    = "mysql:host=$host;dbname=$dbName";
//         echo 3;
        $db = new \PDO($dsn, $user, $pass);
//         echo 2;
    
        $sql="set names utf8";
        $db->exec($sql);
        
        $db2 = Sys::getdb2();
        $sql ="select max(id) from bb_user_suiji";
        $max_id = $db2->fetchOne($sql);
    
        $query = "insert into bb_user_suiji (id) values(?)";
        foreach (range(1,99) as $v) {
            $query.= ",(?)";
        }
        
        $stmt = $db->prepare($query);
        //$stmt->blinparam(':id',1);
        
        
//         $stmt = $dbh->prepare("select * from test where name = ?");
        $ii=$max_id+1;
        $aa=0;
        for($i=$ii;$i <=8000000;$i=$i+100) {
            $temp =[];
            foreach (range(0,99)  as $v ) {
                $temp[]= $i+$v;
            }
//             var_dump($temp);

            
            $stmt->execute($temp);
            $aa++;
            if ($aa%10000==0) {
                echo $i."\n";
            }
        }
    
      
        echo "all ok\n";
    }
    
    public function index24()
    {
        
       $db = Sys::get_container_db();
       for($i=3000000;$i <=8000000;$i++) {
           $db->insert("bb_user_suiji", [
               "id" => $i
           ]);
           if ($i%1000==0) {
               echo $i."\n";
           }
       }
       echo "all ok\n"; 
    }
    
    public function index25()
    {
       echo <<<html
<script type="text/javascript" src="http://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
<script>
  
  var id = Math.floor(Math.random()*100)+1;
  $("#id1").val('');
  $("#div1").html('');
  $("#div1").html($("#div1").html() +"<br>"+'开始聊天了！' );
  data_arr=[];

    function div33(){
      $("#div1").html(  $("#div1").html()+  "<br>"+ $("#id1").val()  );
  }
</script>
<center>
<h2>聊天室</h2>
<div id='div1' style='text-align:left; width:500;height:300px;border:1px solid red;overflow:auto'></div>
<br>

<input id="id1" style='width:300px;height:50px;' type="text" /> &nbsp;
&nbsp;&nbsp;&nbsp;
<button onclick="div33()">发送消息</button>
</center>
               
       
html;
        
    }
    
    
    function bubbleSort($arr)
    {
        $len=count($arr);
        //该层循环控制 需要冒泡的轮数
        for($i=1;$i<$len;$i++)
        { //该层循环用来控制每轮 冒出一个数 需要比较的次数
            for($k=0;$k<$len-$i;$k++)
            {
                if(($arr[$k]>$arr[$k+1]) || ($arr[$k]==0) )
                {
                    $tmp=$arr[$k+1];
                    $arr[$k+1]=$arr[$k];
                    
                    $arr[$k]=$tmp;
                    dump($arr);
                }
            }
        }
        return $arr;
    }
    
    public function clear_redis()
    {
        
       
          $redis = Sys::getredis11();
          $redis->flushAll();
         echo "redis is clear;";
//         }else {
//             echo "not 245";
//         }
        
    }
    
    /**
     * 测试设计模式
     */
    public function rank1()
    {
       $db = Sys::get_container_db();
       $sql="select * from bb_record order by id desc limit 100";
       $rows = $db->fetchAll($sql);
       foreach ($rows as $row) {
           $obj = RecordCheck::get_check_instance($row);
           $obj->success();
           $obj->fail();
           echo "<br>";
       }
    }
   
    /**
     * 测试 消息对象集群
     */
    public function xiaoxi()
    {
//         $aa = \BBExtend\message\MessageType::get_instance_string();
//         $aa->set_title(11);
//         $aa->set_content(234);
      //  echo 33;
        $message = Message::get_instance()
            ->set_title('经验提升')
            ->add_content(Message::simple()->content('你好')->color('红色'))
            ->add_content(Message::simple()->content('你好2')->color('黑色'))
            ->set_type(100)
            ->set_uid(1)
            ->send();
                      
        
    }
    
    
   
   
    
    public function mysql()
    {
        
        echo session_save_path();
        
        $user = Db::table('bb_users')->find();
        dump($user);
        echo "<br>mysql服务测试通过<br>";
        $redis = new \Redis();
        // $redis->connect("10.0.0.88");
        $redis->connect("127.0.0.1");
        $redis->auth('ChenyueAbc.123');
        $redis->set("a","你好");
         echo $redis->get("a");
         echo "<br>";
         echo "上面一行出现你好表示redis测试通过";
    }
    
    
   
    
    
    public function session(){
        Session::set('aa','thinkphp');
        //Session::get('name');
       // $_SESSION['aa']=224;
        echo "<a href='/apptest/index/session2'>跳转</a>";
    }
    
    public function session2(){
       echo Session::get('aa');
    }
    
    public function phpinfo()
    {
        echo "machine name :". get_cfg_var('guaishou.username') ."<br\n>";
        phpinfo();
        
    }
    
    public function ds_clean()
    {
        $db = Sys::get_container_db();
        $arr=[
            "delete from ds_dangan where uid=10023",
            "delete from ds_money_log where uid=10023",
            "delete from ds_money_prepare where uid=10023",
            "delete from ds_record where uid=10023",
            "delete from ds_register_log where uid=10023",
        ];
        foreach ($arr as $v) {
            $db->query($v);
        }
        echo " ok\n";
    }
    
    public function wxtest()
    {
        $serial = "BA".mt_rand(1000, 9999);//订单号
        
        $order = new \app\shop\model\ShopOrderPrepare();
        $order->uid = 10046;
        $order->goods_id = 1;
        $order->address_id = 1;
        $order->serial = $serial;
        $order->data('type',1); //1表示现金购买。
        $order->price = 0.01;
        $order->is_success=0;//等订单正式生成，要改这个字段。
        $order->data('count', 1);
        $order->data('model', '');
        $order->data('style', '');
        $order->data('terminal','');
        $order->data("terminal_type", 1);
        $order->data("third_name",'wx');
        $order->data("third_serial", "");
        $order->save();
        
        
        $obj = new \BBExtend\pay\wxpay\Help();
        return $obj->tongyi_xiadan("鞋子",$serial, 2 );
        
    }
    
    public function serverurl()
    {
        $temp = \BBExtend\common\BBConfig::get_server_url();
        echo  $temp;
    }
    
    public function wxquery($order)
    {
        $obj = new \BBExtend\pay\wxpay\Help();
        return $obj->query_remote($order);
    }
    public function aliquery($order)
    {
        $obj = new \BBExtend\pay\alipay\AlipayHelp();
        $result = $obj->query_remote($order);
        return $result;
    }
    
    public function debugtest()
    {
    //    \BBExtend\Sys::debugxieye("debugxieye");
    //    \BBExtend\Sys::debug("debug123");
        echo "ok";
    }
    
    public function xiufu_user()
    {
        $db = Sys::get_container_db();
        $sql = 'select uid, specialty from bb_users where specialty like \'%null%\'';
//         $sql ='select uid, specialty from bb_users where specialty like \'%"%\'';
        $result = $db->fetchAll($sql);
        dump($result);
        
        $sql = " update bb_users set specialty='' where specialty='null' ";
        $db->query($sql);
        
        
        
        
        
        $sql ='select uid, specialty from bb_users where specialty like \'%"%\'';
        $result = $db->fetchAll($sql);
        dump($result);
        
        foreach ($result as $v) {
            $temp = preg_replace('#"#', '', $v['specialty']);
            $sql ="update bb_users set specialty='{$temp}' where uid= {$v['uid']} ";
            echo $sql."<br>";
            $db->query($sql);
        }
        
    }
    
    
    public  function alitemp()
    {
       
        return ['aa'=>1,'b'=>2];
    }
    
    public static  function alitemp2()
    {
        $db = Sys::get_container_db();
        $sql="SELECT sh1.id FROM bb_user_suiji AS sh1
inner JOIN
(SELECT ROUND(RAND() * 5000000 + 3000000) AS id) AS sh2
WHERE
not exists (select 1 from bb_users where bb_users.uid = sh1.id)
and sh1.id>=sh2.id
limit 1
               ";
        $id = $db->fetchOne($sql);
        if ($id) {
            return $id;
        }else {
            return self::alitemp2();
        }
        
    }
   
}
