<?php
namespace BBExtend\backmodel;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\model\UserInfo;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 用户
 * 
 * User: 谢烨
 */
class OfflineRegisterLog extends Model 
{
    protected $table = 'ds_register_log';
    public $timestamps = false;
    
   public function detail()
   {
       $result = $this->display();
       
       return $result;
   }
    
   /**
    * 这是返回签到列表用的
    * @return NULL[]|unknown[][]
    */
   public function display_signin_index()
   {
       if (  $this->race_status >0) {
           $status = $this->race_status;
       }else {
           if ($this->has_pay) {
               $status = 10; //报名成功
           }else {
               if ( $this->has_dangan ) {
                   $status = 8; // 钱未付，
               }else {
                   $status = 6; // 自定义档案未填好
               }
               
           }
           
       }
       
       $user = \BBExtend\model\User::find( $this->uid );
       $user_pic = $user->get_userpic();
       
       //         $race = \BBExtend\backmodel\Race::find( $this->zong_ds_id );
       //         $race_title = $race->title;
       
       //         $field = \BBExtend\backmodel\RaceField::find( $this->ds_id );
       //         $field_title = $field->title;
       
       
       return [
               
               
               'uid'=>$this->uid,
               
               'user' => [
                       'user_pic' => $user_pic,
               ],
               
               
       ];
   }
    
   
   // xieye :这是 给 某个接口单独使用 的
   public function display_detail()
   {
       $result = $this->display();
       $uid = $result['uid'];
       if ($result['user']['role']== 3 ) {
           
           $new=[];
//            $userinfo =   UserInfo::getinfo($uid);
           
           $db = Sys::get_container_db_eloquent();
           $sql="select pic from  bb_users_card where uid={$uid} and status=3 ";
          // $card_count = DbSelect::fetchOne($db, $sql);
           
           //查询我的动态有多少
           // 查询我的模卡有多少。
         //  $new['dongtai_count'] = $this->private_get_user_video_count($uid, $self_uid);
          // $new['card_count'] = $card_count;
           //             if ($uid==$self_uid) {
           //                $new['parent_phone'] = $userinfo->parent_phone;
           //             }
//            $new['height'] = $userinfo->height;
//            $new['weight'] = $userinfo->weight;
           // $new['parent_phone'] = $userinfo->parent_phone;
           $user = \BBExtend\model\User::find($uid);
           $new['gexing'] = $user->get_gexing_arr();
           $new['jingyan'] = $user->get_jingyan_arr();
           $new['card_list'] = DbSelect::fetchCol($db, $sql);
           $result['vip'] = $new;
       }else {
           $result['vip'] = null;
       }
       return $result;
   }
   
   
   /**
    * 谢烨，这是专门给导出的做到。
    */
   public function get_export(){


       $user = \BBExtend\model\User::find( $this->uid );
       $user_pic = $user->get_userpic();
       
       $race = \BBExtend\backmodel\Race::find( $this->zong_ds_id );
       $race_title = $race->title;

       $field = \BBExtend\backmodel\RaceField::find( $this->ds_id );
       $field_title = $field ? $field->title : '';

       //$age=0;
       $age=date("Y") - substr( $this->birthday,0,4 );
       
       
       return [
           'id' =>$this->id,
           'create_time' => date("Y-m-d H:i", $this->create_time),  //报名时间
           'uid'=>$this->uid,
           'money'=>$this->money,//缴纳的费用
           'phone'=>$this->phone, // 手机
           'sex'=>$this->sex ?"男":"女",
           'birthday'=>$this->birthday,
           'age'  => $age,
           'name'=>$this->name,
           'height'=>$this->height,
           'weight'=>$this->weight,
           'sort'=>$this->sort,
           'pic'   => $this->pic,

           'race_title' =>$race_title,
           'race_id'    => $this->zong_ds_id,
           'field_title' => $field_title,
           'field_id' => $this->ds_id,
       ];
   }
   
   /**
    * 这是返回总的。
    * @return NULL[]|unknown[][]
    */
    public function display()
    {
        if (  $this->race_status >0) {
            $status = $this->race_status;
        }else {
            if ($this->has_pay) {
                $status = 10; //报名成功
            }else {
                if ( $this->has_dangan ) {
                    $status = 8; // 钱未付，
                }else { 
                    $status = 6; // 自定义档案未填好 
                }
                
            }
            
        }
        
        $user = \BBExtend\model\User::find( $this->uid );
        $user_pic = $user->get_userpic();
        
        $race = \BBExtend\backmodel\Race::find( $this->zong_ds_id );
        $race_title = $race->title;
        
        $field = \BBExtend\backmodel\RaceField::find( $this->ds_id );
        $field_title = !empty($field->title) ? $field->title : '';
        
        //$age=0;
        $age=date("Y") - substr( $this->birthday,0,4 );
        
        
        return [
                'id' =>$this->id,
                'sort' =>$this->sort,
                'create_time' =>$this->create_time, //报名时间
                'uid'=>$this->uid,
                'money'=>$this->money,//缴纳的费用
                'phone'=>$this->phone, // 手机
                'sex'=>$this->sex,
                'birthday'=>strtotime($this->birthday),
                'age'  => $age,
                'name'=>$this->name,
                'height'=>$this->height,
                'weight'=>$this->weight,
                'status' => $status,
                'pic'   => $this->pic,
                'user' => [
                        'user_pic' => $user_pic,
                        'role'     => $user->role,
                ],
                'race' =>[
                        'title' =>$race_title,
                        'id'    => $this->zong_ds_id,
                ],
                'field' =>[
                        'title' =>$field_title,
                        'id'    => $this->ds_id ,
                ]
                
        ];
    }
    
//     public function moneys()
//     {
//         // 重要说明：user_id是Money模型里的，id是User模型里的。
//         return $this->hasMany('app\model\Money', 'user_id', 'id');
//     }
}
