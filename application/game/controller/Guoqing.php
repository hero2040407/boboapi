<?php
namespace app\game\controller;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\MoneyRainLog;
use BBExtend\common\Client;

//*
//任务抽奖改成7天签到抽奖*/
class  Guoqing
{

    const starTime = 1538280000;  //2018-09-30 12:00:00s
    const endTime =  1538280000; //2018-10-07 00:00:00
    /**
     * 查是否是游戏的有效时间
     */
    private function valid_time()
    {
        $curent = time();

        if( self::starTime < $curent  &&  $curent >  self::endTime  ){
            return true;
        }
        return false;
    }




    public function check($token='',$uid=0)
    {
        if( self::starTime > $curent   ){
            return ["code"=>0,'message'=>'游戏尚未开始(开始时间9月30日12点)！'];
        }
        if( self::endTime < $curent   ){
            return ["code"=>0,'message'=>'游戏已经结束，下次再来玩哦！'];
        }

        $user = User::find($uid);
        if (!$user) {
            return ["code"=>0,'message'=>'uid error'];
        }

        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        //if ($log) {
        return ['code'=>1,'data'=>[
            'my'=> self::get_today_my_info($uid) ],
            'top50' => \BBExtend\user\MoneyRain::top50(),
            'day' => \BBExtend\user\MoneyRain::get_day(),
        ];
    }




    public static function get_today_my_info($uid)
    {
        $uid = intval($uid);
        $datestr = date("Ymd");
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_money_rain_log where uid=? and datestr= ?";
        $row  = DbSelect::fetchRow($db, $sql,[ $uid,  $datestr]);
        if (!$row) {
            return null;
        }

        $sql="select count(*) from bb_money_rain_log 
             where datestr= ? and score>= ? ";
        $ranking  = DbSelect::fetchOne($db, $sql,[ $datestr, $row['score']]);

        return ['score' =>$row['score'],'ranking' => $ranking  ];

    }




    public static function top50()
    {
        $db = Sys::get_container_db_eloquent();
        $datestr = date("Ymd");

        $sql="
select * from bb_money_rain_log
where datestr=?
and score >0
order by score desc, balance_time desc
limit 50
";
        $result = DbSelect::fetchAll($db, $sql,[ $datestr ]);
        $new=[];
        $i=0;
        foreach ($result as $v) {
            $i++;
            $temp=[];
            $user = \BBExtend\model\User::find( $v['uid'] );
            $temp['nickname'] = $user->get_nickname();
            $temp['pic']      = $user->get_userpic();
            $temp['ranking'] = $i;
            $temp['score'] = $v['score'];

            $new[]= $temp;
        }
        return $new;
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







}

