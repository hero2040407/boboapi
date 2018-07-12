<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class BrandShop extends Model 
{
    protected $table = 'bb_brandshop';
    
    public $timestamps = false;
    
    protected $tool_user;
    
    public function user()
    {
        // 重要说明：user_id是Money模型里的，id是User模型里的。
        return $this->hasOne('BBExtend\model\User', 'uid', 'uid');
    }

    protected function get_tool_user()
    {
        if (!$this->tool_user) {
            $this->tool_user = \app\user\model\UserModel::getinstance($this->uid);
        }
        return $this->tool_user;
    }
    
    // uid 是否关注这个品牌馆
    public function is_focus($uid){
        $help = new \BBExtend\user\Focus($uid );
        $result = $help->has_focus($this->uid );
        return boolval($result );
    }
    
    public function get_level()
    {
        $user = $this->get_tool_user();
        return $user->get_user_level();
    }
    
    
    public function get_userpic()
    {
        $user = $this->get_tool_user();
        return $user->get_userpic();
    }
    
    public function get_nickname()
    {
        $user = $this->get_tool_user();
        return $user->get_nickname();
    }
    
    public function fans_count()
    {
        $help = \BBExtend\user\Focus::getinstance($this->uid );
        return  intval( $help->get_fensi_count() );
    }
    
    /**
     * 通告个数
     * 
     * @return number
     */
    public function act_count()
    {
        $time = time();
        $db = Sys::get_container_db_eloquent();
        $count_huodong = $db::table('bb_task_activity')
           ->where('is_remove' , 0) 
           ->where('is_show' , 1)
           ->whereNotNull("start_time")
           ->where("start_time" ,'<',time())
           ->where("brandshop_id" , $this->id )
           ->count();
//            select * from ds_race
//            where is_active=1 and parent=0
        $count_dasai = $db::table('ds_race')->where('is_active',1)
            ->where('parent',0)
            ->where("brandshop_id" , $this->id )
            ->count() ;
        
        return $count_huodong + $count_dasai ;
    }
    
    
    

    public static function isvalid($uid)
    {
        $user = \BBExtend\model\User::find( $uid );
        if (!$user) {
            return false;
        }
        if ($user->role==4) {
            return true;
        }
        if ($user->role==2 || $user->role==3) {
            return false;
        }
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_brandshop_application where uid=? ";
        $result = $db->fetchRow($sql,[ $uid ]);
        if (!$result) {
            return false;
        }
        if ( in_array($result['status'], [ 1,3,4 ]) ) {
            return true;
        }
        return false;
        
    }
    
    
    // 谢烨注意，此函数谨慎使用，因为会生成记录！！！！！
    public static function getinfo($uid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_brandshop where uid=?";
        $result = DbSelect::fetchRow($db, $sql, [$uid]);
        $time = time();
        if (!$result) {
            $db::table("bb_brandshop")->insert(['uid'=>$uid,'is_show'=>0,
                    'create_time'=>$time ]);
        }
        $temp = BrandShop::where('uid', $uid)->first();
        return $temp;
        
    }
    
    public function addlog($uid,$role) {
        // 机构在哪种情况下，修改个人资料时需要更改申请表。
        // 在status= 1的时候，且需要改成3
        
        
        $db = Sys::get_container_db_eloquent();
        $time=time();
        $sql="select * from bb_brandshop_application where uid=? order by id desc limit 1";
        $result = DbSelect::fetchRow($db, $sql,[ $uid ]);
        if ($result && $result['status']==1) {
            $db::table('bb_brandshop_application')->where('id', $result['id'])->update(
                    [
                            'create_time'=>$time,
                            'status' =>3,
                    ]
            );
        }
        
    }
    
    /**
     * 修改 用户附加信息，请使用此专用函数
     *
     * @param unknown $uid
     * @param unknown $row
     * @return number
     */
    public function updateinfo($uid, $row)
    {
        if (!$row) {
            return 0;
        }
        // $row['update_time'] = time();
        
        $tutor= $this->getinfo($uid);
        foreach ( $row as $k => $v ) {
            $tutor->$k = $v;
        }
        $tutor->save();
        
            
    }
    
    
    
    
    
    
    
    
    
    
}
