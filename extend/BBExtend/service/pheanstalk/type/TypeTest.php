<?php
namespace BBExtend\service\pheanstalk\type;

use BBExtend\service\pheanstalk\WorkerJobPush;
use BBExtend\message\Message;

/**
 * 消息队列。pheanstalk
 * @author Administrator
 *
 * xieye: 20171016
 * 这是客户端代码，使用方法如下，只有两句话。专用于添加消息到队列。
 * 
 *  $client = new \BBExtend\service\pheanstalk\Client();  
 *  $client->add(['type'=>1,'msg' =>'hello!' ]);  
 *
 *
 *  $client = new \BBExtend\service\pheanstalk\Client();
        $client->add(
            new \BBExtend\service\pheanstalk\Data($invite->starmaker_uid,
                \BBExtend\fix\MessageType::yaoqing_dianping_fail,    
                ['key' => $fail->id,'other_uid' => $invite->uid, ], time()  )
        );
 *
 */
class TypeTest extends WorkerJobPush
{
    public function excute()
    {
        
        $type = $this->type ;
        $time = $this->time;
        $target_uid = $this->uid;
        $info = $this->info;
        
//         $bonus = $info['bonus'];
        
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        $db->insert("bb_alitemp", [
                'create_time' => date("Y-m-d H:i:s"),
                'uid' => $target_uid,
                'content'=> "当前时间".date("Y-m-d H:i:s")." 这是一个phean的woker的测试。传入的随机数是" .$info['random'] ,
        ]);
      
        
       
        
    }
   
    
}


