<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users 
add role tinyint NOT NULL DEFAULT 1 COMMENT '1普通用户，2导师，3vip童星，4机构'
html;
Db::query($sql);














echo "创建<br>\n";

