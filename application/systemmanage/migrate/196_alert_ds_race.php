<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add min_age int not null default 0 comment '最小年龄，0表示不限'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add max_age int not null default 0 comment '最大年龄，0表示不限'
html;
Db::query($sql);




echo "创建<br>\n";
