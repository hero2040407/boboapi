<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\fix\MessageType;

/**
 * 叫号系统，
首先，用户持续签到。
在这过程中，客户端设置第一轮。

然后，客户端请求签到列表。
我返回一个数组给客户端 ，

客户端根据我返回的，开始叫一个号。或者过一个号。
差别是：叫号移走(从两个队列中)，并设为当前号。             过号移动到另外一个队列。
共通前提：必须设置一个待叫号队列。


我记录当前叫号，
 * 
 * 
 * @author Administrator
 *
 */
class Round  extends Common
{
    /**
     * redis 的到期时间，暂定为一个星期。
     */
    private function  get_expire()
    {
        return 7* 24 * 3600;
    }
    
    // 获得redis签到列表的键
    private function get_key_signin_list($field_id){
        return "race:signin:list:".intval( $field_id ) ;
    }
    
    // 获得redis待叫号列表的键
    private function get_key_calling_list($field_id, $round){
       return "race:calling:list:".intval( $field_id ).":round:".intval($round) ;
    }
    
    // 获得redis通过对用户列表的键。
    private function get_key_calling_success_list($field_id, $round){
        $round = intval( $round );
        return "race:calling:list:".intval( $field_id ).":round:{$round}:success" ;
    }
    
    // 获得redis失败用户列表的键。
    private function get_key_calling_fail_list($field_id, $round){
        $round = intval( $round );
        return "race:calling:list:".intval( $field_id ).":round:{$round}:fail" ;
    }
    
    // 获得redis当前号的键。
    private function get_key_calling_current($field_id, $round){
        $round = intval( $round );
        return "race:calling:list:".intval( $field_id ).":round:{$round}:current" ;
    }
    
    //获得 redis过号列表的键
    private function get_key_calling_ignore_list($field_id, $round){
        $round = intval( $round );
        return "race:calling:list:".intval( $field_id ).":round:{$round}:ignore" ;
    }
    
    // 获得redis正向的列表的键。带序号。
    private function get_key_calling_sort_list($field_id, $round){
        $round = intval( $round );
        return "race:calling:list:".intval( $field_id ).":round:{$round}:sort" ;
    }
    
    // 得到redis 用户的键。
    private function get_key_userinfo($field_id,$uid){
        return "race:field_id:{$field_id}:user:hash:".intval( $uid ) ;
    }
    
    // 得到redis round的键。
    private function get_key_round($field_id){
        return "race:field_id:{$field_id}:round" ;
    }
    
    // 得到redis 用户分值的哈希的键。
    private function get_key_score_hash($field_id, $round ){
      //  $round = $this->get_round($field_id);
        return "race:field_id:{$field_id}:round:".$round. ":hash"  ;
    }
    
    
    // 得到redis 用户分值的哈希的键。
    private function get_key_current_max_sort($field_id, $round ){
        //  $round = $this->get_round($field_id);
        return "race:field_id:{$field_id}:round:".$round. ":maxsort"  ;
    }
    
    
    private function add_max_sort($field_id, $round)
    {
        $redis = Sys::get_container_redis();
        $key = $this->get_key_current_max_sort($field_id, $round);
        $redis->incr($key);
        $redis->setTimeout( $key, $this->get_expire() );
        
    }
    
    private function set_max_sort($field_id, $round,$sort)
    {
        $redis = Sys::get_container_redis();
        $key = $this->get_key_current_max_sort($field_id, $round);
        $result = $redis->set($key, $sort);
        $redis->setTimeout( $key, $this->get_expire() );
        
    }
    
    private function get_max_sort($field_id, $round)
    {
        $redis = Sys::get_container_redis();
        $key = $this->get_key_current_max_sort($field_id, $round);
        $result = $redis->get($key);
        if ($result===false) {
            return 0;
        }
        return $result;
    }
    
    
    /**
     * 根据用户id，得到他的叫号序号。
     * 
     * @param int $field_id
     * @param int $round
     * @param int $uid
     * @return int
     */
    private function get_sort_by_uid($field_id, $round,$uid)
    {
        $redis = Sys::get_container_redis();
        $key = $this->get_key_calling_sort_list($field_id, $round);
        $sort = $redis->zScore($key, $uid);
        if ($sort!==false) {
            return $sort;
        }else {
            return 0;
        }
    }
    
    
    /**
     * 根据用户id，得到他的某论的打分。。1晋级，2失败，0未打分
     *
     * @param int $field_id
     * @param int $round
     * @param int $uid
     * @return int
     */
    private function get_score_by_uid($field_id,$round, $uid)
    {
        $redis = Sys::get_container_redis();
      //  $round = $this->get_round($field_id);
        $key = $this->get_key_score_hash($field_id, $round);
        
        $score = $redis->hGet( $key, $uid );
        if ($score===false) {
            return 0;
        }
        
        return intval($score);
        
    }
    
