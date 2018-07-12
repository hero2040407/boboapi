<?php
namespace app\apptest\controller;
use think\Config;

// require(  realpath(APP_PATH).  '/../extend/simpletest/autorun.php');
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 
 * @author Administrator
 *        
 */
class Mysql {
    
    public function index(){
       // echo 33;exit;
        $db=Sys::get_container_db_eloquent();
        $sql="select uid from bb_users_starmaker order by id asc";
        $uid_arr = DbSelect::fetchCol($db, $sql);
        dump($uid_arr);
        foreach ($uid_arr as $uid) {
            $sql="select sum(gold) from bb_record_invite_starmaker
 where starmaker_uid = ? 
   and status=3
";
            $count = DbSelect::fetchOne($db, $sql,[$uid]);
            $count = intval($count);
            $sql="update bb_users_starmaker set income=? where uid=?";
            $db::update($sql,[ $count, $uid ]);
            
            echo $count."\n";
            
        }
    }
    
    public function indexwe()
    {
        
        $db = Sys::get_container_db_eloquent();
        $db::table('web_article_media')->insert( [
                'article_id' => 2,
                'url' =>'/newsimg/bigimg1.jpg',
                'media_type' =>1,
                'sort'=>'1',
                'time_length' =>'0',
        ] );
        $db::table('web_article_media')->insert( [
                'article_id' => 3,
                'url' =>'/newsimg/smallimg1.jpg',
                'media_type' =>1,
                'sort'=>'1',
                'time_length' =>'0',
                
        ] );
        $db::table('web_article_media')->insert( [
                'article_id' => 3,
                'url' =>'/newsimg/smallimg2.jpg',
                'media_type' =>1,
                'sort'=>'2',
                'time_length' =>'0',
                
        ] );
        $db::table('web_article_media')->insert( [
                'article_id' => 3,
                'url' =>'/newsimg/smallimg3.jpg',
                'media_type' =>1,
                'sort'=>'3',
                'time_length' =>'0',
        ] );
        
        
        
        
        
        $db::table('web_article_media')->insert( [
                'article_id' => 4,
                'url' =>'/newsimg/big_video1.jpg',
                'media_type' =>1,
                'sort'=>'1',
                'time_length' =>'0',
        ] );
        
        $db::table('web_article_media')->insert( [
                'article_id' => 4,
                'url' =>'http://upload.guaishoubobo.com/6449346/20171214172549.mp4',
                'media_type' =>2,
                'sort'=>'1',
                'time_length' =>'00:10',
        ] );
        
        
        $db::table('web_article_media')->insert( [
                'article_id' => 5,
                'url' =>'/newsimg/small_video1.jpg',
                'media_type' =>1,
                'sort'=>'1',
                'time_length' =>'0',
        ] );
        
        $db::table('web_article_media')->insert( [
                'article_id' => 5,
                'url' =>'http://upload.guaishoubobo.com/cs10914/20180104153311.mp4',
                'media_type' =>2,
                'sort'=>'1',
                'time_length' =>'10:11:12',
        ] );
        
    }
    
