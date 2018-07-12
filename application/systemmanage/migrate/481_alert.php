<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_audition_card_type
add bigtype tinyint not null default 1 comment '1通用类型，2专用类型'
html;
Db::query($sql);











echo "创建<br>\n";

