<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_race_field
add status tinyint not null default 0 comment '0暂停状态，1报名，2比赛，3结束'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race_field
add index status(status)
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race_field
drop column start_time
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race_field
drop column end_time
html;
Db::query($sql);







echo "创建<br>\n";

