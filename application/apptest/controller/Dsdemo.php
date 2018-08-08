<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\common\Image;
use BBExtend\common\HtmlTable;

class Dsdemo
{
   
   /**
    * 这是demo页面，同时包括了get和处理post的逻辑
    * 
    * 1复选框，2文本框，3上传，4简介
    */
   public function index()
   {
       $db = Sys::get_container_db();
       echo "<h2>demo</h2>";
       
       $ds_id = 9;
       
       
       $sql ="select zong_ds_id, uid,name,birthday from ds_register_log where zong_ds_id = {$ds_id}";
       $result = $db->fetchAll($sql);
       //这里查出所有人的档案。
       //$sql = "select * from ds_register_log where zong_ds_id = {$ds_id}";
       
       
       $title_bigin = ['大赛id', '用户id','真实名','生日',];
       $sql = "select * from ds_dangan_config where ds_id = {$ds_id}";
       
       
       $fu_result = $db->fetchAll($sql); // 
       //先找出复选框  type=1
       $fu =[];
       foreach ($fu_result  as $v) {
           if ($v['type'] == 1 ) {
               $fu[]= $v['info'];
           }
       }
       $fu = array_unique($fu);//去除重复，得到真正的标题。
       if ($fu) {
       
           $title_bigin = array_merge($title_bigin, $fu);
           foreach ($result as $k => $v) {//每个$v是一个人。
               foreach ($fu  as $k2=>$v2) {// 对于每个人的某个复选项，例如擅长才艺，可能有多个的答案。type=1
                   $sql ="select ds_dangan_config.title from ds_dangan 
                            left join ds_dangan_config
                              on ds_dangan_config.id = ds_dangan.config_id
                            where ds_dangan.ds_id ={$ds_id} and ds_dangan.uid = {$v['uid']} and ds_dangan.type=1 
                              and ds_dangan_config.info = '{$v2}'
                   ";
                   $temp = $db->fetchCol($sql);
                   $result[$k][]= implode(',', $temp);
                  
               }
           }
           
       }
       
       //dump($fu);       
       
       //现在查大赛的档案。1复选框，2文本框，3上传，4简介
       
       
//        dump($result);
//        exit;
       
       
       $obj = new HtmlTable(
              $title_bigin,$result
               );
       
       echo $obj->to_html();
      
       
             
   }
    
   
}
