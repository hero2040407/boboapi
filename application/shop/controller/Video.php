<?php
namespace app\shop\controller;

use app\shop\model\Record;
use app\shop\model\Rewind;


use BBExtend\user\exp\Exp;
use BBExtend\message\Message;
use BBExtend\fix\MessageType;

/**
 * 
 * 视频购买
 * 
 * Created by PhpStorm.
 * User: xieye
 */
class Video 
{
    /**
     * 视频购买接口,20160921，短视频接口，所以无需指定视频类型
     * 
     */
    public function buy( $uid=0,  $room_id)
    {
        $uid = intval($uid);
        $room_id = strval($room_id);
        if (!$room_id) {
            return ['code'=>0,'message'=>'room_id错误'];
        }
        
        $user = \BBExtend\BBUser::get_user($uid);
        if (!$user) {
            return ["message"=>'用户不存在','code'=>0 ];
        }
        // 谢烨，这里必须判断room_id真的存在。
        
        $movie = Record::get(['room_id'=> $room_id]);
        if (!$movie) {
            $movie = Rewind::get(['room_id'=> $room_id]);
        }
        if (!$movie) {
            return ['code'=> 0,'message'=>'视频不存在'];
        }
        
        //条件验证，首先，该视频有价格，类型必须与用户类型吻合
        $price_type = $movie->getData('price_type');
        if (!in_array($price_type, [2,3])) {
            return ['code'=> 0,'message'=>'视频类型表示无需购买'];
        }
        if ($price_type == 3 && $user['vip'] ) {
            return ['code'=> 0,'message'=>'对于vip课程，vip用户无需购买'];
        }
        $price = $movie->getData('price');
        $price = abs( intval( $price)); //保险啊。只能波币
        if (!$price) {
            return ['code'=> 0,'message'=>'视频价格不能为0'];
        }
        //如果购买过，也无需
        $help = new \BBExtend\user\Relation();
        if ($help->has_buy_video($uid, $room_id) ) {
            return ['code'=> 0,'message'=>'您已购买过此视频，无需再次购买'];
        }
        
        // 现在才开始查价格。
        $user_obj = \app\shop\model\Users::get($uid);
        $info = $user_obj->get_buy_info();
        $user_gold = $info['gold'];
        if ($user_gold < $price) {
            $message = "真遗憾，宝贝您的BO币还不足够观看这个视频哦～\n".
                    "快快去秀场完成今天的任务换取更多的BO币或者直接充值兑换吧～";
            return ["message"=>$message,'code'=>\BBExtend\fix\Err::code_yuebuzu ];
        }
        
        //购买视频
        $result = $help->buy_video($uid, $room_id, $price);
        
        
        //扣减用户波币，并记录日志
        $user_obj->buy_video_success_coin($price);
        
        // 经验
        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_SHOW_LIVE_COURSE )->add_exp();
        
        //发消息
        Message::get_instance()
            ->set_title('系统消息')
            ->add_content(Message::simple()->content("恭喜您购买课程成功。"))
            ->set_type(MessageType::goumai_shipin)
            ->set_uid($uid)
            ->send();
        
        
        $info = $user_obj->get_buy_info();
        //返回给客户端
        $data = [
            "current_gold"    => $info['gold'],         //订单总价
        ];
        return ["data"=>$data, "code"=>1 ];
        
      
    }
    
    
   
    
    
    
    
    
}