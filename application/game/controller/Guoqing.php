<?php
namespace app\game\controller;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\GameLog;
use BBExtend\model\GameSources;

use BBExtend\common\Client;

//*
//任务抽奖改成7天签到抽奖*/
class  Guoqing
{
    const gamename = '18Guoqing'; //游戏标示
    const starTime = 1533323200;  //测试用
    //const starTime = 1538323200;  //2018-10-01 00:00:00s
    const endTime =  1538917200; //2018-10-07 21:00:00
    /**
     * 查是否是游戏的有效时间
     */
    private function valid_time()
    {
        $curent = time();
        $hour = date('H');
        if ($hour >= 21 ) {
            return '小朋友今天的比赛已经结束啦，明天再接再厉哦！';
        }
        if( self::starTime > $curent  ){
            return "游戏10月1日才开始哦~";
        }
        if(  $curent >  self::endTime  ){
            return "游戏已经结束啦，下次再来哦~";
        }
        return '';
    }


    //    获取我的今日数据
    private function get_today_my_info($uid)
    {
        $datestr = date("Ymd");
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_game_scores where uid=? and game=? and datestr=?";
        $row  = DbSelect::fetchRow($db, $sql,[ $uid ,self::gamename,  $datestr]);
        if (!$row) {
            return ['score' => 0 , 'ranking' => 9999999  ];
        }
        //
        $sql="select count(*) from bb_game_scores 
             where datestr= ? and score>= ? ";
        $ranking  = DbSelect::fetchOne($db, $sql,[ $datestr, $row['score']]);
        return ['score' =>$row['score'],'ranking' => $ranking   ];
    }

