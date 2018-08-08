<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_users_platform
add  create_time int(11) NOT NULL DEFAULT 0 COMMENT '创建时间'
html;
Db::query($sql);





echo "创建<br>\n";
