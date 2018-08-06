<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_msg
add sort int not null default 0 comment '排序字段'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_msg
add index sort (sort)
html;
Db::query($sql);











echo "创建<br>\n";