    //    获取我的今日数据
    private function get_ranking($datestr,$source)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select count(*) from bb_game_scores 
             where datestr= ? and game=? and score>= ? ";
        $ranking  = DbSelect::fetchOne($db, $sql,[ $datestr,self::gamename, $source]);
        return $ranking ;
    }

    /**
     * 计算排名时，得给出你超过了谁，需要查一下。
     */
    private function get_compare_target($uid,$score)
    {
        $uid = intval($uid);
        $time = time();
        $datestr = date("Ymd");
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_game_scores where uid !=? and game=? and datestr= ? and score < ? 
           order by score desc limit 1";
        $row  = DbSelect::fetchRow($db, $sql,[ $uid,self::gamename,  $datestr, $score ]);
        if (!$row) {
            return "怪兽BOBO";
        }else {
            $user = \BBExtend\model\User::find( $row['uid'] );
            return $user->get_nickname();
        }
    }

    //获取当天排行榜
    private  function top50()
    {
        $db = Sys::get_container_db_eloquent();
        $datestr = date("Ymd");

        $sql="
select * from bb_game_scores
where datestr=?
and game=?
and score >0
order by score desc, last_time desc
limit 50
";
        $i=0;
        $rank=[];
        $result = DbSelect::fetchAll($db, $sql,[ $datestr ,self::gamename,]);
        foreach ($result as $v) {
            $i++;
            $temp=[];
            $user = \BBExtend\model\User::find( $v['uid'] );
            $temp['nickname'] = $user->get_nickname();
            $temp['pic']      = $user->get_userpic();
            $temp['ranking'] = $i;
            $temp['score'] = $v['score'];
            $rank[]= $temp;
        }
        return $rank;
    }




    public function check($token='',$uid=0)
    {
        $message = $this->valid_time();
        if($message){
            return ["code"=>0,'message'=> $message  ];
        }

        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'用户信息失效请重新登录','relogin'=>'true'];
        }

        if ( !$user->check_token($token ) ) {
            return ["code"=>0,'message'=>'用户信息失效请重新登录','relogin'=>'true'];
        }

        return ['code'=>1, 'data'=>[
            'uid'=> $uid,
             'my'=> self::get_today_my_info($uid) ],
             'ranks' => self::top50(),
             'date' => date("Ymd")
        ];
    }



    //    提交成绩
    public function play($token='',$uid=0,$score='')
    {
        $message = $this->valid_time();
        if($message){
            return ["code"=>0,'message'=> $message  ];
        }

        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'用户信息失效请重新登录','relogin'=>'true'];
        }
        if ( !$user->check_token($token ) ) {
            return ["code"=>0,'message'=>'用户信息失效请重新登录','relogin'=>'true'];
        }

        $time = time();
        $datestr = date("Ymd");

        if (strlen( $score ) < 35 ) {
            return ['code'=>0,'message'=>'参数错误'];
        }

        $score = substr($score, 30, 10);
        $score = intval($score);
        //日志数据
        $log = new GameLog();
        $log->uid = $uid;
        $log->uid = $uid;
        $log->game = "18Guoqing";
        $log->datestr = $datestr;
        $log->score = $score;
        $log->create_time = $time;
        $log->save();

        // 记录排行
        $daylog = GameSources::where( "uid",$uid )->where('game',"18Guoqing")->where('datestr',$datestr )->first();
        $is_best=0;
        $nickname='';
        $max_score = 0;
        if (!$daylog) {
            $daylog = new GameSources();
            $daylog->uid = $uid;
            $daylog->game = "18Guoqing";
            $daylog->datestr = $datestr;
            $daylog->last_time = $time;
            $daylog->score = $score;
            $daylog->times=1;
            $daylog->save();
            $max_score = $score;
        }else {
            if ($score > $daylog->score) {
                $daylog->score = $score;
                $is_best = 1;
                $max_score = $score;
            } else {
                $max_score = $daylog->score;
            }
            $daylog->last_time = $time;
            $daylog->times = $daylog->times + 1;
            $daylog->save();
        }

        return ['code'=>1,'data' =>[
            'score'=>$score,
            'isbest' =>$is_best,
            'nickname' => self::get_compare_target($uid, $max_score ),
            'ranking'  => self::get_ranking($datestr, $max_score),
            'maxscore' => $max_score,
        ] ];
    }




    /**
     * 发奖励
     * 10.1 --- 10.7 每晚9点
     * @param string $pass
     * @return number[]|string[]
     */

    // 计算奖励
    private function get_reward_value_by_sort($sort)
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


    public function reward($pass='')
    {
        //$datestr = $pass;
		$datestr = date("Ymd");
        // if ($pass != $datestr) {
            // return ['code'=>0,'message'=>'日期参数错误' ];
        // }


        $time = time();
        $db = Sys::get_container_db_eloquent();

        $sql = "select * from bb_game_rewards where datestr=? and game=?";
        $row = DbSelect::fetchRow($db, $sql,[ $datestr ,self::gamename]);
        if ($row) {
            return ['code'=>0,'message'=>'今天已经发奖过了' ];
        }

        $sql="
select * from bb_game_scores
where datestr=?
and game=?
and score >0
order by score desc, last_time desc
limit 50
";
		
        $result = DbSelect::fetchAll($db, $sql,[ $datestr,self::gamename ]);
        $sort=0;
        foreach ( $result as $v ) {
            $sort++;
            if ($sort > 50) {
                break;
            }
            $reward = $this->get_reward_value_by_sort($sort);
            $db::table('bb_game_rewards')->insert([
                'create_time' => $time,
                'datestr' => $datestr,
				'game'=>self::gamename,
                'rank' =>    $sort  ,
                'uid' => $v['uid'],
                'score' => $v['score'],
                'money' => $reward,
            ]);
        }

        $sql = "select * from bb_game_rewards where datestr=? and game=?";
        $rows = DbSelect::fetchAll($db, $sql,[ $datestr , self::gamename]);
        foreach ($rows as $v) {
            \BBExtend\Currency::add_bobi($v['uid'], $v['money'], '国庆马拉松' );

            //要给每个人发消息
            $client = new \BBExtend\service\pheanstalk\Client();
            $client->add(
                new \BBExtend\service\pheanstalk\Data($v['uid'],
                    178,
                    ['datestr' => $v['datestr'],'money' => $v['money'], ], time()  )

            );
        }
        return ['code'=>1,'$result'=>$result ,'rank'=> $sort,'$datestr'=>$datestr];
    }
}

