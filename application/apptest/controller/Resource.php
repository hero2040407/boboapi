<?php
namespace app\apptest\controller;


use Pheanstalk\Pheanstalk;
/**
 * 这是门面程序。
 * 使用之前，需要beanstalkd服务已经在本机启动。
 * 我是用框架执行的，如果没有框架，需写成两个php文件。
 *
 * @author Administrator
 *
 */
class Resource
{
    /**
     * 这是添加消息到队列。
     */
    public function add()   {
        $beanstalkd = new Pheanstalk('127.0.0.1', '11300');
        //这是消息数据，在本demo中，type不能省略。区分任务类型。
        $data = array(
                'type'   => mt_rand(1,2),
                'mobile' => '13051662435',
                'id'     => mt_rand(100,200),
                'time'   => date("Y-m-d H:i:s"),
        );
        $delay = (int) strtotime($data['time']) - time();
        $delay=0;
        
        // 把消息放入队列,1024 是优先级
        //$delay 非常重要，假设设置10，则该消息10秒后才被放入队列！非常好使。
        $beanstalkd->useTube( Worker::queue_name  )
        ->put(serialize($data), 1024, $delay);
    }
    
    /**
     * 这是运行队列管理器。
     */
    public function run()
    {
        $worker = new Worker();
        $worker->run();
    }
    
    
    
}

/**
 * 这是真正的任务执行1
 * @author Administrator
 *
 */
class WorkerJob1
{
    public function excute($data)
    {
        file_put_contents('/tmp/1.txt', 'job:type1:'.print_r($data, 1),FILE_APPEND );
        
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        $db->insert("bb_alitemp", [
                'create_time' => date("Y-m-d H:i:s"),
                'uid' => -3,
                'content'=> "当前时间".date("Y-m-d H:i:s")." 这是一个phean的<b>点评队列</b>的测试。传入的随机数是"  ,
        ]);
        
        
        echo "---\n";
        echo  ('job:type1:'.print_r($data, 1));
        echo "---\n";
    }
}

/**
 * 这是真正的任务执行2
 * @author Administrator
 *
 */
class WorkerJob2
{
    public function excute($data)
    {
        file_put_contents('/tmp/1.txt', 'job:type2:'.print_r($data, 1),FILE_APPEND );
        echo "---\n";
        echo  ('job:type2:'.print_r($data, 1));
        echo "---\n";
    }
}

/**
 * 这是队列管理器守护进程，最好用supervisord这个软件支持一下。
 且应该放在linux的后台执行
 * @author Administrator
 *
 */
class Worker {
    
    private $job1; //任务对象
    private $job2; //这是任务对象
    private $pheanstalk;//这是服务
    // 设置队列名称。随意起
    const queue_name ="aaa";
    
    public function __construct() {
        
        $this->log('starting');
        $this->pheanstalk = new Pheanstalk('127.0.0.1:11300');
        $this->log('starting2');
//         dump ( $this->pheanstalk->status() );
//         exit;
        $this->job1 = new WorkerJob1();
        $this->job2 = new WorkerJob2();
    }
    
    public  function excute($data)
    {
        $this->job1->excute($data);
        
//         if ($data['type']==1) {
            
//         }elseif ($data['type']==2) {
//             $this->job2->excute($data);
//         }
    }
    
    public function run() {
        $this->log('starting to run');
        
        while(true) {
            $job = $this->pheanstalk->watch( self::queue_name )->ignore('default')->reserve();
            $job_data = unserialize( $job->getData());
            
            // 真正工作的就这句。
            $this->excute($job_data);
            //删除千万不能忘记。
            $this->pheanstalk->delete($job);
            
          
        }
    }
    
    private function log($txt) {
        echo "worker: ".$txt."\n";
    }
}
