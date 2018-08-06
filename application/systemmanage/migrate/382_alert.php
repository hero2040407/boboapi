<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_task_activity
add red_viewpoint varchar(1000)  not null default '' comment '红方观点，仅pk用'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_task_activity
add blue_viewpoint varchar(1000)  not null default '' comment '蓝方观点，仅pk用'
html;
Db::query($sql);








echo "创建<br>\n";

