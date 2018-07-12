<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_resource
add  pic_gray  varchar(255) not null default '' comment '资源的灰度图'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_resource_group
add  pic  varchar(255) not null default '' comment '组图标'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_resource_group
add  pic_gray  varchar(255) not null default '' comment '组图标的灰度图'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_resource_group
add  index type(type)
html;
Db::query($sql);






echo "创建<br>\n";

