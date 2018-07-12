<?php
/**
 * 该接口定义了一个设置 短视频 连续上传的间隔
 * 
 * 
 * 
 * User: 谢烨
 */
namespace BBExtend\video;

use BBExtend\Sys;

class RecordLimit implements Ilimit
{
    private $redis;
    private $uid;
    private $redis_key;
    
    public function __construct($uid){
        $this->redis = Sys::get_container_redis();
        $this->uid = intval($uid);
        $this->redis_key = "record:limit:".$this->uid;
    }
    
    /**
     * 暂定1分钟，
     * 
     * @see \BBExtend\video\Ilimit::can_upload()
     */
    public function can_upload()
    {
        $uid = $this->uid;
        $redis_key = $this->redis_key; 
        $curr_time = time();
        $result = $this->redis->get($redis_key);
        
        if ($result===false) {
            return true;
        }else {
            // 超过1分钟即可
            if ($curr_time - $result > 10 ){
                return true;
            }
            else {
                return false;
            }
        }
        
        
    }
    
    /**
     * 每次上传成功，就立刻添加限制。需在外面手动调用。
     */
    public function set_limit()
    {
        $this->redis->setEx($this->redis_key, 24*3600, time() );
    }
    
    
}

