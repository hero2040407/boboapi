<?php
namespace BBExtend\service\pheanstalk;
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
 */
class Data
{
    public $uid;
    public $type;
    public $info;
    public $time;
    
   public function __construct($uid,$type,$info,$time){
       $this->uid=$uid;
       $this->type=$type;
       $this->info=$info;
       $this->time=$time;
   }
   
   public function get_uid()
   {
       return $this->uid;
   }
   
   public function get_time()
   {
       return $this->time;
   }
    
   public function get_type()
   {
       return $this->type;
   }
    
   public function get_info()
   {
       return $this->info;
   }
    
    
}





