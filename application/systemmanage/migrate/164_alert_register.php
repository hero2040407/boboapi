<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_register_log 
add has_pay tinyint not null default 0 comment '1付过钱或大赛无需付钱，0未付钱' 
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add has_dangan tinyint not null default 0 comment '1填过档案或大赛无需填档案，0未填档案'
html;
Db::query($sql);





echo "创建<br>\n";
