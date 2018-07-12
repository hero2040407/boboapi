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
class DataDasai
{
    public $uid;
    public $type;//1 表示收集信息，2表示上传视频。
    public $id;
    public $time;
    
    
    
    /**
     * type
     * 1 下单，2取消下单
     * uid     抢单导师uid
     * time    抢单时间
     * 
     * 
     * @param unknown $uid
     * @param unknown $type
     * @param unknown $info
     * @param unknown $time
     */
   public function __construct($uid=0,$id=0,$time=0,$type=1)
   {
       
       $this->uid= intval( $uid);
//        $this->type=$type;
       $this->id  =intval( $id)  ;
       $this->time= intval( $time);
       $this->type = $type;
   }
   
   public function get_uid()
   {
       return $this->uid;
   }
   
   public function get_time()
   {
       return $this->time;
   }
    
    
   public function get_id()
   {
       return $this->id;
   }
   
   public function get_type()
   {
       return $this->type;
   }
    
    
}





