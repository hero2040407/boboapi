<?php

/**
 * 修改bb_record表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_record
add price_type tinyint not null default 1  comment '1免费课程，2付费课程，3vip课程'
html;
Db::query($sql);


$sql=<<<html
alter table bb_rewind
add price_type tinyint not null default 1  comment '1免费课程，2付费课程，3vip课程'
html;
Db::query($sql);



echo "修改bb_record表<br>\n";

