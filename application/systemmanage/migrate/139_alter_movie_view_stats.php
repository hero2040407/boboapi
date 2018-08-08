<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_moive_view_stats
add label int not null default 0 comment '整型标签，即视频小分类'
html;
Db::query($sql);

$sql=<<<html
alter table bb_moive_view_stats
add index label(label)
html;
Db::query($sql);


echo "创建<br>\n";
