<?php
//namespace app\command\controller;


class Myjob  
{
   
    public function perform()
    {
        // Work work work
       // echo $this->args['name'];
       
        echo time();
        
        $db = \BBExtend\Sys::getdb();
        $sql="select * from bb_users limit 1"; //随便写一个数据库查询。
        $result = $db->fetchRow($sql);
        var_dump($result['nickname']);
//         echo "\n";
        
        $config = new \Doctrine\DBAL\Configuration();
        //..
        $connectionParams = array(
            'dbname' => 'bobo',
            'user' => 'root',
            'password' => 'ChenyueAbc.123',
            'host' => '127.0.0.1',
            'driver' => 'pdo_mysql',
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        $row = $conn->fetchAssoc($sql);
        echo $row["nickname"];
        echo "\n";
        
       // echo time(). $result."\n";
    }
    
   

    
    
}