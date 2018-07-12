<?php
/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
namespace BBExtend\pay;
use think\Db;

use BBExtend\message\Message;

// use app\push\controller\Pushmanager;
// use app\record\controller\Recordmanager;
// use think\Request;

class UserPay extends \BBExtend\BBUser
{
    /**
     * 价格列表由程序定义
     * 
     * 改了原先一个bug，当是vip时，但已过时，应该用当前时间来加。
     * @return number[][]|string[][]
     */
    public static function vip_price()
    {
        return array(
            ["type" => 1,"price"=>250,"time"=>'1个月',"additional_info"=>"",
                "additional_yuanjia"=>'(原价300)','second' => 31 * 24 * 3600, ],
            ["type" => 2,"price"=>600,"time"=>'3个月',"additional_info"=>"推荐",
                "additional_yuanjia"=>'(原价900)','second' => 3 * 31 * 24 * 3600,  ],
            ["type" => 3,"price"=>1180,"time"=>'6个月',"additional_info"=>"优惠",
                "additional_yuanjia"=>'(原价1800)', 'second' => 6* 31 * 24 * 3600, ],
            ["type" => 4,"price"=>1980,"time"=>'一年',"additional_info"=>"超值",
                "additional_yuanjia"=>'(原价3600)' ,'second' => 365 * 24 * 3600, ],
        
        );
    }
    
    /**
     * 发消息
     * @param unknown $uid
     * @param unknown $expire
     */
//     protected static function buy_vip_success($uid, $expire)
//     {
//         $ContentDB = \BBExtend\BBMessage::AddMsg([],'恭喜您购买VIP成功,会员到期时间为'.
//                 date("Y-m-d", $expire));
//         \BBExtend\BBMessage::SendMsg(\BBExtend\fix\Message::PUSH_MSG_ADMIN_MESSAGE,'购买VIP成功',$ContentDB,$uid);
//     }
    
    
    //购买VIP
    public static function buy_vip($uid, $type)
    {
        $uid = intval($uid);
        $type = intval($type);
        $UserDB = self::get_user_vip($uid);
        if (!$UserDB) {
            return ['code'=>0,'message'=>"用户不存在"];
        }
        
        //查找价格，延长vip的时间，顺便验证type输入错误
        $price_alllist = self::vip_price();
        $price_arr = [];
        foreach ($price_alllist as $v) {
            if ($v['type'] == $type ) {
                $price_arr = $v;
            }
        }
        if (!$price_arr) {
            return ["code"=>0,"message"=> "type错误" ];
        }
        $count = 0 - abs( $price_arr['price'] ); //确保count是负数。
        $buy_time = $price_arr['second'];
        
        //根据查找的价格，验证余额
        $user_gold = self::get_currency($uid)['gold'];
        if (intval( $user_gold)  + $count < 0 ) { //这是最好的写法。负数依然可以判断。
            return ['message'=>'余额不足请充值','code'=>\BBExtend\fix\Err::code_yuebuzu];
        }
        //然后确定 到期时间
        $time = time();
        if ($UserDB['vip']){
            $temp = ($UserDB['vip_time'] > $time) ?  $UserDB['vip_time'] : $time; //改原来代码的bug
            $new_time = $temp + $buy_time;
        }else {
            $new_time = $time + $buy_time;
        }
        if ($new_time - $time > 2* 365 * 24 * 3600 ) {
            return ['message'=>'您已经是vip，请勿过度消费','code'=>0];
        }
      
        // add_currenty的作用仅仅是增减个人帐号表，并日志，
        // 如果不够扣减，则返回错误
        if(self::add_currency($uid,CURRENCY_GOLD,$count,'购买VIP')) {
            $UserDB['vip'] = true;
            $UserDB['vip_time'] = $new_time;                    //增加天
            \BBExtend\BBRedis::getInstance('user')->hMset($UserDB['uid'],$UserDB); //更新缓存,vip是bool型
            Db::table('bb_users')
                ->where('uid',$UserDB['uid'])                  
                ->update(['vip'=>1,'vip_time'=>$new_time]);    //更新数据库,vip 是int型
            //self::buy_vip_success($uid, $UserDB['vip_time']);  //发消息。
            
            Message::get_instance()
                ->set_title('系统消息')
                ->add_content(Message::simple()->content("恭喜您购买vip成功。"))
                ->set_type(128)
                ->set_uid($uid)
                ->send();
            
            //准备返回消息给接口了。
            $Data = array(
                'vip'      => true,
                'vip_time' => $new_time,
                'gold'     => self::get_currency($uid)['gold'],
            );
            
            \BBExtend\user\Tongji::getinstance($uid)->vip();
            
            return ['data'=>$Data,'message'=>'购买成功','code'=>1];       //返回bool
        }
        //防止最后的意外并发扣钱
        return ["code"=>0,"message"=>'钱不够'];
        
          
    }

}