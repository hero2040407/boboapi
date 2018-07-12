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
class Starmaker extends Model 
{
    protected $table = 'bb_users_starmaker';
   // protected $primaryKey="uid";
    
    public $timestamps = false;
    
    
    
    // 谢烨注意，此函数谨慎使用，因为会生成记录！！！！！
    public static function getinfo($uid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_users_starmaker where uid=?";
        $result = DbSelect::fetchRow($db, $sql, [$uid]);
        $time = time();
        if (!$result) {
            $db::table("bb_users_starmaker")->insert(['uid'=>$uid,'is_show'=>0, 
                    'create_time'=>$time ]);
        }
        $temp = Starmaker::where('uid', $uid)->first();
        return $temp;
        
    }
    
    public function addlog($uid,$role) {
        $db = Sys::get_container_db_eloquent();
        $time=time();
        $sql="select * from bb_starmaker_application where uid=? order by id desc limit 1";
        $result = DbSelect::fetchRow($db, $sql,[ $uid ]);
        if ($result && $result['status']==1) {
            $db::table('bb_starmaker_application')->where('id', $result['id'])->update(
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
        
//         $db = Sys::get_container_db_eloquent();
//         $sql="select * from bb_users_starmaker where uid=?";
//         $result = DbSelect::fetchRow($db, $sql, [$uid]);
//         if ($result) {
//             $db::table("bb_users_info")->where("uid", $uid)->update( $row  );
//         }else {
//             $db::table("bb_users_info")->insert($row);
//         }
        
    }
}
