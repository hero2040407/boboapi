<?php
namespace app\game\controller;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\MoneyRainLog;
use BBExtend\common\Client;

//*
//任务抽奖改成7天签到抽奖*/
class  Moneyrain
{
    const pay = 20;
    
    public function test1(){
        echo 12;
    }
    
    /**
     * 查询数据库，得到天降红包的配置
     * 
     * @return array
     */
    private function get_config()
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_config_str where type=?";
        $config_arr=DbSelect::fetchAll($db, $sql,
                [ \BBExtend\fix\TableType::bb_config_str__type_tianjiang ]);
        $active= $ratio = $count = '';
        
        foreach ( $config_arr as $row ) {
            if ($row['config'] == 'tianjiang_active' ) {
                $active = $row['val'];
            }
            if ($row['config'] == 'tianjiang_ratio' ) {
                $ratio = $row['val'];
            }
            if ($row['config'] == 'tianjiang_count' ) {
                $count = $row['val'];
            }
        }
        return [ json_decode(  $active,1), json_decode(  $ratio,1), $count ];
    }

    
    
    /**
     * 根据传来的参数判断，是否当前可以参加红包游戏
     * 
     * @param array $active
     * @return boolean
     */
    
    private function right_time($active)
    {
        // 先确定时间，
        $is_active=false;
        $ctime = time();
        foreach ( $active as $segment ) {
            if ( $ctime> $this->gettime($segment[ 'start' ]) &&
                    $ctime < $this->gettime($segment[ 'end' ])
                    ) {
                        $is_active = true;
                    }
        }
        return $is_active;
    }
    
    private function get_active_time_info($active)
    {
        // 先确定时间，
        $is_active=false;
        $ctime = time();
        $info="每天";
        $temp='';
        foreach ( $active as $segment ) {
            $temp.=$segment[ 'start' ].'至'. $segment[ 'end' ].'，';
        }
        $temp = trim( $temp,'，' );
        
        $info=$info.$temp."游戏开放";
        return $info;
    }
    
    public function index($token='',$uid=0  )
    {
        return ;
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'uid error'];
        }
        
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        
        //首先我查是否在时间段内。
        list($active, $ratio, $count  ) = $this->get_config();
        $active_info = $this->get_active_time_info($active);
        // 先确定时间，
        $is_active = $this->right_time($active);
        if (!$is_active) {
            return ['code'=>0,'message' => '请在游戏开放的时间来玩吧！' ];
        }
        $pay = self::pay;
        // 查用户的钱是否够
        if ($user->currency->gold < $pay ) {
            return ['code'=>0,'message' => '您的波币不够，'.'至少需要'.$pay."BO币" ];
        }
        
        // 查用户当天是否已玩过。
        // 查用户当前是否玩过
        $has_play = MoneyRainLog::where( "uid",$uid )->where('datestr',date("Ymd") )->first();
        if ($has_play) {
            return ['code'=>0,'message' => '您今天已经玩过了，请明日再来！' ];
        }
        
        return ['code'=>1,'data'=>['server_time' => time(),
                'gold' => $user->currency->gold,
                'active_time' => $this->get_active_time_info($active),
                
        ]];
    }
   
    
    
    /**
     * 开始玩，我应该返回一些id，每个id带了值。
     */
    public function play($token='',$uid=0)
    {
        
        return ;
        
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'uid error'];
        }
        
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        
        //首先我查是否在时间段内。
        list($active, $ratio, $count  ) = $this->get_config();
