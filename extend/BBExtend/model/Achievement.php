<?php

namespace BBExtend\model;

use Illuminate\Database\Eloquent\Model;
use BBExtend\model\help\AchievementPic;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Image;

/**
 * 成就模型类，功能多。
 */
class Achievement extends Model 
{
    protected $table = 'bb_users_achievement';
    protected $primaryKey = "id";
    public $timestamps = false;
    public function create_default_by_user(User $user) {
        $uid = $user->uid;
        $obj = Achievement::where ( "uid", $uid )->first ();
        if ($obj) {
            return $obj;
        } else {
            $obj = new self ();
            $obj->uid = $uid;
            $obj->save ();
            
            $summary = new AchievementSummary ();
            $summary->uid = $uid;
            $summary->save ();
            
            return $obj;
        }
    }
    public function get_ach_count() {
        $count = 0;
        if ($this->dengji > 0) {
            $count ++;
        }
        if ($this->zhibo > 0) {
            $count ++;
        }
        if ($this->pinglun > 0) {
            $count ++;
        }
        if ($this->dianzan > 0) {
            $count ++;
        }
        if ($this->zhubo > 0) {
            $count ++;
        }
        if ($this->hongren > 0) {
            $count ++;
        }
        if ($this->huodong > 0) {
            $count ++;
        }
        if ($this->dasai > 0) {
            $count ++;
        }
        if ($this->neirong > 0) {
            $count ++;
        }
        return $count;
    }
    
