<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_toppic
add android_type tinyint not null default 0 comment '0普通，1安卓设备另外跳转到浏览器'
html;
Db::query($sql);





echo "创建<br>\n";