    /**
     * 得到该赛区的轮次。
     *  
     * @param int $field_id
     * @return int
     */
    private function get_round($field_id)
    {
        $redis = Sys::get_container_redis();
        $key = $this->get_key_round($field_id);
        $result = $redis->get($key);
        return intval( $result );
        
    }
    
    
    /**
     * 数组转换
     * 
     * 把一个uid数组，转换成带有个人信息，和叫号序号的数组。
     * 
     * @param array $list
     */
    private function arr_convert($field_id, $list) 
    {
        if (is_array($list)) {
        //    Sys::debugxieye(12);
            $new=[];
            $round = $this->get_round($field_id);
            foreach ($list as $uid) {
          //      Sys::debugxieye("1233:{$uid}");
                
                $temp =  $this->get_other_userinfo($field_id, $uid);
                if ( !$temp) {
                    continue;
                }
                $sort = $this->get_sort_by_uid($field_id, $round, $uid);
                $temp['sort'] = $sort;
                $new[]= $temp;
            }
            return $new;
        }
        
        $round = $this->get_round($field_id);
        $uid = $list;
        $temp =  $this->get_other_userinfo($field_id, $uid);
        if ( !$temp) {
            return null;
        }
        $temp['sort'] = $this->get_sort_by_uid($field_id, $round, $uid);
        $temp['score'] = $this->get_score_by_uid($field_id,$round, $uid);
        
        return $temp;
        
    }
    
    
    
    private function get_other_userinfo($field_id, $uid)
    {
        $redis = Sys::get_container_redis();
        $user_key = $this->get_key_userinfo($field_id,$uid);
        return $redis->hGetAll($user_key);
    }
    
    private function check_in_success_list($uid,$field_id,$round)
    {
        $redis = Sys::get_container_redis();
        $success_key = $this->get_key_calling_success_list($field_id, $round);
//         $fail_key    = $this->get_key_calling_fail_list($field_id, $round);
         $arr = $redis->lrange( $success_key ,0,-1 );
         return in_array($uid, $arr);
         
    }
    private function check_in_fail_list($uid,$field_id,$round)
    {
        $redis = Sys::get_container_redis();
//         $success_key = $this->get_key_calling_success_list($field_id, $round);
        $fail_key    = $this->get_key_calling_fail_list($field_id, $round);
        $arr = $redis->lrange( $fail_key ,0,-1 );
        return in_array($uid, $arr);
    }
    
    
    
