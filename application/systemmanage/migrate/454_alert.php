<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_race_field
add round int not null default 0 comment '第几轮，默认0'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race_field
add  index round(round)
html;
Db::query($sql);







echo "创建<br>\n";

