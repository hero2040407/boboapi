<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_race_field
add title varchar(255) not null default '' comment '赛区名'
html;
Db::query($sql);









echo "创建<br>\n";

