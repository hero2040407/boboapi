<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter TABLE bb_task_activity
add has_paiming tinyint not null default 0 comment '1已排名过，0未排名，'
html;
Db::query($sql);













echo "创建<br>\n";

