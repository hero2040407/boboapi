<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_register_log 
add sex tinyint not null default 1 comment '1男 ，0女' 
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add birthday varchar(255) not null default '' comment '生日类似 2017-01'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add name varchar(255) not null default '' comment '真实姓名'
html;
Db::query($sql);




echo "创建<br>\n";
