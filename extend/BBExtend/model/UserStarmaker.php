<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Str;

/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class UserStarmaker extends Model 
{
    protected $table = 'bb_users_starmaker';
    //protected $primaryKey="uid";
    
    public $timestamps = false;
    
    public $sql='';
    
    public function user()
    {
        // 重要说明：user_id是Money模型里的，id是User模型里的。
        return $this->hasOne('BBExtend\model\User', 'uid', 'uid');
    }
    
    
    private function paichu($arr, $uid, $uid2){
        $arr=(array)$arr;
       return array_diff($arr, [ $uid, $uid2 ]);

        
    }
    
    /**
     * 把逗号分隔的字符串，转成数组
     * @param unknown $str
     */
    private function filter($str)
    {
        $arr = explode(',', $str);
        $new =[];
        foreach ($arr as $v) {
            $new[] = intval($v);
        }
        return $new;
    }
    
    
    public static function check_and_get($uid)
    {
        $obj = UserStarmaker::where('uid', $uid)->first();
        if (!$obj) {
            return false;
        }
        if ($obj->user->role != 2) {
            return false;
        }
        return $obj;
        
//         $db = Sys::get_container_dbreadonly();
//         $sql="";
    }
    
    public function get_list($uid,$start=0,$length=10, $level=0, $hobby=0, $week=0,$name='')
    {
        $uid    = intval($uid);
        $start  = intval($start);
        $length = intval($length);
        
//         $last_uid=intval($last_uid);
        
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $sql="select hobby_id from bb_user_hobby
               where uid=? ";
        $hobby_id = DbSelect::fetchCol($db, $sql,[$uid]);
        if (!$hobby_id) {
            $hobby_id=1;
        }else {
            $hobby_id = implode(',' , $hobby_id);
        }
        
        
        $redis = Sys::getredis11();
        $key = "focuson_starmaker:get_random:{$hobby_id}";
        //这里啊，我决定用set集合来做这个某个兴趣的
        
        $list  = $redis->sMembers($key);
        $list = null;
        if (!$list) {
            $sql="
            select bb_user_hobby.uid 
from bb_user_hobby 
left join bb_users_starmaker 
on bb_users_starmaker.uid = bb_user_hobby.uid 
where bb_users_starmaker.uid is not null 
  and bb_users_starmaker.is_show=1
  and bb_users_starmaker.uid != {$uid} 
  and bb_users_starmaker.uid != 7049564
and exists (
                 select 1 from bb_users where bb_users.uid = bb_user_hobby.uid
                    and  bb_users.role=2
               )
";
             
             if ($level!=0) {
                 $level_arr = $this->filter($level);
                 $level_str = implode(',' , $level_arr);
                 if (count($level_arr) >1  ) {
                    $sql .= " and  bb_users_starmaker.level in ( {$level_str} ) " ;
                 }else {
                     $sql .= " and  bb_users_starmaker.level = {$level_str}  " ;
                 }
             }
             if ($week!=0) {
                 $week = $this->filter($week);
                 $sql.= " and ( ";
                 $i=0;
                 foreach ($week as $v) {
                     if ($i) {
                         $sql .= " or ";
                     }
                     $sql .= " find_in_set({$v},  bb_users_starmaker.week ) ";
                     $i++;
                 }
                 $sql.= " ) ";
             }
             if ($hobby !=0) {
                 $hobby_arr = $this->filter($hobby);
                 $hobby = implode(',' , $hobby_arr);
                 if (count( $hobby_arr ) >1 ) {
                   $sql .= " and  bb_user_hobby.hobby_id in  ( {$hobby} ) ";
                 }else {
                     $sql .= " and  bb_user_hobby.hobby_id = {$hobby}  ";
                 }
             }
             $name = Str::like($name);
             if ($name) {
                 $sql .= " and exists(
                   select 1 from bb_users
                     where bb_users.uid = bb_users_starmaker.uid
                      and bb_users.nickname like '%{$name}%'
                         )  ";
             }
             
            
$sql .=" group by bb_user_hobby.uid 
order by field(hobby_id ,{$hobby_id}) desc,bb_users_starmaker.level desc 
limit {$start},{$length}
            ";
// Sys::debugxieye($sql);

            $ids = DbSelect::fetchCol($db, $sql);
            $this->sql = $sql;
            if ($ids) {
                foreach ($ids as $idtemp) {
                    $redis->sAdd($key, $idtemp);
                }
                $redis->setTimeout($key, 1*60);
                $list = $redis->sMembers($key);
            }
        }
        $new = [];
        foreach ($ids as $id) {
            $obj = UserStarmaker::where('uid', $id)->first();
            $new[]= $obj->get_info();
        }
        
        return $new;
        
//         if ($list) {
//             return UserStarmaker::where('uid', $list[ array_rand($list)]  )->first()->get_info();
//         }
     //   return null;
    }
    
    
    /**
     * 根据用户id，随机挑选一个星推官
     */
    public function get_random($uid,$last_uid)
    {
        $uid = intval($uid);
        $last_uid=intval($last_uid);
        
        $db = \BBExtend\Sys::get_container_db_eloquent();
        $sql="select hobby_id from bb_user_hobby
               where uid=? limit 1";
        $hobby_id = DbSelect::fetchOne($db, $sql,[$uid]);
        if (!$hobby_id) {
            $hobby_id=1;
        }
        
        
        $redis = Sys::getredis11();
        $key = "focuson_starmaker:get_random:{$hobby_id}";
        //这里啊，我决定用set集合来做这个某个兴趣的

        $list  = $redis->sMembers($key);
        if (!$list) {
            $sql="
            select uid from bb_user_hobby
            where hobby_id =?
            and exists (
            select 1 from bb_users_starmaker
            where bb_users_starmaker.uid = bb_user_hobby.uid
            )
            limit 100
            ";
            $ids = DbSelect::fetchCol($db, $sql, [$hobby_id,]);
            if ($ids) {
                foreach ($ids as $idtemp) {
                    $redis->sAdd($key, $idtemp);
                }
                $redis->setTimeout($key, 1*60);
                $list = $redis->sMembers($key);
            }
        }
        $list = $this->paichu($list, $uid, $last_uid);
        
        if ($list) {
            return UserStarmaker::where('uid', $list[ array_rand($list)]  )->first()->get_info();
        }
        $sql =" select uid from bb_users_starmaker 
                limit 200 ";
        $list = DbSelect::fetchCol($db, $sql);
        $list = $this->paichu($list, $uid, $last_uid);
        
        if ($list) {
            return UserStarmaker::where('uid', $list[ array_rand($list)]  )->first()->get_info();
        }
        return null;
    }
    
    /**
     * 返回这个星推官的个人信息
     * 
     * 注意pay字段，50写这！！
     */
    public function get_info()
    {
        $user = $this->user;
        $db = Sys::get_container_db_eloquent();
        
        $focus_help = \BBExtend\user\Focus::getinstance($this->uid);
        $fensi_count = $focus_help->get_fensi_count(); 
        $sql="
select count(*) from bb_record_invite_starmaker
where starmaker_uid = ? and status=?
                ";
        $dianping_count = DbSelect::fetchOne($db, $sql, [ $this->uid, 
                \BBExtend\fix\TableType::bb_record_invite_starmaker__type_yishenhe  ]);
        
        $sql="select * from bb_label";
        $all_hobby = DbSelect::fetchAll($db, $sql);
        $new=[];
        foreach ( $all_hobby as $v ) {
            $new[$v['id'] ] = $v['name'];
        }
        $all_hobby =$new;
        
        //个人兴趣。
        $sql = "select hobby_id from bb_user_hobby where uid =".$this->uid;
        $result  = DbSelect::fetchCol($db, $sql);
        if (!$result) {
            $hobby = '';
       //     $hobby_word='';
        }else {
            $temp='';
            foreach ($result as $vv) {
                $temp .= '"'. $all_hobby[$vv] .'",';
            }
            $temp = trim($temp, ',' );
            
            $hobby = "[". $temp ."]";
        
        }
        
        return [
          'level' =>$this->level,
            'info' => $this->info,
            'html'=> trim( \BBExtend\common\BBConfig::get_server_url_https(),'/'). 
                '/index/starmaker/info/uid/'.$this->uid,
                
             'nickname' => $this->user->get_nickname(),
            'pic' => $this->user->get_userpic(),
                'sex' => $this->user->get_usersex(),
            'uid' => $this->uid,
            'pay' => $this->get_price(),
            'title' => $this->title, // 头衔
            'fans_count'=> intval( $fensi_count),
            'comment_count' => $dianping_count,
            'hobby' => $hobby,
                
                // 201804
            'role'  => $user->role,
                'badge' => $user->get_badge(),
                'frame' => $user->get_frame(),
                
                
          //  'hobby_word'=>$hobby_word,
            
        ];
    }
    
//     建议一开始每次点评不超过5人民币，根据星级进行阶梯定价
    
    
//     3星	50BO币
//     4星	60BO币
//     5星	80BO币
//     6星	100BO币
    
    public function get_price()
    {
        if ($this->level==6) {
            return 100;
        }
        if ($this->level==5) {
            return 80;
        }
        if ($this->level==4) {
            return 60;
        }
        if ($this->level==3) {
            return 50;
        }
        return 50;
        
    }
    
}
