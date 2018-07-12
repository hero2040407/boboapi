<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_resource
add  author  varchar(255) not null default '' comment '作者'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_resource
add  display_duration  varchar(255) not null default '' comment '时长，类似05:02,就是5分钟2秒的意思'
html;
Db::query($sql);





echo "创建<br>\n";

