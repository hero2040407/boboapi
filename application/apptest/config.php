<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
   //  
    //11
        'session'                => [
                'id'             => '',
                // SESSION_ID的提交变量,解决flash上传跨域
                'var_session_id' => '',
                // SESSION 前缀
                'prefix'         => 'think_houtai',
                // 驱动方式 支持redis memcache memcached
                'type'           => '',
                // 是否自动开启 SESSION
                'auto_start'     => true,
                'use_cookies'=>true,
                'path' =>RUNTIME_PATH."think_houtai/",
                
        ],
        
    
        
];
