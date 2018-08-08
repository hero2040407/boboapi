<?php
namespace app\command\controller;

use think\Controller;
use BBExtend\Sys;
use BBExtend\BBPush;

use BBExtend\message\Message as me;
use BBExtend\message\Umeng;

/**
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */


class Message  extends Controller
{
    // xieye,1800等于半小时。
    const cha =  1800;
    const hebing_cha = 7200;
    
    /**
     * 直播给粉丝发送消息
     * 
     * 粉丝 target_uid
     * 偶像：uid
     * 
     * 你的好友#玩家昵称#，开启了直播，点击进入直播间
     */
    public function push()
    {
        $db = Sys::getdb();
        $target_uid = input('param.target_uid');
        $nickname = input('param.nickname');
        $pic = input('param.pic');
        $uid = input('param.uid');
        $time = input('param.time');
   //     return ["uid" =>$uid ];
      //  $title = input('param.title');
//         $c_time = intval( time() );
        if (time() - $time > self::cha  ) {
            return;
        }
        
        $sql="select * from bb_push where uid = {$uid}";
        $push_row = $db->fetchRow($sql);
        $temp =  BBPush::get_detail_by_row($push_row, $target_uid);
        
        
        me::get_instance()
            ->set_title('系统消息')
            ->add_content(me::simple()->content('你的好友'))
            ->add_content(me::simple()->content($nickname)
                ->url(json_encode(['type'=>9, 'other_uid'=>$uid,"push_info"=>$temp, ], 
                        JSON_UNESCAPED_UNICODE ) )
            )
            ->add_content(me::simple()->content('，开启了直播，点击进入直播间'))
            ->set_type(124)
            ->set_uid($target_uid)
            ->send();
        
        $db->insert("bb_alitemp", [
            'create_time' => time(),
            'test1' => 124,
            'uid'  => $target_uid,
            'url'  => $uid,
            
        ]);
    }

    /**
     * 发布短视频给粉丝发送消息
     * 
     * 你的好友#玩家昵称#上传了新的短视频#视频标题#

     * 
     *  * 好友上传视频                  123(未做)
     * 好友开启直播                  124（未做）
     */
    public function record()
    {
        
//         $data = [
//             'target_uid' =>$target_uid,
//             'time' =>time(),
//             'uid' => $uid,
//             'record_id' =>$record_id,
//             'title' => $title,
//         ];
        
        $target_uid = input('param.target_uid');
        $uid = input('param.uid');
        $title = input('param.title');
        $time = input('param.time');
        $record_id = input('param.record_id');
        
        if (time() - $time > self::cha  ) {
            return;
        }
        
        $user = \app\user\model\UserModel::getinstance($uid);
        $pic = $user->get_user_pic_no_http();
        $nickname = $user->get_nickname();
        
        me::get_instance()
            ->set_title('系统消息')
            ->set_img($pic)
            ->add_content(me::simple()->content('你的好友'))
            ->add_content(me::simple()->content($nickname)
                ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
            )
            ->add_content(me::simple()->content('上传了新的短视频'.$title))
            ->set_type(123)
            ->set_uid($target_uid)
            ->set_other_uid($uid)
            ->set_other_record_id($record_id)
            ->send();
      
    }
    
    
    /**
     * 合并消息，视频被赞
     *
     * 
合并格式：#玩家昵称1#，#玩家昵称2#等XX位用户赞了你的视频#视频标题#
     *
     *
     * 119
     */
    public function record_like()
    {
//         $data = [
//             'target_uid' => $args["target_uid"],
//             'info' => $args["info"],
//         ];
        $target_uid = input('param.target_uid');
        $info = input('param.info');
        Umeng::getinstance()
            ->set_content($info)
            ->set_uid($target_uid)
            ->send_one();
                
    }
    
    
}

