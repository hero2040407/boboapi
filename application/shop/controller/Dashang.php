<?php
namespace app\shop\controller;

use BBExtend\BBUser;
use app\shop\model\Record;
use app\shop\model\Rewind;
use app\shop\model\Push;
use BBExtend\user\Ranking;

use BBExtend\BBRecord;

use BBExtend\Currency;
use think\Db;
use BBExtend\Sys;
use BBExtend\user\Focus;

use BBExtend\message\Message;
use BBExtend\fix\MessageType;

/**
 * 
 * 打赏
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Dashang 
{
    
//     public function send_message($uid,$message)
//     {
//         $ContentDB = \BBExtend\BBMessage::AddMsg([],$message);
//         \BBExtend\BBMessage::SendMsg(\BBExtend\fix\Message::PUSH_MSG_ADMIN_MESSAGE,
//                 '打赏',$ContentDB,$uid);
//     }
    
    public function ranking($room_id=0,$startid=0,$length=8,$uid=0)
    {
        $length = intval($length);
        $length = ($length> 50)?50:$length;
        //$movie_id = intval($movie_id);
        $room_id = strval($room_id);
        
        
        $startid = intval($startid);
        $db = \BBExtend\Sys::get_container_db();
        $sql ="select uid,gold_all  from  bb_dashang_ranking
                where room_id = ?
                  and exists (select 1 from bb_users where bb_users.uid=bb_dashang_ranking.uid)
                order by gold_all desc
                limit {$startid},{$length}
                ";
        
        $list = $db->fetchAll($sql, $room_id);
        $arr =[];
        $qian_arr=[];
        $guanzhu_arr=[];
        foreach ($list as $v) {
            $arr[]= $v['uid'];
            $qian_arr[$v['uid']] = $v['gold_all'];
            $guanzhu_arr[$v['uid']] = Focus::getinstance($uid)->has_focus($v['uid']);
        }
        
        //额外增加，每次都需给一个总数
        $sql ="select count( distinct uid  )  from
        bb_dashang_ranking
        where room_id = ?
        and exists (select 1 from bb_users where bb_users.uid=bb_dashang_ranking.uid)
        ";
         $all_count = $db->fetchOne($sql, $room_id);
//         $result = Db::query($sql);
//         dump($result);
        
        
        $is_bottom = (count($list) == $length)?0:1;
        $data = \BBExtend\user\Common::get_userlist2($arr); 
        foreach ($data as $k => $v) {
            $data[$k]['gold_all'] = $qian_arr[$v['uid']];
            $data[$k]['is_focus'] = boolval( $guanzhu_arr[$v['uid']]);
            
        }
        
        $table = BBRecord::get_table_name($room_id);
        
        $sql="select dashang_all from {$table} where room_id= ?";
        $dashang_all = $db->fetchOne($sql,$room_id);
        
        return ['code'=>1, 'is_bottom'=>$is_bottom ,
            'dashang_all' => intval( $dashang_all),
            'all_count' => $all_count,
            'data'=>$data];
    }
    
    
    /**
     * 返回打赏人列表
     * @param number $movie_id
     * @param number $startid
     * @param number $length
     * 
     * push 以push结尾。
record 以 record_movies结尾
rewind 以mlandclub开头
     * 
     */
    public function list_people($room_id=0,$startid=0,$length=8)
    {
        $length = intval($length);
        $length = ($length> 50)?50:$length;
        //$movie_id = intval($movie_id);
        $room_id = strval($room_id);
        
        
        $startid = intval($startid);
        $db = \BBExtend\Sys::get_container_db();
        $sql ="select uid,max(create_time) as tim from 
              bb_dashang_log
                where room_id = ?
        
                and exists (select 1 from bb_users where bb_users.uid=bb_dashang_log.uid)
                
                 group by uid
                order by tim desc
                limit {$startid},{$length}
                ";
        
        $list = $db->fetchAll($sql, $room_id);
        $arr =[];
        foreach ($list as $v) {
            $arr[]= $v['uid'];
        }
        
        //额外增加，每次都需给一个总数
        $sql ="select count( distinct uid  )  from
        bb_dashang_log
        where room_id = ?
        and exists (select 1 from bb_users where bb_users.uid=bb_dashang_log.uid)
        ";
         $all_count = $db->fetchOne($sql, $room_id);
//         $result = Db::query($sql);
//         dump($result);
        
        
        $is_bottom = (count($list) == $length)?0:1;
        $data = \BBExtend\user\Common::get_userlist($arr); 
        return ['code'=>1, 'is_bottom'=>$is_bottom ,
            'all_count' => $all_count,
            'data'=>$data];
    }
    
    
    /**
     * 打赏，废弃。
     * @param number $uid
     * @param number $movie_id
     * @param number $type
     */
    public function  index($uid=0,$room_id=0,$type=1)
    {
        
        return ['code'=>0,'message'=>'请下载新版本'];
        
        $uid =intval($uid);
         $movie_id= $room_id =  strval($room_id);
         
        $type=intval($type);
        $type_arr = \BBExtend\pay\Dashang::price();
        $price=0;
        foreach ($type_arr as $v) {
            if ($v['type'] == $type ) {
                $price = $v['price'];
            }
        }
        if (!$price) {
            return ['code'=>0, 'message'=>'type类型错误'];
        }
        
        $table = BBRecord::get_table_name($room_id) ;
        $record = BBRecord::get_all_movie_model($room_id);
        
        if (!$table) {
            return ['code'=>0, 'message'=> '打赏不成功'];
        }
        $user = BBUser::get_user($uid);
        if (!$user) {
            return ['code'=>0, 'message' => '用户不存在' ];
        }
        $count = 0-$price;
        //根据查找的价格，验证余额
        $user_gold = Currency::get_currency($uid)['gold'];
        
        if ( intval($user_gold)  + $count < 0 ) { //这是最好的写法。负数依然可以判断。
            return ['message'=>'您的余额不足请充值','code'=>\BBExtend\fix\Err::code_yuebuzu];
        }
        //查找被打赏人信息
        $target_uid = $record->getData('uid');
        $target = BBUser::get_user($target_uid);
        if (!$target) {
            return ['message'=>'被打赏人不存在','code'=>0];
        }
        
        
        
        if(Currency::add_bobi($uid,$count,'打赏')) {
            \BBExtend\BBRedis::getInstance('user')->Del($uid); //更新缓存
            
            // 现在开始给另一个用户加钱。
            Currency::add_bobi($target_uid, $price,'被打赏');
            \BBExtend\BBRedis::getInstance('user')->Del($target_uid);
//             $message = $user['nickname'] .'在'. $record->getData('title') 
//               ."，打赏了您{$price}BO币！进入个人中心查看";
//             $this->send_message($target_uid, $message);
            
            $title = $record->getData('title');
            if (!$title) {
                $title ='您的视频里';
            }
            Message::get_instance()
                ->set_title('系统消息')
                ->add_content(Message::simple()->content($user['nickname'])->color(0xf4a560) 
                        ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                        )
                ->add_content(Message::simple()->content('在'))
                ->add_content(Message::simple()->content( $title )->color(0xf4a560)  )
                ->add_content(Message::simple()->content('，打赏了您BO币！进入'))
                ->add_content(Message::simple()->content( '个人中心' )->color(0x32c9c9) 
                        ->url(json_encode(['type'=>1] ))
                        )
                ->add_content(Message::simple()->content('查看。'))
                ->set_type( MessageType::shipin_beidashang  )
                ->set_uid($target_uid)
                ->send();
                
            $this->log($uid,  $price, $room_id, $target_uid);
            
           // Ranking::getinstance($uid)->add_dashang_ranking($price);
            
            //准备返回消息给接口了。
            $Data = array(
                'current_gold'     => Currency::get_currency($uid)['gold'],
            );
            return ['data'=>$Data,'code'=>1];       //返回bool
        }
        //防止最后的意外并发扣钱
        return ["code"=>0,"message"=>'钱不够'];
    }
    
    /**
     * 返回打赏价格表，为方便客户端，同时查一个用户数据。
     * @param number $target_uid
     */
    public function price($target_uid=0)
    {
        $uid = intval($target_uid);
        $info = BBUser::get_user($target_uid);
        if (!$info) {
            return ['code'=>0, 'message'=>'用户不存在'];
        }
        
        $t=[];
        $user_detail = \BBExtend\model\User::find( $info['uid']);
        
        $t['role'] = $user_detail->role;
        $t['frame'] = $user_detail->get_frame();
        $t['badge'] = $user_detail->get_badge();
        
        $arr = array(
            'user_info' => array(
                "uid" =>$info['uid'],
                "age" =>date('Y') - substr($info['birthday'],0,4),
                    
                    "role" => $t['role'] ,
                    "frame" =>  $t['frame'] ,
                    "badge" => $t['badge']  ,
                    
                    
                "pic" => BBUser::get_userpic($info['uid']) ,
                "sex" =>$info['sex'],
                "city" =>$this->get_address($info['address']),
                "specialty" =>BBUser::get_specialty($uid),
            ),
            'price_list' =>  \BBExtend\pay\Dashang::price(),
        );
        
        return ['code'=>1,"data"=>$arr];
        
    }
    
    private function get_address($address)
    {
        if (!$address) {
            return '';
        }
        if (preg_match('#null#', $address)) {
            return '';
        }
        return $address;
    }
    
    private function log($uid,  $price, $room_id, $target_uid)
    {
        //记录此次打赏日志
        $log = new \app\shop\model\Dashang();
        $log->data('create_time', time());
        $log->data('uid', $uid );
        $log->data('room_id', $room_id );
        $log->data('target_uid', $target_uid );
        $log->data('gold', $price);
        $log->save();
        $time = time();
        //
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_dashang_ranking where uid = {$uid} and room_id=? ";
        $count = $db->fetchOne($sql, $room_id);
        if ($count) {
            //原有修改，数值。
            $sql ="update bb_dashang_ranking 
                    set gold_all = gold_all + {$price},
                      update_time = '{$time}'
                    where uid = {$uid} 
                     and  room_id = ?
                    ";
            $db->query($sql, $room_id);
        }else {
            //直接添加
            $db->insert("bb_dashang_ranking", [
                'uid' =>$uid,
                'target_uid' =>$target_uid,
                'update_time' =>time(),
                'gold_all' =>$price,
                'room_id' =>$room_id,
            ]);
            
        }
        //把视频的表的打赏总数也要加啊。
        $table = BBRecord::get_table_name($room_id);
        $sql ="update {$table} set dashang_all = dashang_all + {$price} where room_id=? ";
        $db->query($sql,$room_id);
    }
   
   
}