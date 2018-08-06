<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_register_log 
add area1_name  varchar(255) not null default '' comment '省名称'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add area2_name  varchar(255) not null default '' comment '市名称'
html;
Db::query($sql);






echo "创建<br>\n";
