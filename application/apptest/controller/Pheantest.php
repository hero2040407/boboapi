<?php
namespace app\apptest\controller;


use BBExtend\Sys;
use BBExtend\DbSelect;


class Pheantest extends \think\Controller
{
    //本函数别改。
    public function index()
    {
        $client = new \BBExtend\service\pheanstalk\Client();
        $uid=-1;
        $type=1900;
        $random = mt_rand(10000,99999);
        $client->add(
                new \BBExtend\service\pheanstalk\Data($uid, $type ,['random' => $random,], time()  )
                );
        echo "产生的随机数是：{$random}<br>";
        echo "-------------  打印队列返回开始         ------------<br>";
        sleep(1);
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_alitemp 
          where uid=? order by id desc limit 1";
        $row = DbSelect::fetchRow($db, $sql,[ $uid ]);
        dump($row);
        echo "---------------    打印队列返回 end       ------------<br>";
        echo "请自行比较上面的随机数。<br><br><br><br>";
        
        echo "---------------    下面测试第2个队列程序       ------------<br>";
        $uid=-2;
       
        $channel = mt_rand(10000,99999);
        
        $client->add_dianping(
                new \BBExtend\service\pheanstalk\Datadp($uid, 1, time(),$channel  )
                );
        
        echo "产生的随机数是：{$channel}<br>";
        echo "-------------  打印队列返回开始         ------------<br>";
        sleep(1);
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_alitemp
          where uid=? order by id desc limit 1";
        $row = DbSelect::fetchRow($db, $sql,[ $uid ]);
        dump($row);
        echo "---------------    打印队列返回 end       ------------<br>";
        echo "请自行比较上面的随机数。<br><br><br><br>";
        
        
    }
    
   
}
