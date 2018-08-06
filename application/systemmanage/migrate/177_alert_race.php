<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add has_pic tinyint not null default 0 comment '是否加个人照片'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add pic varchar(255) not null default '' comment '个人照片'
html;
Db::query($sql);



echo "创建<br>\n";
