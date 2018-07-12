<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_record
add like_count  int not null default 0 comment '赞数，包括机器人点赞。该字段主要用于大赛排名，原先的排名不使用了。'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_record
add index like_count(like_count)
html;
Db::query($sql);









echo "创建<br>\n";

