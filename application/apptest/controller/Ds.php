<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\common\Image;

class Ds
{
   
    public function delete($uid= 8000002){
        if (Sys::is_product_server()) {
            exit ('');
        }
        
        
        $db = Sys::get_container_db();
     //   $uid = 8000002;
        $sql="delete from bb_pic where uid= ?";
        $db->query($sql,[$uid]);
        $sql="delete from ds_register_log where uid= ?";
        $db->query($sql,[$uid]);
        return ['code'=>1];
//         $sql="delete from ds_register_log where uid= ?";
//         $db->query($sql,[$uid]);
        
        
        
        
    }
    
    
    
    
   /**
    * 这是demo页面，同时包括了get和处理post的逻辑
    * 
    * 1复选框，2文本框，3上传，4简介
    */
   public function html()
   {
       Sys::display_all_error();
       //echo  $_SERVER['DOCUMENT_ROOT'];
       
       echo "<form method=post  enctype='multipart/form-data'   >\n";
       
       $db = Sys::get_container_db();
       $ds_id =1;
       $uid=10;
       
       echo "<input type=hidden name=ds_id value={$ds_id} />";
       echo "<input type=hidden name=uid value={$uid} />";
       
       $sql="select * from ds_dangan_config where ds_id={$ds_id} and type=1 ";
       $result = $db->fetchAll($sql);
       foreach ($result as $v) {
          $s ="<label><input name='type1_{$v['id']}' type=checkbox value='1' />{$v['title']}</label>\n";
           echo $s;
       }
       echo "<br>";
       $sql="select * from ds_dangan_config where ds_id={$ds_id} and type=2 ";
       $result = $db->fetchAll($sql);
       foreach ($result as $v) {
           $s ="<label>{$v['title']}:<input name='type2_{$v['id']}' type=text value='' /></label><br>\n";
           echo $s;
       }
       echo "<br>";
       
       $sql="select * from ds_dangan_config where ds_id={$ds_id} and type=3 ";
       $result = $db->fetchAll($sql);
       foreach ($result as $v) {
           $s ="<label>{$v['title']}:<input name='type3_{$v['id']}' type=file /></label><br>\n";
           echo $s;
       }
       
       $sql="select * from ds_dangan_config where ds_id={$ds_id} and type=4 ";
       $result = $db->fetchAll($sql);
       foreach ($result as $v) {
           $s ="<label>{$v['title']}:<textarea  name='type4_{$v['id']}'  rows=3 cols=20></textarea></label><br>\n";
           echo $s;
       }
       echo "<br><input type=submit />";
       echo "<br><a href='/apptest/ds/html'  >刷新页面</a>";
        
       echo "</form>\n";
       
       $request = \think\Request::instance();
       $method= $request->method();
       if ($method=='POST') {
           dump( $_POST);
           dump($_FILES);
           $this->process_post();
           $sql ="select * from ds_dangan where uid={$uid} and ds_id={$ds_id} order by type";
           $result = $db->fetchAll($sql);
           dump($result);
       }
       
   }
   
   /**
    * 这是核心代码
    * 处理上传
    */
   public function process_post()
   {
       $db = Sys::get_container_db();
       // 这里是添加档案到个人的。
       $uid = intval( $_POST['uid']);
       $ds_id = intval($_POST['ds_id']);
       $sql ="delete from ds_dangan where uid={$uid} and ds_id={$ds_id}";
       $db->query($sql);
       

       $keys = array_keys($_POST);
       foreach ($keys as $v) {
           
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
           }
           
       }
       //最后处理上传。
       $keys = array_keys($_FILES);
       foreach ($keys as $v) {
           if (preg_match('#^type3_#', $v) && $_FILES[$v]['name'] ) {
               //进入这里表示：用户选择多行文本框。
               $help = new Image();
               $result = $help->upload_codeguy($v, 'ds');
               if ($result) {
                   $db->insert('ds_dangan', [
                       'ds_id' => $ds_id,
                       'uid' => $uid,
                       'config_id' => intval( preg_replace('#^type3_(.+)$#', '$1', $v)) ,
                       'value' => $help->webname ,
                       'type' =>3,
                       'create_time' => time(),
                   ]);
               }else {
//                    echo $help->msg;
                   return ['code'=>0, 'message' => $help->msg ];
               }
           }
       }
       return ['code'=>1, ];
       
   }
   
   /**
    * 这是生成验证码的图片的接口
    */
   public function captcha()
   {
       include 'Captcha/autoload.php';
       $temp = mt_rand(1000,9999);//这是校验码
       $_SESSION['ds_captcha']  = $temp;
       $builder = new \Gregwar\Captcha\CaptchaBuilder(strval($temp) );
       $builder->setDistortion(false);//禁止扭曲。
       $builder->build();
       echo  "<img src=". $builder->inline().  " />";
   }
   
   //1复选框，2文本框，3上传，4简介
   /**
    * 这是添加测试代码用，请勿在浏览器打开访问。
    */
   public function createdata()
   {
       exit;
       Sys::display_all_error();
       echo 4;
       $name = Sys::get_machine_name();
       if ($name == 'product' ) {
           exit;
       }
       echo 55;
       $db = Sys::get_container_db();
       $sql="delete from ds_race";
       $db->query($sql);
       $db->insert('ds_race', [
           'id' =>1,
           'title' => '测试大赛1',
           'area' =>'area1',
           'has_dangan' =>1,
            
       ]);
       $sql ="delete from ds_dangan_config";
       $db->query($sql);
       $db->insert('ds_dangan_config', [
           'id'    => 101,
           'ds_id' =>1,
           'title' => '复选1',
           'type' => '1',
       ]);
       $db->insert('ds_dangan_config', [
           'id'    => 102,
           'ds_id' =>1,
           'title' => '复选2',
           'type' => '1',
       ]);
        
       $db->insert('ds_dangan_config', [
           'id'    => 201,
           'ds_id' =>1,
           'title' => '文本框1',
           'type' => '2',
       ]);
       $db->insert('ds_dangan_config', [
           'id'    => 202,
           'ds_id' =>1,
           'title' => '文本框2',
           'type' => '2',
       ]);
        
        
       $db->insert('ds_dangan_config', [
           'id'    => 301,
           'ds_id' =>1,
           'title' => '上传框1',
           'type' => '3',
       ]);
       $db->insert('ds_dangan_config', [
           'id'    => 302,
           'ds_id' =>1,
           'title' => '上传框2',
           'type' => '3',
       ]);
        
       $db->insert('ds_dangan_config', [
           'id'    => 401,
           'ds_id' =>1,
           'title' => '大框1',
           'type' => '4',
       ]);
       $db->insert('ds_dangan_config', [
           'id'    => 402,
           'ds_id' =>1,
           'title' => '大框2',
           'type' => '4',
       ]);
        
        
   }
    
   
}
