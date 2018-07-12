<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 
 * 
 * 
 */
class UserInfo extends Model 
{
    protected $table = 'bb_users_info';
//     protected $primaryKey="uid";
    
    public $timestamps = false;
    
    public static function getinfo($uid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_users_info where uid=?";
        $result = DbSelect::fetchRow($db, $sql, [$uid]);
        if (!$result) {
            $db::table("bb_users_info")->insert(['uid'=>$uid ]);
        }
        $temp = UserInfo::where('uid', $uid)->first();
        return $temp;
        
    }

    public function addlog($uid,$role) {
        $db = Sys::get_container_db_eloquent();
        $time=time();
        //vip在一种情况下，才需要，首先，确实是当前身份1，第2，必须有4或者7的记录。第3，不能有3，6的记录。
        $user = \BBExtend\model\User::find( $uid );
        if ($user->role==1) {
            $sql="select count(*) from bb_vip_application_log where uid=? and status in (4,7)";
            $result1 = DbSelect::fetchOne($db, $sql,[ $uid ]);
            $sql="select count(*) from bb_vip_application_log where uid=? and status in (3,6)";
            $result2 = DbSelect::fetchOne($db, $sql,[ $uid ]);
            if ($result1 && (!$result2)) {
                $values=[
                        'uid'=>$uid,
                        'status'=>3,
                        'create_time'=>time(),
                        
                ];
                $db::table('bb_vip_application_log')->insert($values);
            }
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
        $row['update_time'] = time();
        
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_users_info where uid=?";
        $result = DbSelect::fetchRow($db, $sql, [$uid]);
        if ($result) {
            $db::table("bb_users_info")->where("uid", $uid)->update( $row  );
        }else {
            
            // 谢烨，这里，如果没有4，我就加7的记录。
            $sql="select count(*) from bb_vip_application_log where uid=? and status in (4,7)";
            $count = DbSelect::fetchOne($db, $sql,[ $uid ]);
            if (!$count) {
                $values=[
                        'uid'=>$uid,
                        'status'=>7,
                        'create_time'=>time(),
                        
                ];
                $db::table('bb_vip_application_log')->insert($values);
            }
            
            
            $row['uid']=$uid;
            $db::table("bb_users_info")->insert($row);
        }
        
    }

}
