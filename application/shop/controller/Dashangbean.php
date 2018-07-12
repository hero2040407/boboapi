<?php
namespace app\shop\controller;
//use BBExtend\BBShop;
use BBExtend\BBUser;
use app\shop\model\Record;
use app\shop\model\Rewind;
use app\shop\model\Push;
use BBExtend\user\Ranking;

use BBExtend\BBRecord;

use BBExtend\Currency;
use think\Db;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\user\Focus as Fo;

use BBExtend\message\Message;
use BBExtend\user\exp\Exp;
use BBExtend\common\Date;
use BBExtend\fix\MessageType;

use BBExtend\common\Client;

/**
 * 
 * 打赏波豆，新功能
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/11/25
 * Time: 11:42
 */

class Dashangbean 
{
    
//     public function send_message($uid,$message)
//     {
//         $ContentDB = \BBExtend\BBMessage::AddMsg([],$message);
//         \BBExtend\BBMessage::SendMsg(\BBExtend\fix\Message::PUSH_MSG_ADMIN_MESSAGE,
//                 '打赏',$ContentDB,$uid);
//     }
    
    
    
    public function day_ranking($room_id=0,$startid=0,$length=8,$uid=0)
    {
        $length = intval($length);
        $length = ($length> 50)?50:$length;
        //$movie_id = intval($movie_id);
        $room_id = strval($room_id);
    
    
        $startid = intval($startid);
        $uid = intval($uid);
        
        $db = \BBExtend\Sys::get_container_db();
        $daystr=date("Ymd");
        $sql ="select uid,bean_all  from  bb_dashang_day_ranking
        where room_id = ?
         and daystr='{$daystr}'
        and exists (select 1 from bb_users where bb_users.uid=bb_dashang_day_ranking.uid)
        and bean_all > 0
        order by bean_all desc
        limit {$startid},{$length}
        ";
    
        $list = $db->fetchAll($sql, $room_id);
        $arr =[];
        $qian_arr=[];
        $guanzhu_arr=[];
        foreach ($list as $v) {
            $arr[]= $v['uid'];
            $qian_arr[$v['uid']] = $v['bean_all'];
            $guanzhu_arr[$v['uid']] = Fo::getinstance($uid)->has_focus($v['uid']);
        }
    
        //额外增加，每次都需给一个总数
        $sql ="select count( distinct uid  )  from
        bb_dashang_day_ranking
        where room_id = ?
                and daystr='{$daystr}'
        and exists (select 1 from bb_users where bb_users.uid=bb_dashang_day_ranking.uid)
        and bean_all > 0
        ";
        $all_count = $db->fetchOne($sql, $room_id);
        //         $result = Db::query($sql);
        //         dump($result);
    
    
        $is_bottom = (count($list) == $length)?0:1;
        $data = \BBExtend\user\Common::get_userlist2($arr);
        foreach ($data as $k => $v) {
            $data[$k]['bean_all'] = $qian_arr[$v['uid']];
            $data[$k]['is_focus'] = boolval( $guanzhu_arr[$v['uid']]);
    
        }
    
        $table = BBRecord::get_table_name($room_id);
    
//         $sql="select dashang_bean_all from {$table} where room_id= ?";
//         $dashang_all = $db->fetchOne($sql,$room_id);
        
        $time0 = Date::get_day_start();
       // bb_dashang_log
        $sql ="select sum(bean) from bb_dashang_log where room_id =? and create_time > {$time0}";
        $dashang_all = $db->fetchOne($sql,$room_id);
        
        return ['code'=>1, 'is_bottom'=>$is_bottom ,
            'dashang_all' => intval( $dashang_all),
            'all_count' => $all_count,
            'data'=>$data];
    }
    
    
    /**
     * 粉丝贡献榜，取前50名
     * 
     * @param unknown $uid
     * @param unknown $type 1日榜，2周榜  ,3总榜，
     */
    public function fans_ranking($uid , $type=3 )
    {
        $user = \app\user\model\UserModel::getinstance($uid);
        if ($user->has_error()) {
            return ['code'=>0,'message'=>'uid not exists'];
        }
        
        $db = Sys::get_container_db_eloquent();
        if ($type==3) {
            $sql= "
                select uid,sum(gold) as golds from bb_dashang_log 
where target_uid = ?
 and exists (select 1 from bb_focus where bb_focus.focus_uid = bb_dashang_log.target_uid
   and bb_focus.uid = bb_dashang_log.uid )                   
group by uid
order by golds desc
limit ?
                ";
            $sql_sum = "
                select sum(gold) as golds from bb_dashang_log 
where target_uid = ?
  and exists (select 1 from bb_focus where bb_focus.focus_uid = bb_dashang_log.target_uid
   and bb_focus.uid = bb_dashang_log.uid )                  
                ";
        }
        
        if ($type==1) {
            $date = Date::pre_day_start(0);
            $sql= "
                select uid,sum(gold) as golds from bb_dashang_log
where target_uid = ?
 and create_time > {$date}
 and exists (select 1 from bb_focus where bb_focus.focus_uid = bb_dashang_log.target_uid
   and bb_focus.uid = bb_dashang_log.uid )           
group by uid
order by golds desc
limit ?
                ";
            $sql_sum = "
                select sum(gold) as golds from bb_dashang_log
where target_uid = ?
 and create_time > {$date}                
 and exists (select 1 from bb_focus where bb_focus.focus_uid = bb_dashang_log.target_uid
   and bb_focus.uid = bb_dashang_log.uid )
                ";
            
        }
        if ($type==2) {
            $date = Date::pre_day_start(6);
            $sql= "
                select uid,sum(gold) as golds from bb_dashang_log
where target_uid = ?
 and create_time > {$date}                
 and exists (select 1 from bb_focus where bb_focus.focus_uid = bb_dashang_log.target_uid
   and bb_focus.uid = bb_dashang_log.uid )
group by uid
order by golds desc
limit ?
                ";
            $sql_sum = "
            select sum(gold) as golds from bb_dashang_log
            where target_uid = ?
            and create_time > {$date}
            and exists (select 1 from bb_focus where bb_focus.focus_uid = bb_dashang_log.target_uid
   and bb_focus.uid = bb_dashang_log.uid )
            ";
        }
        
       $result = DbSelect::fetchAll($db, $sql, [ $uid, 50 ] );
       $sum = DbSelect::fetchOne($db, $sql_sum, [ $uid ] );
       $sum = intval($sum);
        $new=[];
        foreach ($result as $v){
            $temp=[];
            $temp['gold'] = intval( $v['golds']);
            $temp['uid']=  $v['uid'];
            $temp_user = \app\user\model\UserModel::getinstance($v['uid']); 
            if ($temp_user->has_error()) {
                continue;
            }
            
            $user_detail = \BBExtend\model\User::find( $v['uid'] );
            
            $temp['role'] = $user_detail->role;
            $temp['frame'] = $user_detail->get_frame();
            $temp['badge'] = $user_detail->get_badge();
            
            
            $temp['sex'] = $temp_user->get_usersex();
            $temp['level'] = $temp_user->get_user_level();
            $temp['age'] = $temp_user->get_userage();
            $temp['pic'] = $temp_user->get_userpic();
            $temp['nickname'] = $temp_user->get_nickname();
            $new[] = $temp;
            
        }
        return ['code'=>1,'data'=>[ 
            'list' => $new,
            'sum'  => $sum,
        ]];
    }
    
