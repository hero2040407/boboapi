<?php
/**
 * Created by PhpStorm.
 * User: 谢烨
 */

namespace app\user\controller;

use think\Db;
use BBExtend\message\Message;
use BBExtend\fix\MessageType;
use BBExtend\Sys;

class Jubao 
{
    public function add($type=1, $content='',$count=1,$uid=0)
    {
        $type    = intval($type);
        $count   = intval($count);
        $uid     = intval($uid);
        $content = strval($content);
        
        if ($content=='') {
            return ['code'=>0, 'message'=>'内容不能为空'];
        }
        
        $user = \BBExtend\BBUser::get_user($uid);
        if (!$user) {
            return ['code'=>0, 'message'=>'用户不存在'];
        }
        if (!in_array($type, [1,2])) {
            return ['code'=>0, 'message'=>'type错误'];
        }
        if (!$count) {
            $count = 1;
        }
        $arr=['uid'=>$uid,
            'type' =>$type,
            'count' => $count,
            'is_complete' =>0,
            'content' => $content,
            'create_time' =>time(),
            
        ];
        Db::table('bb_jubao_log')->insert($arr);
        
        //如禁言，则直接禁言
        if ($type==2) {
            
           //$uid_jinyan_count = Db::table('') 
           if ($count >=10 ) { 
             //  $this->jinyan_set($uid);
               return ['code'=>1];
           }else {
               return ['code'=>0];
           }
            
        }
        return ['code'=>1];
    }
    
    private function jinyan_set($uid)
    {
        Db::table('bb_users')->where('uid',$uid)->update(['not_fayan'=>1,]);
        // $user['not_fayan']=1;
        \BBExtend\BBRedis::getInstance('user')->hSet($uid, 'not_fayan',1);
        
        //
        Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content("由于"))
            ->add_content(Message::simple()->content("被用户举报")->color(0xf4a560)  )
            ->add_content(Message::simple()->content('，您已被管理员禁言，如有疑问请联系客服。'))
            ->set_type(MessageType::jinyan)
            ->set_uid($uid)
            ->send();
    }
    
    /**
     * 解除禁言消息通知。
     * @param unknown $uid
     */
    public function jinyan_unset($uid)
    {
//         Db::table('bb_users')->where('uid',$uid)->update(['not_fayan'=>0,]);
        // $user['not_fayan']=1;
        $node_service = Sys::get_container_node();
        $url = \BBExtend\common\BBConfig::get_touchuan_url();
        $data= ['data'=>'您已被解除禁言','uid'=>$uid,'type'=>5];
        $result = $node_service->http_Request($url,$data,'GET');
        
        //
        Message::get_instance()
          ->set_title('系统消息')
          ->add_content(Message::simple()->content('您已被解除禁言，如有疑问请联系客服。'))
          ->set_type(MessageType::jinyan)
          ->set_uid($uid)
          ->send();
        return ['code'=>1 ];
    }
    
    
    public function cancel_zhibo($uid=0)
    {
        $uid     = intval($uid);
    
        $user = \BBExtend\BBUser::get_user($uid);
        if ($user['not_zhibo']==0) {
            return ['code'=>0, 'message'=>'状态原本就是正常的'];
        }
        
        if (!$user) {
            return ['code'=>0, 'message'=>'用户不存在'];
        }
       
    
    
        //如禁言，则直接禁言
//         if ($type==2) {
            Db::table('bb_users')->where('uid',$uid)->update(['not_zhibo'=>0,]);
            // $user['not_fayan']=1;
            \BBExtend\BBRedis::getInstance('user')->hSet($uid, 'not_zhibo',0);
            
          // 这里，nodejs调用
          
            $data =[
                  'uid' => $uid,
                  'message' =>  "您的直播屏蔽已经解除",
              ]  ;
         $result= \BBExtend\BBNodeJS::SendMessage($uid, 4, $data);
            
    
//         }
        return ['code'=>1,'data'=>$result];
    }
    
    
    public function prohibit_zhibo($uid=0)
    {
        $uid     = intval($uid);
    
        $user = \BBExtend\BBUser::get_user($uid);
        if ($user['not_zhibo']==1) {
            return ['code'=>0, 'message'=>'状态原本就是禁止'];
        }
    
        if (!$user) {
            return ['code'=>0, 'message'=>'用户不存在'];
        }
       
        Db::table('bb_users')->where('uid',$uid)->update(['not_zhibo'=>1,]);
        \BBExtend\BBRedis::getInstance('user')->hSet($uid, 'not_zhibo',1);
    
        // 这里，nodejs调用
    
//         $data =[
//             'uid' => $uid,
//             'message' =>  "您的直播屏蔽已经解除",
//         ]  ;
//         \BBExtend\BBNodeJS::SendMessage($uid, 4, $data);
    
    
        //         }
        return ['code'=>1];
    }
         
}
