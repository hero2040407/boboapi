<?php
namespace app\game\controller;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\MoneyRainLog;
use BBExtend\common\Client;

//*
//任务抽奖改成7天签到抽奖*/
class  MoneyrainV2
{
    //const pay = 20;
    
    /**
     * 查是否是游戏的有效时间
     */
    private function is_valid_time()
    {
        $can_play= \BBExtend\user\MoneyRain::is_valid_time();
        return $can_play;
    }
    
    public function test1(){
        echo 12222;
    }
    
    public function ranking_list($day='')
    {
        $datestr = \BBExtend\user\MoneyRain::get_datestr_by_day($day);
        
        $db = Sys::get_container_db_eloquent();
        $sql="
            select * from bb_money_rain_reward
             where datestr=?
             order by sort asc
";
        $result = DbSelect::fetchAll($db, $sql,[ $datestr ]);
        $new=[];
        foreach ($result as $v) {
            $temp=[];
            $user = \BBExtend\model\User::find( $v['uid'] );
            $temp['score'] = $v['score']  ;
            $temp['sort'] =$v['sort'];
            $temp['money'] = $v['money'];
            $temp['nickname'] = $user->get_nickname();
            $temp['pic'] = $user->get_userpic();
            $temp['sex'] = $user->get_usersex();
            $temp['age'] = $user->get_userage();
            $temp['level'] = $user->get_user_level();
            
            
            $new[]=$temp;
        }
        return ['code'=>1,'data' =>['list' => $new,
                'day' =>   \BBExtend\user\MoneyRain::get_day_by_datestr($datestr),
                
        ] ];
    }
    
    
    public function today_info($token='',$uid=0)
    {
        if (!$this->is_valid_time()) {
            return ["code"=>0,'message'=>'请在规定的时间来玩游戏吧！'];
        }
        
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'uid error'];
        }
        
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
       // $log = MoneyRainLog::where( "uid",$uid )->where('datestr',date("Ymd") )->first();
        
        //if ($log) {
            return ['code'=>1,'data'=>[ 'my'=> \BBExtend\user\MoneyRain::get_today_my_info($uid) ],
                    'top50' => \BBExtend\user\MoneyRain::top50(),
                    'day' => \BBExtend\user\MoneyRain::get_day(),
            ];
            
        
        
    }
    
    
    public function play($token='',$uid=0,$score='')
    {
        if (!$this->is_valid_time()) {
            return ["code"=>0,'message'=>'请在规定的时间来玩游戏吧！'];
        }
        
        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'uid error'];
        }
        
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if (strlen( $score ) < 35 ) {
            return ['code'=>0,'message'=>'param error'];
        }
        
        $scoreold = substr($score, 30, 5);
        $scoreold = intval( $scoreold);
        
        $score = substr($score, 30, 10);
        $score = intval($score);
        $score = $score >999999 ?  $scoreold : $score;
        
        
        
        $time = time();
        
        $datestr = date("Ymd");
        $hour = date('H');
        if ($hour >= 21 ) {
            $datestr = date("Ymd", $time + 24 * 3600);
        }
        
        $log = MoneyRainLog::where( "uid",$uid )->where('datestr',$datestr )->first();
        
        $is_best=0;
        $ranking=0;
        $nickname='';
        $max_score=$log->score;
        
        
        if (!$log) {
            $log = new MoneyRainLog();
            $log->uid = $uid;
            $log->datestr = $datestr;
            $log->balance_time = $log->last_time = $time;
            $log->score = $score;
            $log->today_count=1;
            $log->save();
            $max_score = $score;
        }else {
            if ($score > $log->score ) {
                $log->balance_time = $time;
                $log->score = $score;
                
                $is_best=1;
                $temp = \BBExtend\user\MoneyRain::get_today_my_info($uid);
                $ranking = $temp['ranking'];
                $nickname = \BBExtend\user\MoneyRain::get_compare_target($uid, $temp['score'] );
                $max_score = $score;
            }
            $log->last_time = $time;
            $log->today_count = today_count +1;
            $log->save();
        }
        return ['code'=>1,'data' =>[
                'is_best' =>$is_best,
                'nickname' =>$nickname,
                'ranking'  => $ranking,
                'max_score' => $max_score,
        ] ];
    }
    
    /**
     * 发奖励
     * 
     * 年三十到十五
     * 2月15到3月2日
     * 
     * 
     * @param string $pass
     * @return number[]|string[]
     */
    public function reward($pass='')
    {
//         if (!$this->is_valid_time()) {
//             return ["code"=>0,'message'=>'请在规定的时间开奖'];
//         }
        
        $datestr = $pass;
        if ($pass != $datestr) {
            return ['code'=>0,'message'=>'err' ];
        }
        $time = time();
        $db = Sys::get_container_db_eloquent();
        
        $sql = "select * from bb_money_rain_reward where datestr=?";
        $row = DbSelect::fetchRow($db, $sql,[ $datestr ]);
        if ($row) {
            return ['code'=>0,'message'=>'已经发奖过了' ]; 
        }
        
        
        
        
        $sql="
select * from bb_money_rain_log
where datestr=?
and score >0
order by score desc, balance_time desc
limit 50
";
        $result = DbSelect::fetchAll($db, $sql,[ $datestr ]); 
        $sort=0;
        foreach ( $result as $v ) {
            $sort++;
            if ($sort > 50) {
                break;
            }
            $reward = $this->get_reward_value_by_sort($sort);
            $db::table('bb_money_rain_reward')->insert([
                    'create_time' => $time,
                    'datestr' => $datestr,
                    'sort' => $sort,
                    'uid' => $v['uid'],
                    'score' => $v['score'],
                    'money' => $reward,
            ]);
        }
        
        $sql = "select * from bb_money_rain_reward where datestr=?";
        $rows = DbSelect::fetchAll($db, $sql,[ $datestr ]);
        foreach ($rows as $v) {
            \BBExtend\Currency::add_bobi($v['uid'], $v['money'], '天降红包开奖' );
            
            //要给每个人发消息
            $client = new \BBExtend\service\pheanstalk\Client();
            $client->add(
              new \BBExtend\service\pheanstalk\Data($v['uid'],
                    178,
                    ['datestr' => $v['datestr'],'money' => $v['money'], ], time()  )
                    
            );
        }
        return ['code'=>1 ];
        
    }
  
    
    public function get_reward_value_by_sort($sort)
    {
        $sort=intval($sort);
        if ($sort==1) {
            return 888;
        }
        if ($sort==2) {
            return 666;
        }
        if ($sort==3) {
            return 555;
        }
        if ($sort>=4 && $sort <=10) {
            return 300;
        }
        if ($sort>=11 && $sort <=20) {
            return 200;
        }
        if ($sort>=21 && $sort <=30) {
            return 100;
        }
        if ($sort>=31 && $sort <=50) {
            return 60;
        }
        return 0;
        
    }

}

