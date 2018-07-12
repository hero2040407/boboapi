<?php

namespace app\api\controller;

use think\Config;
use BBExtend\Sys;

/**
 * 安卓客户端版本控制器
 * @author xieye
 *
 */
class Version 
{
    /**
     * 返回最新的安卓版本
     */
    public function android_new() 
    {
        $db = Sys::get_container_db();
        $sql = 'select version_name,
                 version_code, is_qiangzhi, url, update_content,size
                from  bb_version_android 
                order by version_code desc
                limit 1
                ';
        $row = $db->fetchRow ( $sql );
        if (! $row) {
            return [ 
                'code' => 0,
                'data' => [ ] 
            ];
        }
        // 2017 05 新补丁， 3.1.0返回固定的3.1.2
        $current_version = Config::get ( "http_head_version" );
        if ($current_version == '3.1.0' || (! $current_version)) {
            $sql = "select version_name, version_code, is_qiangzhi, url, update_content,size
                      from  bb_version_android
                     where version_name='3.1.2'
                    ";
            $row = $db->fetchRow ( $sql );
        }
      
        if (! $row) {
            return [ 
                'code' => 0,
                'data' => [ ] ,
            ];
        }
        return [ 
            'code' => 1,
            'data' => $this->format ( $row ) 
        ];
    }
    
    
    /**
     * 返回最新的50个安卓版本，方便安卓开发人员通过浏览器查看，倒序排
     */
    public function android_list() 
    {
        $db = Sys::get_container_db();
        $sql = 'select version_name,
                 version_code, is_qiangzhi, url, update_content
                from  bb_version_android
                order by version_code desc
                limit 50
                ';
        $row = $db->fetchAll ( $sql );
        if (! $row) {
            return [ 
                'code' => 0,
                'data' => [ ] 
            ];
        }
        $result = [ ];
        foreach ( $row as $v ) {
            $result [] = $this->format ( $v );
        }
        return [ 
            'code' => 1,
            'data' => $result 
        ];
    }
    
    
    /**
     * 按客户端要求的根式返回
     * 只处理单条记录
     * 
     * @param unknown $arr            
     */
    private function format($row) 
    {
        if ($row) {
            $arr = [ 
                'versionName' => $row ['version_name'],
                'versionCode' => intval ( $row ['version_code'] ),
                'isQiangzhi' => intval ( $row ['is_qiangzhi'] ),
                'url' => $row ['url'],
                'updateContent' => $row ['update_content'],
                'size' => intval ( $row ['size'] ) 
            ];
            return $arr;
        }
        return [ ];
    }
}