    /**
     * 公共字段返回
     *
     * @param int $field_id
     * @param int $round
     * @return
     */
    public function get_public_query_common($field_id )
    {
        $redis = Sys::get_container_redis();
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field_id) {
            return ['code'=>400,'message' => '赛区错误' ];
        }
        $common = $this->get_common($field_id, $field->round);
        return ['code' =>1,'data'=>[
                'common' => $common,
        ] ];
    }
    
    
    /**
     * 当前表演者
     *
     * @param int $field_id
     * @param int $round
     * @return
     */
    public function get_public_query_current($field_id )
    {
        $result = $this->get_public_query_common($field_id);
        return [
                'code'=>1,
                'data'=> [
                        'current' => $result['data']['common']['current'],
                ],
        ];
    }
    
    
    /**
     * 等待列表
     *
     * @param int $field_id
     * @param int $round
     * @return
     */
    public function get_public_query_waiting_list($field_id )
    {
        $result = $this->get_public_query_common($field_id);
        return [
                'code'=>1,
                'data'=> [
                        'waiting_list' => $result['data']['common']['waiting_list'],
                ],
        ];
    }
    

    /**
     * 晋级列表
     *
     * @param int $field_id
     * @param int $round
     * @return
     */
    public function get_public_query_success_list($field_id )
    {
        $result = $this->get_public_query_common($field_id);
        return [
                'code'=>1,
                'data'=> [
                        'success_list' => $result['data']['common']['success_list'],
                ],
        ];
    }
    
    
    /**
     * 过号列表
     *
     * @param int $field_id
     * @param int $round
     * @return
     */
    public function get_public_query_ignore_list($field_id )
    {
        $result = $this->get_public_query_common($field_id);
        return [
                'code'=>1,
                'data'=> [
                        'ignore_list' => $result['data']['common']['ignore_list'],
                ],
        ];
    }
    
    
    
    /**
     * 公共字段返回
     * 
     * @param int $field_id
     * @param int $round
     * @return 
     */
    public function get_common($field_id, $round )
    {
        
        $redis      = Sys::get_container_redis();
        $key_waiting   = $this->get_key_calling_list($field_id, $round);
        $key_ignore = $this->get_key_calling_ignore_list($field_id, $round);
        $key_success = $this->get_key_calling_success_list($field_id, $round);
        $key_current = $this->get_key_calling_current($field_id, $round);
        
        $waiting_list = $redis->lrange( $key_waiting,0,-1 );
        $ignore_list = $redis->lrange( $key_ignore, 0,-1 );
        $success_list = $redis->lrange( $key_success ,0,-1 );
        
        if ( $success_list ) {// 晋级列表需倒序。
            $success_list = array_reverse($success_list);
        }
        
        
        $current = $redis->get( $key_current );
     //   Sys::debugxieye($waiting_list);
        $temp1 = $this->arr_convert($field_id, $waiting_list);
        $temp2 = $this->arr_convert($field_id, $ignore_list);
        $temp3 = $this->arr_convert($field_id, $success_list);
        $temp4 = $this->arr_convert($field_id, $current);
        
        return [
                'waiting_list' => $temp1 ,
                'ignore_list'  => $temp2,
                'success_list'  => $temp3 ,
                'current'  => $temp4,
                'round'    => $this->get_round($field_id),
        ];
    }
    
    
    /**
     * 比赛开始，或者 轮次增加。
     * 
     * @param int $field_id
     * @return 
     */
    public function add_round($field_id)
    {
        $redis = Sys::get_container_redis();
        
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field) {
            return ['code'=>400,'message'=>'id err'];
        }
        $round = $field->round;
        if ($round >=1) {
            $key = $this->get_key_calling_list($field_id, $round);
            $size =$redis->lSize($key) ;
            if ($size !==false && $size >0) {
                return ['code'=>400,'message' =>('还有'. $size .'人没有比赛，不能增加轮次') ];
            }
        }
        $field->round = $field->round +1;
        $field->save();
        
        $round = $field->round;
        $key = $this->get_key_calling_current($field_id, $round);
        $redis->delete( $key );
        
        if ($round > 1) {
            $key = $this->get_key_calling_list($field_id, $round);
            $redis->delete( $key );
        }
        
        $key = $this->get_key_calling_success_list($field_id, $round);
        $redis->delete( $key );
        $key = $this->get_key_calling_fail_list($field_id, $round);
        $redis->delete( $key );
        $key = $this->get_key_calling_ignore_list($field_id, $round);
        $redis->delete( $key );

        // 设置round缓存，查询速度快。
        $round_key = $this->get_key_round($field_id);
        $redis->set($round_key, $field->round);
        
        $redis->setTimeout( $round_key, $this->get_expire() );
        
        // 重要，创建等待列表。
        $list = $this->create_calling($field_id);
        
        // 重要，给等待列表加上序号，保存到一个特定的排序集合里。
        $key = $this->get_key_calling_sort_list($field_id, $round);
        $i=0;
        foreach ($list as $v) {
            $i++;
            $redis->zAdd( $key, $i, $v );
            $redis->setTimeout( $key, $this->get_expire() );
        }
        
    //    $common = $this->get_common($field_id, $field->round);
        return ['code' =>1,'data'=>[
                'round' => $field->round,
            //    'common' => $common,
        ] ];
        
    }
    
    
    /**
     * 结束比赛
     * 
     * @param int $field_id
     * @return 
     */
    public function finish($field_id)
    {
        $redis = Sys::get_container_redis();
        
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field) {
            return ['code'=>400,'message'=>'id err'];
        }
        
        if ( $field->status==3 ) {
            return ['code'=>400,'message'=>'已经结束'];
        }
        
        // 查询是否有剩余。
        $round = $field->round;
        if ($round >=1) {
            $key = $this->get_key_calling_list($field_id, $round);
            $size =$redis->lSize($key) ;
            if ($size !==false && $size >0) {
                return ['code'=>400,'message' =>('还有'. $size .'人没有比赛，不能增加轮次') ];
            }
        }
        
        // 最终记录在用户报名日志表中。
        $field->status = 3;
        $field->save();
        // 同时，记录redis中 的成功记录，然后保存到表里。
        $key = $this->get_key_calling_success_list($field_id, $round);
        $list = $redis->lrange( $key,0,-1 );
        $list = (array)$list;
        $time = time();
        $db = Sys::get_container_db_eloquent();
        
        
        $client = new \BBExtend\service\pheanstalk\Client();
        foreach ($list as $v){
            //表中记录成功。
            $db::table("ds_register_log")->where('ds_id',$field_id)->where( "uid",$v )->update(
                    [
                            "is_finish"=>1,
                            'finish_time'=>$time,
                            'race_status'=> 12,
                    ]
                    );
             
            // 谢烨，当用户成功晋级时， 需发送通知给 该用户。
            $client->add(
                    new \BBExtend\service\pheanstalk\Data($v ,
                            MessageType::dasai_message  ,
                            ['small_type' => 'race_msg_promote',
                                    'field_id'   => $field_id,
                                    'round'   => $round,
                            ],
                            time()  )
                    );  
            // 记录日志
            $client->add(
                    new \BBExtend\service\pheanstalk\Data($v ,
                            MessageType::dasai_message  ,
                            ['small_type' => 'race_log',
                                    'ds_id'   => $field->race_id,
                                    'success'   => 1,
                            ],
                            time()  )
                    );  
        }
        
        
        // 表中记录失败。
        $key = $this->get_key_calling_fail_list($field_id, $round);
        $list2 = $redis->lrange( $key,0,-1 );
        $list2 = (array)$list2;
        foreach ($list2 as $v){
            // 表记录失败。
            $db::table("ds_register_log")->where('ds_id',$field_id)->where( "uid",$v )->update(
                    [
                            "is_finish"=>2,
                            'finish_time'=>0,
                            'race_status'=> 13,
                    ]
                    );
            $client->add(
                    new \BBExtend\service\pheanstalk\Data($v ,
                            MessageType::dasai_message  ,
                            ['small_type' => 'race_log',
                                    'ds_id'   => $field->race_id,
                                    'success'   => 2,
                            ],
                            time()  )
                    );  
        }
        
        
        
        return ['code' =>1,'data'=>[
                'success_count' => count( $list ),
        ] ];
    }
    
    
    public function sub_round($field_id)
    {
        $db = Sys::get_container_db_eloquent();
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if ($field_id) {
            return ['code'=>400,'message'=>'id err'];
        }
        $field->round = $field->round -1;
        $field->save();
        return ['code' =>1,'data'=>[
                'round' => $field->round,
        ] ];
        
    }
    
    
    /**
     * 创建等待列表，
     * 
     * @param int $field_id
     * @return boolean
     */
    private function create_calling($field_id)
    {
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        $redis = Sys::get_container_redis();
        if ($field->round == 0) {
            return false;
        }
        
        if ($field->round == 1 ) {
            $key = $this->get_key_calling_list($field_id, $field->round);
            $list = $redis->lrange($key, 0, -1);
            return $list;
        }
        if ($field->round > 1 ) {
            // 此时，取签到数据。
            $read_key = $this->get_key_calling_success_list($field_id, $field->round-1);
            $write_key = $this->get_key_calling_list($field_id, $field->round);
            
            $list = $redis->lrange($read_key, 0, -1);
            foreach ($list as $v) {
                $redis->rPush($write_key, $v);
                $redis->setTimeout( $write_key, $this->get_expire() );
            }
            return $list;
        }
    }
    
    
    /**
     * 叫号 
     * 
     * 叫号时，可以选等待列表，过号列表里的人。并转移到当前表演者。
     * 
     * @param int $field_id
     * @param int $uid
     */
    public function calling($field_id, $uid)
    {
        Sys::debugxieye(3334);
        
        $redis = Sys::get_container_redis();
        
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field_id) {
            return ['code'=>400,'message' => '赛区错误' ];
        }
        // 注意，只有报名阶段 和比赛阶段。，才能签到。
        if ( $field->is_valid ==0 || $field->status == 0 || $field->status == 3   ) {
            return ['code'=>400,'message' => '赛区未激活，或者赛区已经结束' ];
        }
        
        // 必须从第一轮开始，才能叫号。
        if ( $field->round < 1  ) {
            return ['code'=>400,'message' => '第1轮比赛尚未开始，请等待' ];
        }
        
        // 叫第一个号时，会生成所有的记录。
        $list_key = $this->get_key_calling_list($field_id,  $field->round);

        // 从两个列表中，把值移走！！
        $result =false;
        $temp1 = $redis->lSize($list_key);
        if ($temp1 >0) {
            $temp2 = $redis->lRem($list_key, strval( $uid ), 0);
            if ($temp2){
                $result = true;
            }
        }
        $ignore_key = $this->get_key_calling_ignore_list($field_id, $field->round);
        $temp1 = $redis->lSize($ignore_key);
        if ($temp1 >0) {
            $temp2 = $redis->lRem($ignore_key, strval( $uid ), 0);
            if ($temp2){
                $result = true;
            }
        }
        
        
        // 再设置为当前值。
        if ($result) {
            $current_key = $this->get_key_calling_current($field_id,  $field->round);
            $redis->set($current_key, $uid);
            $redis->setTimeout( $current_key, $this->get_expire() );
            return ['code'=>1,'data'=>[
       //             'common' => $this->get_common($field_id, $field->round),
            ] ];
        }else {
            return ['code'=>400,'message'=>'被叫号码不存在' ];
        }
        
    }
    
   
    /**
     * 过号
     * 
     * 可以选等待列表里的人，或当前表演者。 并转移到过号列表
     * 
     * @param int $field_id
     * @param int $uid
     * @return 
     */
    public function ignore($field_id, $uid)
    {
        $redis = Sys::get_container_redis();
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field_id) {
            return ['code'=>400,'message' => '赛区错误' ];
        }
        // 必须从第一轮开始，才能叫号。
        if ( $field->round < 1  ) {
            return ['code'=>400,'message' => '第1轮比赛尚未开始，请等待' ];
        }
        $list_key = $this->get_key_calling_list($field_id,  $field->round);
        
        $result =false;
        $temp1 = $redis->lSize($list_key);
        if ($temp1 >0) {
            $temp2 = $redis->lRem($list_key, strval( $uid ), 0);
            if ($temp2){
                $result = true;
            }
        }
        
        $key_current = $this->get_key_calling_current($field_id, $field->round );
        $current = $redis->get($key_current);
        if ($current==$uid) {
            $result = true;
            $redis->delete($key_current);
        }
        
        
        if ( $result ) {
        // 叫第一个号时，会生成所有的记录。
           $key = $this->get_key_calling_ignore_list($field_id, $field->round );
           $redis->rPush($key, $uid );
           $redis->setTimeout( $key, $this->get_expire() );
      //     $common = $this->get_common($field_id, $field->round);
        //   Sys::debugxieye($common);
           return ['code' =>1,'data'=>[
    //               'common' => $common,
           ] ];
        }else {
            return ['code' =>400,'message'=>'过号失败'];
        }
        
    }
    
    
    /**
     * 打分
     * 
     * @param int $field_id
     * @param int $status 1成功， 2失败
     * @return 
     */
    public function comment( $field_id, $status )
    {
        $db = Sys::get_container_db_eloquent();
        $redis = Sys::get_container_redis();
        
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field_id) {
            return ['code'=>400,'message' => '赛区错误' ];
        }
        $round = $field->round;
        // 叫第一个号时，会生成所有的记录。
        $current_key = $this->get_key_calling_current($field_id,  $round);
        $uid = $redis->get($current_key);
        if ($uid === false) {
            return ['code'=>400,'message'=>'未选中用户'];
        }
        $success_key = $this->get_key_calling_success_list($field_id, $round);
        $fail_key    = $this->get_key_calling_fail_list($field_id, $round);
        
        
        if ($this->check_in_success_list($uid, $field_id, $round)){
            return ['code'=>400,'message'=>'该用户已经被审核通过，不能重复审核'];
        }
        if ($this->check_in_fail_list($uid, $field_id, $round)){
            return ['code'=>400,'message'=>'该用户已经被审核失败，不能重复审核'];
        }
        
        
        $round = $this->get_round($field_id);
        $key_score = $this->get_key_score_hash($field_id, $round);
        if ($status==1) {
            
            $redis->hSet($key_score, $uid, 1);
            $redis->setTimeout( $key_score, $this->get_expire() );
            $redis->rPush( $success_key, $uid );
            $redis->setTimeout( $success_key, $this->get_expire() );
            
            
            
        }else {
            $redis->hSet($key_score, $uid, 2);
            $redis->setTimeout( $key_score, $this->get_expire() );
            $redis->rPush( $fail_key, $uid );
            $redis->setTimeout( $fail_key, $this->get_expire() );
        }
        return ['code'=>1];
    }
    
    
    /**
     * 签到列表
     * 
     * @param int $field_id
     * @return 
     */
    public function signin_index($field_id)
    {
        $time = time();
        
        $db = Sys::get_container_db_eloquent();
        $redis = Sys::get_container_redis();
        $key = $this->get_key_signin_list($field_id);
        $result = $redis->lrange( $key, 0, -1 );
        
        $new= $this->arr_convert($field_id, $result);
        return ['code'=>1, 'data'=>['list' => $new,
                ] ];
        
    }
    
    
    /**
     * 签到。
     * 
     * @param int $field_id
     * @param int $uid
     * @return 
     */
    public function signin($field_id,$uid)
    {
        $db = Sys::get_container_db_eloquent();
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field) {
            return ['code'=>400,'message' => '赛区错误' ];
        }
        // 注意，只有报名阶段 和比赛阶段。，才能签到。
        if ( $field->is_valid ==0 || $field->status == 0 || $field->status == 3   ) {
            return ['code'=>400,'message' => '赛区未激活，或者赛区已经结束' ];
        }
        
        // 注意，只有第2轮之前，才能签到。
        if ( $field->round >1  ) {
            return ['code'=>400,'message' => '第2轮比赛已经开始，无法再签到' ];
        }
        
        $user = \BBExtend\backmodel\User::find( $uid );
        if (!$user) {
            return ['code'=>400,'message' => '用户错误' ];
        }
        
        // 确保没有重复添加。
        $sql="select * from ds_register_log where ds_id=? and uid =? ";
        $row = DbSelect::fetchRow($db, $sql,[ $field_id, $uid ]);
        if (!$row) {
            return ['code'=>400, 'message' =>'该用户未报名' ];
        }
        if ( $row['has_pay']==0 || $row['has_dangan']==0  ) {
            return ['code'=>400, 'message' =>'该用户未缴费或未填写完整个人信息' ];
        }
        if ( in_array( $row['race_status'], [11,12, 13] )  ) {
            return ['code'=>400, 'message' =>'该用户已签到过' ];
        }
        
        // 设置签到成功。
        $db::table('ds_register_log')->where('id', $row['id'])->update([
                'race_status' => 11,
                'signin_time' =>time(),
        ]);
        
        
        // 添加到签到列表
        $redis = Sys::get_container_redis();
        $key = $this->get_key_signin_list($field_id); 
        $redis->rPush( $key, $uid );
        $redis->setTimeout( $key, $this->get_expire() );
        // 添加到 等待列表
        $round = $field->round;
        if ($round==0 || $round==1) {
            $key = $this->get_key_calling_list($field_id, 1);
            $redis->rPush( $key, $uid );
            $redis->setTimeout( $key, $this->get_expire() );
            // 这里的用途，当比赛开始后，依然接受签到，且查找最大序号加1，保存到等待列表中。
            if ($round==1) {
                $key_sort =  $this->get_key_calling_sort_list($field_id, $round);

                // xieye ,得到最大的序号。
                $temp = $redis->zRevRange($key_sort, 0, 0, true);
                $temp = array_values($temp);
                $temp = $temp[0];
               
                $temp++;
                $redis->zAdd( $key_sort, $temp, $uid );
                $redis->setTimeout( $key_sort, $this->get_expire() );
            }
            
        }
        
        // 保存用户信息到redis
        $user_key = $this->get_key_userinfo($field_id,$uid);
        $temp = [
                'uid' => $user->uid,
                'realname' => $row['name'],
                'pic' => $user->field_pic( $field_id ),
               
        ];
        $redis->hset($user_key, 'uid', $temp['uid']);
        $redis->hset($user_key, 'realname', $temp['realname']);
        $redis->hset($user_key, 'pic', $temp['pic']);
        
        
        return ['code'=>1,];
        
    }
    
    
   
  
    
        
}




