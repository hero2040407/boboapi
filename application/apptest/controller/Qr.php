<?php
namespace app\apptest\controller;


use BBExtend\model\Qr as Phone_list;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\PhoneExport;

class Qr  
{
   
    
    public function test($id=279){
    
        $one = Phone_list::find($id);
        $content = $one->content;
        $content = trim($content);
        $content_arr = json_decode($content,1);
        \think\Config::set('default_return_type','json');
        return $content_arr;
    }
    
    
    /**
     * 20171215 maxid = 764
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     */
    public function index(){
        ini_set('memory_limit','50M');
        $db = Sys::get_container_db_eloquent();
         
        $max_list_id=0;
        $sql="select max(list_id) from boc_phone_export ";
        $max_list_id= DbSelect::fetchOne($db, $sql);
        
        //$db = Sys::get_container_db();
        $sql="select id from boc_phone_list where id >={$max_list_id} order by id asc  ";
        $ids = DbSelect::fetchCol($db, $sql);
        $sort=0;
        $fail_count=0;
        $success_count=0;
        foreach ($ids as $v) {
            $sort++;
            echo "================= {$sort}  ==================\n";
            ob_flush();
            flush();
            
            $one = Phone_list::find($v);
            $content = $one->content;
            $content = trim($content);
             
            $content_arr = json_decode($content,1);
            if ($content===null) {
                $fail_count++;
                echo  $v. " : null\n";
            }elseif ($content===false) {
                echo 'false';
            }else {
                 
                if (is_array( $content_arr )) {
                     
                    $success_count++;
                    $this->save($content_arr,$v);
                   // echo $v." : success\n";
                }else {
                    $fail_count++;
                    echo  $v. " : null\n";
                }
            }
        }
    
    }
    
    private function save($arr, $list_id)
    {
        
        foreach ($arr as $key=> $v){
            echo "---{$key}---"."\n";
            ob_flush();
            flush();
            
            $display_name = trim( $v['displayName']);
            if (!$display_name) {
                echo "null\n";
                continue;
            }
            // 取电话，注意是数组
            $info='';
            if (isset ($v['emails']) &&  $v['emails']  ) {
               // echo var_export($v['emails'],1);
                if (is_array( $v['emails'] )) {
                  $info .= 'email:'.$v['emails'][0]['value'].', ';
                }else {
                    $info .= 'email:'. strval( $v['emails']).', ';
                }
            }
            if (isset ($v['addresses']) &&  $v['addresses']  ) {
                if (is_array( $v['addresses'] )) {
                    if (isset( $v['addresses'][0]['value'] )){
                        $info .= 'address:'.$v['addresses'][0]['value'].', ';
                    }elseif (isset( $v['addresses'][0]['formatted'] )){
                        $info .= 'address:'.$v['addresses'][0]['formatted'].', ';
                    }elseif (isset( $v['addresses'][0]['streetAddress'] )){
                        $info .= 'address:'.$v['addresses'][0]['streetAddress'].', ';
                    }
                    
                    
                }else {
                    $info .= 'address:'.strval($v['addresses']).', ';
                }
                
            }
            if (isset ($v['ims']) &&  $v['ims']  ) {
                if (is_array( $v['ims'] )) {
                    $info .= 'ims:'.$v['ims'][0]['value'].', ';
                }else {
                    $info .= 'ims:'.strval($v['ims']).', ';
                }
            }
            if (isset ($v['organizations']) &&  $v['organizations']  ) {
                
                if (is_array( $v['organizations'] )) {
                  //  echo var_export($v['organizations'],1);
                    $info .= 'organizations:'.$v['organizations'][0]['name'].', ';
                }else {
                    $info .= 'organizations:'. strval( $v['organizations']).', ';
                }
            }
            if (isset ($v['birthday']) &&  $v['birthday']  ) {
                $info .= $v['birthday'].', ';
            }
            if (isset ($v['note']) &&  $v['note']  ) {
                $info .= $v['note'].', ';
            }
//             if (isset ($v['photos']) &&  $v['photos']  ) {
//                 $info .= $v['photos'].', ';
//             }
            if (isset ($v['categories']) &&  $v['categories']  ) {
                if (is_array( $v['categories'] )) {
                    $info .= 'categories:'.$v['categories'][0]['value'].', ';
                }else {
                    $info .= 'categories:'.strval($v['categories']).', ';
                }
                //$info .= $v['categories'].', ';
            }
            if (isset ($v['urls']) &&  $v['urls']  ) {
                if (is_array( $v['urls'] )) {
                    $info .= 'urls:'.$v['urls'][0]['value'].', ';
                }else {
                    $info .= 'urls:'.strval($v['urls']).', ';
                }
            }
            $arr_phone = $v['phoneNumbers'];
            if (is_array($arr_phone )) {
                foreach ( $arr_phone as $phones ) {
                    $obj = new PhoneExport();
                    $obj->display_name= $display_name;
                    $obj->list_id = $list_id;
                    $obj->sort_id = $key;
                    $obj->phone = trim( $phones['value']);
                    $obj->create_time = time();
                    $obj->info = $info;
                    try{
                        $obj->save();
                    }catch( \Exception $d  ){
                        echo "repeat..\n";
                        
                    }
                    
                }
                
            }
            
            echo $display_name."\n";
        }
    
    }
    
   
}