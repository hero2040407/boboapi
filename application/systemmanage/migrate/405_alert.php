<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_card_template_material
add uid int NOT NULL DEFAULT 0 COMMENT '用户id'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_card_template_material
add index uid(uid)
html;
Db::query($sql);
















echo "创建<br>\n";

