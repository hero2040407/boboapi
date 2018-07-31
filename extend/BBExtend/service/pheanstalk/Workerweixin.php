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
class Workerweixin
{
    private $pheanstalk;//这是服务
    // 设置队列名称。表示点评抢单
    const queue_name ="workerweixin";
    
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
        
        $data = urlencode($data);
        $command = "/usr/bin/php /var/www/html/public/index.php /systemmanage/phean_process/weixin {$data}";
        
        $out = shell_exec ( $command );
        
    }
    
    
    
    private function log($txt) {
        echo "worker: ".$txt."\n";
    }
    
  
    
}


