<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_offline_register_log
add finish_time int not null default 0 comment '最后通过时间'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_offline_register_log
add is_finish tinyint not null default 0 comment '1最终晋级，'
html;
Db::query($sql);







echo "创建<br>\n";