    public function index7(){
        exit;
        
        $db = Sys::get_container_db_eloquent();
        $db::table('web_article')->insert( [
               'type' => 1,
               'title' =>'新闻1',
                'style' =>1,
                'source'=>'来源1',
                'create_time' => time(),
                'content' =>'123',
        ] );
        $db::table('web_article')->insert( [
                'type' => 1,
                'title' =>'新闻2',
                'style' =>2,
                'source'=>'来源2',
                'create_time' => time(),
                'content' =>'123',
        ] );
        $db::table('web_article')->insert( [
                'type' => 1,
                'title' =>'新闻3',
                'style' =>3,
                'source'=>'来源3',
                'create_time' => time(),
                'content' =>'123',
        ] );
        $db::table('web_article')->insert( [
                'type' => 1,
                'title' =>'新闻4',
                'style' =>4,
                'source'=>'来源4',
                'create_time' => time(),
                'content' =>'123',
        ] );
        $db::table('web_article')->insert( [
                'type' => 1,
                'title' =>'新闻5',
                'style' =>5,
                'source'=>'来源5',
                'create_time' => time(),
                'content' =>'123',
        ] );
        foreach (range(1,20) as $v)  {
            $db::table('web_article')->insert( [
                    'type' => 1,
                    'title' =>'新闻aaaa'.$v,
                    'style' =>5,
                    'source'=>'来源5',
                    'create_time' => time(),
                    'content' =>'123',
            ] );
        }
        
        
    }
    
    
    public function index4444444(){
        
        exit;
       // echo 1234;exit;
        $db = Sys::get_container_db();
        $dbe = Sys::get_container_db_eloquent();
        $db2 = Sys::getdb2();
        
        $time1 = strtotime('2017-12-21 00:00:00');
        // 对于每条短视频。查出 特定web点赞的赞数。再加上，如果为0放弃。
        
        $sql="select uid from bb_users  
where exists (select 1 from bb_msg_user_config
where bb_msg_user_config.uid = bb_users.uid
)
order by uid asc ";
        $query = $db->query($sql);
       
        $i=0;
        while($row = $query->fetch()) {
            
            // $real_like = $row['real_like'];
            $uid = $row['uid'];
            
            // uid, room_id, time, ip, count, datestr
            $sql="select count(*) from bb_msg_user_config
          where uid = ?
           ";
            $count = DbSelect::fetchOne($dbe, $sql,[ $uid ]);
            if ($count==20) {
                echo $i.':'. $uid.":". $count ."\n";
                $dbe::table('bb_msg_user_config')->insert(
                        [
                                "bigtype" =>0,
                                "uid" =>$uid,
                                "value"=>1,
                                "type" => 1100,
                                'title' => "微信消息推送",
                                'sort'  => 38,
                        ]
                        
                ) ;
            }
//             if (!$count) {
//                 continue;
//             }
             $i++;
             
            
        }
        
        
    }
    
    
    public function index6()
    {exit;
        $db = Sys::get_container_db();
        $dbe = Sys::get_container_db_eloquent();
        $db2 = Sys::getdb2();
        
        $time1 = strtotime('2017-12-21 00:00:00');
        // 对于每条短视频。查出 特定web点赞的赞数。再加上，如果为0放弃。
        
        $sql="select * from bb_record
          order by id asc ";
        $query = $db->query($sql);
        $i=0;
        while($row = $query->fetch()) {
            
            // $real_like = $row['real_like'];
            $id = $row['id'];
            
            // uid, room_id, time, ip, count, datestr
            $sql="select sum(count) from bb_record_like
          where datestr>0 and time < {$time1}
            and room_id = ?
           ";
            $count = DbSelect::fetchOne($dbe, $sql,[ $row['room_id'] ]);
            $count =intval( $count );
            if (!$count) {
                continue;
            }
            $i++;
            echo $i.':'. $id.":". $count ."\n";
            
            $sql = "update bb_record set `like`=`like`+{$count} , real_like=real_like +{$count} where id ={$id} ";
         //   $dbe->update($sql);
            $db2->query($sql);
            
        }
        
    }
    
    
    public function index5()
    {exit;
        $db = Sys::get_container_db();
        $dbe = Sys::get_container_db_eloquent();
        
        $sql="select * from t.bb_record_like 
          where time < 100000000
          order by id asc ";
        $query = $db->query($sql);
        $i=0;
        while($row = $query->fetch()) {
            $i++;
           // $real_like = $row['real_like'];
            $id = $row['id'];
            $time = $row['time'];
            $times = strval($time);
            $year = substr($times, 0,2);
            $month = substr($times, 2,2);
            $day   = substr($times, 4,2);
            
            
            
            $new_time = strtotime("{$year}-{$month}-{$day} 00:00:00");
            $new_time+= mt_rand( 8*3600, 20 * 3600 );
            
            
            echo $i.':'. $id.":". $time ."\n";
            // uid, room_id, time, ip, count, datestr
            
            
//             $arr= $this->set_zan($real_like);
            $dbe->table('bb_record_like')->insert([
                    'uid'=> $row['uid'],
                    'room_id' => $row['room_id'],
                    'time' => $new_time,
                    'ip'   => strval( $row['ip'] ),
                    'count' => intval( $row['count'] ),
                    'datestr' => $times,
            ]);
            
        }
        
    }
    
    public function index4(){exit;
    
        $db = Sys::get_container_db();
        $dbe = Sys::get_container_db_eloquent();
    
        $sql="select * from bb_record order by id asc ";
        $query = $db->query($sql);
        while($row = $query->fetch()) {
    
            $real_like = $row['real_like'];
            $id = $row['id'];
            echo $id.":". $real_like ."\n";
    
            $arr= $this->set_zan($real_like);
            $dbe->table('bb_record')->where('id', $id)->update([
                    'look'=> $arr['people'],
                    'like' => $arr['like'],
            ]);
            
        }
        
    
    }
    
    private function set_zan($count)
    {exit;
        if ($count > 1000) {
            $like = mt_rand(7000,8000) ;
            $people = $like* mt_rand( 3,6 )/2 ;
        }elseif($count > 200) {
            $like = mt_rand(5000,7000) ;
            $people = $like* mt_rand( 3,6 )/2 ;
        }elseif($count > 100) {
            $like = mt_rand(3000,5000) ;
            $people = $like* mt_rand( 3,8 )/2 ;
        }elseif($count ==0) {
            // 百分之20概率是0
            if (mt_rand(1,100) < 20 ){
                $like = 0 ;
                $people = 0;
            }else {
                $like = mt_rand(0,3000)  ;
                $people = $like* mt_rand( 2,8 ) ;
            }
        }else{
                $like = mt_rand(0,3000);
                $people = $like* mt_rand( 2,8 ) ;
        }
        return ['like'=>$like, 'people'=> (int)$people ];
        
    }
    
    
    public function index3(){
        exit;
        $db = Sys::get_container_db();
         $dbe = Sys::get_container_db_eloquent();
        
        $sql="select * from bb_record order by id asc ";
        $query = $db->query($sql);
        while($row = $query->fetch()) {
            
            $sql="select count(*) from bb_record_like where room_id =?";
            $count = DbSelect::fetchOne($dbe, $sql,[$row['room_id'] ]);
            
            $dbe::table("bb_record")->where('room_id', $row['room_id'] )->update([ 'real_like'=> 
                    $count ]);
            echo $row['id'].":". $count ."\n";
            
        }
        
        
        
    }
    
    
    public function index2()
    {
        exit;
        
$db = Sys::get_container_db();
$sql="select table_name,engine from information_schema.tables 
where table_schema='bobo'
        and engine='MyISAM'
        ";
$result = $db->fetchAll($sql);

$i=0;

foreach ($result as $v) {
    if ($v['table_name'] == 'bb_user_weixin_push_log' ) {
        $i==1;
    }
    
    
    $sql="repair table {$v['table_name']}  ";
    $db->query( $sql);
    echo $sql;
    echo "\n";
   // break;
}


// dump($result);

//         $redis = new \Redis();
//         $redis->connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
//         $redis->auth(Config::get('REDIS_AUTH'));
//         return $redis;
    }
}
