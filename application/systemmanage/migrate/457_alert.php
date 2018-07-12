<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_theme
add is_valid tinyint not null default 0 comment '1有效'
html;
Db::query($sql);









echo "创建<br>\n";