    /**
     * 某一成就明细。
     *
     * 返回的内容很多，包括
     *
     * event_name 成就汉字名，
     * level  整型， 成就当前的等级。
     * all_bonus 整型，该成就共获取多少波币奖励。
     *
     * list:[
     * complete_status 1未获得，2已获得未领奖。3已获得，已领奖
     * condition: 条件语句，不含未获得3个字。
     * create_time: 整型，成就达成时间，时间戳
     * get_time: 整型，领取奖励时间，时间戳
     * bonus：整型，领取的波币数额。
     * pic：图标。
     * ]
     *
     * 1 未获得，
     * 条件语句写上！！
     *
     * 2、写什么时候达成（成就，即create_time）
     *
     * 3、写什么时候获得波币，波币数量。
     *
     *
     * @param unknown $event
     */
    public function get_one_detail($event)
    {
        $arr=[];
        $event_name = AchievementPic::get_event_name($event);
        if (!$event_name) {
            echo "event error!";
            exit;
        }
        $arr['event_name']=$event_name;
        $arr['level']=$this->$event; // 这种写法要小心。
        $db = Sys::get_container_db_eloquent();
        $sql="select sum(bonus) from bb_users_achievement_bonus where uid =? and get_time>0
                and event = ?
                ";
        $all_bonus = DbSelect::fetchOne($db, $sql,[$this->uid, $event]);
        $arr['all_bonus'] = intval($all_bonus);
        
        $sql="select * from bb_users_achievement_bonus where uid =? 
                and event = ?
                order by level asc";
        $rows = DbSelect::fetchAll($db, $sql,[$this->uid, $event]);
        
        // 谢烨，这里人为的对 奖励改变，如果没有得过奖励，
        // 如果此人该event是3级，且没有3，则插入。
        // 如果此人该event是2级，且没有2，则插入。
        // 如果此人该event是1级，且没有1，则插入。
//         $add1= $add2 = $add3 = 0;
//         if ($arr['level']== 1 && (!$this->rows_has_level($rows, 1)) ) {
//             $add1=1;
//         }
//         if ($arr['level']== 2 && (!$this->rows_has_level($rows, 2)) ) {
//             $add2=1;
//         }
//         if ($arr['level']== 3 && (!$this->rows_has_level($rows, 3)) ) {
//             $add3=1;
//         }
//         $time=time();
//         // 这里必须获得每级的奖励数值。
//         $ach = \BBExtend\user\achievement\Ach::create_ach_by_event($event, $this->uid);
//         if ($add1) {
//             $rows[]= [
//                 'get_time'=>$time,
//                 'create_time'=>$time,
//                 'bonus'=>$ach->get_bonus_value(1),
//                 'level' =>1,
//             ];
//         }
//         if ($add2) {
//             $rows[]= [
//                 'get_time'=>$time,
//                 'create_time'=>$time,
//                 'bonus'=>$ach->get_bonus_value(2),
//                 'level' =>2,
//             ];
//         }
//         if ($add3) {
//             $rows[]= [
//                 'get_time'=>$time,
//                 'create_time'=>$time,
//                 'bonus'=>$ach->get_bonus_value(3),
//                 'level' =>3,
//             ];
//         }
        
        
        // 状态
        $status1 = $status2 = $status3 = 1;
        $help = new AchievementPic ();
        // 图片
        $pic1 = Image::get_grayurl( $help->get_pic_by_key($event, 1,false));
        $pic2 = Image::get_grayurl( $help->get_pic_by_key($event, 2,false));
        $pic3 = Image::get_grayurl( $help->get_pic_by_key($event, 3,false));
        // 条件
        $condition1 = $this->get_word($event_name, 0);
        $condition2 = $this->get_word($event_name, 1);
        $condition3 = $this->get_word($event_name, 2);
        //create_time
        $create_time1 = $create_time2 = $create_time3 = 0;
        // get_time
        $get_time1 = $get_time2 = $get_time3 = 0;
        //bonus
        $bonus1 = $bonus2 = $bonus3 = 0;
        
        foreach ($rows as $v) {
            if ($v['level'] == 1) {
                $status1 = $v['get_time'] ? 3 : 2;
                $pic1 = $help->get_pic_by_key($event, 1);
                $create_time1 = $v['create_time'];
                $get_time1 = $v['get_time'];
                $bonus1 = $v['bonus'];
            }
            if ($v['level'] == 2) {
                $status2 = $v['get_time'] ? 3 : 2;
                $pic2 = $help->get_pic_by_key($event, 2);
                $create_time2 = $v['create_time'];
                $get_time2 = $v['get_time'];
                $bonus2 = $v['bonus'];
                
                $pic1 = $help->get_pic_by_key($event, 1);
                if ($status1==1) {
                    $status1= 3;
                    //$bonus1=$ach->get_bonus_value(1);
                    $bonus1=0;
                    $create_time1=$create_time2;
                    $get_time1 = $get_time2;
                }
            }
            if ($v['level'] == 3) {
                $status3 = $v['get_time'] ? 3 : 2;
                $pic3 = $help->get_pic_by_key($event, 3);
                $create_time3 = $v['create_time'];
                $get_time3 = $v['get_time'];
                $bonus3 = $v['bonus'];
                
                $pic1 = $help->get_pic_by_key($event, 1);
                $pic2 = $help->get_pic_by_key($event, 2);
                if ($status1==1) {
                    $status1= 3;
//                     $bonus1=$ach->get_bonus_value(1);
                    $bonus1=0;
                    $create_time1=$create_time3;
                    $get_time1 = $get_time3;
                }
                if ($status2==1) {
                    $status2= 3;
//                     $bonus2=$ach->get_bonus_value(2);
                    $bonus2=0;
                    $create_time2=$create_time3;
                    $get_time2 = $get_time3;
                }
            }
        }
        $arr['list']=[
            [
                'complete_status'=> $status1,
                'condition'=> $condition1,
                'create_time'=> $create_time1,
                'get_time'=> $get_time1,
                'bonus'=> $bonus1,
                'pic'=> $pic1,
                'level' =>1,
            ],
            [
                'level' =>2,
            'complete_status'=> $status2,
            'condition'=> $condition2,
            'create_time'=> $create_time2,
            'get_time'=> $get_time2,
            'bonus'=> $bonus2,
            'pic'=> $pic2,
            ],
            [
                'level' =>3,
            'complete_status'=> $status3,
            'condition'=> $condition3,
            'create_time'=> $create_time3,
            'get_time'=> $get_time3,
            'bonus'=> $bonus3,
            'pic'=> $pic3,
            ],
        ] ;
        
        $arr['event'] = $event;
        
        return $arr;
    }
    
