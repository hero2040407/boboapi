<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_brandshop
add html_rongyu varchar(3000) not null default '' comment '荣誉，图文混排' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_brandshop
add html_kecheng varchar(3000) not null default '' comment '课程设置，图文混排'
html;
Db::query($sql);



















echo "创建<br>\n";

