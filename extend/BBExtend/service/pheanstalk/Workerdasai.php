<?php
namespace BBExtend\service\pheanstalk;
use Pheanstalk\Pheanstalk;
/**
 * 消息队列。pheanstalk
 * 
 * 本队列的功能：处理导师抢单。
 * 
 * @author 谢烨
 *
 * xieye: 20171016
 * 这是服务端代码，使用方法如下，只有两句话。
 * 
 *  $worker = new \BBExtend\service\pheanstalk\Worker();  
 *  $worker->run();  
 *
 * 如果把程序放后台执行
 *  ( /usr/bin/php /var/www/html/public/index.php /systemmanage/phean/run >> /tmp/phean.log 2>&1 &)
 *
 *
 * 消息队列服务启动停止,注意，停止会自动清空所有消息。
 * service beanstalkd start
 * service beanstalkd stop
 * 
 * 
 * 
 * 代码修改，每发一种消息，
 * 1、message类，添加发送type
 * 
 * 2、WorkerJobPush类，修改工厂方法。
 * 3、type目录下，添加一个文件，做实际的操作。
 * 4、修改代码
 * $client = new \BBExtend\service\pheanstalk\Client();
   $client->add_dianping(
      new \BBExtend\service\pheanstalk\Datadp($uid, $record_id, time()  )
   );
 * 
 * 
 */
class Workerdasai
{
    private $pheanstalk;//这是服务
    // 设置队列名称。表示点评抢单
    const queue_name ="dasai";
    
    public function __construct() {
         
        $this->log('starting');
        $this->pheanstalk = new Pheanstalk('127.0.0.1:11300');
    }
    
    /**
     * 永久执行，通过while(true)
     */
    public function run() {
        $this->log('starting to run dasai');
    
        while(true) {
            $job = $this->pheanstalk->watch( self::queue_name )->ignore('default')->reserve();
          //  $job_data = unserialize( $job->getData());
            $job_data =  $job->getData() ;
            // 真正工作的就这句。
            $this->excute($job_data);
            //删除千万不能忘记。
            $this->pheanstalk->delete($job);
    
            $memory = memory_get_usage();
            //这是一个保险措施。也可以去除。
            if($memory > 100000000) {
                $this->log('exiting run due to memory limit');
                exit;
            }
        }
    }
    
    
   
    
    /**
     * 抢单处理。
     * 
     * @param Data $data
     */
    private  function excute( $data)
    {
        // 谢烨，这分好几种情况，如果名字已经存在，且此单未点评，则必定运行。
        // 如果名字不存在，则为抢单，此时，先检查其余短视频，已邀请当前星推官，且未完成的情况，如有，则返回错误
//         $uid =  $data->get_uid();
//         $id = $data->get_id();
//         $time = $data->get_time();
        
//         $type = $data->get_type();
        
//         if ($uid < 0) {
//             return $this->testlog($uid,$type);
//         }
        
//         if ( $type==1 ) {
//             $this->excute_1($uid, $id);
//         }
//         if ( $type==2 ) {
//             $this->excute_2($uid, $id);
//         }
        
        $data = urlencode($data);
        $command = "/usr/bin/php /var/www/html/public/index.php /systemmanage/phean_process/dasai {$data}";
        
        $out = shell_exec ( $command );
        
    }
    
    /**
     * 
     * @param unknown $uid
     * @param unknown $id
     */
    private function excute_1($uid, $id)
    {
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        
        $sql="select * from ds_register_log where id=?";
        $row= $db->fetchRow($sql,[ $id ]);
        $uid = $row['uid'];
        
        $sql="select original from  bb_users_platform where type=3 and uid = ?";
        $phone = $db->fetchOne($sql,[ $uid ]);
        if (!$phone) {
            $phone = $row['phone'];
        }
        $name = $row['name'];
        $boboid = $uid;
        $birthday = $row['birthday'];
        $sex = $row['sex'];
        
        $data = [
          'phone'=>$phone,
                'name'=>$name,
                'boboid'=>$boboid,
                'birthday'=>$birthday,
                'sex'=>$sex,
        ];
        $url = "http://47.104.197.175/api/v1/auth/bobo";
        $response = \Requests::post( $url ,[ ], $data);
        
//         $result = json_decode ( $response->body, 1 );
    }
    
    
    private function excute_2($uid, $id)
    {
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        
        $sql="select * from bb_record where id=?";
        $row= $db->fetchRow($sql,[ $id ]);
        $uid = $row['uid'];
        
        $pic = \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                $row['big_pic'] , $row['thumbnailpath'] );
        
        $data = [
                'path'=>$row['video_path'],
                'cover'=>$pic,
                'boboid'=>$uid,
                
        ];
        $url = "http://47.104.197.175/api/v1/video/bobo";
        $response = \Requests::post( $url ,[ ], $data);
        
    }
    
    
    private function log($txt) {
        echo "worker: ".$txt."\n";
    }
    
    private function testlog($uid,$type)
    {
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        $db->insert("bb_alitemp", [
                'create_time' => date("Y-m-d H:i:s"),
                'uid' => $uid,
                'content'=> "当前时间".date("Y-m-d H:i:s")." 这是一个phean的<b>大赛队列,type:{$type}</b>的测试。传入的随机数是"  ,
        ]);
    }
    
}


