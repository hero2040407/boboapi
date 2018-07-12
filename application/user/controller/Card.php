<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;

//use think\Request;
//use think\Db;
use BBExtend\DbSelect;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\common\PicPrefixUrl;

class Card
{
    /**
     * 模板列表
     * 
     */
    
    const free_count = 1;
    
    public function template()
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select * from  bb_users_card_template where is_show=1 order by id asc";
        $t_arr = DbSelect::fetchAll($db, $sql);
        
        $sql="
        select bb_users_card_template_material.*
        from bb_users_card_template_material
        left join bb_users_card_template
        on bb_users_card_template.id = bb_users_card_template_material.template_id
        where bb_users_card_template.is_show=1
        order by bb_users_card_template.id asc
";
        $pic_arr = DbSelect::fetchAll($db, $sql);
        $new=[];
        foreach ($t_arr as $v) {
            $temp=[];
            $temp['id'] = $v['id'];
            $temp['title'] = $v['title'];
            $temp2=[];
            $temp3=[];
            foreach ( $pic_arr as $v2 ) {
                if ( $v2['template_id'] == $v['id']) {
                    $temp2[] = $v2['pic'];
                    
                    $temp4=[
                            'pic_width' => $v2['pic_width'],
                            'pic_height' => $v2['pic_height'],
                            'pic' =>$v2['pic'],
                    ];
                    $temp3[]= $temp4;
                }
            }
            $temp['pic_arr'] = $temp2;
            $temp['pic_width_height_arr'] = $temp3;
            
            
            $new[]= $temp;
        }
        
        return ['code'=>1,'data'=>['list' =>$new] ];
        
    }
    
    
    public function index($uid,$startid=0,$length=100){
        $db = Sys::get_container_db_eloquent();
        $startid=intval($startid);
        $length=intval($length);
        
        $uid=intval($uid);
        $sql="select pic,status,pic_width,pic_height from  bb_users_card where uid={$uid} and status=3 order by id asc
  limit {$startid},{$length}
";
       $result =  DbSelect::fetchAll($db, $sql);
       return ['code'=>1,'data'=>['list' =>$result] ];
    }
    
    /**
     * 如果返回结果大于0，则免费。
     * 否则付费制作。
     */
    private function is_valid($uid)
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select count(*) from  bb_users_card where uid={$uid} and (status=3 or status=2) ";
        $count = DbSelect::fetchOne($db, $sql);
        $valid = self::free_count - $count;
        return $valid;
    }
    
    /**
     * 查询状态，查询文字
     * 
     */
    public function query($uid)
    {
       // 假设只有一次免费机会 
       $uid = intval($uid);
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0, 'message'=>'uid error' ];
        }
        if ($user->role != 3) {
            return ['code'=>0, 'message'=>'role error' ];
        }
        $valid = $this->is_valid($uid);
        
        if ($valid >0 ) {
            $word="您还有{$valid}次免费机会";
            $need_pay = 0;
        }else {
            $word ="500 BO币";
            $need_pay=1;
        }
        
        if ($need_pay) {
            $money = 500; 
        }else {
            $money = 0;
        }
        
        return [
                'code'=>1,
                'data'=>[
                        'need_pay' =>$need_pay,
                        'word' => $word,
                        'money' =>$money,
                        'aliyun_upload_dir'=> 'uploads/card_date/'.date("Ymd"),
                ]
        ];
        
    }
    
    // 必须post
    public function order($uid, $token, $template_id, $pic_arr){
        Sys::display_all_error();
        
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_users_card_template where is_show=1 and id=?";
        $template = DbSelect::fetchRow($db, $sql,[ $template_id ]);
        if (!$template) {
            return ['code'=>0,'message'=>'id error'];
        }
        $min_count = $template['min_count'];
        $max_count = $template['max_count'];
        
        $pic_arr = (array)$pic_arr;
        if (empty($pic_arr ) ) {
            return ['code'=>0,'message'=>'至少传'. $min_count .'张图片'];
        }
        
        $count = count($pic_arr); 
        if ($count < $min_count || $count > $max_count) {
            return ['code'=>0,'message'=>'至少传'. $min_count .'张图片，'.'至多传'.$max_count .'张图片' ];
        }
        
        //这里，检查用户钱够不够500波币。
        $valid = $this->is_valid($uid);
        $money=0;
        if ($valid <=0) {
             $result =  \BBExtend\Currency::add_bobi($uid,-500,'模卡制作');
             $money=500;
             if ($result ===false ) {
                 return ['code'=>0,'message'=>'您的 BO币不足 500' ];
             }
        }
        // 首先
        $id =  $db::table('bb_users_card')->insertGetId([
                'uid' =>$uid,
                'status' =>2,
                'create_time' => APP_TIME,
                'money' =>$money,
                'template_id'=>$template_id,
                ]);
        
        foreach ($pic_arr as $v) {
            $db::table('bb_users_card_material')->insert([
                    'order_id' => $id,
                    'create_time' => APP_TIME,
                    'pic' => $v,
                    'uid' => $uid,
            ]);
        }
        
        
        $valid = $this->is_valid($uid);
        
        if ($valid >0 ) {
            $word="您还有{$valid}次免费机会";
            $need_pay = 0;
        }else {
            $word ="500 BO币";
            $need_pay=1;
        }
        
        if ($need_pay) {
            $money = 500;
        }else {
            $money = 0;
        }
        
        
        $sql="select gold from bb_currency where uid=?";
        $current_money = DbSelect::fetchOne($db, $sql,[ $uid ]);
        
        return [
                'code'=>1,
                'data'=>[
                        'need_pay' =>$need_pay,
                        'word' => $word,
                        'money' =>$money,
                        'current_money'=> $current_money,
                        'aliyun_upload_dir'=> 'uploads/card_date/'.date("Ymd"),
                ]
        ];
        
        
        
        
        
        
       // return ['code'=>1,'message' =>'图片上传成功，请等待图片制作完成' ];
    }
   
    
}

