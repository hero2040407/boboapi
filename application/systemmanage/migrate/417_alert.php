<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_register_log
add height int not null default 0 comment '用户身高，单位厘米。' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_register_log
add weight int not null default 0 comment '用户体重，单位公斤。'
html;
Db::query($sql);



















echo "创建<br>\n";

