<?php

/**
 * 修改bb_push表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_push
add price_type tinyint(4) NOT NULL DEFAULT '1' COMMENT '1免费课程，2付费课程，3vip课程'
html;
Db::query($sql);


echo "修改bb_push表<br>\n";
