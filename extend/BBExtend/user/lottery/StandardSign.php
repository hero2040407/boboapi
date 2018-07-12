<?php
namespace BBExtend\user\lottery;

/**
 * 判断是否能抽转盘的类
 * 
 * 本类被多处调用
 * 
 * 抽奖时php调用，
 * 签到时http调用。
 * 
 * PlayCountSign 判断签到转盘抽奖的次数，以及修改次数
 * PlaySign：         抽奖类，调用此类对象，进行签到抽奖
 * StandardSign：返回最近7日签到的状况，连续7日表示可能抽奖。
 * 
 * 
 * 谢烨
 */

use BBExtend\Sys;
use BBExtend\DbSelect;

class StandardSign
{
    private $cache;
    private $uid;
    
    /**
     * 
     * @param unknown $uid
     */
    public function  __construct($uid) 
    {
         $this->uid = $uid;
       
    }
    
    public function can_lottery()
    {
        $cache = $this->get_cache();
        return $cache['can_lottery'];
        
    }
    
    public function get_cache() {
        $uid = $this->uid;
        $this->cache = $this->get($uid);
        return $this->cache;
    }
    
    /**
     * 昨天没有，今天没有，全空。
    
     昨天没有，今天有，1 0 0 0 0 0 0
    
     昨天有，今天没有，1 0 0 0 0 0 0 注意这里不一定，因为往前追溯。
     （1）如果昨天是7，全空
     （2）昨天不是7，最近的1显示。
    
    
     昨天有，今天有，  1 1 0 0 0 0 0，注意这里不一定，因为往前追溯。
     （1）最近的1显示。
    
     */
    private function get() 
    {
        $uid = $this->uid;
        // 查是否已经签到过。
        $db = Sys::get_container_db_eloquent();
        $can_lottery=0;
        
        if (in_array( $this->uid, [ 12700,3025547 ] )){
            $can_lottery=10;
        }
        
        
        // 查今日
        $today_signin=0;
        
        $sql="select * from bb_users_signin_log where uid=? and datestr=?";
        $today_row = DbSelect::fetchRow($db, $sql,[ $uid, date("Ymd") ]);
        if ($today_row) {
            $today_signin=1;
        }
        // 再查天的显示。
        $yestoday = date("Ymd", time() - 24* 3600 );
        $sql="select * from bb_users_signin_log where uid=? and datestr=?";
        $yestoday_row = DbSelect::fetchRow($db, $sql,[ $uid, $yestoday ]);
        
        if ( (!$yestoday_row) && (!$today_row)  ) {
            return [
                'list' => $this->get_7day_null() ,
                'can_lottery' => $can_lottery, //
                'toady_has_signin' => $today_signin,
                'default_bonus' =>$this->get_default_bonus_word(),
            ];
        }
        
        if ( (!$yestoday_row) && $today_row  ) {
            return [
                'list' => [
                    ['date'=> $this->get_date_for_date8(date("Ymd")),
                        'has_signin' => 1, 'bonus' => $this->get_default_bonus_word()   ],
                    ['date'=>$this->get_date(2), 'has_signin' => 0, 'bonus' => $this->get_default_bonus_word()   ],
                    ['date'=>$this->get_date(3), 'has_signin' => 0, 'bonus' => $this->get_default_bonus_word()   ],
                    ['date'=>$this->get_date(4), 'has_signin' => 0, 'bonus' => $this->get_default_bonus_word()   ],
                    ['date'=>$this->get_date(5), 'has_signin' => 0, 'bonus' => $this->get_default_bonus_word()   ],
                    ['date'=>$this->get_date(6), 'has_signin' => 0, 'bonus' => $this->get_default_bonus_word()   ],
                    ['date'=>$this->get_date(7), 'has_signin' => 0, 'bonus' => $this->get_default_bonus_word()   ],
                ] ,
                'can_lottery' => $can_lottery, //
                'toady_has_signin' => $today_signin,
                'default_bonus' =>$this->get_default_bonus_word(),
            ];
        }
        // 注意：以下的所有代码，昨天一定存在！！
        if (!$today_row && $yestoday_row && $yestoday_row['order_number']==7 ) {
            return [
                'list' => $this->get_7day_null() ,
                'can_lottery' => $can_lottery, //
                'toady_has_signin' => $today_signin,
                'default_bonus' =>$this->get_default_bonus_word(),
            ];
        }
        
        
         
        //下面是昨天有，但不是第7天。
        // 查出最近的number=1以及之后的所有数据。
        $sql="select * from bb_users_signin_log where uid=? and order_number=1 order by id desc limit 1 ";
        $result = DbSelect::fetchRow($db, $sql,[ $uid,  ]);
        // 保险起见，还是要写这句话，勿删
        if (!$result) {
            return [
                'list' => $this->get_7day_null(),
                'can_lottery' => $can_lottery, //
                'toady_has_signin' => $today_signin,
                'default_bonus' =>$this->get_default_bonus_word(),
            ];
        }
        //  dump($result);
        $sql="select * from bb_users_signin_log where uid=? and id >= ? order by id asc ";
        $result = DbSelect::fetchAll($db, $sql,[ $uid, $result['id'] ]);
        $new =[];
        $can_lottery=1;// 能否抽奖。
        foreach (range(1,7) as $day ) {
            //假设，数据有
            $fetch = array_shift($result);
            //dump($fetch);
            if ($fetch) {
                $temp = ['date'=> $this->get_date_for_date8($fetch['datestr']),
                    'has_signin' => 1,
                    'bonus' => $this->get_bonus_word($fetch['bonus'])   ];
                 
            } else {
                $temp = ['date'=> $this->get_date($day),
                    'has_signin' => 0,
                    'bonus' => $this->get_default_bonus_word()   ];
                $can_lottery=0;
            }
            $new[] = $temp;
        
        }
        return ['list' => $new,
            'can_lottery' => $can_lottery,
            'toady_has_signin' => $today_signin,
            'default_bonus' =>$this->get_default_bonus_word(),
        ] ;
        
    }
    
