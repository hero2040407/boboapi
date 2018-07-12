<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_card_template
add min_count int NOT NULL DEFAULT 1 COMMENT '最小照片张数'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_card_template
add max_count int NOT NULL DEFAULT 15 COMMENT '最大照片张数'
html;
Db::query($sql);













echo "创建<br>\n";