    // 检查表里有没有该level的奖励。
    private function rows_has_level($rows, $level)
    {
        $has=0;
        foreach ($rows as $v) {
            if ($v['level']== $level ) {
                $has =1;
            }
        }
        return $has;
    }
    
    private function getkey_level($level,$can_award)
    {
        if ($can_award) {
            
        }else { //如果不能领奖，需要按下一级显示，文档中含糊不清。
           $level++;
        }
        
        if ($level>3) {
            $level = 3;
        }
        return $level;
    }
    
    
    /**
     * 名称 内容 初级 中级 高级
     *
     * 等级达人 等级 LV5 LV10 LV20
     * 直播达人 直播时长 10小时 100小时 500小时
     * 评论达人 评论次数 50次 200次 500次
     * 点赞达人 点赞次数 100次 300次 800次
     * 优质主播 被点赞次数 500次 2000次 6000次
     *
     * BOBO小红人 粉丝数 100人 500人 2000人
     * 活动达人 参加活动次数 5次 20次 100次
     * 大赛达人 参加大赛次数 5次 20次 100次
     * 内容缔造者 短视频发布次数 10个 30个 100个
     *
     * update bb_users_achievement
     * set dengji=1,zhibo=2,pinglun=3,dianzan=0,zhubo=1,hongren=2,huodong=3,dasai=0,neirong=1
     *
     *
     *
     * 返回类似
     * [
     * 'title'=> '活动达人','pic'=>'111','val'=>0,
     * 'title'=> '直播达人','pic'=>'111','val'=>1,
     *
     *
     * ]
     */
    public function get_all_data() {
        $help = new AchievementPic ();
        $summary = AchievementSummary::where ( "uid", $this->uid )->first ();
        $user_exp = UserExp::find($this->uid);

        $dengji = new \BBExtend\user\achievement\Dengji($this->uid);
        $zhibo = new \BBExtend\user\achievement\Zhibo($this->uid);
        $pinglun = new \BBExtend\user\achievement\Pinglun($this->uid);
        $dianzan = new \BBExtend\user\achievement\Dianzan($this->uid);
        
        $zhubo = new \BBExtend\user\achievement\Zhubo($this->uid);
        $hongren =  new \BBExtend\user\achievement\Hongren($this->uid);
        $dasai = new  \BBExtend\user\achievement\Dasai($this->uid);
        $neirong = new \BBExtend\user\achievement\Neirong($this->uid);
        $huodong = new \BBExtend\user\achievement\Huodong($this->uid);
        
        $arr = [ ];
        $can_award = $dengji->can_award();
        $arr [] = [  // 1
            'event_name' => '等级达人',
            'event' =>'dengji',
            'pic' => $help->get_pic_by_key ( 'dengji', $this->dengji ),
            'level' => $this->dengji ,
            'tips' =>$this->get_word('等级达人', $this->dengji),
            'current' => $user_exp->level,
            'next'    => $this->get_next('等级达人', $this->dengji),
            'can_award'  =>$can_award,
            'tips_bonus' =>$dengji->get_bonus_value( $this->getkey_level( $this->dengji,$can_award )),
        ];
        $can_award = $zhibo->can_award();
        $arr [] = [  // 2
            'event_name' => '直播达人',
            'event' =>'zhibo',
            'pic' => $help->get_pic_by_key ( 'zhibo', $this->zhibo ),
            'level' => $this->zhibo ,
            'tips' =>$this->get_word('直播达人', $this->zhibo),
            'current' => intval( floor($summary->zhibo / 3600)),
            'next'    => $this->get_next('直播达人', $this->zhibo),
            'can_award'  =>$can_award,
            'tips_bonus' =>$zhibo->get_bonus_value( $this->getkey_level(  $this->zhibo,$can_award)),
        ];
        $can_award = $pinglun->can_award();
        $arr [] = [  // 3
            'event_name' => '评论达人',
            'event' =>'pinglun',
            'pic' => $help->get_pic_by_key ( 'pinglun', $this->pinglun ),
            'level' => $this->pinglun ,
            'tips' =>$this->get_word('评论达人', $this->pinglun),
            'current' => $summary->pinglun,
            'next'    => $this->get_next('评论达人', $this->pinglun),
            'can_award'  =>$can_award,
            'tips_bonus' =>$pinglun->get_bonus_value( $this->getkey_level(  $this->pinglun,$can_award)),
        ];
        $can_award = $dianzan->can_award();
        $arr [] = [  // 4
            'event_name' => '点赞达人',
            'event' =>'dianzan',
            'pic' => $help->get_pic_by_key ( 'dianzan', $this->dianzan ),
            'level' => $this->dianzan,
            'tips' =>$this->get_word('点赞达人', $this->dianzan),
            'current' => $summary->dianzan,
            'next'    => $this->get_next('点赞达人', $this->dianzan),
            'can_award'  =>$can_award,
            'tips_bonus' =>$dianzan->get_bonus_value( $this->getkey_level(  $this->dianzan,$can_award)),
        ];
        $can_award = $zhubo->can_award();
        $arr [] = [  // 5
            'event_name' => '优质主播',
            'event' =>'zhubo',
            'pic' => $help->get_pic_by_key ( 'zhubo', $this->zhubo ),
            'level' => $this->zhubo ,
            'tips' =>$this->get_word('优质主播', $this->zhubo),
            'current' => $summary->zhubo,
            'next'    => $this->get_next('优质主播', $this->zhubo),
            'can_award'  =>$can_award,
            'tips_bonus' =>$zhubo->get_bonus_value( $this->getkey_level(  $this->zhubo,$can_award)),
        ];
        $can_award = $hongren->can_award();
        $arr [] = [  // 6
            'event_name' => 'BOBO小红人 ',
            'event' =>'hongren',
            'pic' => $help->get_pic_by_key ( 'hongren', $this->hongren ),
            'level' => $this->hongren ,
            'tips' =>$this->get_word('BOBO小红人', $this->hongren),
            'current' => $summary->hongren,
            'next'    => $this->get_next('BOBO小红人', $this->hongren),
            'can_award'  =>$can_award,
            'tips_bonus' =>$hongren->get_bonus_value( $this->getkey_level(  $this->hongren,$can_award)),
        ];
        $can_award = $huodong->can_award();
        $arr [] = [  // 7
            'event_name' => '活动达人',
            'event' =>'huodong',
            'pic' => $help->get_pic_by_key ( 'huodong', $this->huodong ),
            'level' => $this->huodong ,
            'tips' =>$this->get_word('活动达人', $this->huodong),
            'current' => $summary->huodong,
            'next'    => $this->get_next('活动达人', $this->huodong),
            'can_award'  =>$can_award,
            'tips_bonus' =>$huodong->get_bonus_value( $this->getkey_level(  $this->huodong,$can_award)),
        ];
        $can_award = $dasai->can_award();
        $arr [] = [  // 8
            'event_name' => '大赛达人',
            'event' =>'dasai',
            'pic' => $help->get_pic_by_key ( 'dasai', $this->dasai ),
            'level' => $this->dasai ,
            'tips' =>$this->get_word('大赛达人', $this->dasai),
            'current' => $summary->dasai,
            'next'    => $this->get_next('大赛达人', $this->dasai),
            'can_award'  =>$can_award,
            'tips_bonus' =>$dasai->get_bonus_value( $this->getkey_level(  $this->dasai,$can_award )),
        ];
        $can_award = $neirong->can_award();
        $arr [] = [  // 9
            'event_name' => '内容缔造者',
            'event' =>'neirong',
            'pic' => $help->get_pic_by_key ( 'neirong', $this->neirong ),
            'level' => $this->neirong ,
            'tips' =>$this->get_word('内容缔造者', $this->neirong),
            'current' => $summary->neirong,
            'next'    => $this->get_next('内容缔造者', $this->neirong),
            'can_award'  =>$can_award,
            'tips_bonus' =>$neirong->get_bonus_value( $this->getkey_level(  $this->neirong,$can_award)),
        ];
        return $arr;
    }
    
    
    
    
    public function get_simple_data() 
    {
        
        $help = new AchievementPic ();
        $summary = AchievementSummary::where ( "uid", $this->uid )->first ();
        $user_exp = UserExp::find($this->uid);
        
        $dengji = new \BBExtend\user\achievement\Dengji($this->uid);
        $zhibo = new \BBExtend\user\achievement\Zhibo($this->uid);
        $pinglun = new \BBExtend\user\achievement\Pinglun($this->uid);
        $dianzan = new \BBExtend\user\achievement\Dianzan($this->uid);
        
        $zhubo = new \BBExtend\user\achievement\Zhubo($this->uid);
        $hongren =  new \BBExtend\user\achievement\Hongren($this->uid);
        $dasai = new  \BBExtend\user\achievement\Dasai($this->uid);
        $neirong = new \BBExtend\user\achievement\Neirong($this->uid);
        $huodong = new \BBExtend\user\achievement\Huodong($this->uid);
        
        $arr = [ ];
        $can_award = $dengji->can_award();
        $arr [] = [  // 1
               // 'event_name' => '等级达人',
               // 'event' =>'dengji',
                'pic' => $help->get_pic_by_key ( 'dengji', $this->dengji ),
                'level' => $this->dengji ,
               // 'tips' =>$this->get_word('等级达人', $this->dengji),
               // 'current' => $user_exp->level,
               // 'next'    => $this->get_next('等级达人', $this->dengji),
               // 'can_award'  =>$can_award,
               // 'tips_bonus' =>$dengji->get_bonus_value( $this->getkey_level( $this->dengji,$can_award )),
        ];
        $can_award = $zhibo->can_award();
        $arr [] = [  // 2
              //  'event_name' => '直播达人',
              //  'event' =>'zhibo',
                'pic' => $help->get_pic_by_key ( 'zhibo', $this->zhibo ),
                'level' => $this->zhibo ,
              //  'tips' =>$this->get_word('直播达人', $this->zhibo),
              //  'current' => intval( floor($summary->zhibo / 3600)),
              //  'next'    => $this->get_next('直播达人', $this->zhibo),
              //  'can_award'  =>$can_award,
              //  'tips_bonus' =>$zhibo->get_bonus_value( $this->getkey_level(  $this->zhibo,$can_award)),
        ];
        $can_award = $pinglun->can_award();
        $arr [] = [  // 3
              //  'event_name' => '评论达人',
              //  'event' =>'pinglun',
                'pic' => $help->get_pic_by_key ( 'pinglun', $this->pinglun ),
                'level' => $this->pinglun ,
              //  'tips' =>$this->get_word('评论达人', $this->pinglun),
              //  'current' => $summary->pinglun,
              //  'next'    => $this->get_next('评论达人', $this->pinglun),
              //  'can_award'  =>$can_award,
              //  'tips_bonus' =>$pinglun->get_bonus_value( $this->getkey_level(  $this->pinglun,$can_award)),
        ];
        $can_award = $dianzan->can_award();
        $arr [] = [  // 4
              //  'event_name' => '点赞达人',
              //  'event' =>'dianzan',
                'pic' => $help->get_pic_by_key ( 'dianzan', $this->dianzan ),
                'level' => $this->dianzan,
              //  'tips' =>$this->get_word('点赞达人', $this->dianzan),
              //  'current' => $summary->dianzan,
              //  'next'    => $this->get_next('点赞达人', $this->dianzan),
              //  'can_award'  =>$can_award,
              //  'tips_bonus' =>$dianzan->get_bonus_value( $this->getkey_level(  $this->dianzan,$can_award)),
        ];
        $can_award = $zhubo->can_award();
        $arr [] = [  // 5
             //   'event_name' => '优质主播',
             //   'event' =>'zhubo',
                'pic' => $help->get_pic_by_key ( 'zhubo', $this->zhubo ),
                'level' => $this->zhubo ,
             //   'tips' =>$this->get_word('优质主播', $this->zhubo),
             //   'current' => $summary->zhubo,
             //   'next'    => $this->get_next('优质主播', $this->zhubo),
             //   'can_award'  =>$can_award,
             //   'tips_bonus' =>$zhubo->get_bonus_value( $this->getkey_level(  $this->zhubo,$can_award)),
        ];
        $can_award = $hongren->can_award();
        $arr [] = [  // 6
             //   'event_name' => 'BOBO小红人 ',
             //   'event' =>'hongren',
                'pic' => $help->get_pic_by_key ( 'hongren', $this->hongren ),
                'level' => $this->hongren ,
             //   'tips' =>$this->get_word('BOBO小红人', $this->hongren),
             //   'current' => $summary->hongren,
             //   'next'    => $this->get_next('BOBO小红人', $this->hongren),
             //   'can_award'  =>$can_award,
             //   'tips_bonus' =>$hongren->get_bonus_value( $this->getkey_level(  $this->hongren,$can_award)),
        ];
        $can_award = $huodong->can_award();
        $arr [] = [  // 7
             //   'event_name' => '活动达人',
             //   'event' =>'huodong',
                'pic' => $help->get_pic_by_key ( 'huodong', $this->huodong ),
                'level' => $this->huodong ,
             //   'tips' =>$this->get_word('活动达人', $this->huodong),
             //   'current' => $summary->huodong,
             //   'next'    => $this->get_next('活动达人', $this->huodong),
             //   'can_award'  =>$can_award,
             //   'tips_bonus' =>$huodong->get_bonus_value( $this->getkey_level(  $this->huodong,$can_award)),
        ];
        $can_award = $dasai->can_award();
        $arr [] = [  // 8
             //   'event_name' => '大赛达人',
             //   'event' =>'dasai',
                'pic' => $help->get_pic_by_key ( 'dasai', $this->dasai ),
                'level' => $this->dasai ,
             //   'tips' =>$this->get_word('大赛达人', $this->dasai),
             //   'current' => $summary->dasai,
             //   'next'    => $this->get_next('大赛达人', $this->dasai),
             //   'can_award'  =>$can_award,
             //   'tips_bonus' =>$dasai->get_bonus_value( $this->getkey_level(  $this->dasai,$can_award )),
        ];
        $can_award = $neirong->can_award();
        $arr [] = [  // 9
             //   'event_name' => '内容缔造者',
             //   'event' =>'neirong',
                'pic' => $help->get_pic_by_key ( 'neirong', $this->neirong ),
                'level' => $this->neirong ,
             //   'tips' =>$this->get_word('内容缔造者', $this->neirong),
             //   'current' => $summary->neirong,
             //   'next'    => $this->get_next('内容缔造者', $this->neirong),
             //   'can_award'  =>$can_award,
             //   'tips_bonus' =>$neirong->get_bonus_value( $this->getkey_level(  $this->neirong,$can_award)),
        ];
        return $arr;
    }
    
    
    
    
    
    
    
    
    
    
    
