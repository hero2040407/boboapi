<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\backmodel\RaceField;
/**
 * 
 * 
 * User: 谢烨
 */
class BackstageAdmin extends Model 
{
    protected $table = 'backstage_admin';
    protected $primaryKey="id";
    
    public $timestamps = false;
    
    public $message='';
    
    
    
    public function check_edit_auth($field_id, $proxy_id)
    {
        
        if ($field_id) {
            // 既然是渠道账号。渠道只能改自己，于是查询该赛区的channel_id即可知道。
            $field = RaceField::find( $field_id );
            if ($field) {
                if ( $field->channel_id == $this->id ) {
                    return true;
                }
            }
            return false;
        }
        
        if ($proxy_id) {
            // 代理账号，可以管他自己，他旗下的渠道账号。
            //$ = RaceField::find( $field_id );
            
            if ($this->id == $proxy_id) {
                return true;
            }
             
            $db = \BBExtend\Sys::get_container_dbreadonly();
            $sql="
select * from ds_race_field 
where exists (
 select 1 from ds_race where 
  ds_race.id = ds_race_field.race_id
   and ds_race.proxy_id=?
)
and channel_id = ? ";
            $result = $db->fetchOne($sql,[  $proxy_id, $this->id ] );
            
            if ($result) {
               return true;
            }
            return false;
        }
        
        
        // 其实这句话只给admin
        return true;
    }
    
    
    public function display()
    {
        return [
           'id' => $this->id,
                'account' => $this->account,
                'realname' => $this->realname,
                'phone' => $this->phone,
                'level' => $this->level,
                'parent' => $this->parent,
                'is_valid'=> $this->is_valid,
                
        ];
    }
    
    public function edit($pwd)
    {
        $realname = $this->realname;
       
        $phone = $this->phone;
        if (empty( $realname )) {
            $this->message = '真实姓名不能为空';
            return false;
        }
        // realname 只能是汉字。
        if ( !\BBExtend\common\Str::is_all_chinese($realname)     ) {
            $this->message = '真实姓名必须全部汉字';
            return false;
        }
        
        
        if (empty( $phone )) {
            $this->message = '手机不能为空';
            return false;
        }
        if ( !\BBExtend\common\Str::is_valid_phone($phone)  ) {
            $this->message = '手机格式错误';
            return false;
        }
        
        
        if ($pwd) {
            
            $check_result = \BBExtend\common\Pwd::check_amdin($pwd);
            
            if ( $check_result['code']==0 ) {
                $this->message = $check_result['message'];
                return false;
            }
            $this->pwd = md5($pwd);
            $this->pwd_original = $pwd;
            
        }
        
      
        $this->save();
        
        return true;
    }
    
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
