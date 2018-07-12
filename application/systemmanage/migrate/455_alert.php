<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_offline_register_log
add signin_time int not null default 0 comment '签到时间'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_offline_register_log
add  index signin_time(signin_time)
html;
Db::query($sql);







echo "创建<br>\n";

