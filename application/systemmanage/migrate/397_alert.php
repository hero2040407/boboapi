<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_info
add parent_phone varchar(255) NOT NULL DEFAULT '' COMMENT '父母手机号'
html;
Db::query($sql);













echo "创建<br>\n";