//         $ratio['x2']=100;
//         $ratio['x3']=100;
        
        // 先确定时间，
        $is_active = $this->right_time($active);
        if (!$is_active) {
            return ['code'=>0,'message' => '请在游戏开放的时间来玩吧！' ];
        }
        $pay = self::pay;
        // 查用户的钱是否够
        if ($user->currency->gold < $pay ) {
            return ['code'=>0,'message' => '您的波币不够，'.'至少需要'.$pay."BO币" ];
        }
        
        // 查用户当前是否玩过
        $has_play = MoneyRainLog::where( "uid",$uid )
          ->where('balance_time','>',0)
          ->where('datestr',date("Ymd") )->first();
        if ($has_play) {
            return ['code'=>0,'message' => '您今天已经玩过了，请明日再来！' ];
        }

        // 扣除波币开始
       \BBExtend\Currency::add_bobi($uid, 0 - self::pay, '天降红包费用', 951);
        
        
        // 我要给他什么
        // 谢烨，首先，我先给每个都搞定，然后，我做修正，确保翻倍最多只有一个，或没有。
        $new=[];
        $id_arr = $this->get_rand_id_arr($count);
        
        $has_x =0;
        foreach ( $id_arr as $v  ) {
            $result = $this->get_rand_val($ratio, $has_x );
            if ($result==='x2' || $result==='x3') {
                $has_x=1;
            }
            
            if ($result == '-1') {
                $result = 0 - mt_rand(1,30);
            }
            
            $new[ intval( $v)] =  strval( $result);
            
            //$rand_id = mt_rand()
        }
        
        MoneyRainLog::where( "uid",$uid )
           ->where('datestr',date("Ymd") )->delete();
        
        
        $log = new MoneyRainLog();
        $log->uid = $uid;
        $log->user_agent = Client::user_agent();
        $log->play_id_arr = serialize($new) ;
        $log->pay = 0- self::pay;
        $log->datestr = date("Ymd");
        
        $log->create_time = time();
        
        $log->save();
        
        
        return ['code'=>1,'data'=>[
             'server_time' => time(),
            // 'list' =>$new,   
                'list'        => base64_encode (json_encode( $new, JSON_UNESCAPED_UNICODE )) ,    
        ]];
        
    }
    
    
    /**
     * 结算用
     *
     * @param string $token
     * @param number $uid
     * @param string $ids
     */
    public function balance($token='',$uid=0,$ids='')
    {
        
        return ;
        
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'uid error'];
        }
        
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        list($active, $ratio, $count  ) = $this->get_config();
        //         $ratio['x2']=100;
        //         $ratio['x3']=100;
        
        // 先确定时间，
        $is_active = $this->right_time($active);
        if (!$is_active) {
            return ['code'=>0,'message' => '请在游戏开放的时间来玩吧！' ];
        }
        
        // 查当前用户是否有记录，且在一分钟内！！
        $log = MoneyRainLog::where( "uid",$uid )->where('datestr',date("Ymd") )->first();
        if (!$log) {
            return ['code'=>0,'message' => 'error1' ];
        }
        if ($log->balance_time>0) {
            return ['code'=>0,'message' => '已结算' ];
        }
        if ( (time() -  $log->create_time) >  60 ) {
            return ['code'=>0,'message' => 'error2' ];
        }
        $agent = Client::user_agent();
        if ($agent != $log->user_agent ) {
            return ['code'=>0,'message' => 'error3' ];
        }
        
        $play_id_arr = unserialize($log->play_id_arr  );
        $play_id_arr_key = array_keys($play_id_arr );
        
        $id_arr = explode(',', $ids) ;
        foreach ( $id_arr as $v ) {
            if (!in_array( $v, $play_id_arr_key )) {
                return ['code'=>0,'message' => 'id error' ];
            }
        }
        
        $x = 1;
        foreach ( $id_arr as $v ) {
            if ($play_id_arr[ $v ] ==='x2'  ) {
                $x = 2;
            }
            if ($play_id_arr[ $v ] ==='x3'  ) {
                $x = 3;
            }
        }
        
        $zhadan_count=0;
        $zhadan_sum = 0;
        foreach ( $id_arr as $v ) {
            $temp = $play_id_arr[ $v ];
            if (is_numeric($temp) && $temp < 0  ) {
                $zhadan_sum += $temp;
                $zhadan_count++;
            }
        }
        
        $zhonglei_arr=[];
        foreach ( $id_arr as $v ) {
            $temp = $play_id_arr[ $v ];
            if (is_numeric($temp) && $temp >= 0  ) {
                // $zhadan_sum += $temp;
                $zhonglei_arr[] = $temp;
            }
        }
        // 去除重复
        $zhonglei_arr = array_unique( $zhonglei_arr );
        $new=[];
        foreach ($zhonglei_arr as $v) {
            $new[ $v ]=0;
        }
        
        foreach ( $id_arr as $v ) {
            $temp = $play_id_arr[ $v ];
            if (is_numeric($temp) && $temp >= 0  ) {
                // $zhadan_sum += $temp;
                $new[ $temp ]++;
            }
        }
        
        // 总共有翻倍，炸弹，普通，3种。
        $result=[];
        foreach ( $new as $k => $v ) {
            $temp =[];
            $temp[ 'name' ] = $k . "BO币红包";
            $temp[ 'count' ] = $v;
            $temp[ 'sum' ] = $k * $v;
            $result[]= $temp;
        }
        $result[]= [
                'name' => '红包炸弹',
                'count' =>  $zhadan_count,
                'sum' => $zhadan_sum,
        ];
        $all_count =0;
        foreach ( $result as $v ) {
            $all_count += $v['sum'];
        }
        
        
        
        if ($x==1) {
            $result[]= [
                    'name' => '红包翻倍',
                    'count' =>  0,
                    'sum' => $x,
            ];
        }else {
            $result[]= [
                    'name' => '红包翻倍',
                    'count' =>  1,
                    'sum' => $x,
            ];
            $all_count = $all_count * $x;
        }
        
        // 数据库记录
        $log->balance_time= time();
        $log->result_gold = $all_count;
        $log->result_str = json_encode($result,  JSON_UNESCAPED_UNICODE );
        $log->save();
        
        return ['code'=>1,'data'=>[
                'final_count' => $all_count,
                'server_time' => time(),
                'result' => $result,
        ]];
        
    }
    
    
    
