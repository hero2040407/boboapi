<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_card_template_material
add pic_width int not null default 0 comment '图片宽度，单位px' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_card_template_material
add pic_height int not null default 0 comment '图片高度，单位px'
html;
Db::query($sql);




















echo "创建<br>\n";

