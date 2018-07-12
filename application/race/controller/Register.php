<?php
namespace app\race\controller;
// use BBExtend\BBShop;
use think\Db;
use think\Controller;
use BBExtend\common\Str;
use BBExtend\Sys;
use BBExtend\common\User;
use BBExtend\BBUser;
use think\Session;
/**
 * 
 * 大赛
 * 
 * 2017 03
 * User: xieye
 */
class Register extends Controller
{
    
    /**
     * 这是生成验证码的图片的接口
     */
    public function captcha()
    {
        include 'Captcha/autoload.php';
        ob_end_clean();
        header('Content-type: image/jpeg');
        $temp = mt_rand(1000,9999);//这是校验码
//         $_SESSION['ds_captcha']  = $temp;
        Session::set('ds_captcha',$temp);
        $builder = new \Gregwar\Captcha\CaptchaBuilder(strval($temp) );
        $builder->setDistortion(false);//禁止扭曲。
        $builder->build();
        $builder->output();
    }
    
   
    
    public function get_dangan_config($ds_id)
    {
        $ds_id = intval($ds_id);
       $db = Sys::get_container_db();
       $sql ="select * from ds_dangan_config where ds_id = {$ds_id} 
               order by type asc, sort desc ";
       $dangan_config = $db->fetchAll($sql);
       return ['code'=>1, 'data' => $dangan_config ];
    }
    
    /**
     * 该接口使用post，不接受get！！
     * 
     */
    public function dangan()
    {
        $db = Sys::get_container_db();
        // 这里是添加档案到个人的。
        $uid = intval( $_POST['uid']);
        $ds_id = intval($_POST['ds_id']);
        
        $sql = "select * from ds_race where id = {$ds_id} and is_active=1";
        $result = $ds_row = $db->fetchRow($sql);
        if (!$result) {
    //        Sys::debugxieye($sql);
            return ['code'=>0,'message' => '大赛参数错误' ];
        }
        //如果传了uid，则必须存在，否则错误！
        if ($uid) {
            if (!User::exists($uid)) {
                return ['code'=>0,'message' => 'uid错误' ];
            }
        }
        
        
        $sql ="delete from ds_dangan where uid={$uid} and ds_id={$ds_id}";
        $db->query($sql);
         
    
        $keys = array_keys($_POST);
        $has_submit=0;
        foreach ($keys as $v) {
          //  Sys::debugxieye($v);
            
            if (preg_match('#^type1_#', $v) && $_POST[$v] ==1 ) {
                //进入这里表示：用户选择复选框。
                $db->insert('ds_dangan', [
                    'ds_id' => $ds_id,
                    'uid' => $uid,
                    'config_id' => intval( preg_replace('#^type1_(.+)$#', '$1', $v)) ,
                    'value' =>1,
                    'type' =>1,
                    'create_time' => time(),
                ]);
                $has_submit=1;
            }
             
            
            if (preg_match('#^type5_#', $v) && $_POST[$v] ==1 ) {
                //进入这里表示：用户选择下拉框。
                $db->insert('ds_dangan', [
                    'ds_id' => $ds_id,
                    'uid' => $uid,
                    'config_id' => intval( preg_replace('#^type5_(.+)$#', '$1', $v)) ,
                    'value' =>1,
                    'type' =>5,
                    'create_time' => time(),
                ]);
                $has_submit=1;
            }
             
            
            if (preg_match('#^type2_#', $v)) {
                //进入这里表示：用户选择文本框。
                $db->insert('ds_dangan', [
                    'ds_id' => $ds_id,
                    'uid' => $uid,
                    'config_id' => intval( preg_replace('#^type2_(.+)$#', '$1', $v)) ,
                    'value' =>trim($_POST[$v]),
                    'type' =>2,
                    'create_time' => time(),
                ]);
                $has_submit=1;
            }
             
            if (preg_match('#^type3_#', $v)) {
                //进入这里表示：用户文件上传。
                $db->insert('ds_dangan', [
                    'ds_id' => $ds_id,
                    'uid' => $uid,
                    'config_id' => intval( preg_replace('#^type3_(.+)$#', '$1', $v)) ,
                    'value' => trim( $_POST[$v]),
                    'type' =>3,
                    'create_time' => time(),
                ]);
                $has_submit=1;
            }
            if (preg_match('#^pic$#', $v)) {
                //进入这里表示 传照片。
               // Sys::debugxieye("uid:{$uid}  ds_id:{$ds_id} pic:{$_POST['pic']}");
                $db->update('ds_register_log', [
                    'pic' => trim( $_POST[$v]),
                ], " uid={$uid} and ds_id={$ds_id}  ");
                $has_submit=1;
            }
            
            if (preg_match('#^type4_#', $v)) {
                //进入这里表示：用户选择多行文本框。
                $db->insert('ds_dangan', [
                    'ds_id' => $ds_id,
                    'uid' => $uid,
                    'config_id' => intval( preg_replace('#^type4_(.+)$#', '$1', $v)) ,
                    'value' => trim( $_POST[$v]),
                    'type' =>4,
                    'create_time' => time(),
                ]);
                $has_submit=1;
            }
             
        }
//         //最后处理上传。
//         $keys = array_keys($_FILES);
//         foreach ($keys as $v) {
//             if (preg_match('#^type3_#', $v) && $_FILES[$v]['name'] ) {
//                 //进入这里表示：用户选择多行文本框。
//                 $help = new Image();
//                 $result = $help->upload_codeguy($v, 'ds');
//                 if ($result) {
//                     $db->insert('ds_dangan', [
//                         'ds_id' => $ds_id,
//                         'uid' => $uid,
//                         'config_id' => intval( preg_replace('#^type3_(.+)$#', '$1', $v)) ,
//                         'value' => $help->webname ,
//                         'type' =>3,
//                         'create_time' => time(),
//                     ]);
//                 }else {
//                     //                    echo $help->msg;
//                     return ['code'=>0, 'message' => $help->msg ];
//                 }
//             }
//         }

        if ($has_submit) {
            $db->update("ds_register_log", ['has_dangan'=>1],  "uid={$uid} and zong_ds_id={$ds_id} ");
        }
        $code_arr = \BBExtend\video\Race::get_user_race_status($uid, $ds_id);
//         get_user_race_status_code($uid, $ds_id)
        if ($code_arr['data'] )
           return ['code'=>$code_arr['data'], 'data'=> ['money' => $ds_row['money'] ] ];
        else {
            return ['code'=>$code_arr['data'],'message'=>$code_arr['message'],   'data'=> ['money' => $ds_row['money'] ] ];
        }
         
    }
    
    
   
    
}