<?php
namespace BBExtend\message;

/**
 * 
 * 现在的难点：
 * 1、消息类型不同，可能字符串，可能数组。
 * 2、推送方式不同，进入系统消息，可能会推送。
 * 
 * 现在我决定，以推送方式为重点构建程序
 * 
 * Message 抽象父类 
 * 有抽象方法，send()
 * 
 * 子类，目前两类，
 * 1、加入系统消息 SystemMessage
 * 2、加入系统消息带推送。 PushMessage    
 * 
 * 客户端如此调用
 * 
 * $message = new PushMessage( 
 *    MessageType::get_instance_string
 *      ->setTitle('经验提升')
 *      ->setContent('12222')
 *      ->setType(1)
 *  );
 * $message->send();
 * 
 * $message = new SystemMessage( 
 *    MessageType::get_instance_array
 *      ->setTitle('经验提升')
 *      ->setContent(['aa':11,'bb':22])
 *      ->setType(1)
 *  );
 * $message->send();
 *  
 *  
 * 
 * @author 谢烨
 */


/**
 * 
 * 
 * @author Administrator
 *
 */
abstract  class MessageMethod
{
    //protected $message_type; // 这是一个消息对象。

   abstract function send(Message $m);
}



