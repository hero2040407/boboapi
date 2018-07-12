<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_group
add  group_or_person  tinyint not null default 1 comment '1qq群号，2qq个人号' 
html;
Db::query($sql);





















echo "创建<br>\n";

