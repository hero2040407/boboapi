<?php
namespace BBExtend\backmodel;
use \Illuminate\Database\Eloquent\Model;
/**
 * 用户
 * 
 * User: 谢烨
 */
class RaceField extends Model 
{
    protected $table = 'ds_race_field';
    public $timestamps = false;
    
   
    public function race()
    {
        // 重要说明：user_id是Money模型里的，id是User模型里的。
        return $this->belongsTo('BBExtend\backmodel\Race', 'race_id', 'id');
    }  
    
    public function display()
    {
        $result = [
          'id' =>$this->id,
                'title' =>$this->title,
                'address' =>$this->address,
                'race_id' => $this->race_id,
                'status'  => $this->status,
                'is_valid' =>$this->is_valid,
                'channel_id'=>$this->channel_id,
                'create_time'=>$this->create_time,
                'race_title' =>$this->race->title,
                
        ];
        
        $account = \BBExtend\backmodel\Admin::find( $this->channel_id );
        
        $temp = [];
        
        if ($account) {
           $temp['account'] = $account->account;
           $temp['pwd_original'] = $account->pwd_original;
           $temp['realname'] = $account->realname;
           $temp['phone'] = $account->phone;
        }
        $result['account'] = $temp;
        
        
        return $result;
        
    }
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
