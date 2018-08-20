<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
/**
 * 
 * 
 * : 谢烨
 */
class DsDanganUser extends Model 
{
    protected $table = 'ds_dangan_config_user_history';
    public $timestamps = false;
    
//     // 查关联的用户
//     public function user()
//     {
//         // 重要说明：
//         return $this->belongsTo('BBExtend\model\User', 'uid', 'uid');
//     }
    
    public static function update_uid($uid, $title, $value)
    {
        $db = \BBExtend\Sys::get_container_db();
        $sql= "select * from ds_dangan_config_user_history where uid=? and title=?";
        $row = $db->fetchRow($sql,[ $uid, $title ]);
        if ($row) {
            $sql ="update ds_dangan_config_user_history set content=? where id=?";
            $db->query( $sql, [ $value, $row['id'] ] );
            
            
        }else {
            //添加
            $obj = new self();
            $obj->uid = $uid;
            $obj->title = $title;
            $obj->content = $value;
            $obj->save();
        }
        
    }

}