    public function ranking($room_id=0,$startid=0,$length=8,$uid=0)
    {//Sys::display_all_error();
        $length = intval($length);
        $length = ($length> 50)?50:$length;
        //$movie_id = intval($movie_id);
        $room_id = strval($room_id);
      //  Sys::display_all_error();
        $uid = intval($uid);
        $startid = intval($startid);
        $db = \BBExtend\Sys::get_container_db();
        
        $dbe = Sys::get_container_db_eloquent();
        
        $sql ="select uid,bean_all  
                 from  bb_dashang_ranking
                where room_id = ?
                  and exists (select 1 from bb_users where bb_users.uid=bb_dashang_ranking.uid)
                  and bean_all > 0
                order by bean_all desc
                limit {$startid},{$length}
                ";
        
//         $list = $db->fetchAll($sql, $room_id);
        $list = DbSelect::fetchAll($dbe, $sql,[ $room_id ]);
        
        $arr =[];
        $qian_arr=[];
        $guanzhu_arr=[];
        foreach ($list as $v) {
            $arr[]= $v['uid'];
            $qian_arr[$v['uid']] = $v['bean_all'];
            $guanzhu_arr[$v['uid']] = Fo::getinstance($uid)->has_focus($v['uid']);
        }
        
        //额外增加，每次都需给一个总数
        $sql ="select count( distinct uid  )  from
        bb_dashang_ranking
        where room_id = ?
        and exists (select 1 from bb_users where bb_users.uid=bb_dashang_ranking.uid)
        and bean_all > 0
        ";
         $all_count = $db->fetchOne($sql, $room_id);
//         $result = Db::query($sql);
//         dump($result);
         
        
        $is_bottom = (count($list) == $length)?0:1;
        $data = \BBExtend\user\Common::get_userlist2($arr); 
        foreach ($data as $k => $v) {
            $data[$k]['bean_all'] = $qian_arr[$v['uid']];
            $data[$k]['is_focus'] = boolval( $guanzhu_arr[$v['uid']]);
            
        }
        
        $table = BBRecord::get_table_name($room_id);
        
        $sql="select dashang_bean_all from {$table} where room_id= ?";
        
        if (!$table) {
            return ['code'=>0,'message'=>'短视频不存在'];
        }
        
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
     * 打赏波豆，注意是新功能
     * @param number $uid
     * @param number $movie_id
     * @param number $type
     */
    public function  index($uid=0,$room_id=0,$type=1,$token='')
    {
        $uid =intval($uid);
        // 2018 02 27 谢烨新加功能：打赏加token校验。
        // 校验代码在下方
        $new_version=0;
        if ( Client::is_android() && Client::big_than_version('3.4.2') ) {
            $new_version=1;
        }
        if ( Client::is_ios()  && Client::big_than_version('3.4.1') ) {
            $new_version=1;
        }
        
        $user = BBUser::get_user($uid);
        if (!$user) {
            return ['code'=>0, 'message' => '用户不存在' ];
        }
        
        // token校验代码
    //    if ($new_version) {
            $model_user = \BBExtend\model\User::find($uid);
            if ( !$model_user->check_token($token ) ) {
                return ['code'=>0,'message'=>'用户检验错误，有版本更新，请下载怪兽bobo新版本。'];
            }
    //    }
        
        
        // 谢烨，如果是机器人，则跳转逻辑。
        if ($user['login_type']==5) {
            return $this->index_robot($uid,$room_id,$type);
        }
        
        
         $movie_id= $room_id =  strval($room_id);
         
        $type=intval($type);
        $db = Sys::get_container_db();
        $sql = "select gold from bb_present where id = {$type}";
        $price = $db->fetchOne($sql);
        
//         $type_arr = \BBExtend\pay\Dashang::price();
//         $price=0;
//         foreach ($type_arr as $v) {
//             if ($v['type'] == $type ) {
//                 $price = $v['price'];
//             }
//         }
        if (!$price) {
            return ['code'=>0, 'message'=>'type类型错误'];
        }
        
        $table = BBRecord::get_table_name($room_id) ;
        $record = BBRecord::get_all_movie_model($room_id);
        if (!$record) {
            return ['code'=>0, 'message'=> '房间号错误，或视频未准备好'];
        }
        
        if (!$table) {
            return ['code'=>0, 'message'=> '打赏不成功'];
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
        
        
        
        
        // xieye 2018 05 ，我设计一下，先查被打赏人最后ip
        if ( $target['email'] && ( !\BBExtend\model\User::is_test($uid) ) ) {
            
            // 假如被打赏人有记录ip地址。
            if ( $target['email'] == \BBExtend\common\Client::ip() ) {
                return ['message'=>'违规操作','code'=>0 ];
            }
            
        }
        
        
        // xieye 2018 05 ，必填手机号
        $bind_help = new \BBExtend\user\BindPhone($uid);
        if (!$bind_help->check()) {
            return $bind_help->get_result_arr();
        }
        
        // 如果打赏人测试账号，被打赏人正常账号，则返回错误
        $dbe = Sys::get_container_db_eloquent();
        $sql = "select count(*) from bb_users_test where uid=?";
        $count1 = DbSelect::fetchOne($dbe, $sql, [ $uid ]);
        $sql = "select count(*) from bb_users_test where uid=?";
        $count2 = DbSelect::fetchOne($dbe, $sql, [ $target_uid ]);
        if ($count1>0 && $count2==0) {
            return ['message'=>'测试账号不能打赏给正式','code'=>0];
        }
        
        
        if(Currency::add_currency($uid,CURRENCY_GOLD,$count,'打赏')) {
            \BBExtend\BBRedis::getInstance('user')->Del($uid); //更新缓存
            // 日志记录
            
            
            
            // 现在开始给另一个用户加钱。但是加波豆。
            $bean = Currency::present_to_bean($price);
            
            Currency::add_bean($target_uid,  $bean, '被打赏');
            \BBExtend\BBRedis::getInstance('user')->Del($target_uid);
            
            Exp::getinstance($uid)->set_typeint(Exp::LEVEL_DASHANG)
              ->set_present_id($type)
              ->set_shi_cha($price)->add_exp();
            
//             $message = $user['nickname'] .'在'. $record->getData('title') 
//               ."，打赏了您{$price}BO币！进入个人中心查看";
//             $this->send_message($target_uid, $message);
            
            $title = $record->getData('title');
            if (!$title) {
                $title ='您的视频里';
            }
            Message::get_instance()
                ->set_title('系统消息')
                ->set_img($user['pic'])
                ->add_content(Message::simple()->content($user['nickname'])->color(0xf4a560) 
                        ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                        )
                ->add_content(Message::simple()->content('在'))
                ->add_content(Message::simple()->content( $title )->color(0xf4a560)  )
                ->add_content(Message::simple()->content("，打赏了您{$bean}个BO豆！进入"))
                ->add_content(Message::simple()->content( '个人中心' )->color(0x32c9c9) 
                        ->url(json_encode(['type'=>1] ))
                        )
                ->add_content(Message::simple()->content('查看。'))
                ->set_type(MessageType::shipin_beidashang)
                ->set_uid($target_uid)
                ->set_other_uid($uid)
                ->send();
                
            $this->log($uid,  $price, $room_id, $target_uid,$type);
            
           // Ranking::getinstance($uid)->add_dashang_ranking($price);
            
            //准备返回消息给接口了。
            $Data = array(
                'current_gold'     => Currency::get_currency($uid)['gold'],
                'bean'             => $bean,
            );
            return ['data'=>$Data,'code'=>1];       //返回bool
        }
        //防止最后的意外并发扣钱
        return ["code"=>0,"message"=>'钱不够'];
    }
    
    /**
     * 谢烨；这是机器人打赏用户的逻辑。
     * 
     * 新增，每个用户直播时最多一天接受90个波豆。限制死。
     */
    private function index_robot($uid=0,$room_id=0,$type=1)
    {
        $uid =intval($uid);
        
        $user = BBUser::get_user($uid);
        if (!$user) {
            return ['code'=>0, 'message' => '用户不存在' ];
        }
        
        $movie_id= $room_id =  strval($room_id);
         
        $type=intval($type);
        $db = Sys::get_container_db();
        $sql = "select gold from bb_present where id = {$type}";
        $price = $db->fetchOne($sql);
        
        if (!$price) {
            return ['code'=>0, 'message'=>'type类型错误'];
        }
        
        $table = BBRecord::get_table_name($room_id) ;
        $record = BBRecord::get_all_movie_model($room_id);
        if (!$record) {
            return ['code'=>0, 'message'=> '房间号错误，或视频未准备好'];
        }
        
        if (!$table) {
            return ['code'=>0, 'message'=> '打赏不成功'];
        }
        
        $count = 0-$price;
        //根据查找的价格，验证余额
        $sql = "select robot_bobi from bb_config limit 1";
        $user_gold = $db->fetchOne($sql) ;
        
        if ( intval($user_gold)  + $count < 0 ) { //这是最好的写法。负数依然可以判断。
            return ['message'=>'您的余额不足请充值','code'=>\BBExtend\fix\Err::code_yuebuzu];
        }
        //查找被打赏人信息
        $target_uid = $record->getData('uid');
        $target = BBUser::get_user($target_uid);
        if (!$target) {
            return ['message'=>'被打赏人不存在','code'=>0];
        }
        
        // 谢烨，新的限制
        $time0 = \BBExtend\common\Date::get_day_start();
        $sql = "select sum(`count`) from bb_currency_log where uid={$target_uid} and type=10
                  and `time` > {$time0}";
        $get_bodou_current_day = $db->fetchOne($sql);
        $get_bodou_current_day = intval($get_bodou_current_day);
        if ($get_bodou_current_day>=90) { // 如果超过90个，则放弃。
            return ['message'=>'too much','code'=>0];
        }
        
        $sql = "update bb_config set robot_bobi = robot_bobi - {$price}";
        $db->query($sql);
        
        \BBExtend\BBRedis::getInstance('user')->Del($uid); //更新缓存
        $bean = Currency::present_to_bean($price);
        Currency::add_bean($target_uid,  $bean, '被打赏');
        \BBExtend\BBRedis::getInstance('user')->Del($target_uid);
        
        $title = $record->getData('title');
        if (!$title) {
            $title ='您的视频里';
        }
        Message::get_instance()
            ->set_title('系统消息')
//                    ->add_content(Message::simple()->content($user['nickname'])->color(0xf4a560)
//                     ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
//                     )
//                     ->add_content(Message::simple()->content('在'))
            ->add_content(Message::simple()->content( $title )->color(0xf4a560)  )
            ->add_content(Message::simple()->content("被打赏{$bean}个BO豆！进入"))
            ->add_content(Message::simple()->content( '个人中心' )->color(0x32c9c9)
                ->url(json_encode(['type'=>1] ))
            )
            ->add_content(Message::simple()->content('查看。'))
            ->set_type(MessageType::shipin_beidashang)
            ->set_uid($target_uid)
            ->send();
        
            $this->log_robot($uid,  $price, $room_id, $target_uid,$type);
            
        // 谢烨，现在要把多个接口返回给客户    
//             uid 机器人uid
//             nickname 机器人昵称
//             faceimg 机器人头像
//             value 礼物ID
//             bobean 礼物的BO豆
//             expvalue 礼物的经验
//             showMsg 礼物的名字
//             gifturlpath 礼物的图片路径
//             room_id 房间ID 
        $user =  \app\user\model\UserModel::getinstance($uid);    
        $sql = "select * from bb_present where id = {$type}";
        $present = $db->fetchRow($sql);
        //准备返回消息给接口了。
        $Data = array(
            'uid' => $uid,
            'nickname' => $user->get_nickname(),
            'faceimg'  => $user->get_userpic(),
            'value' => $type,
            'bobean' => $bean,
            'expvalue' => $present['experience'],
            'showMsg'  => $present['title'],
            'gifturlpath' => \BBExtend\common\Image::geturl($present['pic']),
            'room_id' =>$room_id,
        );
        return ['data'=>$Data,'code'=>1];       //返回bool
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
        
        $arr = array(
            'user_info' => array(
                "uid" =>$info['uid'],
                "age" =>date('Y') - substr($info['birthday'],0,4),
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
    
    private function log($uid,  $price, $room_id, $target_uid, $present_id)
    {
        //记录此次打赏日志
        $log = new \app\shop\model\Dashang();
        $bean = Currency::present_to_bean($price);
        $db = Sys::get_container_db();
        $sql = "select * from bb_present where id = ?";
        $result = $db->fetchRow($sql,$present_id);
        
        // 2018 03 确定是短视频还是直播。
        $record_type=0;
        $old_record_type = BBRecord::get_table_name($room_id);
        if ($old_record_type=='bb_push') {
            $record_type = 1;
        }
        if ($old_record_type=='bb_record') {
            $record_type = 2;
        }
        
        
        
        $log->data('create_time', time());
        $log->data('uid', $uid );
        $log->data('room_id', $room_id );
        $log->data('target_uid', $target_uid );
        $log->data('gold', $price);
        $log->data('present_id',   $present_id);
        $log->data('present_name', $result['title']);
        $log->data('bean', $bean );
        $log->data('record_type', $record_type );
        $log->data('currency_log_id', \BBExtend\Currency::$last_id );
        
        // 201805 
        $log->data('ip', \BBExtend\common\Client::ip() );
        $log->data('agent', \BBExtend\common\Client::user_agent() );
        
        
        $log->save();
        $time = time();
        //
        $db = Sys::get_container_db();
        $sql ="select count(*) from bb_dashang_ranking where uid = {$uid} and room_id=? ";
        $count = $db->fetchOne($sql, $room_id);
        $bean = Currency::present_to_bean($price);
        if ($count) {
            //原有修改，数值。
            $sql ="update bb_dashang_ranking 
                    set bean_all = bean_all + {$bean},
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
                'bean_all' =>$bean,
                'room_id' =>$room_id,
            ]);
            
        }
        
        $daystr = date("Ymd");
        
        $sql ="select count(*) from bb_dashang_day_ranking where uid = {$uid} and room_id=? 
                  and daystr='{$daystr}'";
        $count = $db->fetchOne($sql, $room_id);
        
        if ($count) {
            //原有修改，数值。
            $sql ="update bb_dashang_day_ranking
            set bean_all = bean_all + {$bean},
            update_time = '{$time}'
            where uid = {$uid}
            and  room_id = ?
            and daystr='{$daystr}'
            ";
            $db->query($sql, $room_id);
        }else {
            //直接添加
            $db->insert("bb_dashang_day_ranking", [
                'uid' =>$uid,
                'target_uid' =>$target_uid,
                'update_time' =>time(),
                'bean_all' =>$bean,
                'room_id' =>$room_id,
                'daystr' => $daystr,
            ]);
        
        }
        
        
        //把视频的表的打赏总数也要加啊。
        $table = BBRecord::get_table_name($room_id);
        $sql ="update {$table} set dashang_bean_all = dashang_bean_all + {$price} where room_id=? ";
        $db->query($sql,$room_id);
    }
   
   
    private function log_robot($uid,  $price, $room_id, $target_uid, $present_id)
    {
        //记录此次打赏日志
        $log = new \app\shop\model\Dashang();
        $bean = Currency::present_to_bean($price);
        $db = Sys::get_container_db();
        $sql = "select * from bb_present where id = ?";
        $result = $db->fetchRow($sql,$present_id);
    
    
        $log->data('create_time', time());
        $log->data('uid', $uid );
        $log->data('room_id', $room_id );
        $log->data('target_uid', $target_uid );
        $log->data('gold', $price);
        $log->data('present_id',   $present_id);
        $log->data('present_name', $result['title']);
        $log->data('bean', $bean );
        $log->data('is_robot',1 );
        
    
        $log->save();
        $time = time();
        //
        $db = Sys::get_container_db();
//         $sql ="select count(*) from bb_dashang_ranking where uid = {$uid} and room_id=? ";
//         $count = $db->fetchOne($sql, $room_id);
//         $bean = Currency::present_to_bean($price);
//         if ($count) {
//             //原有修改，数值。
//             $sql ="update bb_dashang_ranking
//             set bean_all = bean_all + {$bean},
//             update_time = '{$time}'
//             where uid = {$uid}
//             and  room_id = ?
//             ";
//             $db->query($sql, $room_id);
//         }else {
//             //直接添加
//             $db->insert("bb_dashang_ranking", [
//                 'uid' =>$uid,
//                 'target_uid' =>$target_uid,
//                 'update_time' =>time(),
//                 'bean_all' =>$bean,
//                 'room_id' =>$room_id,
//             ]);
    
//         }
    
        $daystr = date("Ymd");
    
       
    
        //把视频的表的打赏总数也要加啊。
        $table = BBRecord::get_table_name($room_id);
        $sql ="update {$table} set dashang_bean_all = dashang_bean_all + {$price} where room_id=? ";
        $db->query($sql,$room_id);
    }
    
    
}