<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_card
add template_id int not null default 0 comment '用户选择的模板id' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_card
add index template_id(template_id)
html;
Db::query($sql);




















echo "创建<br>\n";

