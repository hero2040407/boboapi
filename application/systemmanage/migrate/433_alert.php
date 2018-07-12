<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_resource
add  position  int not null default 1 comment '1上方，2中间，3下方。此字段只适用于type=3的新版动图'
html;
Db::query($sql);







echo "创建<br>\n";

