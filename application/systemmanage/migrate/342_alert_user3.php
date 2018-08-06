<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
use BBExtend\Sys;


$db = Sys::get_container_db_eloquent();
$db::table('bb_config_str')->insert([
        'val'=>'[{"start":"09:00:00","end":"09:30:00"},{"start":"18:00:00","end":"18:30:00"}]',
        'config' => 'tianjiang_active',
        'type' =>11,
]);
$db::table('bb_config_str')->insert([
        'val'=>'{"0":500,"1":300,"2":200,"3":100,"5":50,"8":30,"10":20,"20":10,"30":5,"-1":50,"x2":2,"x3":1}',
        'config' => 'tianjiang_ratio',
        'type' =>11,
]);
$db::table('bb_config_str')->insert([
        'val'=>200,
        'config' => 'tianjiang_count',
        'type' =>11,
]);










echo "创建<br>\n";