    /**
     * 根据类似20170101这样的数字，返回1月1日
     *
     * @param unknown $date8
     */
    private function get_date_for_date8($date8)
    {
        $date8=strval($date8);
        $month = preg_replace('/^\d{4}(\d{2})\d{2}$/','$1', $date8);
        $month=intval($month);
        $day = preg_replace('/^\d{4}\d{2}(\d{2})$/','$1', $date8);
        $day=intval($day);
        return $month.'月'.$day.'日';
    }
    
    /**
     * 根据day返回显示的日期。
     * @param unknown $day
     * @return string
     */
    private function get_date($day)
    {
        if ($day == 1) {
            return '第一天';
        } elseif ($day == 2) {
            return '第二天';
        } elseif ($day == 3) {
            return '第三天';
        } elseif ($day == 4) {
            return '第四天';
        } elseif ($day == 5) {
            return '第五天';
        } elseif ($day == 6) {
            return '第六天';
        } elseif ($day == 7) {
            return '第七天';
        } else {
            return '';
        }
    }
    
    public function get_default_bonus()
    {
        return 5;
    }
    
    private function get_default_bonus_word()
    {
        return $this->get_bonus_word($this->get_default_bonus());
    }
    
    
    private function get_bonus_word($bonus)
    {
        return $bonus. 'BO币';
    }
    
    /**
     * 返回连续的空的7天。
     */
    private function get_7day_null()
    {
        $new=[];
        foreach (range(1,7) as $day) {
            $new[] = [
                'date'=> $this->get_date($day), 
                'has_signin' => 0, 
                'bonus' => $this->get_default_bonus_word(),   
            ];
        }
        return $new;
    }
    

}