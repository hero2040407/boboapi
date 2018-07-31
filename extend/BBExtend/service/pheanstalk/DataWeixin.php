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
class DataWeixin
{
    public $uid;
    public $code;
    public $token;
    
    
    
    /**
     * 
     * 
     */
    public function __construct($uid=0,$code='', $token='')
   {
       
       $this->uid= intval( $uid);
       $this->code  = $code  ;
       $this->token=  $token;
   }
   
  
    
}





