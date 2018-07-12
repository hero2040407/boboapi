<?php
namespace BBExtend\backmodel;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户
 * 
 * User: 谢烨
 */
class User extends \BBExtend\model\User 
{
    
    public function round_display()
    {
        return [
           'pic' => $this->get_userpic(),   
                'nickname' =>$this->get_nickname(),
                'uid' => $this->uid,
        ];
    }
    
    
    public function field_pic($field_id)
    {
        $db = \BBExtend\Sys::get_container_dbreadonly();
        $sql="select pic from ds_register_log where uid=? and ds_id=?  ";
        $row = $db->fetchRow($sql,[ $this->uid, $field_id ]);
        
        if ($row) {
            return $row['pic'];
        }
        return $this->get_userpic() ;
    }
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
