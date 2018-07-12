<?php
namespace app\apptest\controller;

use BBExtend\BBRedis;
use  think\Db;
use BBExtend\Sys;
use BBExtend\BBUser;
//use BBExtend\message\Message;
use BBExtend\message\Umeng;
use BBExtend\service\pheanstalk\Data;

//require_once ( realpath( realpath( EXTEND_PATH)."/umeng/UmengPushAndroid.php"));

class Message
{
    public function index($uid=12500)
    {
       Sys::display_all_error();
        echo 33;
       // Sys::debugxieye(123);
//         Message::get_instance()
//             ->set_title('系统消息')
//             ->add_content(Message::simple()->content('哈哈成为了您的新粉丝。'))
//             ->set_type(113)
//             ->set_uid(12431)
//             ->send();
//         echo "ok";
        
//         \BBExtend\message\UmengBroadcast::getinstance()
//             ->set_content("快来看怪兽BoBo吧，小伙伴们都在等你true无_IOSBroadcast。")
//             ->set_production_mode("true")
//             ->send_all();
//          echo "全体广播end";
       // $uid = 12500;
        Umeng::getinstance()
        ->set_content("谢烨从你".date("H:i:s"))
        ->set_uid($uid)
        ->real_send_one();
        echo "ok";
    }
    
    public function test()
    {
        $client = new \BBExtend\service\pheanstalk\Client();
        $client->add(
            new Data(5850400,1010,['other_uid' => 11136,], time()  )
        );

    }
    
   
}
