<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_record
add  theme_id  int not null default 0 comment '话题id'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record
add  theme_title  varchar(255) not null default '' comment '话题内容'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_record
add index theme_id(theme_id)
html;
Db::query($sql);



echo "创建<br>\n";

