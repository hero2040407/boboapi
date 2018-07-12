<?php
namespace app\apptest\controller;

class Workertest
{
    
    /**
     * 这是测试队列是否可用的接口
     * 如果5313683的手机收到短信，则ok
     * bobo.yimwing.com/apptest/workertest/index
     */
    public function index()
    {
       
        $target_uid = 5313683;
        $uid = 10160;
        $user = \app\user\model\UserModel::getinstance($uid);
        $nickname = $user->get_nickname();
        $pic = $user->get_userpic();
        $time=time();
              
        \Resque::setBackend('127.0.0.1:6380');
        $args = array(
                'target_uid' => $target_uid,
                'uid'  => $uid,
                'time' => $time,
                
                'pic'      => $pic,
                'nickname' => $nickname,
                'type' => '124',
                
            );
        \Resque::enqueue('jobs2', '\app\command\controller\Job2', $args);
       
   }
   
   
}
