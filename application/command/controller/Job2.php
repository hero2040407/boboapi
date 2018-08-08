<?php
namespace app\command\controller;
use BBExtend\Sys;


// Job2,往表里插入一条数据。

class Job2  
{
    /**
     * 发一条大消息
     * @param unknown $args
     */
    public function fu_push($args) {
        $data = [
           'target_uid' => $args["target_uid"],
           'info' => $args["info"],
        ];
        \Requests::post('http://127.0.0.1/command/message/record_like', array(), $data);
    }
    
    
    public function perform()
    {
        $type = $this->args['type'];
        
        if ($type==10000) {
            $this->test();
            return ;
        }
        
        if (in_array($type, [119,121,123 ])) { //这几个合并消息发送。
          $this->fu_push($this->args);
          return;
        }
        
        $target_uid = $this->args['target_uid'];
        $nickname = $this->args['nickname'];
        $pic = $this->args['pic'];
        $uid = $this->args['uid'];
        $time = $this->args['time'];
        
        
        $data = [
            'target_uid' =>$target_uid,
            'time' =>$time,
            'uid' => $uid,
            'nickname' => $nickname,
            'pic' =>$pic,
        ];
        if ($type== 123) {
            $data['title'] = $this->args['title'];
        }
        
        if ($type==124) { //直播
           $response = \Requests::post('http://127.0.0.1/command/message/push', array(), $data);
        }
        if ($type==123) { //短视频
            $response = \Requests::post('http://127.0.0.1/command/message/record', array(), $data);
        }
        
    }
    public function test()
    {
        echo time() .":  test Job2 ok!\n";
        $db = Sys::getdb();
        $db->insert("bb_alitemp", [
            'url' => "test Job2 ok!",
            'create_time' => date("Y-m-d H:i:s"),
        ]);
    }
    
    
}