<?php
namespace BBExtend\service\pheanstalk;
use Pheanstalk\Pheanstalk;
use BBExtend\Sys;

/**
 * 消息队列。pheanstalk
 * @author Administrator
 *
 * xieye: 20171016
 * 这是客户端代码，使用方法如下，只有两句话。专用于添加消息到队列。
 * 
 *  $client = new \BBExtend\service\pheanstalk\Client();
 *  $client->add(
 *      new Data(5850400,1010,['other_uid' => 11136,], time()  )
 *  );  
 *
 */
class Client
{
    /**
     * 把消息放入队列
     * @param Data $data 所有消息的统一对象，由info字段确保各自的差别。
     */
    public function add(Data $data)
    {
        if ( Sys::is_phpunit_server() ) {
            return ;
        }
        
      //  if (\BBExtend\Sys::is_product_server()) {
        
            $beanstalkd = new Pheanstalk('127.0.0.1', '11300');
            //这是消息数据，在本demo中，type不能省略。区分任务类型。
    //         $delay = (int) strtotime($data['time']) - time();
            $delay=0;
            
            // 把消息放入队列,1024 是优先级
            //$delay 非常重要，假设设置10，则该消息10秒后才被放入队列！非常好使。
            $beanstalkd->useTube( Worker::queue_name  )
                ->put(serialize($data), 1024, $delay);
     //   }
    }
   
    /**
     * 这是点评抢单的 下单接口
     * @param Datadp $data
     */
    public function add_dianping(Datadp $data)
    {
     //   if (\BBExtend\Sys::is_product_server()) {
            
        if ( Sys::is_phpunit_server() ) {
            return ;
        }
        
            $beanstalkd = new Pheanstalk('127.0.0.1', '11300');
            //这是消息数据，在本demo中，type不能省略。区分任务类型。
            //         $delay = (int) strtotime($data['time']) - time();
            $delay=0;
            
            // 把消息放入队列,1024 是优先级
            //$delay 非常重要，假设设置10，则该消息10秒后才被放入队列！非常好使。
            $beanstalkd->useTube( Workerdp::queue_name  )
              ->put(serialize($data), 1024, $delay);
    //    }
        
    }
    
    // 完全测试用
    public function add_dianpingtest(Datadp $data)
    {
        
        if ( Sys::is_phpunit_server() ) {
            return ;
        }
        
        $beanstalkd = new Pheanstalk('127.0.0.1', '11300');
        $delay=0;
        $beanstalkd->useTube( Workerdptest::queue_name  )
          ->put(serialize($data), 1024, $delay);
    }
    
    
    /**
     * 这是点评抢单的 下单接口
     * @param Datadp $data
     */
    public function add_dasai(DataDasai $data)
    {
        //   if (\BBExtend\Sys::is_product_server()) {
        
        if ( Sys::is_phpunit_server() ) {
            return ;
        }
        
        $beanstalkd = new Pheanstalk('127.0.0.1', '11300');
        //这是消息数据，在本demo中，type不能省略。区分任务类型。
        //         $delay = (int) strtotime($data['time']) - time();
        $delay=0;
        
        // 把消息放入队列,1024 是优先级
        //$delay 非常重要，假设设置10，则该消息10秒后才被放入队列！非常好使。
        $beanstalkd->useTube( Workerdasai::queue_name  )
        ->put(serialize($data), 1024, $delay);
        //    }
        
    }
    
    
    /**
     * 这是点
     * @param 
     */
    public function add_weixin(DataWeixin $data)
    {
        //   if (\BBExtend\Sys::is_product_server()) {
        
        if ( Sys::is_phpunit_server() ) {
            return ;
        }
        
        $beanstalkd = new Pheanstalk('127.0.0.1', '11300');
        //这是消息数据，在本demo中，type不能省略。区分任务类型。
        //         $delay = (int) strtotime($data['time']) - time();
        $delay=0;
        
        // 把消息放入队列,1024 是优先级
        //$delay 非常重要，假设设置10，则该消息10秒后才被放入队列！非常好使。
        $beanstalkd->useTube( Workerweixin::queue_name  )
        ->put(serialize($data), 1024, $delay);
        //    }
        
    }
    
    
    
    
    
}


