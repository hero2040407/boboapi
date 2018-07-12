<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_brandshop
add html_info varchar(3000) not null default '' comment '机构h5详情' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_brandshop
add is_free tinyint not null default 0 comment '1开始免费预约试听，0不开启' 
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_brandshop
add rongyu varchar(1024) not null default '' comment '荣誉，竖线分割'
html;
Db::query($sql);




















echo "创建<br>\n";