//     public function test($has_x=0){
//         list($active, $ratio, $count  ) = $this->get_config();
//         if ($has_x) {
//             $new=[];
//             foreach ($ratio as $k => $v) {
//                 if ( $k != 'x2' && $k != 'x3' ) {
//                     $new[$k ] = $v;
//                 }
//             }
//             $ratio = $new;
//         }
//         $arr = $ratio;
        
// //         return $arr;
        
//         $new=[];
//         foreach ($arr as $k=> $v) {
//             $temp=0;
//             foreach ($arr as $k2=> $v2) {
//                 $temp+= $v2;
//                 if ($k2 === $k) {
//                     break;
//                 }
//             }
//             $new[$k] = $temp;
//         }
//         return $new;
        
//         $rand = mt_rand(1,array_sum($arr));
//         foreach ($new as $k=>$v) {
//             if ($rand <= $v ) {
//                 return $k;
//             }
//         }
        
//     }
    
    
    /**
     * 传来一个概率数组，我根据概率返回键。
     * 
     * @param array $ratio
     * @param number $has_x 0无限制，1屏蔽x。
     */
    private function get_rand_val($ratio,  $has_x=0)
    {
       // {"0":500,"1":300,"2":200,"3":100,"5":50,"8":30,"10":20,"20":10,"30":5,"-1":50,"x2":2,"x3":1}
        if ($has_x===1) {
            $new=[];
            foreach ($ratio as $k => $v) {
                if ( $k !== 'x2' && $k !== 'x3' ) {
                    $new[$k ] = $v;
                }
            }
            $ratio = $new;
        }
        $arr = $ratio;
        
        
        $new=[];
        foreach ($arr as $k=> $v) {
            $temp=0;
            foreach ($arr as $k2=> $v2) {
                $temp+= $v2;
                if ($k2 === $k) {
                    break;
                }
            }
            $new[$k] = $temp;
        }
        
        $rand = mt_rand(1,array_sum($arr));
        foreach ($new as $k=>$v) {
            if ($rand <= $v ) {
                return $k;
            }
        }
        
        
    }
    
    
    /**
     * 得到一个随机数
     * 
     * @param number $count
     */
    private function get_rand_id_arr($count=100)
    {
        $arr= range(100000, 200000);
        $key_arr = array_rand( $arr , $count);
        $new=[];
        foreach ( $key_arr as $k ) {
            $new[] = $arr[$k];
        }
        return $new;
    }
    
    
    
    // 传入 09:00:00 ,返回加上当天日期的时间戳
    private function gettime($time_str)
    {
         $date = date("Y-m-d").' ' . $time_str;
         return strtotime($date);
    }
   
  

}

