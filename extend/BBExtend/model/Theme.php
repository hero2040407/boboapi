<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\Sys;
/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class Theme extends Model 
{
    protected $table = 'bb_theme';
    public $timestamps = false;
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }

    public static function get_and_create_id( $title )
    {
        $title =trim($title);
        if (empty($title)){
            return 0;
        }
        $db = Sys::get_container_db();
        $sql="select * from bb_theme where title =?";
        $row =$db->fetchRow($sql,[ $title ]);
        if ($row) {
            
            $sql="update bb_theme set last_use_time =? , use_count=use_count+1 where id=?";
            $db->query( $sql, [ time(), $row['id'] ] );
            
            
            return $row['id'];
        }else {
            $bind=[
                    'title' =>$title,
                    'last_use_time' => time(),
                    'use_count' =>1,
                    'is_valid' =>0,
            ];
            $db->insert("bb_theme", $bind);
            return $db->lastInsertId();
        }
        
    }
    
    
}
