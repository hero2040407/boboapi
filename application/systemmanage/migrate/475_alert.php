<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter TABLE bb_user_activity
add record_id int not null default 0 comment '短视频id'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_user_activity
add index record_id (record_id)
html;
Db::query($sql);














echo "创建<br>\n";