    /**
     * 得到成就图片。
     */
    public function get_pic_arr()
    {
        $arr = $this->get_all_data();
        $new=[];
        foreach ($arr as $v) {
            if ($v['level'] >0 ){
                $new[]= $v['pic'];
            }
        }
        return $new;
    }
    
    
    /**
     * 根据事件和等级，得到提示语。
     *
     * @param string $event_name
     * @param int $level
     */
    private function get_next($event_name, $level) {
        switch ($event_name) {
            case "等级达人" :
                switch ($level) {
                    case 0 :
                        return 5;
                    case 1 :
                        return 10;
                    case 2 :
                        return 20;
                    case 3 :
                        return 20;
                }
                break;
            case "直播达人" :
                switch ($level) {
                    case 0 :
                        return 10;
                    case 1 :
                        return 100;
                    case 2 :
                        return 500;
                    case 3 :
                        return 500;
                }
                break;
            case "评论达人" :
                switch ($level) {
                    case 0 :
                        return 50;
                    case 1 :
                        return 200;
                    case 2 :
                        return 500;
                    case 3 :
                        return 500;
                }
    
                break;
            case "点赞达人" :
                switch ($level) {
                    case 0 :
                        return 100;
                    case 1 :
                        return 300;
                    case 2 :
                        return 800;
                    case 3 :
                        return 800;
                }
    
                break;
            case "优质主播" :
                switch ($level) {
                    case 0 :
                        return 1000;
                    case 1 :
                        return 4000;
                    case 2 :
                        return 12000;
                    case 3 :
                        return 12000;
                }
    
                break;
            case "BOBO小红人" :
                switch ($level) {
                    case 0 :
                        return 100;
                    case 1 :
                        return 500;
                    case 2 :
                        return 2000;
                    case 3 :
                        return 2000;
                }
    
                break;
            case "活动达人" :
                switch ($level) {
                    case 0 :
                        return 5;
                    case 1 :
                        return 20;
                    case 2 :
                        return 100;
                    case 3 :
                        return 100;
                }
    
                break;
            case "大赛达人" :
                switch ($level) {
                    case 0 :
                        return 5;
                    case 1 :
                        return 20;
                    case 2 :
                        return 100;
                    case 3 :
                        return 100;
                }
    
                break;
            case "内容缔造者" :
                switch ($level) {
                    case 0 :
                        return 10;
                    case 1 :
                        return 30;
                    case 2 :
                        return 100;
                    case 3 :
                        return 100;
                }
    
                break;
        }
    }
    
    
    
    
    
    
    /**
     * 根据事件和等级，得到提示语。
     * 
     * @param string $event_name            
     * @param int $level            
     */
    private function get_word($event_name, $level) {
        switch ($event_name) {
            case "等级达人" :
                switch ($level) {
                    case 0 :
                        return 'LV升至5，可升级';
                    case 1 :
                        return 'LV升至10，可升级';
                    case 2 :
                        return 'LV升至20，可升级';
                    case 3 :
                        return '已完成';
                }
                break;
            case "直播达人" :
                switch ($level) {
                    case 0 :
                        return '直播时长达到10H，可升级';
                    case 1 :
                        return '直播时长达到100H，可升级';
                    case 2 :
                        return '直播时长达到500H，可升级';
                    case 3 :
                        return '已完成';
                }
                break;
            case "评论达人" :
                switch ($level) {
                    case 0 :
                        return '评论达到50次，可升级';
                    case 1 :
                        return '评论达到200次，可升级';
                    case 2 :
                        return '评论达到500次，可升级';
                    case 3 :
                        return '已完成';
                }
                
                break;
            case "点赞达人" :
                switch ($level) {
                    case 0 :
                        return '点赞次数达到100次，可升级';
                    case 1 :
                        return '点赞次数达到300次，可升级';
                    case 2 :
                        return '点赞次数达到800次，可升级';
                    case 3 :
                        return '已完成';
                }
                
                break;
            case "优质主播" :
                switch ($level) {
                    case 0 :
                        return '被点赞次数达到1000次，可升级';
                    case 1 :
                        return '被点赞次数达到4000次，可升级';
                    case 2 :
                        return '被点赞次数达到12000次，可升级';
                    case 3 :
                        return '已完成';
                }
                
                break;
            case "BOBO小红人" :
                switch ($level) {
                    case 0 :
                        return '粉丝数达到100，可升级';
                    case 1 :
                        return '粉丝数达到500，可升级';
                    case 2 :
                        return '粉丝数达到2000，可升级';
                    case 3 :
                        return '已完成';
                }
                
                break;
            case "活动达人" :
                switch ($level) {
                    case 0 :
                        return '参加活动达到5次，可升级';
                    case 1 :
                        return '参加活动达到20次，可升级';
                    case 2 :
                        return '参加活动达到100次，可升级';
                    case 3 :
                        return '已完成';
                }
                
                break;
            case "大赛达人" :
                switch ($level) {
                    case 0 :
                        return '参加大赛达到5次，可升级';
                    case 1 :
                        return '参加大赛达到20次，可升级';
                    case 2 :
                        return '参加大赛达到100次，可升级';
                    case 3 :
                        return '已完成';
                }
                
                break;
            case "内容缔造者" :
                switch ($level) {
                    case 0 :
                        return '短视频发布达到10次，可升级';
                    case 1 :
                        return '短视频发布达到30次，可升级';
                    case 2 :
                        return '短视频发布达到100次，可升级';
                    case 3 :
                        return '已完成';
                }
                
                break;
        }
    }
}
