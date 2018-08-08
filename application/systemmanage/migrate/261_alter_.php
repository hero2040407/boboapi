<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_msg
add col1 int not null default 0 comment '附加字段1'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_msg
add col2 int not null default 0 comment '附加字段2'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_msg
add  index col1(col1)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_msg
add  index col2(col2)
html;
Db::query($sql);










echo "创建<br>\n";
